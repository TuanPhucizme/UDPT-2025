import {
  createPatient,
  getAllPatients,
  getPatientById,
  updatePatient,
} from '../models/patient.model.js';

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
