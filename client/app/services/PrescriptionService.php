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
    
    public function getAllPrescriptions($filters = []) {
        $query = '';
        if (!empty($filters)) {
            $query = '?' . http_build_query($filters);
        }
        
        return $this->request('GET', "/api/prescriptions{$query}");
    }
    
    public function getPrescriptionById($id) {
        return $this->request('GET', "/api/prescriptions/{$id}");
    }
    
    public function createPrescription($data) {
        return $this->request('POST', "/api/prescriptions", $data);
    }
    
    public function updateStatus($id, $status) {
        return $this->request('PUT', "/api/prescriptions/{$id}", [
            'status' => $status,
            'pharmacist_id' => $_SESSION['user']['id'] ?? null
        ]);
    }
    
    public function getAllMedicines() {
        $result = $this->request('GET', "/api/prescriptions/medicines");
        return $result['data'] ?? [];
    }
    
    public function getRecordPrescriptions($recordId) {
        return $this->request('GET', "/api/prescriptions/record/{$recordId}");
    }
}