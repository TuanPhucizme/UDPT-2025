import express from 'express';
import {
  registerPatient,
  getPatients,
  getPatient,
  updatePatientInfo,
} from '../controllers/patient.controller.js';

import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// ✅ Gắn authMiddleware vào các route cần xác thực
router.post(
  '/',
  authMiddleware,
  authorizeRoles('receptionist', 'admin'), // lễ tân có quyền tạo bệnh nhân
  registerPatient
);

router.get(
  '/',
  authMiddleware,
  authorizeRoles('doctor', 'receptionist', 'admin'), // cho phép xem DS bệnh nhân
  getPatients
);

router.get(
  '/:id',
  authMiddleware,
  authorizeRoles('doctor', 'receptionist', 'pharmacist', 'admin'), // cho phép xem chi tiết bệnh nhân
  getPatient
);

router.put(
  '/:id',
  authMiddleware,
  authorizeRoles('receptionist', 'admin'), // chỉ lễ tân & admin được sửa
  updatePatientInfo
);

export default router;
