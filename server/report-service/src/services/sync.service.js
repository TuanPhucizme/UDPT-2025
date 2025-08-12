const axios = require('axios');
const db = require('../config/db');

const AUTH_HEADER = {
  headers: {
    Authorization: `Bearer ${process.env.SERVICE_ACCESS_TOKEN}` // Token gắn trong .env
  }
};

async function syncPatients() {
  try {
    const res = await axios.get('http://localhost:3001/api/patients', AUTH_HEADER);
    const patients = res.data;

    for (const p of patients) {
      await db.query(
        `REPLACE INTO patients (id, name, gender, created_at) VALUES (?, ?, ?, ?)`,
        [p.id, p.name ?? p.full_name ?? 'N/A', p.gender ?? null, p.created_at ?? p.createdAt ?? new Date()]
      );

    }

    console.log(`[✓] Đồng bộ ${patients.length} bệnh nhân`);
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

async function syncPrescriptions() {
  try {
    const resPatients = await axios.get('http://localhost:3001/api/patients', AUTH_HEADER);
    const patients = resPatients.data;

    let total = 0;
    for (const p of patients) {
      const res = await axios.get(
        `http://localhost:3003/api/prescriptions/patient/${p.id}`,
        AUTH_HEADER
      );
      const items = res.data;

      
      for (const pr of items) {
        const medicineValue =
        pr.medicines != null
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
      }
      total += items.length;
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


module.exports = { syncPatients, syncPrescriptions };
