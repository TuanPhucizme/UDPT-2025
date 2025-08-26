import express from 'express';
import { 
  getStaff, 
  getDepartment, 
  listDepartments,
  getDepartmentStaff 
} from '../controllers/staff.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Public routes (with client auth)
router.get(
  '/staff/:id',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  getStaff
);
// Department routes
router.get(
  '/departments',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  listDepartments
);

router.get(
  '/departments/:id',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  getDepartment
);

router.get(
  '/departments/:id/staff',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  getDepartmentStaff
);

export default router;