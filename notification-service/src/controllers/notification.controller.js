// controllers/notification.controller.js
const notificationService = require('../services/notification.service');

exports.getNotifications = async (req, res) => {
  try {
    const { patientId } = req.params;
    const data = await notificationService.getNotificationsByPatientId(patientId);
    res.json(data);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi lấy thông báo', error: err.message });
  }
};
