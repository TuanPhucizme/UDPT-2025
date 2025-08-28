const db = require('../../config/db');

exports.fetchPatientMonthlyStats = async () => {
  const [rows] = await db.query(
    'SELECT month_year, patient_count FROM patient_monthly_stats ORDER BY month_year DESC'
  );
  return rows;
};

exports.fetchPrescriptionStats = async () => {
  const [rows] = await db.query(
    'SELECT report_date, total_prescriptions FROM prescription_stats ORDER BY report_date DESC'
  );
  return rows;
};
