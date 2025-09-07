require('dotenv').config();
const express = require('express');
const reportRoutes = require('./src/routes/report.routes');
const healthRoutes = require('./src/routes/health.routes');
const { syncPatients, syncPrescriptions } = require('./src/routes/services/sync.service');

// Đồng bộ khi service khởi động
(async () => {
  await syncPatients();
  await syncPrescriptions();
})();

const app = express();
app.use(express.json());
app.use('/api/reports', reportRoutes);
app.use('/health', healthRoutes);
module.exports = app;
