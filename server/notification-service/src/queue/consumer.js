const amqp = require('amqplib');
const notificationService = require('../services/notification.service');

async function startConsumer() {
  const url = process.env.RABBITMQ_URL;
  const queue = process.env.QUEUE_NAME;
  const prefetch = Number(process.env.PREFETCH || 10);

  while (true) {
    try {
      const connection = await amqp.connect(url);
      const channel = await connection.createChannel();
      await channel.assertQueue(queue, { durable: true });
      channel.prefetch(prefetch);

      console.log('[*] Listening on queue:', queue);

      channel.consume(queue, async (msg) => {
        if (!msg) return;
        try {
          const content = JSON.parse(msg.content.toString());
          // Validate
          if (!content || !content.type || !content.event || !content.patientId || !content.message) {
            console.error('[!] Invalid message:', content);
            channel.nack(msg, false, false); // drop
            return;
          }
          await notificationService.createNotification(content);
          channel.ack(msg);
        } catch (err) {
          console.error('[!] Process error:', err.message);
          // Có thể chuyển sang DLQ; ở đây drop để tránh kẹt
          channel.nack(msg, false, false);
        }
      });

      // Keep alive
      await new Promise(() => {});
    } catch (err) {
      console.error('[!] RabbitMQ connection error. Reconnecting in 3s...', err.message);
      await new Promise((r) => setTimeout(r, 3000));
    }
  }
}

module.exports = startConsumer;
