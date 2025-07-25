import db from '../db.js';

export const createMedicalRecord = async (data) => {
  const { patient_id, diagnosis, treatment, doctor_name } = data;
  const [rows] = await db.query(
    'INSERT INTO medical_records (patient_id, diagnosis, treatment, doctor_name) VALUES (?, ?, ?, ?)',
    [patient_id, diagnosis, treatment, doctor_name]
  );
  return rows;
};

export const getMedicalRecordsByPatient = async (patientId) => {
  const [rows] = await db.query(
    'SELECT * FROM medical_records WHERE patient_id = ? ORDER BY visit_date DESC',
    [patientId]
  );
  return rows;
};

export const autoCreateRecord = async (data) => {
  const { appointment_id, patient_id, doctor_id, visit_date } = data;
  const [rows] = await db.query(
    `INSERT INTO medical_records (appointment_id, patient_id, doctor_id, visit_date)
     VALUES (?, ?, ?, ?)`,
    [appointment_id, patient_id, doctor_id, visit_date]
  );
  return rows;
};
