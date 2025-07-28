// server.js
const app = require('./app');
const PORT = process.env.PORT || 3004;
const connectQueue = require('./src/queue/consumer');
connectQueue();
app.listen(PORT, () => {
  console.log(`[✓] Notification Service is running on port ${PORT}`);
});
