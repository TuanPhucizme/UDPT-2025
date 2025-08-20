import express from 'express';
import {
  create,
  updateStatus,
  getByPatient,
  getByRecordId
} from '../controllers/prescription.controller.js';
import { authMiddleware, internalAuthMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Client routes
router.post('/', authMiddleware, authorizeRoles('bacsi'), create);
router.put('/:id', authMiddleware, authorizeRoles('duocsi', 'admin'), updateStatus);
router.get(
  '/patient/:patient_id',
  authMiddleware,
  authorizeRoles('bacsi', 'duocsi', 'admin'),
  getByPatient
);

// Internal service routes
router.get(
  '/internal/record/:record_id',
  internalAuthMiddleware,
  getByRecordId
);

export default router;
