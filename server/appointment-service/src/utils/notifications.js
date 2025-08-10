// src/queue/notifications.js
import { publishToQueue } from './rabbitmq.js';

/**
 * Gửi sự kiện "đặt lịch thành công"
 */
export async function notifyAppointmentScheduled({ patientId, appointmentId, time }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.scheduled',
    patientId: String(patientId),
    message: `Bạn đã đặt lịch #${appointmentId} lúc ${time}.`,
    meta: { appointmentId, time }
  };
  await publishToQueue(payload);
}

/**
 * Gửi sự kiện "lịch khám đã xác nhận"
 */
export async function notifyAppointmentConfirmed({ patientId, appointmentId, time }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.confirmed',
    patientId: String(patientId),
    message: `Lịch #${appointmentId} đã được xác nhận (${time}).`,
    meta: { appointmentId, time }
  };
  await publishToQueue(payload);
}

/**
 * (tuỳ chọn) Hủy lịch
 */
export async function notifyAppointmentCanceled({ patientId, appointmentId, reason }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.canceled',
    patientId: String(patientId),
    message: `Lịch #${appointmentId} đã bị hủy${reason ? `: ${reason}` : ''}.`,
    meta: { appointmentId, reason }
  };
  await publishToQueue(payload);
}
