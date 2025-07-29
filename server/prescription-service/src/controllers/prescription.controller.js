import {
  createPrescription,
  updatePrescriptionStatus,
  getPrescriptionsByPatient
} from '../models/prescription.model.js';
import sendNotification from '../utils/sendNotification.js';
export const create = async (req, res) => {
  try {
    const { record_id, patient_id, doctor_id, medicines } = req.body;
    const result = await createPrescription({ record_id, patient_id, doctor_id, medicines });

    // 🔔 Gửi thông báo đến notification-service
    await sendNotification({
      type: 'prescription',
      patientId: patient_id,
      message: `Bác sĩ đã kê đơn thuốc mới cho bạn.`
    });

    res.status(201).json({ message: 'Tạo đơn thuốc thành công', id: result.insertId });
  } catch (err) {
    res.status(500).json({ message: 'Lỗi tạo đơn thuốc', error: err.message });
  }
};

export const updateStatus = async (req, res) => {
  try {
    const { id } = req.params;
    const { status } = req.body;
    await updatePrescriptionStatus(id, status);

    // 🔔 Gửi thông báo khi đơn thuốc được đánh dấu là "đã lấy"
    if (status === 'collected') {
      await sendNotification({
        type: 'prescription',
        patientId: patient_id,
        message: `Bạn đã nhận đơn thuốc thành công. Chúc bạn mau khỏe!`
      });
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
