import express from 'express';
import dotenv from 'dotenv';
import appointmentRoutes from './routes/appointment.routes.js';

dotenv.config();
const app = express();

app.use(express.json());
app.use('/api/appointments', appointmentRoutes);

const PORT = process.env.PORT || 3002;
app.listen(PORT, () => {
  console.log(`Appointment Service running on port ${PORT}`);
});
