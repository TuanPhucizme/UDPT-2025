<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\controllers\RecordController.php
require_once __DIR__ . '/../services/RecordService.php';
require_once __DIR__ . '/../services/PatientService.php';
require_once __DIR__ . '/../services/AppointmentService.php';
require_once __DIR__ . '/../services/PrescriptionService.php';

class RecordController {
    private $recordService;
    private $patientService;
    private $appointmentService;
    private $prescriptionService;
    
    public function __construct() {
        $this->recordService = new RecordService();
        $this->patientService = new PatientService();
        $this->appointmentService = new AppointmentService();
        $this->prescriptionService = new PrescriptionService();
    }
    
    public function index() {
        // List all records the doctor has access to
        $records = $this->recordService->getAllRecords();
        require '../app/views/records/index.php';
    }
    
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Get departments for the form
                $departments = $this->appointmentService->getDepartments();
                // Check if we're creating from an appointment
                $appointmentId = $_GET['appointment_id'] ?? null;
                $patientId = $_GET['patient_id'] ?? null;
                
                $appointment = null;
                $patient = null;
                if ($appointmentId) {
                    $result = $this->appointmentService->getAppointmentById($appointmentId);
                    if ($result['statusCode'] === 200) {
                        $appointment = $result['data'];
                        $patientId = $appointment['patient_id'];
                    }
                }
                
                if ($patientId) {
                    $result = $this->patientService->getPatientById($patientId);
                    if ($result['statusCode'] === 200) {
                        $patient = $result['data'];
                    }
                }
                
                require '../app/views/records/create.php';
            } else {
                // Handle form submission
                $requiredFields = ['patient_id', 'doctor_id', 'department_id', 'lydo', 'chan_doan'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Thiếu thông tin: $field");
                    }
                }
                
                $data = [
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $_SESSION['user']['id'], // Doctor ID from session
                    'department_id' => $_POST['department_id'],
                    'lydo' => $_POST['lydo'],
                    'chan_doan' => $_POST['chan_doan'],
                    'ngay_taikham' => !empty($_POST['ngay_taikham']) ? $_POST['ngay_taikham'] : null,
                    'ghichu' => $_POST['ghichu'] ?? ''
                ];
                $result = $this->recordService->createMedicalRecord($data);
                if ($result['statusCode'] === 201) {
                    $_SESSION['success'] = 'Đã tạo hồ sơ khám bệnh thành công';
                    
                    // // If coming from an appointment, mark it as completed
                    // if (!empty($_POST['appointment_id'])) {
                    //     $this->appointmentService->completeAppointment($_POST['appointment_id']);
                    // }
                    
                    // Redirect to the prescription creation page
                    header('Location: /prescriptions/create?record_id=' . $result['data']['id']);
                    exit;
                } else {
                    throw new Exception($result['message'] ?? 'Không thể tạo hồ sơ khám bệnh');
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            error_log('error: '.$e->getMessage());
            header('Location: /records');
            exit;
        }
    }
    
    public function view($id) {
        try {
            $record = $this->recordService->getMedicalRecordById($id);
            
            if ($record['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy hồ sơ khám bệnh');
            }
            
            require '../app/views/records/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /records');
            exit;
        }
    }
}