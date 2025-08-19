import express from 'express';
import {
  bookAppointment,
  listAppointments,
  confirmAppointment,
  proposeAppointmentTime,   // NEW
  declineAppointment
} from '../controllers/appointment.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Bệnh nhân đặt lịch khám
router.post('/', authMiddleware, authorizeRoles('patient'), bookAppointment);

// Lễ tân, bác sĩ và admin xem danh sách lịch
router.get('/', authMiddleware, authorizeRoles('receptionist', 'doctor', 'admin'), listAppointments);
router.put('/:id/propose', authMiddleware, authorizeRoles('doctor'), proposeAppointmentTime);   // NEW
// Bác sĩ hoặc admin xác nhận lịch khám
router.put('/:id/confirm', authMiddleware, authorizeRoles('doctor', 'admin'), confirmAppointment); 
router.put('/:id/decline', authMiddleware, authorizeRoles('doctor', 'admin'), declineAppointment); // NEW
export default router;
