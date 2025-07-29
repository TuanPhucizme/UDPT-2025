import express from 'express';
import { register, login } from '../controllers/auth.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';
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
export default router;
