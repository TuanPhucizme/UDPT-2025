const db = require('../../config/db');

exports.fetchPatientMonthlyStats = async (options = {}) => {
  const { startDate, endDate } = options;
  
  let query = `
    SELECT DATE_FORMAT(month_year, '%Y-%m') as month_year,
           COUNT(DISTINCT patient_id) as patient_count
    FROM patient_record_stats
  `;
  
  const params = [];
  if (startDate || endDate) {
    query += ' WHERE ';
    if (startDate) {
      query += 'month_year >= ?';
      params.push(startDate);
    }
    
    if (startDate && endDate) {
      query += ' AND ';
    }
    
    if (endDate) {
      query += 'month_year <= ?';
      params.push(endDate);
    }
  }
  
  query += ' GROUP BY DATE_FORMAT(month_year, "%Y-%m") ORDER BY month_year DESC';
  
  const [rows] = await db.query(query, params);
  return rows;
};

exports.fetchPrescriptionStats = async (options = {}) => {
  const { startDate, endDate, groupBy = 'day' } = options;
  
  // Determine grouping format based on the groupBy parameter
  let dateFormat = '%Y-%m-%d'; // default: day
  if (groupBy === 'month') {
    dateFormat = '%Y-%m';
  } else if (groupBy === 'year') {
    dateFormat = '%Y';
  }
  
  let query = `
    SELECT DATE_FORMAT(created_at, '${dateFormat}') as report_date,
           COUNT(id) as total_prescriptions,
           COUNT(CASE WHEN status = 'dispensed' THEN 1 END) as dispensed_count,
           COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
           COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count
    FROM prescriptions
  `;
  
  const params = [];
  if (startDate || endDate) {
    query += ' WHERE ';
    if (startDate) {
      query += 'created_at >= ?';
      params.push(startDate);
    }
    
    if (startDate && endDate) {
      query += ' AND ';
    }
    
    if (endDate) {
      query += 'created_at <= ?';
      params.push(endDate);
    }
  }
  
  query += ` GROUP BY DATE_FORMAT(created_at, '${dateFormat}') ORDER BY report_date DESC`;
  
  const [rows] = await db.query(query, params);
  return rows;
};

exports.fetchMedicineStats = async (options = {}) => {
  const { startDate, endDate, isLiquid } = options;
  
  let query = `
    SELECT 
      medicine_id,
      medicine_name,
      SUM(total_prescribed) as prescription_count,
      SUM(total_quantity) as total_quantity,
      SUM(total_liquid_volume) as total_liquid_volume,
      is_liquid
    FROM medicine_prescription_stats
    WHERE 1=1
  `;
  
  const params = [];
  
  if (typeof isLiquid === 'boolean') {
    query += ' AND is_liquid = ?';
    params.push(isLiquid ? 1 : 0);
  }
  
  if (startDate) {
    query += ' AND month_year >= ?';
    params.push(startDate);
  }
  
  if (endDate) {
    query += ' AND month_year <= ?';
    params.push(endDate);
  }
  
  query += ' GROUP BY medicine_id ORDER BY prescription_count DESC';
  
  const [rows] = await db.query(query, params);
  return rows;
};

exports.fetchDepartmentStats = async (options = {}) => {
  const { startDate, endDate } = options;
  
  let query = `
    SELECT 
      department_id,
      department_name,
      COUNT(DISTINCT record_id) as record_count,
      COUNT(DISTINCT patient_id) as patient_count
    FROM patient_record_stats
    WHERE department_id IS NOT NULL
  `;
  
  const params = [];
  
  if (startDate) {
    query += ' AND visit_date >= ?';
    params.push(startDate);
  }
  
  if (endDate) {
    query += ' AND visit_date <= ?';
    params.push(endDate);
  }
  
  query += ' GROUP BY department_id ORDER BY record_count DESC';
  
  const [rows] = await db.query(query, params);
  return rows;
};

exports.fetchDiagnosisStats = async (options = {}) => {
  const { startDate, endDate, limit = 10 } = options;
  
  let query = `
    SELECT 
      diagnosis,
      COUNT(DISTINCT record_id) as record_count
    FROM patient_record_stats
    WHERE diagnosis IS NOT NULL
  `;
  
  const params = [];
  
  if (startDate) {
    query += ' AND visit_date >= ?';
    params.push(startDate);
  }
  
  if (endDate) {
    query += ' AND visit_date <= ?';
    params.push(endDate);
  }
  
  query += ' GROUP BY diagnosis ORDER BY record_count DESC LIMIT ?';
  params.push(limit);
  
  const [rows] = await db.query(query, params);
  return rows;
};
