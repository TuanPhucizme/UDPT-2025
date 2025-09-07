import { consumeQueue } from '../utils/rabbitmq.js';
import { serviceCall } from '../utils/serviceCall.js';
import services from '../config/services.js';
import { addToSyncQueue } from '../models/sync_queue.model.js';

// Process notification messages
const processNotification = async (data) => {
  try {
    if (data.type === 'notify_patient_created' || data.type === 'notify_record_created') {
      const notificationData = {
        type: data.type.replace('notify_', ''),
        user_id: data.payload.userId || null,
        patient_id: data.payload.patientId,
        message: data.payload.message || `Notification for ${data.type}`,
        metadata: data.payload.metadata || {}
      };
      
      await serviceCall(`${services.NOTIFICATION_SERVICE_URL}/api/notifications`, {
        method: 'POST',
        data: notificationData
      });
      
      console.log(`Successfully sent ${data.type} notification`);
    }
  } catch (error) {
    console.error(`Failed to process notification: ${error.message}`);
    throw error; // Rethrow to trigger requeue
  }
};

// Process report sync messages
const processReportSync = async (data) => {
  try {
    if (data.type === 'report_patient_data') {
      await serviceCall(`${services.REPORT_SERVICE_URL}/api/sync/patient/${data.payload.patientId}`, {
        method: 'POST'
      });
      
      console.log(`Successfully synced patient data to report service: ${data.payload.patientId}`);
    } else if (data.type === 'report_record_data') {
      await serviceCall(`${services.REPORT_SERVICE_URL}/api/sync/record/${data.payload.recordId}`, {
        method: 'POST'
      });
      
      console.log(`Successfully synced record data to report service: ${data.payload.recordId}`);
    }
  } catch (error) {
    console.error(`Failed to sync report data: ${error.message}`);
    throw error; // Rethrow to trigger requeue
  }
};

// Consume from database sync queue and republish to RabbitMQ
const migrateDatabaseQueue = async () => {
  const { getPendingSyncItems, updateSyncItem } = await import('../models/sync_queue.model.js');
  const pendingItems = await getPendingSyncItems(20);
  
  console.log(`Migrating ${pendingItems.length} items from database queue to RabbitMQ`);
  
  for (const item of pendingItems) {
    try {
      await updateSyncItem(item.id, 'processing');
      
      // Determine the appropriate queue based on type
      let queueName;
      if (item.type.startsWith('notify_')) {
        queueName = 'notifications';
      } else if (item.type.startsWith('report_')) {
        queueName = 'report_sync';
      } else {
        queueName = 'patient_service_sync';
      }
      
      // Publish to RabbitMQ
      const { queueOperation } = await import('../utils/rabbitmq.js');
      await queueOperation(queueName, {
        type: item.type,
        payload: JSON.parse(item.payload)
      });
      
      await updateSyncItem(item.id, 'success');
    } catch (error) {
      console.error(`Failed to migrate item ${item.id} to RabbitMQ: ${error.message}`);
      await updateSyncItem(item.id, 'failed', error.message);
    }
  }
};

// Process a failed operation that comes back from the dead letter queue
const processDLQ = async (data) => {
  try {
    // Get original queue from headers
    const originalQueue = data.headers?.['x-original-queue'] || 'notifications';
    
    // If we've exceeded retry count, log to database for manual inspection
    if (data.retryCount >= 5) {
      await addToSyncQueue(data.type, data.payload, 'max_retries_exceeded');
      console.log(`Max retries exceeded for ${data.type}, moved to database`);
      return;
    }
    
    // Republish to original queue
    const { queueOperation } = await import('../utils/rabbitmq.js');
    await queueOperation(originalQueue, data);
    
    console.log(`Requeued ${data.type} after ${data.retryCount} retries`);
  } catch (error) {
    console.error(`Error processing DLQ message: ${error.message}`);
    throw error;
  }
};

// Initialize all consumers
export const startSyncConsumers = async () => {
  try {
    // Start consuming from RabbitMQ queues
    await consumeQueue('notifications', processNotification);
    await consumeQueue('report_sync', processReportSync);
    await consumeQueue('failed_operations', processDLQ);
    
    // Migrate any existing database queue items
    await migrateDatabaseQueue();
    
    console.log('RabbitMQ sync consumers started successfully');
  } catch (error) {
    console.error('Failed to start RabbitMQ sync consumers:', error);
  }
};

export default {
  startSyncConsumers
};
