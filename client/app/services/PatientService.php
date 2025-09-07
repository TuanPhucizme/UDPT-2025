<?php
require_once '../configuration/services.php';
require_once 'BaseService.php';

class PatientService extends BaseService {
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = PATIENT_SERVICE_PORT;
    }
    
    /**
     * Override request method to handle offline service gracefully
     */
    public function request($method, $endpoint, $data = null) {
        try {
            // Try the standard request
            return parent::request($method, $endpoint, $data);
        } catch (Exception $e) {
            // Check if this is a service unavailability error
            if (strpos($e->getMessage(), 'Could not connect') !== false || 
                strpos($e->getMessage(), 'Connection refused') !== false ||
                strpos($e->getMessage(), 'timeout') !== false) {
                
                // For GET requests that can return cached/fallback data
                if ($method === 'GET') {
                    // Try to get data from cache if available
                    $cachedData = $this->getCachedData($endpoint);
                    if ($cachedData) {
                        return [
                            'statusCode' => 200,
                            'data' => $cachedData,
                            'serviceUnavailable' => true,
                            'message' => 'Using cached data: Patient service is currently unavailable'
                        ];
                    }
                }
                
                // For write operations that can be queued
                if ($method === 'POST' || $method === 'PUT') {
                    // Store the request for later processing
                    $this->queueRequest($method, $endpoint, $data);
                    
                    return [
                        'statusCode' => 202, // Accepted
                        'data' => [],
                        'serviceUnavailable' => true,
                        'queued' => true,
                        'message' => 'Your request has been queued and will be processed when the service is available'
                    ];
                }
                
                // Return a standard error for other cases
                return [
                    'statusCode' => 503,
                    'data' => [],
                    'serviceUnavailable' => true,
                    'message' => 'Patient service is currently unavailable'
                ];
            }
            
            // For other errors, re-throw
            throw $e;
        }
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
        // Add a flag to indicate if the phone is being explicitly changed
        if (isset($data['sdt']) && !empty($data['sdt']) && !preg_match('/^x+\d{3}$/', $data['sdt'])) {
            // This is a new phone number entered by the user
            $data['phone_changed'] = true;
        }
        
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
            
            // Process patient data to ensure phone numbers are properly formatted for display
            if (isset($result['data']) && is_array($result['data'])) {
                foreach ($result['data'] as &$patient) {
                    if (isset($patient['sdt'])) {
                        // The backend already encodes phone numbers, but in case we need custom formatting:
                        $patient['sdt_display'] = $patient['sdt']; // Keep the encoded version for display
                    }
                }
            }
            
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