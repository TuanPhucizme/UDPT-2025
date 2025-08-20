import express from 'express';
import {
  bookAppointment,
  listAppointments,
  confirmAppointment,
  proposeAppointmentTime,
  declineAppointment,
  getAppointmentById
} from '../controllers/appointment.controller.js';
import { authMiddleware, internalAuthMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Client routes
router.post('/', authMiddleware, authorizeRoles('benhnhan'), bookAppointment);
router.get('/', authMiddleware, authorizeRoles('letan', 'bacsi', 'admin'), listAppointments);
router.put('/:id/propose', authMiddleware, authorizeRoles('bacsi'), proposeAppointmentTime);
router.put('/:id/confirm', authMiddleware, authorizeRoles('bacsi', 'admin'), confirmAppointment);
router.put('/:id/decline', authMiddleware, authorizeRoles('bacsi', 'admin'), declineAppointment);

// Internal service routes
router.get('/internal/:id', internalAuthMiddleware, getAppointmentById);

export default router;
