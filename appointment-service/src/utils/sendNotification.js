// utils/sendNotification.js
const amqp = require('amqplib');

let channel = null;

async function connectQueue() {
  const connection = await amqp.connect('amqp://localhost');
  channel = await connection.createChannel();
  await channel.assertQueue('hospital_notifications');
}

async function sendNotification(notification) {
  try {
    if (!channel) await connectQueue();
    channel.sendToQueue(
      'hospital_notifications',
      Buffer.from(JSON.stringify(notification))
    );
    console.log('[→] Gửi thông báo đến queue:', notification);
  } catch (err) {
    console.error('[X] Lỗi gửi thông báo:', err);
  }
}

module.exports = sendNotification;
