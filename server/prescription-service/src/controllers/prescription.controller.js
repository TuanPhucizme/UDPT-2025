import {
  createPrescription,
  updatePrescriptionStatus,
  getPrescriptionsByPatient,
  getPrescriptionById,
  getPrescriptionsByRecordId,
  getAllMedicines,
  getPrescriptionsByStatus,
  getAllPrescriptions
} from '../models/prescription.model.js';

import {
  notifyPrescriptionCreated,
  notifyPrescriptionDispensed
} from '../utils/notifications.js';

export const create = async (req, res) => {
  try {
    const { record_id, doctor_id, medicines } = req.body;
    
    if (!record_id || !doctor_id || !medicines || !Array.isArray(medicines) || medicines.length === 0) {
      return res.status(400).json({ 
        message: 'Thiếu thông tin: record_id, doctor_id, medicines là bắt buộc' 
      });
    }
    
    const result = await createPrescription({ record_id, doctor_id, medicines });

    const prescriptionId = result.insertId;
    const patientId = result.patient_id;

    // Notify about prescription creation
    await notifyPrescriptionCreated({
      patientId,
      prescriptionId,
      medicines
    });

    // Include information about auto-added notes if any
    const autoNotes = result.autoNotesAdded || false;

    res.status(201).json({ 
      message: 'Tạo đơn thuốc thành công' + (autoNotes ? ' (Đã tự động thêm hướng dẫn sử dụng cho thuốc dạng chai)' : ''), 
      id: prescriptionId,
      autoNotesAdded: autoNotes
    });
  } catch (err) {
    console.error('Error creating prescription:', err);
    res.status(500).json({ 
      message: 'Lỗi tạo đơn thuốc', 
      error: err.message 
    });
  }
};

export const updateStatus = async (req, res) => {
  try {
    const { id } = req.params;
    const { status, pharmacist_id } = req.body;
    
    // Validate inputs
    if (!status || !['pending', 'dispensed', 'cancelled'].includes(status)) {
      return res.status(400).json({ 
        message: 'Trạng thái không hợp lệ' 
      });
    }
    
    // Require pharmacist_id for dispensed status
    if (status === 'dispensed' && !pharmacist_id) {
      return res.status(400).json({
        message: 'Pharmacist ID is required to dispense medications'
      });
    }
    
    const result = await updatePrescriptionStatus(id, status, pharmacist_id);

    // Generate appropriate response message
    let message = 'Cập nhật trạng thái đơn thuốc thành công';
    if (result.reduced_stock) {
      message += ' và đã cập nhật số lượng thuốc trong kho';
    }

    // Send notification if dispensed
    if (status === 'dispensed' && result.patient_id) {
      // Notify that prescription has been dispensed
      await notifyPrescriptionDispensed({
        patientId: result.patient_id,
        prescriptionId: id,
        pharmacistId: pharmacist_id,
        stockUpdated: result.reduced_stock
      });
    }

    res.json({ 
      message,
      stock_updated: result.reduced_stock,
      pharmacist_id: result.pharmacist_id
    });
  } catch (err) {
    console.error('Error updating prescription status:', err);
    res.status(500).json({ 
      message: 'Lỗi cập nhật trạng thái', 
      error: err.message 
    });
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

// Add these controller methods
export const getMedicines = async (req, res) => {
  try {
    const medicines = await getAllMedicines();
    res.json(medicines);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy danh sách thuốc', error: err.message });
  }
};

export const getById = async (req, res) => {
  try {
    const { id } = req.params;
    const prescription = await getPrescriptionById(id);
    
    if (!prescription) {
      return res.status(404).json({ message: 'Không tìm thấy đơn thuốc' });
    }
    
    res.json(prescription);
  } catch (err) {
    res.status(500).json({ message: 'Lỗi lấy thông tin đơn thuốc', error: err.message });
  }
};

export const getByStatus = async (req, res) => {
  try {
    const { status } = req.params;
    
    // Validate status parameter
    if (!status || !['pending', 'dispensed', 'cancelled'].includes(status)) {
      return res.status(400).json({ 
        message: 'Trạng thái không hợp lệ. Vui lòng sử dụng một trong các giá trị: pending, dispensed, cancelled' 
      });
    }
    
    const prescriptions = await getPrescriptionsByStatus(status);
    
    res.json(prescriptions);
  } catch (error) {
    console.error('Error in getByStatus:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy danh sách đơn thuốc', 
      error: error.message 
    });
  }
};
export const getAll = async (req, res) => {
  try {
    // Extract query parameters for filtering
    const { 
      status, 
      record_id, 
      start_date, 
      end_date, 
      limit = 100, 
      offset = 0 
    } = req.query;
    
    // Build filters object
    const filters = {};
    if (status) filters.status = status;
    if (record_id) filters.record_id = record_id;
    if (start_date) filters.start_date = start_date;
    if (end_date) filters.end_date = end_date;
    if (limit) filters.limit = limit;
    if (offset) filters.offset = offset;
    
    const prescriptions = await getAllPrescriptions(filters);
    
    res.json(prescriptions);
  } catch (error) {
    console.error('Error in getAll:', error);
    res.status(500).json({ 
      message: 'Lỗi lấy danh sách đơn thuốc', 
      error: error.message 
    });
  }
};