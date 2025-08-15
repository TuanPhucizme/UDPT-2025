import express from 'express';
import {
  create,
  updateStatus,
  getByPatient
} from '../controllers/prescription.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// ✅ Bác sĩ tạo đơn thuốc
router.post('/', authMiddleware, authorizeRoles('doctor'), create);

// ✅ Dược sĩ (và admin) cập nhật tình trạng đơn thuốc
router.put('/:id', authMiddleware, authorizeRoles('pharmacist', 'admin'), updateStatus);

// ✅ Bác sĩ + dược sĩ + admin có thể xem đơn thuốc theo bệnh nhân
router.get(
  '/patient/:patient_id',
  authMiddleware,
  authorizeRoles('doctor', 'pharmacist', 'admin'),
  getByPatient
);

export default router;
