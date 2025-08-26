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
                // Validate required fields
                if (empty($_POST['hoten_bn']) || empty($_POST['dob']) || empty($_POST['gender'])) {
                    throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
                }

                $patientData = [
                    'hoten_bn' => $_POST['hoten_bn'],
                    'dob' => $_POST['dob'],
                    'gender' => $_POST['gender'],
                    'sdt' => $_POST['sdt'] ?? null,
                    'diachi' => $_POST['diachi'] ?? null,
                    'tiensu_benh' => $_POST['tiensu_benh'] ?? null,
                    'lichsu_kham' => $_POST['lichsu_kham'] ?? null
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
            'age' => !empty($_GET['age']) ? intval($_GET['age']) : null
        ];
        try {
            $result = $this->patientService->searchPatients($filters);
            if ($result['statusCode'] === 401) {
                session_destroy();
                header('Location: /auth/login');
                exit;
            }
            
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

    public function searchAjax() {
        try {
            $term = $_GET['term'] ?? '';
            
            // Validate search term
            if (strlen($term) < 2) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Từ khóa tìm kiếm phải có ít nhất 2 ký tự',
                    'data' => []
                ]);
                return;
            }

            // Determine search type based on input
            $isPhone = preg_match('/^\d+$/', $term);
            $filters = $isPhone 
                ? ['phone' => $term]
                : ['name' => $term];

            $result = $this->patientService->searchPatientsAjax($filters);
            
            if ($result['statusCode'] === 401) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'data' => []
                ]);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => array_slice($result['data'] ?? [], 0, 10)
            ]);
            
        } catch (Exception $e) {
            error_log("Patient search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi hệ thống',
                'data' => []
            ]);
        }
    }
}