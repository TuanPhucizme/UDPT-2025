const mongoose = require('mongoose');

const notificationSchema = new mongoose.Schema(
  {
    type: { type: String, enum: ['appointment', 'prescription'], required: true },
    event: { type: String, required: true }, // ví dụ: appointment.scheduled, prescription.dispensed
    patientId: { type: String, required: true },
    message: { type: String, required: true },
    isRead: { type: Boolean, default: false },
    // idempotency
    msgId: { type: String, required: false, index: true, unique: true, sparse: true },
    meta: { type: Object }, // tuỳ ý, ví dụ appointmentId, prescriptionId...
  },
  { timestamps: true }
);

notificationSchema.index({ patientId: 1, createdAt: -1 });
notificationSchema.index({ patientId: 1, isRead: 1 });

module.exports = mongoose.model('Notification', notificationSchema);
