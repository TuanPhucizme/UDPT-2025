import express from 'express';
import {
  registerPatient,
  getPatients,
  getPatient,
  updatePatientInfo
} from '../controllers/patient.controller.js';
import { authMiddleware, internalAuthMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Client routes
router.post('/', authMiddleware, authorizeRoles('letan', 'admin'), registerPatient);
router.get('/', authMiddleware, authorizeRoles('bacsi', 'letan', 'admin'), getPatients);
router.get('/:id', authMiddleware, authorizeRoles('bacsi', 'letan', 'duocsi', 'admin'), getPatient);
router.put('/:id', authMiddleware, authorizeRoles('letan', 'admin'), updatePatientInfo);

// Internal service routes
router.get('/internal/:id', internalAuthMiddleware, getPatient);

export default router;
