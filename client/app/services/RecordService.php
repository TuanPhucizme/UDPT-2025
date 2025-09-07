<?php
require_once __DIR__ . '/../../configuration/services.php';
require_once __DIR__ . '/BaseService.php';

class RecordService extends BaseService {
    private $patientServiceUrl;
    
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = PATIENT_SERVICE_PORT;
        $this->patientServiceUrl = PATIENT_SERVICE_URL;
    }
    
    public function getAllRecords() {
        // Only get records relevant to the logged in user's role
        $userId = $_SESSION['user']['id'] ?? '';
        $query = '';
        
        if ($_SESSION['user']['role'] === 'bacsi') {
            $query = "?doctor_id={$userId}";
        }
        
        return $this->request('GET', "/api/medical-records{$query}");
    }
    
    public function getMedicalRecordById($id) {
        return $this->request('GET', "/api/medical-records/{$id}");
    }
    
    public function createMedicalRecord($data) {
        return $this->request('POST', "/api/medical-records", $data);
    }
    
    public function updateMedicalRecord($id, $data) {
        return $this->request('PUT', "/api/medical-records/{$id}", $data);
    }
    
    public function getPatientRecords($patientId) {
        return $this->request('GET', "/api/medical-records/patient/{$patientId}");
    }
}