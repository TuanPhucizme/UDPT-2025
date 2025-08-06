const express = require('express');
const router = express.Router();
const controller = require('../controllers/report.controller');

router.get('/patients', controller.getPatientStats);
router.get('/prescriptions', controller.getPrescriptionStats);

module.exports = router;
