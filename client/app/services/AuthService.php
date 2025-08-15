<?php
require_once 'BaseService.php';

class AuthService extends BaseService {
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = AUTH_SERVICE_PORT;
    }

    public function login($username, $password) {
        try {
            $response = $this->request('POST', '/api/auth/login', [
                'username' => $username,
                'password' => $password
            ]);
            return $response;
        } catch (Exception $e) {
            error_log('Auth Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function register($userData) {
        return $this->request('POST', '/api/auth/register', $userData);
    }

    public function verifyToken() {
        return $this->request('GET', '/api/auth/me');
    }
}