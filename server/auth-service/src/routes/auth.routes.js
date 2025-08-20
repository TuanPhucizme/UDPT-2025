import express from 'express';
import { register, login } from '../controllers/auth.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';
import {
  getDoctors,
  getPharmacists,
  getReceptionists,
  getPatients
} from '../controllers/auth.controller.js';
const router = express.Router();

router.post('/register', register);
router.post('/login', login);

// Route mới để kiểm tra token hợp lệ
router.get('/me', authMiddleware, (req, res) => {
  res.json({
    message: 'Xác thực thành công!',
    user: req.user
  });
});

// Chỉ Admin mới được truy cập
router.get('/admin-only', authMiddleware, authorizeRoles('admin'), (req, res) => {
  res.json({ message: 'Chào Admin!' });
});

// Chỉ Doctor mới được truy cập
router.get('/doctor-only', authMiddleware, authorizeRoles('doctor'), (req, res) => {
  res.json({ message: 'Chào Bác sĩ!' });
});

// Cả Doctor và Admin đều được phép
router.get('/medical-staff', authMiddleware, authorizeRoles('doctor', 'admin'), (req, res) => {
  res.json({ message: 'Chào nhân viên y tế!' });
});

// Chỉ lễ tân
router.get('/reception-only', authMiddleware, authorizeRoles('receptionist'), (req, res) => {
  res.json({ message: 'Chào lễ tân!' });
});

// Chỉ dược sĩ
router.get('/pharmacist-only', authMiddleware, authorizeRoles('pharmacist'), (req, res) => {
  res.json({ message: 'Chào dược sĩ!' });
});

// Cả bác sĩ + lễ tân xem bệnh nhân
router.get('/view-patient', authMiddleware, authorizeRoles('doctor', 'receptionist'), (req, res) => {
  res.json({ message: 'Bạn được phép xem bệnh nhân!' });
});

// Dược sĩ và bác sĩ có quyền xử lý đơn thuốc
router.get('/view-prescription', authMiddleware, authorizeRoles('pharmacist', 'doctor'), (req, res) => {
  res.json({ message: 'Bạn được phép xem đơn thuốc!' });
});

// Bác sĩ (lọc thêm ?specialty=Noi khoa)
router.get(
  '/doctors',
  authMiddleware,
  authorizeRoles('admin'),
  getDoctors
);

// Dược sĩ
router.get(
  '/pharmacists',
  authMiddleware,
  authorizeRoles('admin'),
  getPharmacists
);

// Lễ tân
router.get(
  '/receptionists',
  authMiddleware,
  authorizeRoles('admin'),
  getReceptionists
);

// Bệnh nhân
router.get(
  '/patients',
  authMiddleware,
  authorizeRoles('admin', 'receptionist', 'doctor', 'pharmacist'),
  getPatients
);

export default router;
