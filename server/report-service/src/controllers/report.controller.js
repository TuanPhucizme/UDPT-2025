const reportService = require('../routes/services/report.service');

exports.getPatientStats = async (req, res) => {
  const data = await reportService.fetchPatientMonthlyStats();
  res.json(data);
};

exports.getPrescriptionStats = async (req, res) => {
  const data = await reportService.fetchPrescriptionStats();
  res.json(data);
};
