import express from 'express';
import { addMedicalRecord, getRecordsByPatient } from '../controllers/record.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Chỉ role "doctor" và 'receptionist' mới được thêm hồ sơ
router.post('/', authMiddleware, authorizeRoles('doctor', 'receptionist'), addMedicalRecord);

router.get(
  '/patient/:patientId',
  authMiddleware,
  authorizeRoles('doctor', 'pharmacist', 'admin', 'receptionist'), // Chỉ bác sĩ, dược sĩ, admin và lễ tân mới được xem hồ sơ bệnh nhân
  getRecordsByPatient
);


export default router;
