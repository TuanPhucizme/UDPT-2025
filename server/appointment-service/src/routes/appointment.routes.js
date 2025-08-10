import express from 'express';
import {
  bookAppointment,
  listAppointments,
  confirmAppointment,
} from '../controllers/appointment.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Bệnh nhân đặt lịch khám
router.post('/', authMiddleware, authorizeRoles('patient'), bookAppointment);

// Lễ tân, bác sĩ và admin xem danh sách lịch
router.get('/', authMiddleware, authorizeRoles('receptionist', 'doctor', 'admin'), listAppointments);

// Bác sĩ hoặc admin xác nhận lịch khám
router.put('/:id', authMiddleware, authorizeRoles('doctor', 'admin'), confirmAppointment);

export default router;
