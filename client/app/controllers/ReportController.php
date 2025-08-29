<?php
// app/controllers/ReportController.php
require_once __DIR__ . '/../services/ReportService.php';
require_once __DIR__ . '/../services/PrescriptionService.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AppointmentService.php';
class ReportController {
    private $reportService;
    private $prescriptionService;
    private $authService;
    
    public function __construct() {
        $this->reportService = new ReportService();
        $this->prescriptionService = new PrescriptionService();
        $this->authService = new AuthService();
        $this->appointmentService = new AppointmentService();
    }
    
    /**
     * Dashboard - Show main reports
     */
    public function index() {
        try {
            // Get patient stats for the last 6 months
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 months'));
            
            $patientStats = $this->reportService->getPatientStats($startDate, $endDate);
            $prescriptionStats = $this->reportService->getPrescriptionStats($startDate, $endDate, 'month');
            
            // Get medicine usage stats
            $medicineStats = $this->reportService->getMedicineStats($startDate, $endDate);
            
            // Get department stats
            $departmentStats = $this->reportService->getDepartmentStats($startDate, $endDate);
            
            // Get diagnosis stats (top 5)
            $diagnosisStats = $this->reportService->getDiagnosisStats($startDate, $endDate, 5);
            
            require '../app/views/reports/index.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy dữ liệu báo cáo: ' . $e->getMessage();
            header('Location: /home');
            exit;
        }
    }
    
    /**
     * Patient report detailed view
     */
    public function patients() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-12 months'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            $patientStats = $this->reportService->getPatientStats($startDate, $endDate);
            
            // Get departments for filtering
            $departments = $this->appointmentService->getDepartments();
            
            require '../app/views/reports/patients.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy báo cáo bệnh nhân: ' . $e->getMessage();
            header('Location: /reports');
            exit;
        }
    }
    
    /**
     * Prescription report detailed view
     */
    public function prescriptions() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $groupBy = $_GET['group_by'] ?? 'day';
            
            $prescriptionStats = $this->reportService->getPrescriptionStats($startDate, $endDate, $groupBy);
            
            require '../app/views/reports/prescriptions.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy báo cáo đơn thuốc: ' . $e->getMessage();
            header('Location: /reports');
            exit;
        }
    }
    
    /**
     * Medicine report detailed view
     */
    public function medicines() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $isLiquid = isset($_GET['is_liquid']) ? ($_GET['is_liquid'] === 'true') : null;
            
            $medicineStats = $this->reportService->getMedicineStats($startDate, $endDate, $isLiquid);
            
            require '../app/views/reports/medicines.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy báo cáo thuốc: ' . $e->getMessage();
            header('Location: /reports');
            exit;
        }
    }
    
    /**
     * Department report detailed view
     */
    public function departments() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            $departmentStats = $this->reportService->getDepartmentStats($startDate, $endDate);
            
            require '../app/views/reports/departments.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy báo cáo chuyên khoa: ' . $e->getMessage();
            header('Location: /reports');
            exit;
        }
    }
    
    /**
     * Diagnosis report detailed view
     */
    public function diagnoses() {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $limit = $_GET['limit'] ?? 20;
            
            $diagnosisStats = $this->reportService->getDiagnosisStats($startDate, $endDate, $limit);
            
            require '../app/views/reports/diagnoses.php';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể lấy báo cáo chẩn đoán: ' . $e->getMessage();
            header('Location: /reports');
            exit;
        }
    }
    
    /**
     * Sync data from services
     */
    public function sync() {
        try {
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                $_SESSION['error'] = "Bạn không có quyền truy cập chức năng này.";
                header('Location: /home');
                return;
            }
            
            $reportService = new ReportService();
            
            // Check if a sync operation is requested
            if (isset($_GET['type'])) {
                $type = $_GET['type'];
                $syncResult = $reportService->syncReportData($type);
                
                if (isset($syncResult['error'])) {
                    $_SESSION['error'] = "Lỗi đồng bộ dữ liệu: " . $syncResult['error'];
                } else {
                    $_SESSION['success'] = "Đồng bộ dữ liệu thành công!";
                }
                
                // Redirect back to sync page without the type parameter
                header('Location: /reports/sync');
                return;
            }
            
            // Get sync status information
            $syncStatus = $reportService->getSyncStatus();
            
            require '../app/views/reports/sync.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /reports');
        }
    }
}
