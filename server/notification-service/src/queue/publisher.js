const amqp = require('amqplib');

let channel;

async function getChannel() {
  if (channel) return channel;
  const connection = await amqp.connect(process.env.RABBITMQ_URL);
  channel = await connection.createChannel();
  await channel.assertQueue(process.env.QUEUE_NAME, { durable: true });
  return channel;
}

exports.publish = async (payload) => {
  const ch = await getChannel();
  const ok = ch.sendToQueue(
    process.env.QUEUE_NAME,
    Buffer.from(JSON.stringify(payload)),
    { persistent: true, contentType: 'application/json' }
  );
  if (!ok) throw new Error('sendToQueue failed');
};
