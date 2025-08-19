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
            error_log('statuscode:'.$result['statusCode']);
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

    public function search() {
        $filters = [
            'name' => $_GET['name'] ?? '',
            'gender' => $_GET['gender'] ?? '',
            'phone' => $_GET['phone'] ?? '',
        ];

        try {
            $result = $this->patientService->searchPatients($filters);
            $patients = $result['data'];
            require '../app/views/patients/search.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }

    public function view($id) {
        try {
            $patient = $this->patientService->getPatientById($id)['data'];
            $medicalRecords = $this->patientService->getMedicalRecords($id)['data'];
            require '../app/views/patients/view.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $patientData = [
                    'hoten_bn' => $_POST['hoten_bn'],
                    'dob' => $_POST['dob'],
                    'gender' => $_POST['gender'],
                    'diachi' => $_POST['diachi'],
                    'sdt' => $_POST['sdt']
                ];
                
                $result = $this->patientService->updatePatient($id, $patientData);
                
                if ($result['statusCode'] === 200) {
                    $_SESSION['success'] = 'Cập nhật thông tin bệnh nhân thành công';
                    header('Location: /patients');
                    exit;
                }
                
                $error = $result['data']['message'] ?? 'Cập nhật thất bại';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        try {
            $patient = $this->patientService->getPatientById($id)['data'];
            require '../app/views/patients/edit.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }
}