import express from 'express';
import {
  create,
  updateStatus,
  getByPatient
} from '../controllers/prescription.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

router.post('/', authMiddleware, authorizeRoles('doctor'), create);
router.put('/:id', authMiddleware, authorizeRoles('admin', 'patient'), updateStatus);
router.get('/patient/:patient_id', authMiddleware, getByPatient);

export default router;
