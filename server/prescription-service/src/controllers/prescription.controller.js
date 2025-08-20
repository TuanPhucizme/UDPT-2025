import {
  createPrescription,
  updatePrescriptionStatus,
  getPrescriptionsByPatient,
  getPrescriptionById,
  getPrescriptionsByRecordId
} from '../models/prescription.model.js';

import {
  notifyPrescriptionCreated,
  notifyPrescriptionDispensed
} from '../utils/notifications.js';

export const create = async (req, res) => {
  try {
    const { record_id, patient_id, doctor_id, medicines } = req.body;
    const result = await createPrescription({ record_id, patient_id, doctor_id, medicines });

    const prescriptionId = result.insertId;

    // Gửi notify: đơn thuốc đã tạo
    await notifyPrescriptionCreated({
      patientId: patient_id,
      prescriptionId,
      medicines
    });

    res.status(201).json({ message: 'Tạo đơn thuốc thành công', id: prescriptionId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi tạo đơn thuốc', error: err.message });
  }
};

export const updateStatus = async (req, res) => {
  try {
    const { id } = req.params;
    const { status } = req.body; // 'dispensed' | 'pending' | 'canceled'
    await updatePrescriptionStatus(id, status);

    if (status === 'dispensed') {
      // Lấy chi tiết để có patient_id
      const p = await getPrescriptionById(id);
      if (p) {
        await notifyPrescriptionDispensed({
          patientId: p.patient_id,
          prescriptionId: p.id,
          pharmacistId: req.user?.id // nếu middleware gắn id user
        });
      }
    }

    res.json({ message: 'Cập nhật trạng thái đơn thuốc thành công' });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi cập nhật trạng thái', error: err.message });
  }
};

export const getByPatient = async (req, res) => {
  const patient_id = req.params.patient_id;
  const prescriptions = await getPrescriptionsByPatient(patient_id);
  res.json(prescriptions);
};

/**
 * Get prescriptions by medical record ID (internal service route)
 */
export const getByRecordId = async (req, res) => {
  try {
    const recordId = req.params.record_id;
    const prescriptions = await getPrescriptionsByRecordId(recordId);
    res.json(prescriptions);
  } catch (error) {
    console.error('Error in getByRecordId:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy thông tin đơn thuốc', 
      error: error.message 
    });
  }
};
