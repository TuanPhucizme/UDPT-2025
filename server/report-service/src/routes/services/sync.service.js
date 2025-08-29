const axios = require('axios');
const db = require('../../config/db');

const PATIENT_SERVICE_URL = process.env.PATIENT_SERVICE_URL;
const PRESCRIPTION_SERVICE_URL = process.env.PRESCRIPTION_SERVICE_URL;
const AUTH_SERVICE_URL = process.env.AUTH_SERVICE_URL;

const AUTH_HEADER = {
  headers: {
    Authorization: `Bearer ${process.env.SERVICE_ACCESS_TOKEN}`
  }
};

// Function to log sync operation start
async function logSyncStart(syncType) {
  try {
    const [result] = await db.query(
      'INSERT INTO sync_log (sync_type, status) VALUES (?, ?)',
      [syncType, 'running']
    );
    return result.insertId;
  } catch (error) {
    console.error('Failed to log sync start:', error);
    return null;
  }
}

// Function to log sync operation completion
async function logSyncEnd(logId, recordsProcessed, status = 'completed', message = null) {
  if (!logId) return;
  
  try {
    await db.query(
      'UPDATE sync_log SET end_time = CURRENT_TIMESTAMP, records_processed = ?, status = ?, message = ? WHERE id = ?',
      [recordsProcessed, status, message, logId]
    );
  } catch (error) {
    console.error('Failed to log sync end:', error);
  }
}

// Helper function to get current month-year date for monthly stats
function getCurrentMonthYear() {
  const date = new Date();
  return new Date(date.getFullYear(), date.getMonth(), 1);
}

// Helper function to extract month-year from date string
function extractMonthYear(dateString) {
  const date = new Date(dateString);
  return new Date(date.getFullYear(), date.getMonth(), 1);
}

