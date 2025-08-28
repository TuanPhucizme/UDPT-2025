import amqplib from 'amqplib';
import dotenv from 'dotenv';
import { autoCreateRecord } from '../models/record.model.js';

dotenv.config();

const RABBITMQ_URL = process.env.RABBITMQ_URL;

// Hàm tiện ích để chờ một khoảng thời gian
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

export const consumeMedicalRecordQueue = async (retries = 5, delay = 5000) => {
  // Sử dụng vòng lặp để thử lại kết nối
  while (retries > 0) {
    try {
      console.log(`[RabbitMQ] Đang kết nối đến: ${RABBITMQ_URL} (còn ${retries} lần thử)...`);
      const connection = await amqplib.connect(RABBITMQ_URL);

      // Xử lý sự kiện đóng kết nối để ứng dụng có thể thoát và được Docker khởi động lại
      connection.on('close', () => {
        console.error('[RabbitMQ] Kết nối đã bị đóng!');
        process.exit(1);
      });

      console.log('[RabbitMQ] Kết nối thành công!');

      const channel = await connection.createChannel();
      const queueName = 'medical_record_created';

      await channel.assertQueue(queueName, { durable: true });
      console.log(`[RabbitMQ] Đang chờ tin nhắn trong hàng đợi: ${queueName}`);

      channel.consume(queueName, async (msg) => {
        if (msg !== null) {
          try {
            const data = JSON.parse(msg.content.toString());
            console.log('⏺️  [RabbitMQ] Nhận được tin nhắn:', data);
            await autoCreateRecord(data);
            channel.ack(msg);
            console.log('✅ [RabbitMQ] Đã xử lý và xác nhận tin nhắn.');
          } catch (error) {
            console.error('[✘] [RabbitMQ] Lỗi khi xử lý tin nhắn:', error);
            channel.nack(msg, false, true);
          }
        }
      });

      // Nếu mọi thứ thành công, thoát khỏi vòng lặp
      return;

    } catch (error) {
      retries--;
      console.error(`[✘] [RabbitMQ] Kết nối thất bại: ${error.message}. Thử lại sau ${delay / 1000} giây...`);
      if (retries === 0) {
        console.error('[✘] [RabbitMQ] Hết số lần thử, ứng dụng sẽ thoát.');
        process.exit(1);
      }
      // Chờ trước khi thử lại
      await sleep(delay);
    }
  }
};