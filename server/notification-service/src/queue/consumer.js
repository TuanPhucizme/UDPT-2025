// queue/consumer.js
const amqp = require('amqplib');
const notificationService = require('../services/notification.service');

async function connectQueue() {
  const connection = await amqp.connect(process.env.RABBITMQ_URL);
  const channel = await connection.createChannel();
  await channel.assertQueue(process.env.QUEUE_NAME);

  console.log('[*] Listening on queue:', process.env.QUEUE_NAME);

  channel.consume(process.env.QUEUE_NAME, async (msg) => {
    if (msg !== null) {
      const content = JSON.parse(msg.content.toString());
      console.log('[x] Message received:', content);

      await notificationService.createNotification(content);
      channel.ack(msg);
    }
  });
}

module.exports = connectQueue;
