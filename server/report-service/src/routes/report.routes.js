const express = require('express');
const router = express.Router();
const controller = require('../controllers/report.controller');

// Original routes
router.get('/patients', controller.getPatientStats);
router.get('/prescriptions', controller.getPrescriptionStats);

// New routes
router.get('/medicines', controller.getMedicineStats);
router.get('/departments', controller.getDepartmentStats);
router.get('/diagnoses', controller.getDiagnosisStats);

// Sync data on demand
router.post('/sync', controller.syncReportData);

module.exports = router;
