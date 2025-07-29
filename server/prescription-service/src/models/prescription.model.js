import db from '../db.js';

export const createPrescription = async (data) => {
  const { record_id, patient_id, doctor_id, medicines } = data;
  const [result] = await db.query(
    `INSERT INTO prescriptions (record_id, patient_id, doctor_id, medicines)
     VALUES (?, ?, ?, ?)`,
    [record_id, patient_id, doctor_id, JSON.stringify(medicines)]
  );
  return result;
};

export const updatePrescriptionStatus = async (id, status) => {
  const [result] = await db.query(
    'UPDATE prescriptions SET status = ? WHERE id = ?',
    [status, id]
  );
  return result;
};

export const getPrescriptionsByPatient = async (patient_id) => {
  const [rows] = await db.query(
    'SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC',
    [patient_id]
  );
  return rows;
};
