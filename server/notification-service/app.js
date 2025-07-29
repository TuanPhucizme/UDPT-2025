// app.js
require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const connectQueue = require('./src/queue/consumer');

const app = express();
app.use(express.json());

// Kết nối MongoDB
mongoose.connect(process.env.MONGO_URI, {
  useNewUrlParser: true,
  useUnifiedTopology: true,
}).then(() => {
  console.log('[✓] MongoDB connected');
  connectQueue();
}).catch(err => console.error('[X] MongoDB error:', err));

// Routes
const notificationRoutes = require('./src/routes/notification.routes');
app.use('/api/notifications', notificationRoutes);

module.exports = app;
