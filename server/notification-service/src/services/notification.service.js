const Notification = require('../models/Notification');

exports.createNotification = async ({ type, event, patientId, message, msgId, meta }) => {
  if (!type || !event || !patientId || !message) {
    throw new Error('Thiếu trường bắt buộc: type, event, patientId, message');
  }
  // Ngăn trùng lặp nếu có msgId
  if (msgId) {
    const exists = await Notification.findOne({ msgId });
    if (exists) return exists;
  }
  return await Notification.create({ type, event, patientId, message, msgId, meta });
};

exports.getNotificationsByPatientId = async (patientId, { page = 1, limit = 20, type, isRead } = {}) => {
  const q = { patientId };
  if (type) q.type = type;
  if (typeof isRead !== 'undefined') q.isRead = isRead === 'true' || isRead === true;

  const skip = (Number(page) - 1) * Number(limit);
  const [items, total] = await Promise.all([
    Notification.find(q).sort({ createdAt: -1 }).skip(skip).limit(Number(limit)),
    Notification.countDocuments(q),
  ]);

  return { items, pagination: { page: Number(page), limit: Number(limit), total, pages: Math.ceil(total / limit) } };
};

exports.markAsRead = async (id) => {
  return await Notification.findByIdAndUpdate(id, { isRead: true }, { new: true });
};

exports.markAllAsReadByPatient = async (patientId) => {
  const res = await Notification.updateMany({ patientId, isRead: false }, { $set: { isRead: true } });
  return { modified: res.modifiedCount };
};
