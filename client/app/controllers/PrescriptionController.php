<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\controllers\PrescriptionController.php
require_once __DIR__ . '/../services/PrescriptionService.php';
require_once __DIR__ . '/../services/PatientService.php';
require_once __DIR__ . '/../services/RecordService.php';

class PrescriptionController {
    private $prescriptionService;
    private $patientService;
    private $recordService;
    
    public function __construct() {
        $this->prescriptionService = new PrescriptionService();
        $this->patientService = new PatientService();
        $this->recordService = new RecordService();
    }
    
    public function index() {
        // List prescriptions based on role
        $prescriptions = $this->prescriptionService->getAllPrescriptions();
        require '../app/views/prescriptions/index.php';
    }
    
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Get record info if provided
                $recordId = $_GET['record_id'] ?? null;
                $patientId = $_GET['patient_id'] ?? null;
                
                $record = null;
                $patient = null;
                
                if ($recordId) {
                    $result = $this->recordService->getMedicalRecordById($recordId);
                    if ($result['statusCode'] === 200) {
                        $record = $result['data'];
                        $patientId = $record['patient_id'];
                    }
                }
                
                if ($patientId) {
                    $result = $this->patientService->getPatientById($patientId);
                    if ($result['statusCode'] === 200) {
                        $patient = $result['data'];
                    }
                }
                
                // Get medicines list
                $medicines = $this->prescriptionService->getAllMedicines();
                
                require '../app/views/prescriptions/create.php';
            } else {
                // Handle form submission
                $requiredFields = ['record_id', 'patient_id', 'medicines'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Thiếu thông tin: $field");
                    }
                }
                
                // Parse medicines array from form data
                $medicines = [];
                foreach ($_POST['medicines'] as $index => $medicineId) {
                    if (!empty($medicineId)) {
                        $medicines[] = [
                            'id' => $medicineId,
                            'dosage' => $_POST['dosage'][$index],
                            'frequency' => $_POST['frequency'][$index],
                            'duration' => $_POST['duration'][$index],
                            'note' => $_POST['note'][$index] ?? ''
                        ];
                    }
                }
                
                if (empty($medicines)) {
                    throw new Exception("Đơn thuốc phải có ít nhất 1 loại thuốc");
                }
                
                $data = [
                    'record_id' => $_POST['record_id'],
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $_SESSION['user']['id'],
                    'medicines' => $medicines
                ];
                
                $result = $this->prescriptionService->createPrescription($data);
                
                if ($result['statusCode'] === 201) {
                    $_SESSION['success'] = 'Đã tạo đơn thuốc thành công';
                    header('Location: /records/view/' . $_POST['record_id']);
                    exit;
                } else {
                    throw new Exception($result['message'] ?? 'Không thể tạo đơn thuốc');
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            // If coming from a record, redirect back to it
            if (!empty($_GET['record_id'])) {
                header('Location: /records/view/' . $_GET['record_id']);
            } else {
                header('Location: /prescriptions');
            }
            exit;
        }
    }
    
    public function view($id) {
        try {
            $prescription = $this->prescriptionService->getPrescriptionById($id);
            
            if ($prescription['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy đơn thuốc');
            }
            
            require '../app/views/prescriptions/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /prescriptions');
            exit;
        }
    }
    
    public function dispense($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $result = $this->prescriptionService->updateStatus($id, 'dispensed');
            
            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đơn thuốc đã được phát thuốc';
            } else {
                throw new Exception($result['message'] ?? 'Không thể cập nhật trạng thái đơn thuốc');
            }
            
            header('Location: /prescriptions/view/' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /prescriptions');
            exit;
        }
    }
}