import amqp from 'amqplib';

let connection = null;
let channel = null;

// Connect to RabbitMQ server
export const connectRabbitMQ = async () => {
  try {
    connection = await amqp.connect(process.env.RABBITMQ_URL || 'amqp://localhost');
    channel = await connection.createChannel();
    
    // Declare queues with durability
    await channel.assertQueue('patient_service_sync', { durable: true });
    await channel.assertQueue('notifications', { durable: true });
    await channel.assertQueue('report_sync', { durable: true });
    
    // Create dead letter exchange for failed messages
    await channel.assertExchange('dlx', 'direct', { durable: true });
    await channel.assertQueue('failed_operations', {
      durable: true,
      arguments: {
        'x-dead-letter-exchange': 'dlx',
        'x-message-ttl': 60000 * 30 // Retry after 30 minutes
      }
    });
    
    console.log('Connected to RabbitMQ');
    return { connection, channel };
  } catch (err) {
    console.error('Failed to connect to RabbitMQ:', err);
    // Fallback to database queue if RabbitMQ is down
    console.log('Using database sync queue as fallback');
    return null;
  }
};

// Replace addToSyncQueue with this function
export const queueOperation = async (queueName, data) => {
  try {
    if (!channel) {
      await connectRabbitMQ();
      if (!channel) {
        // Fallback to database if RabbitMQ is unavailable
        const { addToSyncQueue } = await import('../models/sync_queue.model.js');
        return await addToSyncQueue(data.type, data.payload);
      }
    }

    // Publish to RabbitMQ with persistence
    channel.sendToQueue(
      queueName,
      Buffer.from(JSON.stringify(data)),
      { persistent: true }
    );
    
    return true;
  } catch (err) {
    console.error(`Failed to queue operation to ${queueName}:`, err);
    // Fallback to database queue
    const { addToSyncQueue } = await import('../models/sync_queue.model.js');
    return await addToSyncQueue(data.type, data.payload);
  }
};

// Consumer setup
export const consumeQueue = async (queueName, processCallback) => {
  if (!channel) await connectRabbitMQ();
  if (!channel) return false;

  await channel.prefetch(1); // Process one message at a time
  
  channel.consume(queueName, async (msg) => {
    if (msg) {
      try {
        const data = JSON.parse(msg.content.toString());
        await processCallback(data);
        channel.ack(msg); // Acknowledge successful processing
      } catch (err) {
        console.error(`Error processing message from ${queueName}:`, err);
        
        // Reject and requeue with a delay via dead letter exchange
        channel.nack(msg, false, false);
        
        // Send to dead letter queue with delay
        const retryData = JSON.parse(msg.content.toString());
        retryData.retryCount = (retryData.retryCount || 0) + 1;
        
        if (retryData.retryCount < 5) {
          channel.sendToQueue('failed_operations', Buffer.from(JSON.stringify(retryData)), {
            persistent: true,
            headers: { 'x-original-queue': queueName }
          });
        }
      }
    }
  });
  
  console.log(`Consuming messages from ${queueName}`);
  return true;
};