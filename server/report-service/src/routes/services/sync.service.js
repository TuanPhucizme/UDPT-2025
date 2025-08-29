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
  try {
    const res = await axios.get(`${PATIENT_SERVICE_URL}/api/patients`, AUTH_HEADER);
    const patients = res.data;

    for (const p of patients) {
      await db.query(
        `REPLACE INTO patients (id, name, gender, created_at) VALUES (?, ?, ?, ?)`,
        [p.id, p.name ?? p.full_name ?? p.hoten_bn ?? 'N/A', p.gender ?? null, p.created_at ?? p.createdAt ?? new Date()]
      );
    }

    console.log(`[✓] Đồng bộ ${patients.length} bệnh nhân`);
    
    // After syncing patients, sync their medical records
    await syncMedicalRecords();
  } catch (err) {
    console.error('[✘] Lỗi syncPatients:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
  }
}

async function syncMedicalRecords() {
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
          `${PATIENT_SERVICE_URL}/api/patients/${patient.id}/records`, 
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
            
            totalRecords++;
          }
        }
      } catch (error) {
        console.warn(`[!] Không thể lấy hồ sơ bệnh án cho bệnh nhân ${patient.id}:`, error.message);
      }
    }
    
    console.log(`[✓] Đồng bộ ${totalRecords} hồ sơ bệnh án`);
  } catch (err) {
    console.error('[✘] Lỗi syncMedicalRecords:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
  }
}

async function syncPrescriptions() {
  try {
    // Get all prescriptions first
    const res = await axios.get(`${PRESCRIPTION_SERVICE_URL}/api/prescriptions`, AUTH_HEADER);
    const prescriptions = res.data;
    
    let total = 0;
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
        // Get prescription details
        const detailsRes = await axios.get(
          `${PRESCRIPTION_SERVICE_URL}/api/prescriptions/${pr.id}/medicines`, 
          AUTH_HEADER
        );
        
        if (detailsRes.data && Array.isArray(detailsRes.data)) {
          const monthYear = extractMonthYear(pr.created_at ?? pr.createdAt ?? new Date());
          
          for (const item of detailsRes.data) {
            if (item.medicine_id) {
              const medicineInfo = medicines[item.medicine_id] || {
                name: 'Unknown Medicine',
                is_liquid: false,
                volume_per_bottle: 0
              };
              
              // Calculate total liquid volume for liquid medicines
              const liquidVolume = medicineInfo.is_liquid
                ? (item.dose * (medicineInfo.volume_per_bottle || 0))
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
                  item.medicine_id,
                  medicineInfo.name,
                  item.dose || 0,
                  liquidVolume,
                  medicineInfo.is_liquid ? 1 : 0,
                  monthYear
                ]
              );
            }
          }
        }
      } catch (detailsError) {
        console.warn(`[!] Không thể lấy chi tiết đơn thuốc ${pr.id}:`, detailsError.message);
      }
      
      total++;
    }
    
    console.log(`[✓] Đồng bộ ${total} đơn thuốc`);
  } catch (err) {
    console.error('[✘] Lỗi syncPrescriptions:',
      err?.message,
      err?.code,
      err?.response?.status,
      err?.response?.data,
      err?.sqlMessage
    );
  }
}

// Combine all sync operations for better consistency
async function syncAll() {
  console.log("[i] Bắt đầu đồng bộ dữ liệu...");
  await syncPatients();
  await syncPrescriptions();
  console.log("[i] Hoàn tất đồng bộ dữ liệu.");
}

module.exports = { syncPatients, syncPrescriptions, syncMedicalRecords, syncAll };
