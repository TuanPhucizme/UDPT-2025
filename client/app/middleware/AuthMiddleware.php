<?php
require_once '../app/services/AuthService.php';

class AuthMiddleware {
    public static function authenticate() {
        if (!isset($_SESSION['token'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /auth/login');
            exit;
        }

        try {
            $authService = new AuthService();
            $result = $authService->verifyToken();

            if ($result['statusCode'] !== 200) {
                session_destroy();
                header('Location: /auth/login');
                exit;
            }
        } catch (Exception $e) {
            session_destroy();
            header('Location: /auth/login');
            exit;
        }
    }

    public static function authorizeRoles(...$roles) {
        return function() use ($roles) {
            if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles)) {
                header('HTTP/1.1 403 Forbidden');
                require '../app/views/error/403.php';
                exit;
            }
        };
    }
}