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
      `${services.PATIENT_SERVICE_URL}/api/patients/${patient_id}`
    );
    if (!patient) {
      throw new Error('Patient not found');
    }

    // Verify doctor and department
    const [doctor, department] = await Promise.all([
      serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${doctor_id}`),
      serviceCall(`${services.AUTH_SERVICE_URL}/api/departments/${department_id}`)
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
        DATE_FORMAT(a.thoi_gian_hen, '%Y-%m-%d %H:%i:%s') as thoi_gian_hen,
        a.lydo,
        a.status,
        a.note,
        DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
        DATE_FORMAT(a.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
      FROM appointments a
      WHERE 1=1
    `;
    const params = [];

    // Filter by doctor
    if (filters.doctor_id) {
      sql += ' AND a.doctor_id = ?';
      params.push(filters.doctor_id);
    }

    // Filter by status
    if (filters.status) {
      sql += ' AND a.status = ?';
      params.push(filters.status);
    }

    // Filter by date
    if (filters.date) {
      sql += ' AND DATE(a.thoi_gian_hen) = ?';
      params.push(filters.date);
    }

    // Filter by patient name or phone
    if (filters.name || filters.phone) {
      try {
        // Build the correct search query string
        let searchParams = '';
        if (filters.name) {
          searchParams = `name=${encodeURIComponent(filters.name)}`;
        } else if (filters.phone) {
          searchParams = `phone=${encodeURIComponent(filters.phone)}`;
        }
        
        
        // Call the patient service search endpoint
        const patients = await serviceCall(
          `${services.PATIENT_SERVICE_URL}/api/patients?${searchParams}`
        );

        if (patients && patients.length > 0) {
          const patientIds = patients.map(p => p.id);
          sql += ` AND a.patient_id IN (${patientIds.join(',')})`;
        } else {
          // No matching patients, return empty result
          return [];
        }
      } catch (error) {
        console.error('Error searching patients:', error);
        // If patient search fails, continue without patient filter
      }
    }

    sql += ' ORDER BY a.thoi_gian_hen DESC';

    const [appointments] = await db.query(sql, params);
    // Enrich with data from other services
    const enrichedAppointments = await Promise.all(
      appointments.map(async (apt) => {
        try {
          const [patient, doctor, department] = await Promise.all([
            serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${apt.patient_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${apt.doctor_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/departments/${apt.department_id}`)
          ]);
          
          return {
            ...apt,
            patient_name: patient?.hoten_bn || 'Unknown',
            patient_phone: patient?.sdt || 'N/A',
            doctor_name: doctor?.hoten_nv || 'Unknown',
            department_name: department?.ten_ck || 'Unknown'
          };
        } catch (error) {
          console.error(`Failed to enrich appointment ${apt.id}:`, error);
          return {
            ...apt,
            patient_name: 'Unknown',
            patient_phone: 'N/A',
            doctor_name: 'Unknown',
            department_name: 'Unknown'
          };
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
            `SELECT 
                a.*,
                DATE_FORMAT(a.thoi_gian_hen, '%Y-%m-%d %H:%i:%s') as thoi_gian_hen,
                DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                DATE_FORMAT(a.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at
            FROM appointments a 
            WHERE a.id = ?`,
            [id]
        );
        if (!appointments.length) return null;

        const apt = appointments[0];

        // Get related data from other services
        const [patient, doctor, department, receptionist] = await Promise.all([
            serviceCall(`${services.PATIENT_SERVICE_URL}/api/patients/${apt.patient_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${apt.doctor_id}`),
            serviceCall(`${services.AUTH_SERVICE_URL}/api/departments/${apt.department_id}`),
            apt.receptionist_id ? 
                serviceCall(`${services.AUTH_SERVICE_URL}/api/staff/${apt.receptionist_id}`) : 
                null
        ]);
        return {
            ...apt,
            patient_name: patient?.hoten_bn || 'Unknown',
            patient_phone: patient?.sdt || null,
            patient_dob: patient?.dob || null,
            doctor_name: doctor?.hoten_nv || 'Unknown',
            department_name: department?.ten_ck || 'Unknown',
            receptionist_name: receptionist?.hoten_nv || null,
            // Add any additional enriched data here
            status_text: getStatusText(apt.status),
            status_color: getStatusColor(apt.status)
        };
        
    } catch (error) {
        console.error('Error in getAppointmentById:', error);
        throw error;
    }
};

export const getDoctorSchedule = async (doctorId, date) => {
  try {
    // Get appointments with base data
    const [appointments] = await db.query(
      `SELECT 
        a.*,
        DATE_FORMAT(a.thoi_gian_hen, '%Y-%m-%d %H:%i:%s') as thoi_gian_hen
       FROM appointments a 
       WHERE a.doctor_id = ? 
       AND DATE(a.thoi_gian_hen) = ?
       AND a.status != 'cancelled'
       ORDER BY a.thoi_gian_hen`,
      [doctorId, date]
    );

    // Enrich with patient data using service call
    const enrichedAppointments = await Promise.all(
      appointments.map(async (apt) => {
        try {
          const patient = await serviceCall(
            `${services.PATIENT_SERVICE_URL}/api/patients/${apt.patient_id}`
          );

          return {
            ...apt,
            patient_name: patient?.hoten_bn || 'Unknown',
            patient_phone: patient?.sdt || null
          };
        } catch (error) {
          console.error(`Failed to get patient data for appointment ${apt.id}:`, error);
          return {
            ...apt,
            patient_name: 'Unknown',
            patient_phone: null
          };
        }
      })
    );

    return enrichedAppointments;
  } catch (error) {
    console.error('Error in getDoctorSchedule:', error);
    throw error;
  }
};

// Example: Available slots are every 30 minutes from 08:00 to 17:00, excluding booked slots
export const getDoctorAvailableSlots = async (doctorId, date) => {
  try {
    // Get all booked slots for the doctor on the given date
    const [appointments] = await db.query(
      `SELECT DATE_FORMAT(thoi_gian_hen, '%Y-%m-%d %H:%i:%s') as thoi_gian_hen FROM appointments 
       WHERE doctor_id = ? 
       AND DATE(thoi_gian_hen) = ?
       AND status = 'confirmed'`,
      [doctorId, date]
    );

    // Generate all possible slots
    const slots = [];
    const startHour = 8;
    const endHour = 17;
    for (let hour = startHour; hour < endHour; hour++) {
      for (let min = 0; min < 60; min += 30) {
        const slot = `${date} ${hour.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}:00`;
        slots.push(slot);
      }
    }
    // Remove booked slots
    const booked = appointments.map(a => a.thoi_gian_hen.slice(0, 16)); // 'YYYY-MM-DD HH:MM'
    const available = slots.filter(slot => !booked.includes(slot.slice(0, 16)));

    // Return as array of objects
    return available.map(datetime => ({ datetime }));
  } catch (error) {
    console.error('Error in getDoctorAvailableSlots:', error);
    throw error;
  }
};

export const updateAppointmentStatus = async (id, status, note = null) => {
  try {
    const updates = ['status = ?'];
    const params = [status];

    if (note) {
      updates.push('note = CONCAT(IFNULL(note,""), ?)');
      params.push(`\n[${status} reason]: ${note}`);
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

// Helper functions for status formatting
function getStatusText(status) {
    const statusMap = {
        'pending': 'Chờ duyệt',
        'confirmed': 'Đã xác nhận',
        'cancelled': 'Đã hủy',
        'completed': 'Đã hoàn thành'
    };
    return statusMap[status] || status;
}

function getStatusColor(status) {
    const colorMap = {
        'pending': 'warning',
        'confirmed': 'success',
        'cancelled': 'danger',
        'completed': 'info'
    };
    return colorMap[status] || 'secondary';
}
