// services/notification.service.js
const Notification = require('../models/Notification');

exports.createNotification = async ({ type, patientId, message }) => {
  const notification = new Notification({ type, patientId, message });
  return await notification.save();
};

exports.getNotificationsByPatientId = async (patientId) => {
  return await Notification.find({ patientId }).sort({ createdAt: -1 });
};
