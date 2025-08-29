const reportService = require('../routes/services/report.service');

exports.getPatientStats = async (req, res) => {
  try {
    const { start_date, end_date } = req.query;
    
    const data = await reportService.fetchPatientMonthlyStats({
      startDate: start_date,
      endDate: end_date
    });
    
    res.json(data);
  } catch (error) {
    console.error('Error in getPatientStats:', error);
    res.status(500).json({ error: 'Failed to fetch patient statistics' });
  }
};

exports.getPrescriptionStats = async (req, res) => {
  try {
    const { start_date, end_date, group_by } = req.query;
    
    const data = await reportService.fetchPrescriptionStats({
      startDate: start_date,
      endDate: end_date,
      groupBy: group_by
    });
    
    res.json(data);
  } catch (error) {
    console.error('Error in getPrescriptionStats:', error);
    res.status(500).json({ error: 'Failed to fetch prescription statistics' });
  }
};

exports.getMedicineStats = async (req, res) => {
  try {
    const { start_date, end_date, is_liquid } = req.query;
    
    const data = await reportService.fetchMedicineStats({
      startDate: start_date,
      endDate: end_date,
      isLiquid: is_liquid === 'true' ? true : 
               (is_liquid === 'false' ? false : undefined)
    });
    
    res.json(data);
  } catch (error) {
    console.error('Error in getMedicineStats:', error);
    res.status(500).json({ error: 'Failed to fetch medicine statistics' });
  }
};

exports.getDepartmentStats = async (req, res) => {
  try {
    const { start_date, end_date } = req.query;
    
    const data = await reportService.fetchDepartmentStats({
      startDate: start_date,
      endDate: end_date
    });
    
    res.json(data);
  } catch (error) {
    console.error('Error in getDepartmentStats:', error);
    res.status(500).json({ error: 'Failed to fetch department statistics' });
  }
};

exports.getDiagnosisStats = async (req, res) => {
  try {
    const { start_date, end_date, limit } = req.query;
    
    const data = await reportService.fetchDiagnosisStats({
      startDate: start_date,
      endDate: end_date,
      limit: parseInt(limit) || 10
    });
    
    res.json(data);
  } catch (error) {
    console.error('Error in getDiagnosisStats:', error);
    res.status(500).json({ error: 'Failed to fetch diagnosis statistics' });
  }
};

exports.syncReportData = async (req, res) => {
  try {
    const { type } = req.query;
    const syncService = require('../routes/services/sync.service');
    
    if (type === 'patients') {
      await syncService.syncPatients();
      res.json({ message: 'Patient data synchronized successfully' });
    } else if (type === 'prescriptions') {
      await syncService.syncPrescriptions();
      res.json({ message: 'Prescription data synchronized successfully' });
    } else if (type === 'records') {
      await syncService.syncMedicalRecords();
      res.json({ message: 'Medical records synchronized successfully' });
    } else {
      await syncService.syncAll();
      res.json({ message: 'All data synchronized successfully' });
    }
  } catch (error) {
    console.error('Error in syncReportData:', error);
    res.status(500).json({ error: 'Data synchronization failed' });
  }
};
