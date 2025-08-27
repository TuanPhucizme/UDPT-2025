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
  getDoctorSchedule,
  getDoctorAvailableSlots
} from '../models/appointment.model.js';

import { notifyAppointment } from '../utils/notifications.js';

export const bookAppointment = async (req, res) => {
  try {
    const { 
      patient_id, 
      department_id,
      doctor_id, 
      thoi_gian_hen,
      lydo,
      note 
    } = req.body;
    console.log(req.body); 
    // Validate required fields
    if (!patient_id || !department_id || !doctor_id || !thoi_gian_hen) {
      return res.status(400).json({ message: 'Thiếu thông tin bắt buộc' });
    }

    const result = await createAppointment({
      patient_id,
      department_id,
      doctor_id,
      receptionist_id: req.user.id,
      thoi_gian_hen,
      lydo,
      note
    });
    await notifyAppointment({
      type: 'APPOINTMENT_CREATED',
      patientId: patient_id,
      appointmentId: result.insertId,
      time: thoi_gian_hen
    });

    res.status(201).json({ 
      message: 'Đặt lịch thành công', 
      id: result.insertId 
    });
  } catch (err) {
    res.status(500).json({ 
      message: 'Lỗi đặt lịch', 
      error: err.message 
    });
  }
};

export const listAppointments = async (req, res) => {
  try {
    const filters = {
      date: req.query.date,
      status: req.query.status
    };
    if(!isNaN(req.query.keyword) && !isNaN(parseFloat(req.query.keyword)))
    {
      filters.phone=req.query.keyword;
    }
    else{
      filters.name=req.query.keyword;
    }
    
    
    // Filter by role
    if (req.user.role === 'bacsi') {
      filters.doctor_id = req.user.id;
    }
    const appointments = await getAppointments(filters);
    console.log(appointments);
    res.json(appointments);
  } catch (err) {
    console.error('Error in listAppointments:', err);
    res.status(500).json({ 
      message: 'Lỗi lấy danh sách lịch hẹn',
      error: err.message 
    });
  }
};

export const confirmAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const appointment = await getAppointmentById(id);

    if (!appointment) {
      return res.status(404).json({ message: 'Không tìm thấy lịch hẹn' });
    }

    if (appointment.doctor_id !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Không có quyền xác nhận lịch hẹn này' });
    }

    await updateAppointmentStatus(id, 'confirmed');

    await notifyAppointment({
      type: 'APPOINTMENT_CONFIRMED',
      patientId: appointment.patient_id,
      appointmentId: appointment.id,
      time: appointment.thoi_gian_hen
    });

    res.json({ message: 'Đã xác nhận lịch hẹn' });
  } catch (err) {
    res.status(500).json({ 
      message: 'Lỗi xác nhận lịch hẹn', 
      error: err.message 
    });
  }
};

export const cancelAppointment = async (req, res) => {
  try {
    const id = req.params.id;
    const { reason } = req.body;
    const appointment = await getAppointmentById(id);

    if (!appointment) {
      return res.status(404).json({ message: 'Không tìm thấy lịch hẹn' });
    }

    await updateAppointmentStatus(id, 'cancelled', reason);

    await notifyAppointment({
      type: 'APPOINTMENT_CANCELLED',
      patientId: appointment.patient_id,
      appointmentId: appointment.id,
      time: appointment.thoi_gian_hen,
      reason
    });

    res.json({ message: 'Đã huỷ lịch hẹn' });
  } catch (err) {
    res.status(500).json({ 
      message: 'Lỗi huỷ lịch hẹn', 
      error: err.message 
    });
  }
};

// GET /api/appointments/doctor-schedule?doctor_id=...&date=...
export const getDoctorAvailability = async (req, res) => {
  try {
    const { doctor_id, date } = req.query;
    if (!doctor_id || !date) {
      return res.status(400).json({ message: 'Missing doctor_id or date' });
    }

    // Get schedule with enriched patient data
    const schedule = await getDoctorSchedule(doctor_id, date);

    // Format the response to include patient details
    const formattedSchedule = schedule.map(apt => ({
      id: apt.id,
      thoi_gian_hen: apt.thoi_gian_hen,
      lydo: apt.lydo,
      status: apt.status,
      patient_id: apt.patient_id,
      patient_name: apt.patient_name,
      patient_phone: apt.patient_phone || 'Không có SĐT',
      note: apt.note
    }));

    res.json(formattedSchedule);
  } catch (err) {
    console.error('Error in getDoctorAvailability:', err);
    res.status(500).json({ 
      message: 'Lỗi lấy lịch bác sĩ', 
      error: err.message 
    });
  }
};

// GET /api/appointments/doctor-slots?doctor_id=...&date=...
export const getDoctorAvailableSlotsApi = async (req, res) => {
  try {
    const { doctor_id, date } = req.query;
    if (!doctor_id || !date) {
      return res.status(400).json({ message: 'Missing doctor_id or date' });
    }
    const slots = await getDoctorAvailableSlots(doctor_id, date);
    res.json(slots);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy khung giờ trống', error: err.message });
  }
};

export const getAppointmentDetails = async (req, res) => {
    try {
        const id = req.params.id;
        const appointment = await getAppointmentById(id);

        if (!appointment) {
            return res.status(404).json({ 
                message: 'Không tìm thấy lịch hẹn' 
            });
        }

        // Check permissions
        if (req.user.role === 'bacsi' && appointment.doctor_id !== req.user.id) {
            return res.status(403).json({ 
                message: 'Không có quyền xem lịch hẹn này' 
            });
        }

        const formattedAppointment = {
            ...appointment,
            thoi_gian_hen: appointment.thoi_gian_hen,
            created_at: appointment.created_at,
            updated_at: appointment.updated_at,
            // Add any additional formatting here
        };

        res.json(formattedAppointment);
    } catch (err) {
        console.error('Error in getAppointmentDetails:', err);
        res.status(500).json({ 
            message: 'Lỗi lấy thông tin lịch hẹn',
            error: err.message 
        });
    }
};
