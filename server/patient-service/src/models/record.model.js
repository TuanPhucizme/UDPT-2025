import db from '../db.js';
import axios from 'axios';
import services from '../config/services.js';

export const createMedicalRecord = async (data) => {
  const { 
    patient_id, 
    doctor_id, 
    department_id,
    ngaykham,
    lydo,
    chan_doan,
    ngay_taikham,
    ghichu 
  } = data;

  // Verify doctor and department exist in auth service
  try {
    const [doctorResponse, departmentResponse] = await Promise.all([
      axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      }),
      axios.get(`${services.AUTH_SERVICE_URL}/api/departments/${department_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      })
    ]);

    if (!doctorResponse.data || !departmentResponse.data) {
      throw new Error('Invalid doctor or department ID');
    }
  } catch (error) {
    throw new Error(`Failed to verify doctor/department: ${error.message}`);
  }

  const [result] = await db.query(
    `INSERT INTO medical_records (
      patient_id, 
      doctor_id,
      department_id,
      ngaykham,
      lydo,
      chan_doan,
      ngay_taikham,
      ghichu
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
    [patient_id, doctor_id, department_id, ngaykham, lydo, chan_doan, ngay_taikham, ghichu]
  );

  return result;
};

export const getMedicalRecordsByPatient = async (patientId) => {
  // First get the medical records
  const [records] = await db.query(
    `SELECT 
      mr.*
    FROM medical_records mr
    WHERE mr.patient_id = ? 
    ORDER BY mr.ngaykham DESC`,
    [patientId]
  );

  // Get patient info from local database
  const [patients] = await db.query(
    'SELECT * FROM patients WHERE id = ?',
    [patientId]
  );

  if (patients.length === 0) {
    throw new Error('Patient not found');
  }

  // Enrich records with data from other services
  const enrichedRecords = await Promise.all(
    records.map(async (record) => {
      try {
        // Get doctor and department info from auth service
        const [doctorResponse, departmentResponse] = await Promise.all([
          axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${record.doctor_id}`, {
            headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
          }),
          axios.get(`${services.AUTH_SERVICE_URL}/api/departments/${record.department_id}`, {
            headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
          })
        ]);

        return {
          ...record,
          patient_name: patients[0].hoten_bn,
          patient_dob: patients[0].dob,
          patient_gender: patients[0].gender,
          doctor_name: doctorResponse.data.hoten_nv,
          department_name: departmentResponse.data.ten_ck
        };
      } catch (error) {
        console.error('Failed to fetch related data:', error);
        return record;
      }
    })
  );

  return enrichedRecords;
};

export const getMedicalRecordDetails = async (recordId) => {
  try {
    // Get record data
    const [records] = await db.query(
      'SELECT * FROM medical_records WHERE id = ?',
      [recordId]
    );

    if (records.length === 0) {
      return null;
    }

    const record = records[0];

    // Get patient info from local database
    const [patients] = await db.query(
      'SELECT * FROM patients WHERE id = ?',
      [record.patient_id]
    );

    if (patients.length === 0) {
      throw new Error('Patient not found');
    }

    // Get doctor and department info from auth service
    const [doctorResponse, departmentResponse] = await Promise.all([
      axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${record.doctor_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      }),
      axios.get(`${services.AUTH_SERVICE_URL}/api/departments/${record.department_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      })
    ]);

    const enrichedRecord = {
      ...record,
      patient_name: patients[0].hoten_bn,
      patient_dob: patients[0].dob,
      patient_gender: patients[0].gender,
      patient_address: patients[0].diachi,
      patient_phone: patients[0].sdt,
      doctor_name: doctorResponse.data.hoten_nv,
      department_name: departmentResponse.data.ten_ck
    };

    // Format dates
    if (enrichedRecord.ngaykham) {
      enrichedRecord.ngaykham = new Date(enrichedRecord.ngaykham);
    }
    if (enrichedRecord.ngay_taikham) {
      enrichedRecord.ngay_taikham = new Date(enrichedRecord.ngay_taikham);
    }
    if (enrichedRecord.created_at) {
      enrichedRecord.created_at = new Date(enrichedRecord.created_at);
    }

    return enrichedRecord;
  } catch (error) {
    console.error('Error in getMedicalRecordDetails:', error);
    throw error;
  }
};

export const updateMedicalRecord = async (recordId, data) => {
  const {
    chan_doan,
    ngay_taikham,
    ghichu
  } = data;

  const [result] = await db.query(
    `UPDATE medical_records 
     SET chan_doan = ?,
         ngay_taikham = ?,
         ghichu = ?
     WHERE id = ?`,
    [chan_doan, ngay_taikham, ghichu, recordId]
  );

  return result;
};

export const autoCreateRecord = async (data) => {
  const { 
    patient_id, 
    doctor_id, 
    department_id,
    ngaykham,
    lydo 
  } = data;

  try {
    // Verify doctor and department exist in auth service
    const [doctorResponse, departmentResponse] = await Promise.all([
      axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      }),
      axios.get(`${services.AUTH_SERVICE_URL}/api/departments/${department_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      })
    ]);

    if (!doctorResponse.data || !departmentResponse.data) {
      throw new Error('Invalid doctor or department ID');
    }

    // Create initial empty medical record
    const [result] = await db.query(
      `INSERT INTO medical_records (
        patient_id, 
        doctor_id,
        department_id,
        ngaykham,
        lydo,
        status,
        created_at
      ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())`,
      [patient_id, doctor_id, department_id, ngaykham, lydo]
    );

    console.log('✅ Created initial medical record:', result.insertId);
    return result;

  } catch (error) {
    console.error('Error in autoCreateRecord:', error);
    throw new Error(`Failed to create initial medical record: ${error.message}`);
  }
};
