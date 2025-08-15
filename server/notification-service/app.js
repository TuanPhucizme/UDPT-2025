require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const notificationRoutes = require('./src/routes/notification.routes');
const startConsumer = require('./src/queue/consumer');

const app = express();
app.use(express.json());

// Kết nối MongoDB
(async () => {
  await mongoose.connect(process.env.MONGO_URI, { autoIndex: true });
  console.log('[✓] Mongo connected');
})().catch(err => {
  console.error('[✘] Mongo connect error:', err.message);
  process.exit(1);
});

// Routes
app.use('/api/notifications', notificationRoutes);

// Khởi động consumer (RabbitMQ)
startConsumer().catch(err => {
  console.error('[✘] Consumer start error:', err.message);
});

// Healthcheck
app.get('/health', (req, res) => res.json({ ok: true }));

module.exports = app;
