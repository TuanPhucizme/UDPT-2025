/*import db from '../db.js';

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
*/
import db from '../db.js';

export const createAppointment = async (data) => {
  const { patient_id, doctor_id, appointment_time, requested_time, note } = data;
  // chấp nhận cả appointment_time lẫn requested_time từ client
  const reqTime = requested_time || appointment_time || null;

  const [rows] = await db.query(
    `INSERT INTO appointments (patient_id, doctor_id, requested_time, appointment_time, note, status)
     VALUES (?, ?, ?, NULL, ?, 'pending')`,
    [patient_id, doctor_id, reqTime, note]
  );
  return rows;
};

export const getAppointments = async () => {
  const [rows] = await db.query(
    `SELECT * FROM appointments
     ORDER BY COALESCE(appointment_time, proposed_time, requested_time, created_at) DESC`
  );
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

/* === mới thêm === */
export const proposeTime = async (id, doctor_id, proposed_time) => {
  const [res] = await db.query(
    `UPDATE appointments
     SET proposed_time = ?, status = 'proposed', updated_at = CURRENT_TIMESTAMP
     WHERE id = ? AND doctor_id = ? AND status IN ('pending','proposed')`,
    [proposed_time, id, doctor_id]
  );
  return res;
};

export const confirmTime = async (id, doctor_id, appointment_time) => {
  const [res] = await db.query(
    `UPDATE appointments
     SET appointment_time = ?, status = 'confirmed', updated_at = CURRENT_TIMESTAMP
     WHERE id = ? AND doctor_id = ? AND status IN ('pending','proposed')`,
    [appointment_time, id, doctor_id]
  );
  return res;
};

export const declineRequest = async (id, doctor_id, reason) => {
  const [res] = await db.query(
    `UPDATE appointments
     SET status = 'declined', note = CONCAT(IFNULL(note,''), ?), updated_at = CURRENT_TIMESTAMP
     WHERE id = ? AND doctor_id = ? AND status IN ('pending','proposed')`,
    [reason ? `\n[Declined reason]: ${reason}` : '\n[Declined]', id, doctor_id]
  );
  return res;
};
