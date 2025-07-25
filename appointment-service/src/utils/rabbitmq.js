import amqplib from 'amqplib';

export const publishToQueue = async (queue, message) => {
  const connection = await amqplib.connect('amqp://localhost');
  const channel = await connection.createChannel();
  await channel.assertQueue(queue, { durable: true });
  channel.sendToQueue(queue, Buffer.from(JSON.stringify(message)));
  setTimeout(() => {
    connection.close();
  }, 1000);
};
