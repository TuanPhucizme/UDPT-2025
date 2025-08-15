// src/queue/notifications.js
import { publishToQueue } from './rabbitmq.js';

/**
 * Khi bác sĩ tạo đơn thuốc
 */
export async function notifyPrescriptionCreated({ patientId, prescriptionId, medicine }) {
  const payload = {
    type: 'prescription',
    event: 'prescription.created',
    patientId: String(patientId),
    message: `Đơn thuốc #${prescriptionId} đã được tạo${medicine ? `: ${medicine}` : ''}.`,
    meta: { prescriptionId, medicine }
  };
  await publishToQueue(payload);
}

/**
 * Khi dược sĩ xác nhận “đã phát thuốc”
 */
export async function notifyPrescriptionDispensed({ patientId, prescriptionId, pharmacistId }) {
  const payload = {
    type: 'prescription',
    event: 'prescription.dispensed',
    patientId: String(patientId),
    message: `Đơn #${prescriptionId} đã được phát.`,
    meta: { prescriptionId, pharmacistId }
  };
  await publishToQueue(payload);
}
