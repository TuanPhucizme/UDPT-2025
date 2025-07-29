import { createMedicalRecord, getMedicalRecordsByPatient } from '../models/record.model.js';

export const addMedicalRecord = async (req, res) => {
  try {
    const result = await createMedicalRecord(req.body);
    res.status(201).json({ message: 'Thêm hồ sơ bệnh án thành công', id: result.insertId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi tạo hồ sơ bệnh án', error: err.message });
  }
};

export const getRecordsByPatient = async (req, res) => {
  try {
    const { patientId } = req.params;
    const records = await getMedicalRecordsByPatient(patientId);
    res.json(records);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi khi lấy hồ sơ', error: err.message });
  }
};
