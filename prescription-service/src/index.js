import express from 'express';
import dotenv from 'dotenv';
import prescriptionRoutes from './routes/prescription.routes.js';

dotenv.config();
const app = express();

app.use(express.json());
app.use('/api/prescriptions', prescriptionRoutes);

const PORT = process.env.PORT || 3003;
app.listen(PORT, () => {
  console.log(`Prescription service running on port ${PORT}`);
});
