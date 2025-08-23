<?php
session_start();
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
    'patients' => ['index', 'create', 'update', 'delete', 'view', 'edit', 'search'],
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