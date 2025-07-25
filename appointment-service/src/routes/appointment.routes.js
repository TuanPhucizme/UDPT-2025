import express from 'express';
import {
  bookAppointment,
  listAppointments,
  confirmAppointment,
} from '../controllers/appointment.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

router.post('/', authMiddleware, authorizeRoles('patient'), bookAppointment);
router.get('/', authMiddleware, listAppointments); // (tuỳ quyền)
router.put('/:id', authMiddleware, authorizeRoles('doctor', 'admin'), confirmAppointment);

export default router;
