import express from 'express';
import {
  registerPatient,
  getPatients,
  getPatient,
  updatePatientInfo,
} from '../controllers/patient.controller.js';

import { authMiddleware } from '../middleware/auth.middleware.js';

const router = express.Router();

// ✅ Gắn authMiddleware vào các route cần xác thực
router.post('/', authMiddleware, registerPatient);
router.get('/', authMiddleware, getPatients);
router.get('/:id', authMiddleware, getPatient);
router.put('/:id', authMiddleware, updatePatientInfo);

export default router;
