const express = require('express');
const router = express.Router();
const controller = require('../controllers/notification.controller');

// Lấy thông báo theo bệnh nhân
router.get('/:patientId', controller.getNotifications);

// Đánh dấu 1 thông báo đã đọc
router.put('/read/:id', controller.markAsRead);

// Đánh dấu tất cả thông báo của 1 bệnh nhân là đã đọc
router.put('/read-all/:patientId', controller.markAllAsRead);

// Test publish message vào queue
router.post('/test-publish', controller.testPublish);

module.exports = router;
