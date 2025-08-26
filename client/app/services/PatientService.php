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
    public function searchPatients($filters) {
        $queryString = http_build_query($filters);
        return $this->request('GET', "/api/patients?{$queryString}");
    }
    public function searchPatientsAjax($filters) {
        try {
            if (empty($filters['name']) && empty($filters['phone'])) {
                return [
                    'statusCode' => 400,
                    'data' => [],
                    'message' => 'Search criteria required'
                ];
            }

            $queryString = http_build_query([
                'name' => $filters['name'] ?? '',
                'phone' => $filters['phone'] ?? ''
            ]);

            $result = $this->request('GET', "/api/patients?{$queryString}");
            error_log($queryString);
            return [
                'statusCode' => $result['statusCode'] ?? 500,
                'data' => $result['data'] ?? [],
                'message' => $result['message'] ?? 'Unknown error'
            ];
        } catch (Exception $e) {
            error_log("PatientService searchPatients error: " . $e->getMessage());
            return [
                'statusCode' => 500,
                'data' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    public function getMedicalRecords($patientId) {
        return $this->request('GET', "/api/medical-records/patient/{$patientId}");
    }
}