async function syncPatients() {
  const logId = await logSyncStart('patients');
  let recordsProcessed = 0;
  
  try {
    
    const res = await axios.get(`${PATIENT_SERVICE_URL}/api/patients`, AUTH_HEADER);
    const patients = res.data;

    for (const p of patients) {
      await db.query(
        `REPLACE INTO patients (id, name, gender, created_at) VALUES (?, ?, ?, ?)`,
        [p.id, p.name ?? p.full_name ?? p.hoten_bn ?? 'N/A', p.gender ?? null, new Date(p.createdAt) ?? new Date()]
      );
      recordsProcessed++;
    }

    console.log(`[✓] Đồng bộ ${recordsProcessed} bệnh nhân`);
    
    // After syncing patients, sync their medical records
    await syncMedicalRecords();
    
    await logSyncEnd(logId, recordsProcessed);
    return recordsProcessed;
  } catch (err) {
    console.error('[✘] Lỗi syncPatients:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
    await logSyncEnd(logId, recordsProcessed, 'failed', err.message);
    throw err;
  }
}

async function syncMedicalRecords() {
  const logId = await logSyncStart('medical_records');
  let recordsProcessed = 0;
  
  try {
    const [patients] = await db.query('SELECT id FROM patients');
    let totalRecords = 0;
    
    // Get departments for reference
    const departmentsRes = await axios.get(`${AUTH_SERVICE_URL}/api/departments`, AUTH_HEADER);
    const departments = {};
    if (departmentsRes.data && Array.isArray(departmentsRes.data)) {
      departmentsRes.data.forEach(dept => {
        departments[dept.id] = dept.ten_ck;
      });
    }
    
    for (const patient of patients) {
      try {
        // Get medical records for each patient
        const recordsRes = await axios.get(
          `${PATIENT_SERVICE_URL}/api/medical-records/patient/${patient.id}`, 
          AUTH_HEADER
        );
        
        if (recordsRes.data && Array.isArray(recordsRes.data)) {
          for (const record of recordsRes.data) {
            const visitDate = new Date(record.ngaykham);
            const monthYear = extractMonthYear(record.ngaykham);
            
            await db.query(
              `REPLACE INTO patient_record_stats 
              (patient_id, department_id, department_name, diagnosis, visit_date, month_year, record_id)
              VALUES (?, ?, ?, ?, ?, ?, ?)`,
              [
                patient.id,
                record.department_id,
                departments[record.department_id] || null,
                record.chan_doan || null,
                visitDate,
                monthYear,
                record.id
              ]
            );
            
            recordsProcessed++;
          }
        }
      } catch (error) {
        console.warn(`[!] Không thể lấy hồ sơ bệnh án cho bệnh nhân ${patient.id}:`, error.message);
      }
    }
    
    console.log(`[✓] Đồng bộ ${recordsProcessed} hồ sơ bệnh án`);
    await logSyncEnd(logId, recordsProcessed);
    return recordsProcessed;
  } catch (err) {
    console.error('[✘] Lỗi syncMedicalRecords:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
    await logSyncEnd(logId, recordsProcessed, 'failed', err.message);
    throw err;
  }
}

async function syncPrescriptions() {
  const logId = await logSyncStart('prescriptions');
  let recordsProcessed = 0;
  
  try {
    
    await db.query('TRUNCATE TABLE medicine_prescription_stats');
    console.log("[✓] Reset medicine prescription statistics table");
    
    // Get all prescriptions first
    const res = await axios.get(`${PRESCRIPTION_SERVICE_URL}/api/prescriptions`, AUTH_HEADER);
    const prescriptions = res.data;
    
    // Get medicine data for reference
    const medicinesRes = await axios.get(`${PRESCRIPTION_SERVICE_URL}/api/medicines`, AUTH_HEADER);
    const medicines = {};
    if (medicinesRes.data && Array.isArray(medicinesRes.data)) {
      medicinesRes.data.forEach(med => {
        medicines[med.id] = {
          name: med.ten_thuoc,
          is_liquid: med.is_liquid === 1,
          volume_per_bottle: med.volume_per_bottle
        };
      });
    }
    
    for (const pr of prescriptions) {
      // Basic prescription information
      const medicineValue = pr.medicines != null
        ? (typeof pr.medicines === 'string' ? pr.medicines : JSON.stringify(pr.medicines))
        : (pr.medicine ?? null);
        
      await db.query(
        `REPLACE INTO prescriptions (id, patient_id, medicine, status, created_at)
          VALUES (?, ?, ?, ?, ?)`,
        [
          pr.id,
          pr.patient_id ?? pr.patientId,
          medicineValue,
          pr.status ?? 'pending',
          pr.created_at ?? pr.createdAt ?? new Date()
        ]
      );
      
      // Now process detailed medicine information if available
      try {
        // Get prescription details - this returns a single prescription with medicines array
        const detailsRes = await axios.get(
          `${PRESCRIPTION_SERVICE_URL}/api/prescriptions/${pr.id}`, 
          AUTH_HEADER
        );
        
        // Check if medicines array exists in the response
        if (detailsRes.data && Array.isArray(detailsRes.data.medicines)) {
          const monthYear = extractMonthYear(pr.created_at ?? pr.createdAt ?? new Date());
          
          for (const item of detailsRes.data.medicines) {
            if (item.id) {
              const medicineInfo = medicines[item.id] || {
                name: item.name || 'Unknown Medicine',
                is_liquid: false,
                volume_per_bottle: 0
              };
              
              // Extract dosage numeric value from formatted string (e.g., "2 viên" -> 2)
              let dosage = 0;
              if (item.dosage) {
                const dosageMatch = item.dosage.match(/^(\d+(\.\d+)?)/);
                dosage = dosageMatch ? parseFloat(dosageMatch[1]) : 0;
              }
              
              // Calculate total liquid volume for liquid medicines
              const liquidVolume = medicineInfo.is_liquid
                ? (dosage * (medicineInfo.volume_per_bottle || 0))
                : 0;
              
              // Upsert into medicine_prescription_stats
              await db.query(
                `INSERT INTO medicine_prescription_stats
                  (medicine_id, medicine_name, total_prescribed, total_quantity, total_liquid_volume, is_liquid, month_year)
                VALUES (?, ?, 1, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                  total_prescribed = total_prescribed + 1,
                  total_quantity = total_quantity + VALUES(total_quantity),
                  total_liquid_volume = total_liquid_volume + VALUES(total_liquid_volume)`,
                [
                  item.id,
                  medicineInfo.name,
                  dosage || 0,
                  liquidVolume,
                  medicineInfo.is_liquid ? 1 : 0,
                  monthYear
                ]
              );
            }
          }
        } else {
          console.log(`No medicines array found for prescription ${pr.id}, response:`, detailsRes.data);
        }
      } catch (detailsError) {
        console.warn(`[!] Không thể lấy chi tiết đơn thuốc ${pr.id}:`, detailsError.message);
      }
      
      recordsProcessed++;
    }
    
    console.log(`[✓] Đồng bộ ${recordsProcessed} đơn thuốc`);
    await logSyncEnd(logId, recordsProcessed);
    return recordsProcessed;
  } catch (err) {
    console.error('[✘] Lỗi syncPrescriptions:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
    await logSyncEnd(logId, recordsProcessed, 'failed', err.message);
    throw err;
  }
}

// Combine all sync operations for better consistency
async function syncAll() {
  const logId = await logSyncStart('all');
  let totalRecords = 0;
  let status = 'completed';
  let errorMessage = null;
  
  console.log("[i] Bắt đầu đồng bộ dữ liệu...");
  
  try {
    const patientCount = await syncPatients();
    totalRecords += patientCount;
  } catch (error) {
    status = 'partially_completed';
    errorMessage = 'Error syncing patients: ' + error.message;
    console.error(errorMessage);
  }
  
  try {
    const prescriptionCount = await syncPrescriptions();
    totalRecords += prescriptionCount;
  } catch (error) {
    status = 'partially_completed';
    errorMessage = (errorMessage ? errorMessage + '; ' : '') + 'Error syncing prescriptions: ' + error.message;
    console.error('Error syncing prescriptions:', error.message);
  }
  
  console.log("[i] Hoàn tất đồng bộ dữ liệu.");
  await logSyncEnd(logId, totalRecords, status, errorMessage);
}

module.exports = { syncPatients, syncPrescriptions, syncMedicalRecords, syncAll };
