import db from '../db.js';
import services from '../config/services.js';
import { serviceCall } from '../utils/serviceCall.js';

export const createPrescription = async (data) => {
  const { record_id, patient_id, doctor_id, medicines } = data;
  
  try {
    // Use internal endpoints
    const doctor = await serviceCall(
      `${services.AUTH_SERVICE_URL}/api/internal/staff/${doctor_id}`
    );
    
    const patient = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/internal/patients/${patient_id}`
    );

    // Create prescription
    const [result] = await db.query(
      `INSERT INTO prescriptions (
        record_id, 
        patient_id, 
        doctor_id, 
        medicines,
        status,
        created_at
      ) VALUES (?, ?, ?, ?, 'pending', NOW())`,
      [record_id, patient_id, doctor_id, JSON.stringify(medicines)]
    );

    return result;
  } catch (error) {
    console.error('Error in createPrescription:', error);
    throw error;
  }
};

export const getPrescriptionsByPatient = async (patient_id) => {
  try {
    // Get prescriptions from local database
    const [prescriptions] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p 
      WHERE p.patient_id = ? 
      ORDER BY p.created_at DESC`,
      [patient_id]
    );

    // Get patient info
    const patient = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/patients/${patient_id}`
    );

    // Enrich prescriptions with patient and staff info
    const enrichedPrescriptions = await Promise.all(
      prescriptions.map(async (prescription) => {
        try {
          const doctor = await serviceCall(
            `${services.AUTH_SERVICE_URL}/api/staff/${prescription.doctor_id}`
          );

          return {
            ...prescription,
            medicines: JSON.parse(prescription.medicines),
            patient_name: patient.hoten_bn,
            doctor_name: doctor.hoten_nv,
            department: doctor.department?.ten_ck || 'N/A'
          };
        } catch (error) {
          console.error(`Failed to fetch related data: ${error.message}`);
          return prescription;
        }
      })
    );

    return enrichedPrescriptions;
  } catch (error) {
    console.error('Error in getPrescriptionsByPatient:', error);
    throw error;
  }
};

export const updatePrescriptionStatus = async (id, status, pharmacist_id) => {
  try {
    // Verify pharmacist exists
    const pharmacistResponse = await axios.get(
      `${services.AUTH_SERVICE_URL}/api/staff/${pharmacist_id}`,
      {
        headers: {
          'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}`
        }
      }
    );

    if (!pharmacistResponse.data) {
      throw new Error('Pharmacist not found');
    }

    const [result] = await db.query(
      `UPDATE prescriptions 
       SET status = ?, 
           pharmacist_id = ?,
           updated_at = NOW() 
       WHERE id = ?`,
      [status, pharmacist_id, id]
    );

    return result;
  } catch (error) {
    console.error('Error in updatePrescriptionStatus:', error);
    throw error;
  }
};

export const getPrescriptionById = async (id) => {
  try {
    const [rows] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p 
      WHERE p.id = ?`,
      [id]
    );

    if (!rows[0]) return null;

    const prescription = rows[0];

    // Get related data in parallel
    const [patientResponse, doctorResponse, pharmacistResponse] = await Promise.all([
      axios.get(`${services.PATIENT_SERVICE_URL}/api/patients/${prescription.patient_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      }),
      axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.doctor_id}`, {
        headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
      }),
      prescription.pharmacist_id ? 
        axios.get(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`, {
          headers: { 'Authorization': `Bearer ${process.env.INTERNAL_API_TOKEN}` }
        }) : Promise.resolve({ data: null })
    ]);

    return {
      ...prescription,
      medicines: JSON.parse(prescription.medicines),
      patient_name: patientResponse.data.hoten_bn,
      doctor_name: doctorResponse.data.hoten_nv,
      pharmacist_name: pharmacistResponse.data?.hoten_nv || null,
      department: doctorResponse.data.department?.ten_ck || 'N/A'
    };
  } catch (error) {
    console.error('Error in getPrescriptionById:', error);
    throw error;
  }
};

export const getPrescriptionsByRecordId = async (recordId) => {
  try {
    const [prescriptions] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at
      FROM prescriptions p 
      WHERE p.record_id = ? 
      ORDER BY p.created_at DESC`,
      [recordId]
    );

    // Enrich prescriptions with staff info
    const enrichedPrescriptions = await Promise.all(
      prescriptions.map(async (prescription) => {
        try {
          const doctor = await serviceCall(
            `${services.AUTH_SERVICE_URL}/api/internal/staff/${prescription.doctor_id}`
          );

          const pharmacist = prescription.pharmacist_id ? 
            await serviceCall(
              `${services.AUTH_SERVICE_URL}/api/internal/staff/${prescription.pharmacist_id}`
            ) : null;

          return {
            ...prescription,
            medicines: JSON.parse(prescription.medicines),
            doctor_name: doctor?.hoten_nv || 'Unknown',
            pharmacist_name: pharmacist?.hoten_nv || null
          };
        } catch (error) {
          console.error(`Failed to fetch staff data: ${error.message}`);
          return prescription;
        }
      })
    );

    return enrichedPrescriptions;
  } catch (error) {
    console.error('Error in getPrescriptionsByRecordId:', error);
    throw error;
  }
};
