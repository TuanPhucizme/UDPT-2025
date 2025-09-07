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
        try {
            // Build filters from GET parameters
            $filters = [];
            
            if (!empty($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            
            if (!empty($_GET['start_date'])) {
                $filters['start_date'] = $_GET['start_date'];
            }
            
            if (!empty($_GET['end_date'])) {
                $filters['end_date'] = $_GET['end_date'];
            }
            
            // Get prescriptions based on filters
            $result = $this->prescriptionService->getAllPrescriptions($filters);
            $prescriptions = $result['data'] ?? [];
            if (isset($prescriptions['serviceUnavailable']) && $prescriptions['serviceUnavailable']) {
                $this->handleServiceUnavailable($prescriptions);
                return;
            }
            require '../app/views/prescriptions/index.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /');
            exit;
        }
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
                        // Get values from form
                        $dosage = $_POST['dosage'][$index] ?? '';
                        
                        // For frequency and duration, use combined values from hidden fields
                        // or fallback to constructing them from separate inputs
                        $frequency = isset($_POST['frequency'][$index]) ? $_POST['frequency'][$index] : '';
                        if (empty($frequency) && !empty($_POST['frequency_number'][$index]) && !empty($_POST['frequency_unit'][$index])) {
                            $frequency = $_POST['frequency_number'][$index] . ' ' . $_POST['frequency_unit'][$index];
                        }
                        
                        $duration = isset($_POST['duration'][$index]) ? $_POST['duration'][$index] : '';
                        if (empty($duration) && !empty($_POST['duration_number'][$index]) && !empty($_POST['duration_unit'][$index])) {
                            $duration = $_POST['duration_number'][$index] . ' ' . $_POST['duration_unit'][$index];
                        }
                        
                        $note = $_POST['note'][$index] ?? '';
                        
                        // Validate required fields
                        if (empty($dosage) || empty($frequency) || empty($duration)) {
                            throw new Exception("Vui lòng điền đầy đủ thông tin liều dùng, tần suất và thời gian dùng cho tất cả thuốc");
                        }
                        
                        // Ensure dosage is numeric
                        if (!is_numeric($dosage)) {
                            throw new Exception("Liều lượng phải là số");
                        }
                        
                        $medicines[] = [
                            'id' => $medicineId,
                            'dosage' => (int)$dosage,  // Cast to integer
                            'frequency' => $frequency,
                            'duration' => $duration,
                            'note' => $note,
                            'total_amount' => $_POST['total_amount'][$index] ?? 0 // Include total amount
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
                // Check for service unavailability
                if (isset($result['serviceUnavailable']) && $result['serviceUnavailable']) {
                    $this->handleServiceUnavailable($result);
                    return;
                }
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
    private function handleServiceUnavailable($response) {
        $_SESSION['error'] = $response['message'] ?? 'Dịch vụ hiện không khả dụng';
        
        // Log technical details for debugging
        if (isset($response['technicalDetails'])) {
            error_log('Service unavailable: ' . $response['technicalDetails']);
        }
        
        // Redirect to appropriate page
        header('Location: /');
        exit;
    }
    public function view($id) {
        try {
            $prescription = $this->prescriptionService->getPrescriptionById($id);
            
            if (isset($prescription['serviceUnavailable']) && $prescription['serviceUnavailable']) {
                $this->handleServiceUnavailable($prescription);
                return;
            }
            
            if ($prescription['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy đơn thuốc');
            }
            
            // If we don't have medicine prices in the response, fetch them
            if (isset($prescription['data']['medicines']) && is_array($prescription['data']['medicines'])) {
                foreach ($prescription['data']['medicines'] as $index => $medicine) {
                    if (!isset($medicine['unit_price'])) {
                        // Fetch medicine details to get price
                        $medicineDetails = $this->prescriptionService->getMedicineById($medicine['id']);
                        if ($medicineDetails['statusCode'] === 200 && isset($medicineDetails['data'])) {
                            $prescription['data']['medicines'][$index]['unit_price'] = $medicineDetails['data']['don_gia'] ?? 0;
                        } else {
                            $prescription['data']['medicines'][$index]['unit_price'] = 0;
                        }
                    }
                }
            }
            
            require '../app/views/prescriptions/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            error_log($e->getMessage());
            header('Location: /prescriptions');
            exit;
        }
    }
    
    public function dispense($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            // Ensure the user is a pharmacist
            if ($_SESSION['user']['role'] !== 'duocsi') {
                throw new Exception('Only pharmacists can dispense medications');
            }
            
            $result = $this->prescriptionService->updateStatus($id, 'dispensed', $_SESSION['user']['id']);
            if (isset($result['serviceUnavailable']) && $result['serviceUnavailable']) {
                    $this->handleServiceUnavailable($result);
                    return;
                }
            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đơn thuốc đã được phát thuốc';
            } else {
                throw new Exception($result['message'] ?? 'Không thể cập nhật trạng thái đơn thuốc');
            }
            
            header('Location: /prescriptions/view/' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /prescriptions/view/' . $id);
            exit;
        }
    }
    
    public function pending() {
        try {
            // Ensure the user is a pharmacist
            if ($_SESSION['user']['role'] !== 'duocsi') {
                throw new Exception('Chỉ dược sĩ mới có thể truy cập trang này');
            }
            
            // Get all pending prescriptions
            $result = $this->prescriptionService->getAllPrescriptions(['status' => 'pending']);
            if (isset($result['serviceUnavailable']) && $result['serviceUnavailable']) {
                    $this->handleServiceUnavailable($result);
                    return;
                }
            $prescriptions = $result['data'] ?? [];
            
            require '../app/views/prescriptions/pending.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /home');
            exit;
        }
    }
}