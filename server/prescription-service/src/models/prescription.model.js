import db from '../db.js';
import services from '../config/services.js';
import { serviceCall } from '../utils/serviceCall.js';

export const createPrescription = async (data) => {
  const { record_id, patient_id, doctor_id, medicines } = data;
  
  try {
    const [doctor, patient] = await Promise.all([
      serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`),
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${patient_id}`)
    ]);

    if (!doctor || !patient) {
      throw new Error('Invalid doctor or patient ID');
    }

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
    const [prescriptions, patient] = await Promise.all([
      db.query(
        `SELECT 
          p.*,
          DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
          DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
        FROM prescriptions p 
        WHERE p.patient_id = ? 
        ORDER BY p.created_at DESC`,
        [patient_id]
      ),
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${patient_id}`)
    ]);

    const enrichedPrescriptions = await Promise.all(
      prescriptions[0].map(async (prescription) => {
        const doctor = await serviceCall(
          `${services.AUTH_SERVICE_URL}/api/staff/${prescription.doctor_id}`
        );

        return {
          ...prescription,
          medicines: JSON.parse(prescription.medicines),
          patient_name: patient?.hoten_bn || 'Unknown',
          doctor_name: doctor?.hoten_nv || 'Unknown',
          department: doctor?.department?.ten_ck || 'N/A'
        };
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
    if (pharmacist_id) {
      const pharmacist = await serviceCall(
        `${services.AUTH_SERVICE_URL}/api/staff/${pharmacist_id}`
      );
      if (!pharmacist) {
        throw new Error('Pharmacist not found');
      }
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
    
    const [patient, doctor, pharmacist] = await Promise.all([
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${prescription.patient_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.doctor_id}`),
      prescription.pharmacist_id ? 
        serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`) : 
        null
    ]);

    return {
      ...prescription,
      medicines: JSON.parse(prescription.medicines),
      patient_name: patient?.hoten_bn || 'Unknown',
      doctor_name: doctor?.hoten_nv || 'Unknown',
      pharmacist_name: pharmacist?.hoten_nv || null,
      department: doctor?.department?.ten_ck || 'N/A'
    };
  } catch (error) {
    console.error('Error in getPrescriptionById:', error);
    throw error;
  }
};

export const getPrescriptionsByRecordId = async (recordId) => {
  try {
    // Get prescriptions with medicine details
    const [prescriptions] = await db.query(
      `SELECT 
        p.*,
        m.ten_thuoc as medicine_name,
        m.don_vi as medicine_unit,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at
      FROM prescriptions p 
      JOIN medicines m ON p.medicine_id = m.id
      WHERE p.record_id = ? 
      ORDER BY p.created_at DESC`,
      [recordId]
    );

    // Enrich prescriptions with staff info
    const enrichedPrescriptions = await Promise.all(
      prescriptions.map(async (prescription) => {
        try {
          const pharmacist = prescription.pharmacist_id ? 
            await serviceCall(
              `${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`
            ) : null;

          return {
            id: prescription.id,
            medicine: {
              id: prescription.medicine_id,
              name: prescription.medicine_name,
              unit: prescription.medicine_unit,
              dosage: prescription.lieu,
              frequency: prescription.thoigian,
              note: prescription.ghichu
            },
            status: prescription.status,
            pharmacist_name: pharmacist?.hoten_nv || null,
            created_at: prescription.created_at
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

// Add this method
export const getAllMedicines = async () => {
  try {
    const [rows] = await db.query(
      `SELECT 
        id, 
        ten_thuoc, 
        don_vi, 
        gia,
        huong_dan_su_dung,
        tac_dung_phu
      FROM medicines
      ORDER BY ten_thuoc ASC`
    );
    return rows;
  } catch (error) {
    console.error('Error in getAllMedicines:', error);
    throw error;
  }
};
