import express from 'express';
import {
  registerPatient,
  getPatients,
  getPatient,
  updatePatientInfo,
  getPatientRecordIds
} from '../controllers/patient.controller.js';
import { authMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Client routes
router.post('/', authMiddleware, authorizeRoles('letan', 'admin'), registerPatient);
router.get('/', authMiddleware, authorizeRoles('bacsi', 'letan', 'admin','duocsi'), getPatients);
router.get('/:id', authMiddleware, authorizeRoles('bacsi', 'letan', 'duocsi', 'admin'), getPatient);
router.put('/:id', authMiddleware, authorizeRoles('letan', 'admin'), updatePatientInfo);
router.get(
  '/internal/:id/record-ids',
  authMiddleware,
  getPatientRecordIds
);
export default router;
