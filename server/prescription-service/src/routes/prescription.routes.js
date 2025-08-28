import express from 'express';
import {
  create,
  updateStatus,
  getByPatient,
  getByRecordId,
  getMedicines,
  getById
} from '../controllers/prescription.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

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
router.get(
  '/record/:record_id',
  authMiddleware,
  getByRecordId
);
router.get('/medicines', authMiddleware, getMedicines);
router.get('/:id', authMiddleware, authorizeRoles('bacsi', 'duocsi', 'admin'), getById);
router.get('/internal/medicines', authMiddleware, getMedicines); // Internal route

export default router;
