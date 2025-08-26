<?php
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); // Adjust path

// Report all errors (but only log them)
error_reporting(E_ALL);
require_once '../configuration/services.php';
require_once '../app/middleware/AuthMiddleware.php';

// Get the URI and remove leading/trailing slashes
$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri, PHP_URL_PATH);
$uri = trim($uri, '/');

// Remove index.php from URI if present
$uri = str_replace('index.php/', '', $uri);

// Remove the base path if your application is in a subdirectory
$basePath = 'UDPT/UDPT-2025/client/public';
$uri = str_replace($basePath, '', $uri);

// Default route
if (empty($uri)) {
    $uri = 'home';
}

// Parse route parameters
$segments = explode('/', $uri);
$controller = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$params = array_slice($segments, 2);

// Define valid routes
$routes = [
    'auth' => ['login', 'logout', 'register'],
    'patients' => ['index', 'create', 'update', 'delete', 'view', 'edit', 'search','searchAjax'],
    'home' => ['index'],
    'appointments' => [
        'index', 
        'create', 
        'view', 
        'pending',
        'propose', 
        'confirm', 
        'decline',
        'getAvailableSlots'
    ],
    'api' => [
        'patients',
        'departments',
        'doctors'
    ]
];
try {
    // Validate route
    if (!array_key_exists($controller, $routes) || 
        !in_array($action, $routes[$controller])) {
        throw new Exception('Route not found', 404);
    }
    switch ($controller) {
        case 'auth':
            require_once '../app/controllers/AuthController.php';
            $controller = new AuthController();
            
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
            } else {
                throw new Exception('Action not found', 404);
            }
            break;
        case 'patients':
            AuthMiddleware::authenticate();
            require_once '../app/controllers/PatientController.php';
            $controller = new PatientController();
            
            if (method_exists($controller, $action)) {
                // Check role-based access
                if ($action === 'create' || $action === 'update') {
                    AuthMiddleware::authorizeRoles('letan', 'admin')();
                } elseif ($action === 'search') {
                    AuthMiddleware::authorizeRoles('bacsi', 'duocsi', 'letan', 'admin')();
                }
                call_user_func_array([$controller, $action], $params);
            } else {
                throw new Exception('Action not found', 404);
            }
            break;
        case 'appointments':
            AuthMiddleware::authenticate();
            require_once '../app/controllers/AppointmentController.php';
            $controller = new AppointmentController();
            if (method_exists($controller, $action)) {
                switch ($action) {
                    case 'create':
                        AuthMiddleware::authorizeRoles('letan', 'admin')();
                        break;
                    case 'pending':
                    case 'propose':
                    case 'confirm':
                    case 'decline':
                        AuthMiddleware::authorizeRoles('bacsi', 'admin')();
                        break;
                }
                call_user_func_array([$controller, $action], $params);
            } else {
                throw new Exception('Action not found', 404);
            }
            break;
        case 'home':
            require_once '../app/views/home.php';
            break;
        case 'api':
            AuthMiddleware::authenticate();
            $apiPath = implode('/', array_slice($segments, 1));
            error_log("api path: $apiPath");
            switch ($apiPath) {
                case 'patients/search':
                    require_once '../app/controllers/PatientController.php';
                    $controller = new PatientController();
                    $controller->searchAjax();
                    break;
                    
                case (preg_match('/^departments\/(\d+)\/doctors$/', $apiPath, $matches) ? true : false):
                    require_once '../app/controllers/AppointmentController.php';
                    error_log('getDoctorsByDepartment called');
                    $controller = new AppointmentController();
                    $controller->getDoctorsByDepartment($matches[1]);
                    break;
                    
                case (preg_match('/^doctors\/(\d+)\/schedule$/', $apiPath, $matches) ? true : false):
                    require_once '../app/controllers/AppointmentController.php';
                    error_log('getDoctorSchedule called');
                    $controller = new AppointmentController();
                    $controller->getDoctorSchedule();
                    break;
                    
                case (preg_match('/^doctors\/(\d+)\/slots$/', $apiPath, $matches) ? true : false):
                    require_once '../app/controllers/AppointmentController.php';
                    error_log('getAvailableSlots called');
                    $controller = new AppointmentController();
                    $controller->getAvailableSlots();
                    break;
                    
                default:
                    throw new Exception('API route not found', 404);
            }
            break;
        default:
            throw new Exception('Controller not found', 404);
    }
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    http_response_code($statusCode);
    
    $error = [
        'message' => $e->getMessage(),
        'code' => $statusCode
    ];
    
    require '../app/views/error.php';
}