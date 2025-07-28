require('dotenv').config();
const express = require('express');
const reportRoutes = require('./src/routes/report.routes');
const { syncPatients, syncPrescriptions } = require('./src/services/sync.service');

// Đồng bộ khi service khởi động
(async () => {
  await syncPatients();
  await syncPrescriptions();
})();

const app = express();
app.use(express.json());
app.use('/api/reports', reportRoutes);

module.exports = app;
