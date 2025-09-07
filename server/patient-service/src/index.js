import express from 'express';
import dotenv from 'dotenv';
import patientRoutes from './routes/patient.routes.js';
import recordRoutes from './routes/record.routes.js';
import healthRoutes from './routes/health.routes.js';
import { consumeMedicalRecordQueue } from './queues/record.consumer.js';
import { startSyncConsumers } from './queues/sync.consumer.js';
import { connectRabbitMQ } from './utils/rabbitmq.js';

// Initialize background services
consumeMedicalRecordQueue(); // 👈 chạy khi khởi động patient-service

// // Initialize RabbitMQ connection and consumers
// (async () => {
//   await connectRabbitMQ(); // Establish RabbitMQ connection first
//   await startSyncConsumers(); // Start sync consumers
// })();

dotenv.config();
const app = express();

app.use(express.json());
app.use('/api/patients', patientRoutes);
app.use('/api/medical-records', recordRoutes);
app.use('/health', healthRoutes);
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
  console.log(`Patient Service running on port ${PORT}`);
});
