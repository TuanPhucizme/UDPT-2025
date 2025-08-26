import { 
    getMedicalRecordsByPatient,
    createMedicalRecord,
    getMedicalRecordDetails,
    updateMedicalRecord
} from '../models/record.model.js';
import axios from 'axios';
import services from '../config/services.js';

export const getPatientRecords = async (req, res) => {
  try {
    const patientId = req.params.id;
    
    // Get medical records with department and doctor info
    const records = await getMedicalRecordsByPatient(patientId);

    // Fetch prescription details for each record
    const recordsWithPrescriptions = await Promise.all(
      records.map(async (record) => {
        try {
          const prescriptionResponse = await axios.get(
            `${services.PRESCRIPTION_SERVICE_URL}/api/prescriptions/record/${record.id}`,
            {
              headers: {
                'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
              }
            }
          );
          
          return {
            ...record,
            prescriptions: prescriptionResponse.data
          };
        } catch (error) {
          return {
            ...record,
            prescriptions: []
          };
        }
      })
    );

    res.json(recordsWithPrescriptions);
  } catch (error) {
    res.status(500).json({ 
      message: 'Internal server error', 
      error: error.message 
    });
  }
};

export const createRecord = async (req, res) => {
  try {
    const {
      patient_id,
      doctor_id,
      department_id,
      lydo,
      chan_doan,
      ngay_taikham,
      ghichu
    } = req.body;

    // Verify patient exists
    const patientExists = await checkPatientExists(patient_id);
    if (!patientExists) {
      return res.status(400).json({ message: 'Invalid patient ID' });
    }

    // Verify doctor exists in auth service
    try {
      await axios.get(
        `${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`,
        {
          headers: {
            'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
          }
        }
      );
    } catch (error) {
      return res.status(400).json({ message: 'Invalid doctor ID' });
    }

    const recordData = {
      patient_id,
      doctor_id,
      department_id,
      ngaykham: new Date(),
      lydo,
      chan_doan,
      ngay_taikham,
      ghichu
    };

    const result = await createMedicalRecord(recordData);

    // Notify appointment service to update appointment status if exists
    try {
      await axios.post(
        `${services.NOTIFICATION_SERVICE_URL}/api/notifications`,
        {
          type: 'MEDICAL_RECORD_CREATED',
          patient_id,
          doctor_id,
          record_id: result.insertId
        },
        {
          headers: {
            'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
          }
        }
      );
    } catch (error) {
      console.error('Failed to send notification:', error);
    }

    res.status(201).json({
      message: 'Medical record created successfully',
      id: result.insertId
    });
  } catch (error) {
    console.error('Error in createRecord:', error);
    res.status(500).json({ 
      message: 'Failed to create medical record', 
      error: error.message 
    });
  }
};

export const updateRecord = async (req, res) => {
    try {
        const recordId = req.params.id;
        const {
            chan_doan,
            dieu_tri,
            ngay_taikham,
            ghichu,
            status
        } = req.body;

        // Get existing record
        const existingRecord = await getMedicalRecordDetails(recordId);
        if (!existingRecord) {
            return res.status(404).json({ message: 'Không tìm thấy hồ sơ bệnh án' });
        }

        // Only allow updates if record status is not 'completed'
        if (existingRecord.status === 'completed') {
            return res.status(400).json({ 
                message: 'Không thể cập nhật hồ sơ đã hoàn thành' 
            });
        }

        // Verify doctor is the one who created the record
        if (existingRecord.doctor_id !== req.user.id) {
            return res.status(403).json({ 
                message: 'Chỉ bác sĩ tạo hồ sơ mới được cập nhật' 
            });
        }

        const updateData = {
            chan_doan,
            dieu_tri,
            ngay_taikham,
            ghichu,
            status: status || existingRecord.status,
            updated_at: new Date()
        };

        await updateMedicalRecord(recordId, updateData);

        // Send notification if status changed to completed
        if (status === 'completed' && existingRecord.status !== 'completed') {
            try {
                await axios.post(
                    `${services.NOTIFICATION_SERVICE_URL}/api/notifications`,
                    {
                        type: 'MEDICAL_RECORD_COMPLETED',
                        patient_id: existingRecord.patient_id,
                        doctor_id: existingRecord.doctor_id,
                        record_id: recordId
                    },
                    {
                        headers: {
                            'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
                        }
                    }
                );
            } catch (error) {
                console.error('Failed to send completion notification:', error);
            }
        }

        res.json({ 
            message: 'Cập nhật hồ sơ bệnh án thành công',
            id: recordId 
        });
    } catch (error) {
        console.error('Error in updateRecord:', error);
        res.status(500).json({ 
            message: 'Lỗi cập nhật hồ sơ bệnh án', 
            error: error.message 
        });
    }
};

export const getRecordDetails = async (req, res) => {
    try {
        const recordId = req.params.id;
        const record = await getMedicalRecordDetails(recordId);

        if (!record) {
            return res.status(404).json({ 
                message: 'Không tìm thấy hồ sơ bệnh án' 
            });
        }

        // Fetch prescriptions and format dates
        const enrichedRecord = {
            ...record,
            ngaykham: new Date(record.ngaykham).toISOString(),
            ngay_taikham: record.ngay_taikham ? 
                new Date(record.ngay_taikham).toISOString() : null,
            prescriptions: []
        };

        try {
            const prescriptionResponse = await axios.get(
                `${services.PRESCRIPTION_SERVICE_URL}/api/prescriptions/record/${recordId}`,
                {
                    headers: {
                        'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
                    }
                }
            );
            
            enrichedRecord.prescriptions = prescriptionResponse.data;
        } catch (error) {
            console.error('Failed to fetch prescriptions:', error);
        }
        console.log('Enriched Record:', enrichedRecord);
        res.json(enrichedRecord);
    } catch (error) {
        console.error('Error in getRecordDetails:', error);
        res.status(500).json({ 
            message: 'Lỗi lấy thông tin hồ sơ bệnh án', 
            error: error.message 
        });
    }
};

const checkPatientExists = async (patientId) => {
  try {
    const [rows] = await db.query(
      'SELECT id FROM patients WHERE id = ?',
      [patientId]
    );
    return rows.length > 0;
  } catch (error) {
    console.error('Error checking patient:', error);
    return false;
  }
};
