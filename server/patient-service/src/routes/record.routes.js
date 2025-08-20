import express from 'express';
import { 
    createRecord, 
    getPatientRecords, 
    getRecordDetails,
    updateRecord 
} from '../controllers/record.controller.js';
import { authMiddleware, internalAuthMiddleware, authorizeRoles } from '../middleware/auth.middleware.js';

const router = express.Router();

// Client routes
router.post('/', authMiddleware, authorizeRoles('bacsi'), createRecord);
router.get(
    '/patient/:id',
    authMiddleware,
    authorizeRoles('bacsi', 'duocsi', 'letan', 'admin'),
    getPatientRecords
);
router.get('/:id', authMiddleware, authorizeRoles('bacsi', 'duocsi', 'letan', 'admin'), getRecordDetails);
router.put('/:id', authMiddleware, authorizeRoles('bacsi'), updateRecord);

// Internal service routes
router.get('/internal/:id', internalAuthMiddleware, getRecordDetails);
router.get('/internal/patient/:id', internalAuthMiddleware, getPatientRecords);

export default router;
