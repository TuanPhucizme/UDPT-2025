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
  try {
    const id = req.params.id;
    
    // First, get the original patient record to check phone number
    const originalPatient = await getPatientById(id, { adminMode: true });
    
    if (!originalPatient) {
      return res.status(404).json({ message: 'Patient not found' });
    }
    
    // Prepare data for update
    const updateData = { ...req.body };
    // Handle phone number cases:
    // 1. If the phone in the request is encoded (like 'xxxxxxxxx123'), use the original
    // 2. If the phone is explicitly changed (non-encoded), use the new value
    // 3. If phone_changed flag is present, ensure we use the new phone number
    if (updateData.sdt) {
      const isPhoneEncoded = updateData.sdt && updateData.sdt.match(/^x+\d{3}$/);
      
      if (isPhoneEncoded && !updateData.phone_changed) {
        // If the phone appears encoded and not explicitly changed, 
        // use the original phone from the database
        updateData.sdt = originalPatient.sdt;
      }
    }
    
    // Remove the phone_changed flag as it's not needed in the database
    delete updateData.phone_changed;
    
    // Update the patient
    await updatePatient(id, updateData);
    res.json({ message: 'Cập nhật thành công' });
  } catch (err) {
    console.error('Error updating patient:', err);
    res.status(500).json({ message: 'Error updating patient', error: err.message });
  }
};

export const getPatientRecordIds = async (req, res) => {
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
