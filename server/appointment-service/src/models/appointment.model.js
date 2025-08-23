import db from '../db.js';
import { serviceCall } from '../utils/serviceCall.js';
import services from '../config/services.js';

export const createAppointment = async (data) => {
  const { 
    patient_id, 
    department_id,
    doctor_id, 
    receptionist_id,
    thoi_gian_hen,
    lydo,
    note 
  } = data;

  try {
    // Verify patient exists
    const patient = await serviceCall(
      `${services.PATIENT_SERVICE_URL}/api/internal/patients/${patient_id}`
    );
    if (!patient) {
      throw new Error('Patient not found');
    }

    // Verify doctor and department
    const [doctor, department] = await Promise.all([
      serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/staff/${doctor_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/departments/${department_id}`)
    ]);

    if (!doctor || !department) {
      throw new Error('Invalid doctor or department');
    }

    // Create appointment
    const [result] = await db.query(
      `INSERT INTO appointments (
        patient_id, 
        department_id,
        doctor_id, 
        receptionist_id,
        thoi_gian_hen,
        lydo,
        status,
        note,
        created_at
      ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())`,
      [patient_id, department_id, doctor_id, receptionist_id, thoi_gian_hen, lydo, note]
    );

    return result;
  } catch (error) {
    console.error('Error in createAppointment:', error);
    throw error;
  }
};

export const getAppointments = async (filters = {}) => {
  try {
    // Get base appointment data
    let sql = `
      SELECT 
        a.id,
        a.patient_id,
        a.doctor_id,
        a.department_id,
        a.thoi_gian_hen,
        a.lydo,
        a.status,
        a.note,
        a.created_at,
        a.updated_at
      FROM appointments a
      WHERE 1=1
    `;
    const params = [];

    if (filters.doctor_id) {
      sql += ' AND a.doctor_id = ?';
      params.push(filters.doctor_id);
    }

    if (filters.status) {
      sql += ' AND a.status = ?';
      params.push(filters.status);
    }

    sql += ' ORDER BY a.thoi_gian_hen DESC';

    const [appointments] = await db.query(sql, params);

    // Enrich with data from other services
    const enrichedAppointments = await Promise.all(
      appointments.map(async (apt) => {
        try {
          const [patient, doctor, department] = await Promise.all([
            serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/internal/${apt.patient_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/staff/${apt.doctor_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/departments/${apt.department_id}`)
          ]);
          
          return {
            ...apt,
            patient_name: patient?.hoten_bn,
            patient_phone: patient?.sdt,
            doctor_name: doctor?.hoten_nv,
            department_name: department?.ten_ck
          };
        } catch (error) {
          console.error(`Failed to enrich appointment ${apt.id}:`, error);
          return apt;
        }
      })
    );

    return enrichedAppointments;
  } catch (error) {
    console.error('Error in getAppointments:', error);
    throw error;
  }
};

export const getAppointmentById = async (id) => {
  try {
    const [appointments] = await db.query(
      'SELECT * FROM appointments WHERE id = ?',
      [id]
    );

    if (!appointments.length) return null;

    const apt = appointments[0];

    // Get related data from other services
    const [patient, doctor, department] = await Promise.all([
      serviceCall(`${services.PATIENT_SERVICE_URL}/api/internal/patients/${apt.patient_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/staff/${apt.doctor_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/internal/departments/${apt.department_id}`)
    ]);

    return {
      ...apt,
      patient_name: patient?.hoten_bn,
      patient_phone: patient?.sdt,
      doctor_name: doctor?.hoten_nv,
      department_name: department?.ten_ck
    };
  } catch (error) {
    console.error('Error in getAppointmentById:', error);
    throw error;
  }
};

export const getDoctorSchedule = async (doctorId, date) => {
  try {
    // Verify doctor exists
    const doctor = await serviceCall(
      `${services.AUTH_SERVICE_URL}/api/internal/staff/${doctorId}`
    );
    if (!doctor) {
      throw new Error('Doctor not found');
    }

    // Get schedule
    const [appointments] = await db.query(
      `SELECT * FROM appointments 
       WHERE doctor_id = ? 
       AND DATE(thoi_gian_hen) = ?
       AND status != 'cancelled'
       ORDER BY thoi_gian_hen`,
      [doctorId, date]
    );

    return appointments;
  } catch (error) {
    console.error('Error in getDoctorSchedule:', error);
    throw error;
  }
};

export const updateAppointmentStatus = async (id, status, note = null) => {
  try {
    const updates = ['status = ?'];
    const params = [status];

    if (note) {
      updates.push('note = CONCAT(IFNULL(note,""), ?)');
      params.push(`\n[${status}]: ${note}`);
    }

    updates.push('updated_at = CURRENT_TIMESTAMP');
    params.push(id);

    const [result] = await db.query(
      `UPDATE appointments SET ${updates.join(', ')} WHERE id = ?`,
      params
    );

    return result;
  } catch (error) {
    console.error('Error in updateAppointmentStatus:', error);
    throw error;
  }
};
