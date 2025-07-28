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
      await db.promise().query(
        `REPLACE INTO patients (id, name, gender, created_at) VALUES (?, ?, ?, ?)`,
        [p.id, p.name, p.gender, p.createdAt || new Date()]
      );
    }

    console.log(`[✓] Đồng bộ ${patients.length} bệnh nhân`);
  } catch (err) {
    console.error('[✘] Lỗi syncPatients:', err.message);
  }
}

async function syncPrescriptions() {
  try {
    const res = await axios.get('http://localhost:3003/api/prescriptions', AUTH_HEADER);
    const prescriptions = res.data;

    for (const p of prescriptions) {
      await db.promise().query(
        `REPLACE INTO prescriptions (id, patient_id, medicine, status, created_at) VALUES (?, ?, ?, ?, ?)`,
        [p.id, p.patientId, p.medicine, p.status, p.createdAt || new Date()]
      );
    }

    console.log(`[✓] Đồng bộ ${prescriptions.length} đơn thuốc`);
  } catch (err) {
    console.error('[✘] Lỗi syncPrescriptions:', err.message);
  }
}

module.exports = { syncPatients, syncPrescriptions };
