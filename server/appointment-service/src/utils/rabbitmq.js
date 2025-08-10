// src/queue/rabbitmq.js
import amqp from 'amqplib';

let connection = null;
let channel = null;

const RABBITMQ_URL = process.env.RABBITMQ_URL || 'amqp://localhost';
const DEFAULT_QUEUE = process.env.NOTIFY_QUEUE || 'hospital_notifications';

export async function getChannel() {
  if (channel) return channel;

  connection = await amqp.connect(RABBITMQ_URL);
  channel = await connection.createChannel();
  await channel.assertQueue(DEFAULT_QUEUE, { durable: true });

  // Tự phục hồi biến nếu connection đóng/lỗi
  connection.on('close', () => {
    channel = null;
    connection = null;
    console.warn('[RabbitMQ] connection closed');
  });
  connection.on('error', (err) => {
    console.error('[RabbitMQ] connection error:', err.message);
  });

  return channel;
}

/**
 * Publish JSON payload vào queue
 * - durable queue
 * - persistent message
 */
export async function publishToQueue(payload, queue = DEFAULT_QUEUE) {
  const ch = await getChannel();
  const ok = ch.sendToQueue(
    queue,
    Buffer.from(JSON.stringify(payload)),
    { persistent: true, contentType: 'application/json' }
  );
  if (!ok) throw new Error('sendToQueue failed');
}

/** (tuỳ chọn) graceful shutdown từ server.js */
export async function closeRabbitMQ() {
  try {
    if (channel) await channel.close();
    if (connection) await connection.close();
  } catch {}
}
