export default {
  AUTH_SERVICE_URL: process.env.AUTH_SERVICE_URL || 'http://localhost:3000',
  PATIENT_SERVICE_URL: process.env.PATIENT_SERVICE_URL || 'http://localhost:3001',
  NOTIFICATION_SERVICE_URL: process.env.NOTIFICATION_SERVICE_URL || 'http://localhost:3004',
  INTERNAL_API_TOKEN: process.env.INTERNAL_API_TOKEN
};