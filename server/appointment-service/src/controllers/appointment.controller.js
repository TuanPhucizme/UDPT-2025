/*import {
  createAppointment,
  getAppointments,
  updateAppointmentStatus,
  getAppointmentById
} from '../models/appointment.model.js';

import { publishToQueue } from '../utils/rabbitmq.js';
const sendNotification = require('../utils/sendNotification');
export const bookAppointment = async (req, res) => {
  try {
    const { patient_id, doctor_id, appointment_time, note } = req.body;
    const result = await createAppointment({ patient_id, doctor_id, appointment_time, note });

    // 🔔 Gửi thông báo đến notification-service
    await sendNotification({
      type: 'appointment',
      patientId: patient_id,
      message: `Bạn đã đặt lịch khám lúc ${appointment_time}.`
    });

    res.status(201).json({ message: 'Đặt lịch thành công', id: result.insertId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi đặt lịch', error: err.message });
  }
};


export const listAppointments = async (req, res) => {
  const result = await getAppointments();
  res.json(result);
};

export const confirmAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const { status } = req.body;

    await updateAppointmentStatus(id, status);

    // 🔁 Gửi khi lịch khám được xác nhận
    if (status === 'confirmed') {
      const appointment = await getAppointmentById(id); // bạn thêm hàm này
      await publishToQueue('medical_record_created', {
        appointment_id: appointment.id,
        patient_id: appointment.patient_id,
        doctor_id: appointment.doctor_id,
        visit_date: appointment.appointment_time
      });

      await sendNotification({
        type: 'appointment',
        patientId: appointment.patient_id,
        message: `Lịch khám của bạn vào ${appointment.appointment_time} đã được bác sĩ xác nhận.`
      });

    }

    res.json({ message: 'Cập nhật trạng thái thành công' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi cập nhật trạng thái', error: err.message });
  }
};
*/
/*// src/controllers/appointment.controller.js
import {
  createAppointment,
  getAppointments,
  updateAppointmentStatus,
  getAppointmentById
} from '../models/appointment.model.js';

import { publishToQueue } from '../utils/rabbitmq.js'; // dùng khi cần publish custom queue khác
import {
  notifyAppointmentScheduled,
  notifyAppointmentConfirmed
} from '../utils/notifications.js'; // gửi đúng schema cho notification-service

export const bookAppointment = async (req, res) => {
  try {
    const { patient_id, doctor_id, appointment_time, note } = req.body;

    const result = await createAppointment({
      patient_id,
      doctor_id,
      appointment_time,
      note
    });

    const appointmentId = result.insertId;

    // 🔔 Gửi thông báo đặt lịch (đúng schema: type + event + patientId + message + meta)
    // Có thể fire-and-forget: notifyAppointmentScheduled(...).catch(console.error);
    await notifyAppointmentScheduled({
      patientId: patient_id,
      appointmentId,
      time: appointment_time
    });

    res.status(201).json({ message: 'Đặt lịch thành công', id: appointmentId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi đặt lịch', error: err.message });
  }
};

export const listAppointments = async (req, res) => {
  try {
    const result = await getAppointments();
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách lịch', error: err.message });
  }
};

export const confirmAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const { status } = req.body;

    await updateAppointmentStatus(id, status);

    if (status === 'confirmed') {
      const appointment = await getAppointmentById(id); // cần trả về { id, patient_id, doctor_id, appointment_time, ... }

      // (Tuỳ chọn) publish sang queue nội bộ khác nếu bạn có workflow tạo bệnh án tự động
      // Không liên quan notification-service
      // await publishToQueue({
      //   type: 'appointment',
      //   event: 'medical_record.create',
      //   patientId: String(appointment.patient_id),
      //   meta: {
      //     appointmentId: appointment.id,
      //     doctorId: appointment.doctor_id,
      //     visitDate: appointment.appointment_time
      //   }
      // }, process.env.MEDICAL_QUEUE_NAME || 'medical_record_created');

      // 🔔 Gửi notify "đã xác nhận lịch"
      await notifyAppointmentConfirmed({
        patientId: appointment.patient_id,
        appointmentId: appointment.id,
        time: appointment.appointment_time
      });
    }

    res.json({ message: 'Cập nhật trạng thái thành công' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi cập nhật trạng thái', error: err.message });
  }
};
*/

import {
  createAppointment,
  getAppointments,
  updateAppointmentStatus,
  getAppointmentById,
  proposeTime,         // NEW
  confirmTime,         // NEW
  declineRequest       // NEW
} from '../models/appointment.model.js';

import {
  notifyAppointmentScheduled,
  notifyAppointmentProposed,   // NEW
  notifyAppointmentConfirmed,
  notifyAppointmentDeclined    // NEW
} from '../utils/notifications.js';

export const bookAppointment = async (req, res) => {
  try {
    const { patient_id, doctor_id, appointment_time, requested_time, note } = req.body;

    const result = await createAppointment({
      patient_id,
      doctor_id,
      requested_time: requested_time || appointment_time || null,
      note
    });

    const appointmentId = result.insertId;

    await notifyAppointmentScheduled({
      patientId: patient_id,
      appointmentId,
      time: requested_time || appointment_time || null
    });

    res.status(201).json({ message: 'Tạo yêu cầu lịch thành công', id: appointmentId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi tạo yêu cầu lịch', error: err.message });
  }
};

export const listAppointments = async (req, res) => {
  try {
    const result = await getAppointments();
    res.json(result);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách lịch', error: err.message });
  }
};

// NEW: bác sĩ nhận đơn + đề xuất giờ
export const proposeAppointmentTime = async (req, res) => {
  try {
    const id = req.params.id;
    const { proposed_time } = req.body;

    const apm = await getAppointmentById(id);
    if (!apm) return res.status(404).json({ message: 'Không tìm thấy lịch' });

    // chỉ đúng bác sĩ của lịch hoặc admin
    if (apm.doctor_id !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Bạn không phải là bác sĩ của lịch này' });
    }

    await proposeTime(id, apm.doctor_id, proposed_time);

    await notifyAppointmentProposed({
      patientId: apm.patient_id,
      appointmentId: apm.id,
      time: proposed_time
    });

    res.json({ message: 'Đã đề xuất thời gian khám' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi đề xuất thời gian', error: err.message });
  }
};

export const confirmAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const { appointment_time } = req.body; // giờ chốt

    const apm = await getAppointmentById(id);
    if (!apm) return res.status(404).json({ message: 'Không tìm thấy lịch' });

    if (apm.doctor_id !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Bạn không phải là bác sĩ của lịch này' });
    }

    await confirmTime(id, apm.doctor_id, appointment_time);

    await notifyAppointmentConfirmed({
      patientId: apm.patient_id,
      appointmentId: apm.id,
      time: appointment_time
    });

    res.json({ message: 'Đã xác nhận lịch khám' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi xác nhận lịch', error: err.message });
  }
};

// NEW: bác sĩ từ chối
export const declineAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const { reason } = req.body;

    const apm = await getAppointmentById(id);
    if (!apm) return res.status(404).json({ message: 'Không tìm thấy lịch' });

    if (apm.doctor_id !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Bạn không phải là bác sĩ của lịch này' });
    }

    await declineRequest(id, apm.doctor_id, reason || '');

    await notifyAppointmentDeclined({
      patientId: apm.patient_id,
      appointmentId: apm.id,
      reason
    });

    res.json({ message: 'Đã từ chối yêu cầu lịch' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi từ chối lịch', error: err.message });
  }
};
