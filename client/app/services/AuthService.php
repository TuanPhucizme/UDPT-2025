<?php
require_once 'BaseService.php';

class AuthService extends BaseService {
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = AUTH_SERVICE_PORT;
    }

    public function login($username, $password) {
        return $this->request('POST', '/api/auth/login', [
            'username' => $username,
            'password' => $password
        ]);
    }

    public function register($userData) {
        return $this->request('POST', '/api/auth/register', $userData);
    }

    public function verifyToken() {
        return $this->request('GET', '/api/auth/me');
    }
}