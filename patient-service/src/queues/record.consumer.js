import amqplib from 'amqplib';
import { autoCreateRecord } from '../models/record.model.js';

export const consumeMedicalRecordQueue = async () => {
  const connection = await amqplib.connect('amqp://localhost');
  const channel = await connection.createChannel();
  await channel.assertQueue('medical_record_created', { durable: true });

  channel.consume('medical_record_created', async (msg) => {
    if (msg !== null) {
      const data = JSON.parse(msg.content.toString());

      console.log('⏺️ Nhận lịch khám xác nhận:', data);

      // Tạo hồ sơ bệnh án rỗng ban đầu
      await autoCreateRecord(data);
      channel.ack(msg);
    }
  });
};
