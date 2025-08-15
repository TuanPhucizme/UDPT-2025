<?php
require_once '../app/services/AuthService.php';
require_once '../app/models/User.php';

class AuthController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User();
            $user->username = $_POST['username'] ?? '';
            $user->password = $_POST['password'] ?? '';

            if ($user->validate()) {
                try {
                    $result = $this->authService->login($user->username, $user->password);
                    
                    if ($result['statusCode'] === 200 && isset($result['data']['token'])) {
                        $_SESSION['token'] = $result['data']['token'];
                        
                        // Verify token and get user data
                        $userData = $this->authService->verifyToken();
                        if ($userData['statusCode'] === 200) {
                            $_SESSION['user'] = $userData['data']['user'];
                            header('Location: /');
                            exit;
                        }
                    }
                    
                    $error = $result['data']['message'] ?? 'Đăng nhập thất bại';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $errors = $user->getErrors();
            }
        }
        
        require '../app/views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User();
            $user->setAttributes($_POST);

            if ($user->validate()) {
                try {
                    $result = $this->authService->register($user->toArray());
                    if ($result['statusCode'] === 201) {
                        header('Location: /auth/login');
                        exit;
                    }
                    $error = $result['data']['message'] ?? 'Đăng ký thất bại';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $errors = $user->getErrors();
            }
        }
        require '../app/views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}
?>