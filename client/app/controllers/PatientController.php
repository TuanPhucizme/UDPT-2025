<?php
require_once '../app/services/PatientService.php';

class PatientController {
    private $patientService;

    public function __construct() {
        $this->patientService = new PatientService();
    }

    public function index() {
        try {
            $result = $this->patientService->getAllPatients();
            
            if ($result['statusCode'] === 401) {
                session_destroy();
                header('Location: /auth/login');
                exit;
            }
            
            $patients = $result['data'];
            require '../app/views/patients/index.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $patientData = [
                    'name' => $_POST['name'],
                    'dob' => $_POST['dob'],
                    'gender' => $_POST['gender'],
                    'address' => $_POST['address'],
                    'phone' => $_POST['phone'],
                    'email' => $_POST['email']
                ];
                
                $result = $this->patientService->createPatient($patientData);
                
                if ($result['statusCode'] === 401) {
                    session_destroy();
                    header('Location: /auth/login');
                    exit;
                }
                
                if ($result['statusCode'] === 201) {
                    $_SESSION['success'] = 'Thêm bệnh nhân thành công';
                    header('Location: /patients');
                    exit;
                }
                
                $error = $result['data']['message'] ?? 'Tạo bệnh nhân thất bại';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        require '../app/views/patients/create.php';
    }
}