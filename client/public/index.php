<?php
require_once '../config/services.php';

$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'appointments/search':
        require_once '../app/controllers/AppointmentController.php';
        $controller = new AppointmentController();
        $controller->search();
        break;
    
    case 'appointments/create':
        require_once '../app/controllers/AppointmentController.php';
        $controller = new AppointmentController();
        $controller->create();
        break;
    
    // Add other routes for different services
    
    default:
        // Show 404 or homepage
        require_once '../app/views/home.php';
        break;
}