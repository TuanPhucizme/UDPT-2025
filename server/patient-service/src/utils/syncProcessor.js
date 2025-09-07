import { getPendingSyncItems, updateSyncItem } from '../models/sync_queue.model.js';
import { serviceCall } from '../utils/serviceCall.js';
import services from '../config/services.js';

/**
 * Process a batch of sync queue items
 */
export const processSyncQueue = async () => {
  try {
    console.log('Processing sync queue...');
    const pendingItems = await getPendingSyncItems(10);
    
    if (pendingItems.length === 0) {
      console.log('No pending sync items');
      return;
    }
    
    console.log(`Found ${pendingItems.length} pending sync items`);
    
    for (const item of pendingItems) {
      try {
        console.log(`Processing sync item #${item.id} (type: ${item.type})`);
        await updateSyncItem(item.id, 'processing');
        
        const payload = JSON.parse(item.payload);
        
        // Process based on type
        switch (item.type) {
          case 'notify_patient_created':
            await processNotifyPatientCreated(payload);
            break;
            
          case 'notify_record_created':
            await processNotifyRecordCreated(payload);
            break;
            
          case 'report_patient_data':
            await processReportPatientData(payload);
            break;
            
          default:
            console.warn(`Unknown sync item type: ${item.type}`);
            await updateSyncItem(item.id, 'failed', `Unknown sync item type: ${item.type}`);
            continue;
        }
        
        // Mark as success if we got here
        await updateSyncItem(item.id, 'success');
        console.log(`Successfully processed sync item #${item.id}`);
        
      } catch (err) {
        console.error(`Error processing sync item #${item.id}:`, err);
        await updateSyncItem(item.id, 'failed', err.message);
        
        // If the item has been retried too many times, mark it as permanently failed
        if (item.retry_count >= 5) {
          console.warn(`Sync item #${item.id} has failed too many times, marking as permanently failed`);
          await updateSyncItem(item.id, 'failed', `Exceeded maximum retry count: ${err.message}`);
        }
      }
    }
    
  } catch (err) {
    console.error('Error processing sync queue:', err);
  }
};

/**
 * Process a notification for patient creation
 */
async function processNotifyPatientCreated(payload) {
  const { patientId, patientName } = payload;
  
  const notificationData = {
    type: 'patient_created',
    user_id: null,
    patient_id: patientId,
    message: `Bệnh nhân mới đã được tạo: ${patientName}`
  };
  
  await serviceCall(`${services.NOTIFICATION_SERVICE_URL}/api/notifications`, {
    method: 'POST',
    data: notificationData
  });
}

/**
 * Process a notification for medical record creation
 */
async function processNotifyRecordCreated(payload) {
  const { recordId, patientId, doctorId } = payload;
  
  const notificationData = {
    type: 'record_created',
    user_id: doctorId,
    patient_id: patientId,
    message: `Hồ sơ khám bệnh mới đã được tạo`,
    metadata: { record_id: recordId }
  };
  
  await serviceCall(`${services.NOTIFICATION_SERVICE_URL}/api/notifications`, {
    method: 'POST',
    data: notificationData
  });
}

/**
 * Process reporting patient data to the report service
 */
async function processReportPatientData(payload) {
  const { patientId } = payload;
  
  await serviceCall(`${services.REPORT_SERVICE_URL}/api/sync/patient/${patientId}`, {
    method: 'POST'
  });
}
