import express from 'express';
import dotenv from 'dotenv';
import authRoutes from './routes/auth.routes.js';
import staffRoutes from './routes/staff.routes.js';
import healthRoutes from './routes/health.routes.js';
dotenv.config();
const app = express();
app.use(express.json());

app.use('/api/auth', authRoutes);
app.use('/api', staffRoutes);
app.use('/health', healthRoutes);
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Auth Service running on port ${PORT}`));
