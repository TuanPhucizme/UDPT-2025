import e from 'express';
import {
  createPatient,
  getAllPatients,
  getPatientById,
  updatePatient,
} from '../models/patient.model.js';
import db from '../db.js'; // Add this import at the top if not present

export const registerPatient = async (req, res) => {
  try {
    const result = await createPatient(req.body);
    res.status(201).json({ message: 'Thêm bệnh nhân thành công', id: result.insertId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi tạo bệnh nhân', error: err.message });
  }
};

export const getPatients = async (req, res) => {
  const filters = {
    name: req.query.name,
    gender: req.query.gender,
    phone:req.query.phone,
    age: req.query.age ? parseInt(req.query.age) : undefined
  };
  const patients = await getAllPatients(filters);
  res.json(patients);
};


export const getPatient = async (req, res) => {
  const id = req.params.id;
  const patient = await getPatientById(id);
  if (!patient) return res.status(404).json({ message: 'Không tìm thấy' });
  res.json(patient);
};

export const updatePatientInfo = async (req, res) => {
  const id = req.params.id;
  await updatePatient(id, req.body);
  res.json({ message: 'Cập nhật thành công' });
};

export const getPatientRecordIds = async (req, res) => {
  console.log('getPatientRecordIds called with params:', req.params);
  try {
    const patientId = req.params.id;

    // Validate patientId
    if (!patientId || isNaN(patientId)) {
      return res.status(400).json({ message: 'Invalid patient ID' });
    }

    // Get just the record IDs for this patient
    const [records] = await db.query(
      'SELECT id FROM medical_records WHERE patient_id = ?',
      [patientId]
    );

    // Extract just the IDs
    const recordIds = records.map(record => record.id);
    console.log(`Found ${recordIds.length} records for patient ID ${patientId}`);
    res.json({
      patient_id: patientId,
      recordIds
    });
  } catch (error) {
    console.error('Error in getPatientRecordIds:', error);
    res.status(500).json({
      message: 'Error retrieving patient record IDs',
      error: error.message
    });
  }
};
