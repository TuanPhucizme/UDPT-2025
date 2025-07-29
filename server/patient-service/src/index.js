import express from 'express';
import dotenv from 'dotenv';
import patientRoutes from './routes/patient.routes.js';
import recordRoutes from './routes/record.routes.js';

import { consumeMedicalRecordQueue } from './queues/record.consumer.js';

consumeMedicalRecordQueue(); // 👈 chạy khi khởi động patient-service

dotenv.config();
const app = express();

app.use(express.json());
app.use('/api/patients', patientRoutes);
app.use('/api/medical-records', recordRoutes);

const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
  console.log(`Patient Service running on port ${PORT}`);
});
