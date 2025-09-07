<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\controllers\AdminController.php

require_once '../app/middleware/ServiceAvailabilityMiddleware.php';

class AdminController {
    public function dashboard() {
        // Only admin can access this page
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: /');
            exit;
        }
        
        require '../app/views/admin/dashboard.php';
    }
    
    public function serviceHealth() {
        // Only admin can access this page
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: /');
            exit;
        }
        
        // Check all services
        $healthResults = ServiceAvailabilityMiddleware::checkMultipleServices();
        
        // If this is an AJAX request, return JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($healthResults);
            exit;
        }
        
        // Otherwise, render the page
        require '../app/views/admin/service-health.php';
    }
    
    public function refreshServiceHealth() {
        // Only admin can access this
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Get specific services to check if provided
        $services = isset($_POST['services']) && is_array($_POST['services']) 
            ? $_POST['services'] 
            : [];
        
        $healthResults = ServiceAvailabilityMiddleware::checkMultipleServices($services);
        
        header('Content-Type: application/json');
        echo json_encode($healthResults);
        exit;
    }
}