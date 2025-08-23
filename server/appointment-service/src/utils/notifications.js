// src/queue/notifications.js
import { publishToQueue } from './rabbitmq.js';

/**
 * Unified appointment notification function
 */
export async function notifyAppointment(data) {
  const { type, patientId, appointmentId, time, reason = null } = data;
  
  const eventMap = {
    'APPOINTMENT_CREATED': {
      event: 'appointment.created',
      message: `Bạn đã tạo yêu cầu lịch #${appointmentId}${time ? ` (mong muốn: ${time})` : ''}.`
    },
    'APPOINTMENT_PROPOSED': {
      event: 'appointment.proposed',
      message: `Bác sĩ đã đề xuất thời gian cho lịch #${appointmentId}: ${time}.`
    },
    'APPOINTMENT_CONFIRMED': {
      event: 'appointment.confirmed',
      message: `Lịch #${appointmentId} đã được xác nhận (${time}).`
    },
    'APPOINTMENT_DECLINED': {
      event: 'appointment.declined',
      message: `Yêu cầu lịch #${appointmentId} đã bị từ chối${reason ? `. Lý do: ${reason}` : ''}.`
    },
    'APPOINTMENT_CANCELLED': {
      event: 'appointment.cancelled',
      message: `Lịch #${appointmentId} đã bị huỷ${reason ? `. Lý do: ${reason}` : ''}.`
    },
    'APPOINTMENT_REMINDER': {
      event: 'appointment.reminder',
      message: `Nhắc nhở: Bạn có lịch khám #${appointmentId} vào ${time}.`
    }
  };

  if (!eventMap[type]) {
    throw new Error(`Invalid notification type: ${type}`);
  }

  const { event, message } = eventMap[type];

  const payload = {
    type: 'appointment',
    event,
    patientId: String(patientId),
    message,
    meta: {
      appointmentId,
      time,
      ...(reason && { reason })
    },
    timestamp: new Date().toISOString()
  };

  try {
    await publishToQueue(payload);
    console.log(`✅ Notification sent: ${event}`, { appointmentId, patientId });
  } catch (error) {
    console.error(`❌ Failed to send notification: ${event}`, error);
    // Don't throw error to prevent appointment operation failure
  }
}

/**
 * Individual notification functions for backward compatibility
 */
export async function notifyAppointmentScheduled({ patientId, appointmentId, time }) {
  return notifyAppointment({
    type: 'APPOINTMENT_CREATED',
    patientId,
    appointmentId,
    time
  });
}

export async function notifyAppointmentProposed({ patientId, appointmentId, time }) {
  return notifyAppointment({
    type: 'APPOINTMENT_PROPOSED',
    patientId,
    appointmentId,
    time
  });
}

export async function notifyAppointmentConfirmed({ patientId, appointmentId, time }) {
  return notifyAppointment({
    type: 'APPOINTMENT_CONFIRMED',
    patientId,
    appointmentId,
    time
  });
}

export async function notifyAppointmentDeclined({ patientId, appointmentId, reason }) {
  return notifyAppointment({
    type: 'APPOINTMENT_DECLINED',
    patientId,
    appointmentId,
    reason
  });
}

export async function notifyAppointmentCancelled({ patientId, appointmentId, time, reason }) {
  return notifyAppointment({
    type: 'APPOINTMENT_CANCELLED',
    patientId,
    appointmentId,
    time,
    reason
  });
}

export async function notifyAppointmentReminder({ patientId, appointmentId, time }) {
  return notifyAppointment({
    type: 'APPOINTMENT_REMINDER',
    patientId,
    appointmentId,
    time
  });
}
