import express from 'express';
import {
  bookAppointment,
  listAppointments,
  confirmAppointment,
  cancelAppointment,
  getDoctorAvailability
} from '../controllers/appointment.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Index route
router.get('/', authMiddleware, authorizeRoles('bacsi', 'letan', 'admin'), listAppointments);

// Other routes
router.post('/book', authMiddleware, authorizeRoles('letan', 'admin'), bookAppointment);
router.put('/:id/confirm', authMiddleware, authorizeRoles('bacsi', 'admin'), confirmAppointment);
router.put('/:id/cancel', authMiddleware, authorizeRoles('bacsi', 'letan', 'admin'), cancelAppointment);
router.get('/doctor-schedule', authMiddleware, getDoctorAvailability);

export default router;
