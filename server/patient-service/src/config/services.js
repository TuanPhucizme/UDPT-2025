export default {
  DOCTOR_SERVICE_URL: process.env.DOCTOR_SERVICE_URL || 'http://localhost:3004',
  PRESCRIPTION_SERVICE_URL: process.env.PRESCRIPTION_SERVICE_URL || 'http://localhost:3005',
  AUTH_SERVICE_URL: process.env.AUTH_SERVICE_URL || 'http://localhost:3001',
  NOTIFICATION_SERVICE_URL: process.env.NOTIFICATION_SERVICE_URL || 'http://localhost:3006'
};