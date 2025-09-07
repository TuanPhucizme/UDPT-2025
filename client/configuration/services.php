<?php
/**
 * Microservices Configuration
 */

// Base URLs
define('BASE_URL', 'http://localhost');

// Service Ports
define('AUTH_SERVICE_PORT', '3000');
define('PATIENT_SERVICE_PORT', '3001');
define('APPOINTMENT_SERVICE_PORT', '3002');
define('PRESCRIPTION_SERVICE_PORT', '3003');
define('NOTIFICATION_SERVICE_PORT', '3004');
define('REPORT_SERVICE_PORT', '3005');

// Service URLs
define('AUTH_SERVICE_URL', 'http://localhost:' . AUTH_SERVICE_PORT);
define('PATIENT_SERVICE_URL', 'http://localhost:' . PATIENT_SERVICE_PORT);
define('APPOINTMENT_SERVICE_URL', 'http://localhost:' . APPOINTMENT_SERVICE_PORT);
define('NOTIFICATION_SERVICE_URL', 'http://localhost:' . NOTIFICATION_SERVICE_PORT);
define('PRESCRIPTION_SERVICE_URL', 'http://localhost:' . PRESCRIPTION_SERVICE_PORT);
define('REPORT_SERVICE_URL', 'http://localhost:' . REPORT_SERVICE_PORT);

// Security
define('INTERNAL_API_TOKEN', 'your-secure-internal-api-token');

// RabbitMQ Configuration
define('RABBITMQ_HOST', 'localhost');
define('RABBITMQ_PORT', '5672');
define('RABBITMQ_USER', 'guest');
define('RABBITMQ_PASS', 'guest');
define('RABBITMQ_VHOST', '/');

// Service configuration as array for easier use
$config['services'] = [
    'auth_service' => AUTH_SERVICE_URL,
    'patient_service' => PATIENT_SERVICE_URL,
    'appointment_service' => APPOINTMENT_SERVICE_URL,
    'notification_service' => NOTIFICATION_SERVICE_URL . '/api/notifications',
    'prescription_service' => PRESCRIPTION_SERVICE_URL,
    'report_service' => REPORT_SERVICE_URL,
];

// Service health check endpoints
$config['health_checks'] = [
    'auth' => AUTH_SERVICE_URL . '/health',
    'patient' => PATIENT_SERVICE_URL . '/health',
    'appointment' => APPOINTMENT_SERVICE_URL . '/health',
    'notification' => NOTIFICATION_SERVICE_URL . '/health',
    'prescription' => PRESCRIPTION_SERVICE_URL . '/health',
    'report' => REPORT_SERVICE_URL . '/health',
];

// RabbitMQ configuration
$config['rabbitmq'] = [
    'host' => RABBITMQ_HOST,
    'port' => RABBITMQ_PORT,
    'user' => RABBITMQ_USER,
    'password' => RABBITMQ_PASS,
    'vhost' => RABBITMQ_VHOST,
    'url' => sprintf(
        'amqp://%s:%s@%s:%s%s',
        RABBITMQ_USER,
        RABBITMQ_PASS,
        RABBITMQ_HOST,
        RABBITMQ_PORT,
        RABBITMQ_VHOST
    ),
];