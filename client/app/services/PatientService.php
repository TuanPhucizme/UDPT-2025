<?php
require_once '../configuration/services.php';
require_once 'BaseService.php';

class PatientService extends BaseService {
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = PATIENT_SERVICE_PORT;
    }

    public function getAllPatients() {
        return $this->request('GET', '/api/patients');
    }

    public function getPatientById($id) {
        return $this->request('GET', "/api/patients/$id");
    }

    public function createPatient($data) {
        return $this->request('POST', '/api/patients', $data);
    }

    public function updatePatient($id, $data) {
        return $this->request('PUT', "/api/patients/$id", $data);
    }
}