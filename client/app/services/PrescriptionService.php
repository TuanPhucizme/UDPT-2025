<?php
require_once __DIR__ . '/../../configuration/services.php';
require_once __DIR__ . '/BaseService.php';

class PrescriptionService extends BaseService {
    private $prescriptionServiceUrl;
    
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = PRESCRIPTION_SERVICE_PORT;
        $this->prescriptionServiceUrl = PRESCRIPTION_SERVICE_URL . ':' . PRESCRIPTION_SERVICE_PORT;
    }
    
    /**
     * Get all prescriptions with optional filters
     * 
     * @param array $filters Optional filters: status, record_id, start_date, end_date
     * @return array The API response
     */
    public function getAllPrescriptions($filters = []) {
        $queryString = '';
        if (!empty($filters)) {
            $queryString = '?' . http_build_query($filters);
        }
        
        return $this->request('GET', "/api/prescriptions{$queryString}");
    }
    
    public function getPrescriptionById($id) {
        return $this->request('GET', "/api/prescriptions/{$id}");
    }
    
    public function createPrescription($data) {
        return $this->request('POST', "/api/prescriptions", $data);
    }
    
    public function updateStatus($id, $status, $pharmacistId = null) {
        $data = [
            'status' => $status
        ];
        
        if ($pharmacistId) {
            $data['pharmacist_id'] = $pharmacistId;
        }
        
        return $this->request('PUT', "/api/prescriptions/{$id}", $data);
    }
    
    /**
     * Get all medicines with optional filters
     * 
     * @param array $filters Optional filters: search, stock_status
     * @return array The API response
     */
    public function getAllMedicines($filters = []) {
        $queryString = '';
        if (!empty($filters)) {
            $queryString = '?' . http_build_query($filters);
        }
        
        $result = $this->request('GET', "/api/medicines{$queryString}");
        return $result['data'] ?? [];
    }
    
    public function getRecordPrescriptions($recordId) {
        return $this->request('GET', "/api/prescriptions/record/{$recordId}");
    }
    
    /**
     * Get prescriptions by status
     * 
     * @param string $status The status to filter by (pending, dispensed, cancelled)
     * @return array The API response
     */
    public function getPrescriptionsByStatus($status) {
        return $this->request('GET', "/api/prescriptions/status/{$status}");
    }

    /**
     * Get detailed information about a medicine
     */
    public function getMedicineById($id) {
        return $this->request('GET', "/api/medicines/{$id}");
    }

    /**
     * Create a new medicine
     */
    public function createMedicine($data) {
        return $this->request('POST', "/api/medicines", $data);
    }

    /**
     * Update medicine information
     */
    public function updateMedicine($id, $data) {
        return $this->request('PUT', "/api/medicines/{$id}", $data);
    }

    /**
     * Update medicine stock
     */
    public function updateMedicineStock($id, $data) {
        return $this->request('PUT', "/api/medicines/{$id}/stock", $data);
    }

    /**
     * Get medicine stock history
     */
    public function getMedicineStockHistory($id) {
        return $this->request('GET', "/api/medicines/{$id}/stock-history");
    }

    /**
     * Get liquid medicines usage report
     */
    public function getLiquidMedicinesReport() {
        return $this->request('GET', "/api/reports/liquid-medicines");
    }
}