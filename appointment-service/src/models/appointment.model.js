import db from '../db.js';

export const createAppointment = async (data) => {
  const { patient_id, doctor_id, appointment_time, note } = data;
  const [rows] = await db.query(
    'INSERT INTO appointments (patient_id, doctor_id, appointment_time, note) VALUES (?, ?, ?, ?)',
    [patient_id, doctor_id, appointment_time, note]
  );
  return rows;
};

export const getAppointments = async () => {
  const [rows] = await db.query('SELECT * FROM appointments ORDER BY appointment_time DESC');
  return rows;
};

export const updateAppointmentStatus = async (id, status) => {
  const [rows] = await db.query('UPDATE appointments SET status = ? WHERE id = ?', [status, id]);
  return rows;
};

export const getAppointmentById = async (id) => {
  const [rows] = await db.query('SELECT * FROM appointments WHERE id = ?', [id]);
  return rows[0];
};
