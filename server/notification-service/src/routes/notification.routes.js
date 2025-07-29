// routes/notification.routes.js
const express = require('express');
const router = express.Router();
const controller = require('../controllers/notification.controller');

router.get('/:patientId', controller.getNotifications);

module.exports = router;
