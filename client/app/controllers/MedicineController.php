<?php
require_once __DIR__ . '/../services/PrescriptionService.php';

class MedicineController {
    private $prescriptionService;
    
    public function __construct() {
        $this->prescriptionService = new PrescriptionService();
    }
    
    public function index() {
        try {
            // Apply filters
            $filters = [];
            
            if (!empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            
            // Check if filter parameter exists and map it to stock_status
            if (!empty($_GET['filter'])) {
                if ($_GET['filter'] !== 'all') {
                    $filters['stock_status'] = $_GET['filter'];
                }
            }
            // Also check direct stock_status parameter (for backward compatibility)
            elseif (!empty($_GET['stock_status'])) {
                $filters['stock_status'] = $_GET['stock_status'];
            }
            
            // Get all medicines
            $medicines = $this->prescriptionService->getAllMedicines($filters);
            
            // Count medicines for status badges
            $lowStockCount = 0;
            $outOfStockCount = 0;
            $liquidMedicinesCount = 0;
            
            foreach ($medicines as $medicine) {
                if ($medicine['so_luong'] <= 0) {
                    $outOfStockCount++;
                } elseif ($medicine['so_luong'] <= 10) {
                    $lowStockCount++;
                }
                
                if ($medicine['is_liquid']) {
                    $liquidMedicinesCount++;
                }
            }
            
            require '../app/views/medicines/index.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /home');
            exit;
        }
    }
    
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                require '../app/views/medicines/create.php';
            } else {
                // Validate form data
                $requiredFields = ['ten_thuoc', 'don_vi', 'don_gia', 'so_luong'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Thiếu thông tin: $field");
                    }
                }
                
                // Process form data
                $data = [
                    'ten_thuoc' => $_POST['ten_thuoc'],
                    'don_vi' => $_POST['don_vi'],
                    'don_gia' => (float)$_POST['don_gia'],
                    'so_luong' => (int)$_POST['so_luong'],
                    'is_liquid' => isset($_POST['is_liquid']) ? 1 : 0
                ];
                
                // Handle liquid-specific fields
                if (isset($_POST['is_liquid']) && $_POST['is_liquid'] == 1) {
                    $data['volume_per_bottle'] = (float)$_POST['volume_per_bottle'];
                    $data['volume_unit'] = $_POST['volume_unit'];
                }
                
                $result = $this->prescriptionService->createMedicine($data);
                
                if ($result['statusCode'] === 201) {
                    $_SESSION['success'] = 'Đã thêm thuốc mới thành công';
                    header('Location: /medicines');
                    exit;
                } else {
                    throw new Exception($result['message'] ?? 'Không thể thêm thuốc');
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /medicines/create');
            exit;
        }
    }
    
    public function edit($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $this->prescriptionService->getMedicineById($id);
                
                if ($result['statusCode'] !== 200) {
                    throw new Exception('Không tìm thấy thuốc');
                }
                
                $medicine = $result['data'];
                require '../app/views/medicines/edit.php';
            } else {
                // Validate form data
                $requiredFields = ['ten_thuoc', 'don_vi', 'don_gia'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Thiếu thông tin: $field");
                    }
                }
                
                // Process form data
                $data = [
                    'ten_thuoc' => $_POST['ten_thuoc'],
                    'don_vi' => $_POST['don_vi'],
                    'don_gia' => (float)$_POST['don_gia'],
                    'is_liquid' => isset($_POST['is_liquid']) ? 1 : 0
                ];
                
                // Handle liquid-specific fields
                if (isset($_POST['is_liquid']) && $_POST['is_liquid'] == 1) {
                    $data['volume_per_bottle'] = (float)$_POST['volume_per_bottle'];
                    $data['volume_unit'] = $_POST['volume_unit'];
                }
                
                $result = $this->prescriptionService->updateMedicine($id, $data);
                
                if ($result['statusCode'] === 200) {
                    $_SESSION['success'] = 'Đã cập nhật thông tin thuốc thành công';
                    header('Location: /medicines');
                    exit;
                } else {
                    throw new Exception($result['message'] ?? 'Không thể cập nhật thuốc');
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /medicines');
            exit;
        }
    }
    
    public function updateStock($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $this->prescriptionService->getMedicineById($id);
                
                if ($result['statusCode'] !== 200) {
                    throw new Exception('Không tìm thấy thuốc');
                }
                
                $medicine = $result['data'];
                require '../app/views/medicines/update_stock.php';
            } else {
                // Validate form data
                if (!isset($_POST['quantity']) || $_POST['quantity'] === '') {
                    throw new Exception("Vui lòng nhập số lượng");
                }
                
                if (!isset($_POST['action_type']) || $_POST['action_type'] === '') {
                    throw new Exception("Vui lòng chọn loại thao tác");
                }
                
                $data = [
                    'quantity' => (int)$_POST['quantity'],
                    'action_type' => $_POST['action_type'],
                    'note' => $_POST['note'] ?? ''
                ];
                
                $result = $this->prescriptionService->updateMedicineStock($id, $data);
                
                if ($result['statusCode'] === 200) {
                    $_SESSION['success'] = 'Đã cập nhật kho thuốc thành công';
                    header('Location: /medicines');
                    exit;
                } else {
                    throw new Exception($result['message'] ?? 'Không thể cập nhật kho thuốc');
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /medicines');
            exit;
        }
    }
    
    public function stockHistory($id) {
        try {
            $result = $this->prescriptionService->getMedicineById($id);
            
            if ($result['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy thuốc');
            }
            
            $medicine = $result['data'];
            
            // Get stock history
            $historyResult = $this->prescriptionService->getMedicineStockHistory($id);
            $stockHistory = $historyResult['data'] ?? [];
            
            require '../app/views/medicines/stock_history.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /medicines');
            exit;
        }
    }
    
    public function report() {
        try {
            // Get liquid medicines report
            $result = $this->prescriptionService->getLiquidMedicinesReport();
            $liquidMedicines = $result['data'] ?? [];
            
            require '../app/views/medicines/report.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /medicines');
            exit;
        }
    }
    
    /**
     * API method to get medicine details
     * 
     * @param int $id Medicine ID
     * @return void Outputs JSON
     */
    public function apiGetMedicineDetails($id) {
        try {
            $result = $this->prescriptionService->getMedicineById($id);
            
            if ($result['statusCode'] !== 200) {
                http_response_code($result['statusCode']);
                echo json_encode(['error' => $result['message'] ?? 'Medicine not found']);
                return;
            }
            
            // Return medicine data
            header('Content-Type: application/json');
            echo json_encode($result['data']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while fetching medicine details'
            ]);
        }
    }
}