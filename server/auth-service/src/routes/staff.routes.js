import express from 'express';
import { 
  getStaff, 
  getDepartment, 
  listDepartments,
  getDepartmentStaff 
} from '../controllers/staff.controller.js';
import { authMiddleware, authorizeRoles, internalAuthMiddleware } from '../middleware/auth.middleware.js';

const router = express.Router();

// Public routes (with client auth)
router.get(
  '/staff/:id',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  getStaff
);

// Internal service routes
router.get(
  '/internal/staff/:id',
  internalAuthMiddleware,
  getStaff
);

router.get(
  '/internal/departments',
  authMiddleware,
  authorizeRoles('admin', 'bacsi', 'duocsi', 'letan'),
  listDepartments
);
router.get(
  '/internal/departments/:id',
  internalAuthMiddleware,
  getDepartment
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