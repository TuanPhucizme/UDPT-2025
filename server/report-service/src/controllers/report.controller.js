const reportService = require('../routes/services/report.service');
const db = require('../config/db'); // Ensure correct path to your db config

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

exports.getSyncStatus = async (req, res) => {
  try {
    // Get last sync status for each type
    const [rows] = await db.query(`
      SELECT sync_type, 
             MAX(start_time) as last_start_time, 
             MAX(end_time) as last_end_time,
             SUM(CASE WHEN status = 'completed' OR status = 'partially_completed' THEN records_processed ELSE 0 END) as total_records,
             MAX(CASE WHEN id = (SELECT MAX(id) FROM sync_log WHERE sync_type = s.sync_type) THEN status END) as last_status,
             MAX(CASE WHEN id = (SELECT MAX(id) FROM sync_log WHERE sync_type = s.sync_type) THEN message END) as last_message
      FROM sync_log s
      GROUP BY sync_type
      ORDER BY MAX(start_time) DESC
    `);
    
    res.json({
      sync_status: rows,
      last_sync: rows.length > 0 ? {
        time: rows[0].last_end_time || rows[0].last_start_time,
        status: rows[0].last_status
      } : null
    });
  } catch (error) {
    console.error('Error in getSyncStatus:', error);
    res.status(500).json({ error: 'Failed to fetch sync status' });
  }
};
