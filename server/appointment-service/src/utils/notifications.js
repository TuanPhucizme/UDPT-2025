// src/queue/notifications.js
/*import { publishToQueue } from './rabbitmq.js';

//Gửi sự kiện "đặt lịch thành công"

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

//Gửi sự kiện "lịch khám đã xác nhận"

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

//(tuỳ chọn) Hủy lịch

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
*/
import { publishToQueue } from './rabbitmq.js';

export async function notifyAppointmentScheduled({ patientId, appointmentId, time }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.scheduled',
    patientId: String(patientId),
    message: `Bạn đã tạo yêu cầu lịch #${appointmentId}${time ? ` (mong muốn: ${time})` : ''}.`,
    meta: { appointmentId, requestedTime: time }
  };
  await publishToQueue(payload);
}

export async function notifyAppointmentProposed({ patientId, appointmentId, time }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.proposed',
    patientId: String(patientId),
    message: `Bác sĩ đã đề xuất thời gian cho lịch #${appointmentId}: ${time}.`,
    meta: { appointmentId, proposedTime: time }
  };
  await publishToQueue(payload);
}

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

export async function notifyAppointmentDeclined({ patientId, appointmentId, reason }) {
  const payload = {
    type: 'appointment',
    event: 'appointment.declined',
    patientId: String(patientId),
    message: `Yêu cầu lịch #${appointmentId} đã bị từ chối.${reason ? ` Lý do: ${reason}` : ''}`,
    meta: { appointmentId, reason }
  };
  await publishToQueue(payload);
}
