// test-publisher.js
const amqp = require('amqplib');

async function sendMessage() {
  const conn = await amqp.connect('amqp://localhost');
  const channel = await conn.createChannel();

  const queue = 'hospital_notifications';
  await channel.assertQueue(queue);

  const message = {
    type: 'appointment',
    patientId: 'patient123',
    message: 'Lịch khám của bạn vào 9h sáng mai đã được xác nhận.'
  };

  channel.sendToQueue(queue, Buffer.from(JSON.stringify(message)));
  console.log('[✓] Message sent to queue');

  setTimeout(() => {
    conn.close();
    process.exit(0);
  }, 500);
}

sendMessage().catch(console.error);
