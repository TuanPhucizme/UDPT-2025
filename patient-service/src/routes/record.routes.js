import express from 'express';
import { addMedicalRecord, getRecordsByPatient } from '../controllers/record.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// ✅ Chỉ role "doctor" mới được thêm hồ sơ
router.post('/', authMiddleware, authorizeRoles('doctor'), addMedicalRecord);

// ✅ Ai cũng xem được (bác sĩ, admin, bệnh nhân) – hoặc bạn giới hạn sau
router.get('/patient/:patientId', authMiddleware, getRecordsByPatient);

export default router;
