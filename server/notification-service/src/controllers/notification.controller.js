const notificationService = require('../services/notification.service');
const publisher = require('../queue/publisher'); // tuỳ chọn, chỉ để test

exports.getNotifications = async (req, res) => {
  try {
    const { patientId } = req.params;
    const { page, limit, type, isRead } = req.query;
    const data = await notificationService.getNotificationsByPatientId(patientId, { page, limit, type, isRead });
    res.json(data);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi lấy thông báo', error: err.message });
  }
};

exports.markAsRead = async (req, res) => {
  try {
    const { id } = req.params;
    const updated = await notificationService.markAsRead(id);
    if (!updated) return res.status(404).json({ message: 'Không tìm thấy thông báo' });
    res.json(updated);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi cập nhật', error: err.message });
  }
};

exports.markAllAsRead = async (req, res) => {
  try {
    const { patientId } = req.params;
    const result = await notificationService.markAllAsReadByPatient(patientId);
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi cập nhật', error: err.message });
  }
};

// API test để publish 1 message vào queue (tuỳ chọn)
exports.testPublish = async (req, res) => {
  try {
    const payload = req.body; // { type, event, patientId, message, msgId, meta }
    await publisher.publish(payload);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ message: 'Publish lỗi', error: err.message });
  }
};
