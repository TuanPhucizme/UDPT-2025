// app.js
require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const connectQueue = require('./src/queue/consumer');

const app = express();
app.use(express.json());

// Kết nối MongoDB
mongoose.connect(process.env.MONGODB_URI)
  .then(() => console.log('[✓] Connected to MongoDB Atlas'))
  .catch(err => console.error('[✘] MongoDB connection error:', err));

// Routes
const notificationRoutes = require('./src/routes/notification.routes');
app.use('/api/notifications', notificationRoutes);

module.exports = app;
