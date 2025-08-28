import db from '../db.js';
import services from '../config/services.js';
import { serviceCall } from '../utils/serviceCall.js';

export const createPrescription = async (data) => {
  const { record_id, doctor_id, medicines } = data;
  const conn = await db.getConnection();
  
  try {
    await conn.beginTransaction();

    // Get patient ID from the record
    const record = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${record_id}`
    );

    if (!record) {
      throw new Error('Invalid record ID');
    }

    // Verify doctor exists
    const doctor = await serviceCall(
      `${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`
    );

    if (!doctor) {
      throw new Error('Invalid doctor ID');
    }

    // Create prescription
    const [result] = await conn.query(
      `INSERT INTO prescriptions (
        record_id,
        status,
        created_at
      ) VALUES (?, 'pending', NOW())`,
      [record_id]
    );

    const prescriptionId = result.insertId;

    // Add medicines to prescription
    for (const medicine of medicines) {
      // Get medicine details to check unit
      const [medicineDetails] = await conn.query(
        `SELECT ten_thuoc, don_vi FROM medicines WHERE id = ?`,
        [medicine.id]
      );
      
      if (!medicineDetails.length) {
        throw new Error(`Medicine with ID ${medicine.id} not found`);
      }
      
      const medicineUnit = medicineDetails[0].don_vi.toLowerCase();
      
      // Auto-add bottle usage note if not provided
      let note = medicine.note || null;
      if (medicineUnit === 'chai' && (!note || note.trim() === '')) {
        note = 'Tham khảo hướng dẫn sử dụng trên chai';
      }
      
      await conn.query(
        `INSERT INTO prescription_medicines (
          prescription_id,
          medicine_id,
          dose,
          frequency,
          duration,
          note
        ) VALUES (?, ?, ?, ?, ?, ?)`,
        [
          prescriptionId,
          medicine.id,
          medicine.dosage,
          medicine.frequency,
          medicine.duration,
          note
        ]
      );
    }

    await conn.commit();

    return {
      insertId: prescriptionId,
      patient_id: record.patient_id // Return patient_id for notifications
    };
  } catch (error) {
    await conn.rollback();
    console.error('Error in createPrescription:', error);
    throw error;
  } finally {
    conn.release();
  }
};

export const getPrescriptionsByPatient = async (patient_id) => {
  try {
    // Get all prescriptions from this patient from the patient service
    const patientRecords = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/patients/internal/${patient_id}/record-ids`
    );
    
    // If no records, return empty array
    if (!patientRecords || !patientRecords.recordIds || !patientRecords.recordIds.length) {
      return [];
    }
    
    // Get prescriptions for these record IDs
    const recordIds = patientRecords.recordIds;
    const [rows] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at,
        m.id as medicine_id,
        m.ten_thuoc as medicine_name,
        m.don_vi as medicine_unit,
        pm.dose, 
        pm.frequency, 
        pm.duration, 
        pm.note
      FROM prescriptions p
      JOIN prescription_medicines pm ON p.id = pm.prescription_id
      JOIN medicines m ON pm.medicine_id = m.id
      WHERE p.record_id IN (?)
      ORDER BY p.created_at DESC, p.id`,
      [recordIds]
    );

    if (!rows.length) {
      return [];
    }

    // Group medicines by prescription_id
    const prescriptionMap = new Map();
    rows.forEach(row => {
      if (!prescriptionMap.has(row.id)) {
        // Create new prescription entry
        prescriptionMap.set(row.id, {
          id: row.id,
          record_id: row.record_id,
          pharmacist_id: row.pharmacist_id,
          status: row.status,
          created_at: row.created_at,
          updated_at: row.updated_at,
          medicines: []
        });
      }
      
      // Add medicine to this prescription
      prescriptionMap.get(row.id).medicines.push({
        id: row.medicine_id,
        name: row.medicine_name,
        unit: row.medicine_unit,
        dosage: `${row.dose} ${row.medicine_unit}`,
        frequency: row.frequency,
        duration: row.duration,
        note: row.note || ''
      });
    });

    // Convert map to array
    const prescriptions = Array.from(prescriptionMap.values());

    // Enrich each prescription with additional data
    const enrichedPrescriptions = await Promise.all(
      prescriptions.map(async (prescription) => {
        try {
          // Get record details to fetch patient and doctor info
          const record = await serviceCall(
            `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${prescription.record_id}`
          );

          if (!record) {
            throw new Error(`Record ${prescription.record_id} not found`);
          }

          // Get doctor information from the record
          const doctor = await serviceCall(
            `${services.AUTH_SERVICE_URL}/api/staff/${record.doctor_id}`
          );

          // Get pharmacist information if available
          let pharmacist = null;
          if (prescription.pharmacist_id) {
            pharmacist = await serviceCall(
              `${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`
            );
          }

          return {
            ...prescription,
            patient_id: record.patient_id,
            doctor_id: record.doctor_id,
            patient_name: record.patient_name || 'Unknown',
            doctor_name: doctor?.hoten_nv || 'Unknown',
            pharmacist_name: pharmacist?.hoten_nv || null,
            department_name: record.department_name || 'Unknown',
            record_date: record?.ngaykham || prescription.created_at
          };
        } catch (error) {
          console.error(`Error enriching prescription ${prescription.id}:`, error);
          // Return with minimal information if enrichment fails
          return {
            ...prescription,
            patient_name: 'Unknown',
            doctor_name: 'Unknown',
            pharmacist_name: null,
            department_name: 'Unknown'
          };
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
  const conn = await db.getConnection();
  
  try {
    await conn.beginTransaction();
    
    // Validate pharmacist if provided
    if (pharmacist_id) {
      const pharmacist = await serviceCall(
        `${services.AUTH_SERVICE_URL}/api/staff/${pharmacist_id}`
      );
      if (!pharmacist) {
        throw new Error('Invalid pharmacist ID');
      }
      // Check if the user is a pharmacist (role_id = 2)
      if (pharmacist.role !== 'duocsi') {
        throw new Error('Only pharmacists can dispense medications');
      }
    } else if (status === 'dispensed') {
      // Pharmacist ID is required for dispensed status
      throw new Error('Pharmacist ID is required to dispense medications');
    }
    
    // Get current prescription status before update
    const [currentStatusResult] = await conn.query(
      'SELECT status FROM prescriptions WHERE id = ?',
      [id]
    );
    
    if (!currentStatusResult.length) {
      throw new Error(`Prescription with ID ${id} not found`);
    }
    
    const currentStatus = currentStatusResult[0].status;
    
    // Only reduce stock when status changes to 'dispensed'
    const shouldReduceStock = (status === 'dispensed' && currentStatus !== 'dispensed');
    
    let prescriptionMedicines = [];
    if (shouldReduceStock) {
      // Get all medicines for this prescription
      const [medicinesResult] = await conn.query(
        `SELECT 
          pm.medicine_id, 
          m.so_luong AS current_stock,
          m.ten_thuoc,
          m.don_vi,
          pm.dose,
          pm.note
        FROM prescription_medicines pm
        JOIN medicines m ON pm.medicine_id = m.id
        WHERE pm.prescription_id = ?`,
        [id]
      );
      
      prescriptionMedicines = medicinesResult;
      
      // Check if we have enough stock and ensure bottle instructions
      for (const medicine of prescriptionMedicines) {
        // Get medicine details to determine if it's liquid
        const [medicineDetails] = await conn.query(
          `SELECT 
            is_liquid, 
            volume_per_bottle,
            volume_unit,
            so_luong AS bottles_in_stock,
            ten_thuoc,
            don_vi
          FROM medicines 
          WHERE id = ?`,
          [medicine.medicine_id]
        );
        
        const isLiquid = medicineDetails[0]?.is_liquid || false;
        
        // Add bottle usage note if missing
        if (medicine.don_vi.toLowerCase() === 'chai' && (!medicine.note || medicine.note.trim() === '')) {
          await conn.query(
            `UPDATE prescription_medicines 
             SET note = 'Tham khảo hướng dẫn sử dụng trên chai'
             WHERE prescription_id = ? AND medicine_id = ?`,
            [id, medicine.medicine_id]
          );
        }
        
        // Check stock differently based on whether it's liquid or not
        if (isLiquid) {
          // For liquid medicines, we need to calculate how many bottles we need
          const volumePerBottle = medicineDetails[0].volume_per_bottle;
          const bottlesInStock = medicineDetails[0].bottles_in_stock;
          
          // Calculate how many bottles we need based on prescribed volume
          // If dose is in ml and bottle is 100ml, we might need multiple bottles
          const totalVolumeNeeded = medicine.dose; // Assuming dose is already in correct units (ml)
          const bottlesNeeded = Math.ceil(totalVolumeNeeded / volumePerBottle);
          
          if (bottlesNeeded > bottlesInStock) {
            throw new Error(
              `Không đủ số lượng thuốc "${medicineDetails[0].ten_thuoc}" trong kho ` +
              `(cần ${bottlesNeeded} chai, hiện có ${bottlesInStock} chai)`
            );
          }
          
          // Store the calculated bottles needed for later use
          medicine.bottlesNeeded = bottlesNeeded;
        } else {
          // Standard check for non-liquid medicines
          if (medicineDetails[0].bottles_in_stock < medicine.dose) {
            throw new Error(
              `Không đủ số lượng thuốc "${medicineDetails[0].ten_thuoc}" trong kho ` +
              `(cần ${medicine.dose}, hiện có ${medicineDetails[0].bottles_in_stock})`
            );
          }
        }
      }
      
      // Update stock for each medicine
      for (const medicine of prescriptionMedicines) {
        const [medicineDetails] = await conn.query(
          `SELECT is_liquid FROM medicines WHERE id = ?`,
          [medicine.medicine_id]
        );
        
        const isLiquid = medicineDetails[0]?.is_liquid || false;
        
        if (isLiquid) {
          // For liquid medicines, subtract the number of bottles
          await conn.query(
            'UPDATE medicines SET so_luong = so_luong - ? WHERE id = ?',
            [medicine.bottlesNeeded, medicine.medicine_id]
          );
          
          // Log the transaction for tracking bottle usage
          await conn.query(
            `INSERT INTO medicine_stock_log (
              medicine_id,
              prescription_id,
              action_type,
              quantity_change,
              bottles_used,
              volume_used,
              note
            ) VALUES (?, ?, 'dispense', ?, ?, ?, ?)`,
            [
              medicine.medicine_id,
              id,
              medicine.bottlesNeeded,
              medicine.bottlesNeeded,
              medicine.dose, // Volume used
              `Dispensed ${medicine.dose}${medicineDetails[0].volume_unit} from ${medicine.bottlesNeeded} bottles`
            ]
          );
        } else {
          // Standard update for non-liquid medicines
          await conn.query(
            'UPDATE medicines SET so_luong = so_luong - ? WHERE id = ?',
            [medicine.dose, medicine.medicine_id]
          );
        }
      }
    }
    
    // Update prescription status
    const [result] = await conn.query(
      `UPDATE prescriptions 
       SET status = ?, 
           pharmacist_id = ?,
           updated_at = NOW()
       WHERE id = ?`,
      [status, pharmacist_id, id]
    );
    
    if (result.affectedRows === 0) {
      throw new Error('Failed to update prescription status');
    }
    
    // Get record_id for notification purposes
    const [rows] = await conn.query(
      `SELECT record_id FROM prescriptions WHERE id = ?`,
      [id]
    );
    
    // Commit the transaction
    await conn.commit();
    
    if (rows.length > 0) {
      // Get record to find patient_id
      const record = await serviceCall(
        `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${rows[0].record_id}`
      );
      
      if (record) {
        return {
          ...result,
          patient_id: record.patient_id,
          reduced_stock: shouldReduceStock,
          pharmacist_id
        };
      }
    }
    
    return {
      ...result,
      reduced_stock: shouldReduceStock,
      pharmacist_id
    };
  } catch (error) {
    // Rollback the transaction in case of error
    await conn.rollback();
    console.error('Error in updatePrescriptionStatus:', error);
    throw error;
  } finally {
    conn.release();
  }
};

export const getPrescriptionById = async (id) => {
  try {
    // Get prescription details
    const [rows] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p
      WHERE p.id = ?`,
      [id]
    );

    if (!rows.length) return null;
    const prescription = rows[0];

    // Get medicines for this prescription
    const [medicineRows] = await db.query(
      `SELECT 
        pm.*,
        m.ten_thuoc as name,
        m.don_vi as unit
      FROM prescription_medicines pm
      JOIN medicines m ON pm.medicine_id = m.id
      WHERE pm.prescription_id = ?`,
      [id]
    );

    // Format medicines data with formatted dose (combining numeric value with unit)
    const medicines = medicineRows.map(med => ({
      id: med.medicine_id,
      name: med.name,
      unit: med.unit,
      dosage: `${med.dose} ${med.unit}`, // Combine numeric dose with unit for display
      frequency: med.frequency,
      duration: med.duration,
      note: med.note || ''
    }));

    // Get record details to fetch patient and doctor info
    const record = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${prescription.record_id}`
    );

    if (!record) {
      throw new Error(`Record ${prescription.record_id} not found`);
    }

    // Get related information from other services
    const [patient, doctor, pharmacist] = await Promise.all([
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${record.patient_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${record.doctor_id}`),
      prescription.pharmacist_id ? 
        serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`) : 
        null
    ]);

    return {
      ...prescription,
      medicines,
      patient_id: record.patient_id,
      doctor_id: record.doctor_id,
      diagnosis: record.chan_doan || '',
      patient_name: patient?.hoten_bn || 'Unknown',
      doctor_name: doctor?.hoten_nv || 'Unknown',
      pharmacist_name: pharmacist?.hoten_nv || null,
      department_name: doctor?.department?.ten_ck || 'Unknown'
    };
  } catch (error) {
    console.error('Error in getPrescriptionById:', error);
    throw error;
  }
};

export const getPrescriptionsByRecordId = async (record_id) => {
  try {
    // Get prescriptions for this record
    const [prescriptionRows] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p
      WHERE p.record_id = ?
      ORDER BY p.created_at DESC`,
      [record_id]
    );

    if (!prescriptionRows.length) {
      return [];
    }

    // Get all prescription IDs to fetch medicines in a single query
    const prescriptionIds = prescriptionRows.map(p => p.id);
    
    // Get all medicines for these prescriptions
    const [medicineRows] = await db.query(
      `SELECT 
        pm.*,
        m.ten_thuoc as name,
        m.don_vi as unit,
        pm.prescription_id
      FROM prescription_medicines pm
      JOIN medicines m ON pm.medicine_id = m.id
      WHERE pm.prescription_id IN (?)`,
      [prescriptionIds]
    );

    // Group medicines by prescription_id
    const medicinesByPrescription = medicineRows.reduce((acc, med) => {
      if (!acc[med.prescription_id]) {
        acc[med.prescription_id] = [];
      }
      acc[med.prescription_id].push({
        id: med.medicine_id,
        name: med.name,
        unit: med.unit,
        dosage: med.dose,
        frequency: med.frequency,
        duration: med.duration,
        note: med.note || ''
      });
      return acc;
    }, {});
    // Get the record details for doctor and patient info
    const record = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${record_id}`
    );

    // Enrich each prescription
    const enrichedPrescriptions = await Promise.all(
      prescriptionRows.map(async (prescription) => {
        try {
          // Get pharmacist information if available
          let pharmacist = null;
          if (prescription.pharmacist_id) {
            pharmacist = await serviceCall(
              `${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`
            );
          }

          return {
            ...prescription,
            medicines: medicinesByPrescription[prescription.id] || [],
            doctor_id: record?.doctor_id,
            patient_id: record?.patient_id,
            pharmacist_name: pharmacist?.hoten_nv || null
          };
        } catch (error) {
          console.error(`Error enriching prescription ${prescription.id}:`, error);
          return {
            ...prescription,
            medicines: medicinesByPrescription[prescription.id] || [],
            pharmacist_name: null
          };
        }
      })
    );

    return enrichedPrescriptions;
  } catch (error) {
    console.error('Error in getPrescriptionsByRecordId:', error);
    throw error;
  }
};
export const getAllMedicines = async () => {
  try {
    const [rows] = await db.query(
      `SELECT 
        id,
        ten_thuoc,
        so_luong,
        don_vi,
        don_gia
      FROM medicines
      ORDER BY ten_thuoc ASC`
    );
    
    return rows;
  } catch (error) {
    console.error('Error in getAllMedicines:', error);
    throw error;
  }
};

export const getMedicineById = async (id) => {
  try {
    const [rows] = await db.query(
      `SELECT 
        id,
        ten_thuoc,
        so_luong,
        don_vi,
        don_gia
      FROM medicines
      WHERE id = ?`,
      [id]
    );
    
    return rows.length ? rows[0] : null;
  } catch (error) {
    console.error('Error in getMedicineById:', error);
    throw error;
  }
};
// Add this to prescription.model.js
export const getPrescriptionsByStatus = async (status) => {
  try {
    // Get all prescriptions with the specified status
    const [prescriptionRows] = await db.query(
      `SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p
      WHERE p.status = ?
      ORDER BY p.created_at DESC`,
      [status]
    );

    if (!prescriptionRows.length) {
      return [];
    }

    // Get all prescription IDs to fetch medicines in a single query
    const prescriptionIds = prescriptionRows.map(p => p.id);
    
    // Get all medicines for these prescriptions
    const [medicineRows] = await db.query(
      `SELECT 
        pm.*,
        m.ten_thuoc as name,
        m.don_vi as unit,
        pm.prescription_id
      FROM prescription_medicines pm
      JOIN medicines m ON pm.medicine_id = m.id
      WHERE pm.prescription_id IN (?)`,
      [prescriptionIds]
    );

    // Group medicines by prescription_id
    const medicinesByPrescription = medicineRows.reduce((acc, med) => {
      if (!acc[med.prescription_id]) {
        acc[med.prescription_id] = [];
      }
      acc[med.prescription_id].push({
        id: med.medicine_id,
        name: med.name,
        unit: med.unit,
        dosage: `${med.dose} ${med.unit}`, // Format dosage for display
        frequency: med.frequency,
        duration: med.duration,
        note: med.note || ''
      });
      return acc;
    }, {});

    // Enrich each prescription with additional data
    const enrichedPrescriptions = await Promise.all(
      prescriptionRows.map(async (prescription) => {
        try {
          // Get record details to fetch patient and doctor info
          const record = await serviceCall(
            `${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${prescription.record_id}`
          );

          if (!record) {
            throw new Error(`Record ${prescription.record_id} not found`);
          }

          // Get related information from other services
          const [patient, doctor, pharmacist] = await Promise.all([
            serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${record.patient_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${record.doctor_id}`),
            prescription.pharmacist_id ? 
              serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${prescription.pharmacist_id}`) : 
              null
          ]);

          return {
            ...prescription,
            medicines: medicinesByPrescription[prescription.id] || [],
            patient_id: record.patient_id,
            doctor_id: record.doctor_id,
            patient_name: patient?.hoten_bn || 'Unknown',
            doctor_name: doctor?.hoten_nv || 'Unknown',
            pharmacist_name: pharmacist?.hoten_nv || null,
            department_name: record?.department_name || doctor?.department?.ten_ck || 'Unknown'
          };
        } catch (error) {
          console.error(`Error enriching prescription ${prescription.id}:`, error);
          // Return with minimal information if enrichment fails
          return {
            ...prescription,
            medicines: medicinesByPrescription[prescription.id] || [],
            patient_name: 'Unknown',
            doctor_name: 'Unknown',
            pharmacist_name: null,
            department_name: 'Unknown'
          };
        }
      })
    );

    return enrichedPrescriptions;
  } catch (error) {
    console.error('Error in getPrescriptionsByStatus:', error);
    throw error;
  }
};
export const getAllPrescriptions = async (filters = {}) => {
  try {
    let query = `
      SELECT 
        p.*,
        DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM prescriptions p
    `;
    
    const queryParams = [];
    const conditions = [];
    
    // Add status filter if provided
    if (filters.status) {
      conditions.push('p.status = ?');
      queryParams.push(filters.status);
    }
    
    // Add record_id filter if provided
    if (filters.record_id) {
      conditions.push('p.record_id = ?');
      queryParams.push(filters.record_id);
    }
    
    // Add date range filter if provided
    if (filters.start_date) {
      conditions.push('DATE(p.created_at) >= ?');
      queryParams.push(filters.start_date);
    }
    
    if (filters.end_date) {
      conditions.push('DATE(p.created_at) <= ?');
      queryParams.push(filters.end_date);
    }
    
    // Add WHERE clause if there are conditions
    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }
    
    // Add ordering
    query += ' ORDER BY p.created_at DESC';
    
    // Add limit and offset for pagination
    if (filters.limit) {
      query += ' LIMIT ?';
      queryParams.push(parseInt(filters.limit, 10));
      
      if (filters.offset) {
        query += ' OFFSET ?';
        queryParams.push(parseInt(filters.offset, 10));
      }
    }
    
    const [prescriptionRows] = await db.query(query, queryParams);

    if (!prescriptionRows.length) {
      return [];
    }

    // Get all prescription IDs to fetch medicines in a single query
    const prescriptionIds = prescriptionRows.map(p => p.id);
    
    // Get all medicines for these prescriptions
    const [medicineRows] = await db.query(
      `SELECT 
        pm.*,
        m.ten_thuoc as name,
        m.don_vi as unit,
        pm.prescription_id
      FROM prescription_medicines pm
      JOIN medicines m ON pm.medicine_id = m.id
      WHERE pm.prescription_id IN (?)`,
      [prescriptionIds]
    );

    // Group medicines by prescription_id
    const medicinesByPrescription = medicineRows.reduce((acc, med) => {
      if (!acc[med.prescription_id]) {
        acc[med.prescription_id] = [];
      }
      acc[med.prescription_id].push({
        id: med.medicine_id,
        name: med.name,
        unit: med.unit,
        dosage: `${med.dose} ${med.unit}`,
        frequency: med.frequency,
        duration: med.duration,
        note: med.note || ''
      });
      return acc;
    }, {});

    // Get unique record IDs to fetch record details in batches
    const recordIds = [...new Set(prescriptionRows.map(p => p.record_id))];
    const recordDetailsPromises = recordIds.map(id => 
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/medical-records/internal/${id}`)
    );
    const recordsResponse = await Promise.all(recordDetailsPromises);
    
    // Create a map of record details for quick lookup
    const recordDetails = {};
    recordsResponse.forEach(record => {
      if (record && record.id) {
        recordDetails[record.id] = record;
      }
    });
    
    // Collect all patient and doctor IDs
    const patientIds = [];
    const doctorIds = [];
    const pharmacistIds = [];
    
    prescriptionRows.forEach(prescription => {
      const record = recordDetails[prescription.record_id];
      if (record) {
        if (record.patient_id) patientIds.push(record.patient_id);
        if (record.doctor_id) doctorIds.push(record.doctor_id);
      }
      if (prescription.pharmacist_id) pharmacistIds.push(prescription.pharmacist_id);
    });
    
    // Fetch all patients, doctors, and pharmacists in bulk
    const uniquePatientIds = [...new Set(patientIds)];
    const uniqueDoctorIds = [...new Set(doctorIds)];
    const uniquePharmacistIds = [...new Set(pharmacistIds)];
    
    const [patientsResponse, doctorsResponse, pharmacistsResponse] = await Promise.all([
      Promise.all(uniquePatientIds.map(id => serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${id}`))),
      Promise.all(uniqueDoctorIds.map(id => serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${id}`))),
      Promise.all(uniquePharmacistIds.map(id => serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${id}`)))
    ]);
    
    // Create lookup maps
    const patients = {};
    const doctors = {};
    const pharmacists = {};
    
    patientsResponse.forEach(patient => {
      if (patient && patient.id) patients[patient.id] = patient;
    });
    
    doctorsResponse.forEach(doctor => {
      if (doctor && doctor.id) doctors[doctor.id] = doctor;
    });
    
    pharmacistsResponse.forEach(pharmacist => {
      if (pharmacist && pharmacist.id) pharmacists[pharmacist.id] = pharmacist;
    });

    // Enrich prescriptions with the data we've collected
    const enrichedPrescriptions = prescriptionRows.map(prescription => {
      const record = recordDetails[prescription.record_id] || {};
      const patient = patients[record.patient_id] || {};
      const doctor = doctors[record.doctor_id] || {};
      const pharmacist = pharmacists[prescription.pharmacist_id] || null;
      
      return {
        ...prescription,
        medicines: medicinesByPrescription[prescription.id] || [],
        patient_id: record.patient_id,
        patient_name: patient.hoten_bn || 'Unknown',
        doctor_id: record.doctor_id,
        doctor_name: doctor.hoten_nv || 'Unknown',
        pharmacist_name: pharmacist ? pharmacist.hoten_nv : null,
        department_name: record.department_name || (doctor.department ? doctor.department.ten_ck : 'Unknown')
      };
    });

    return enrichedPrescriptions;
  } catch (error) {
    console.error('Error in getAllPrescriptions:', error);
    throw error;
  }
};