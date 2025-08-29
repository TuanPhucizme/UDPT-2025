<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../configuration/services.php';

class ReportService extends BaseService {
    public function __construct() {
        $this->baseUrl = REPORT_SERVICE_URL;
        $this->port = REPORT_SERVICE_PORT;
    }
    
    /**
     * Get patient statistics by month
     */
    public function getPatientStats($startDate = null, $endDate = null) {
        $queryParams = [];
        if ($startDate) {
            $queryParams[] = 'start_date=' . urlencode($startDate);
        }
        if ($endDate) {
            $queryParams[] = 'end_date=' . urlencode($endDate);
        }
        
        $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
        $response = $this->request('GET', "/api/reports/patients{$queryString}");
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? null
        ];
    }
    
    /**
     * Get prescription statistics
     */
    public function getPrescriptionStats($startDate = null, $endDate = null, $groupBy = 'day') {
        $queryParams = [];
        if ($startDate) {
            $queryParams[] = 'start_date=' . urlencode($startDate);
        }
        if ($endDate) {
            $queryParams[] = 'end_date=' . urlencode($endDate);
        }
        if ($groupBy) {
            $queryParams[] = 'group_by=' . urlencode($groupBy);
        }
        
        $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
        $response = $this->request('GET', "/api/reports/prescriptions{$queryString}");
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? null
        ];
    }
    
    /**
     * Get medicine statistics
     */
    public function getMedicineStats($startDate = null, $endDate = null, $isLiquid = null) {
        $queryParams = [];
        if ($startDate) {
            $queryParams[] = 'start_date=' . urlencode($startDate);
        }
        if ($endDate) {
            $queryParams[] = 'end_date=' . urlencode($endDate);
        }
        if ($isLiquid !== null) {
            $queryParams[] = 'is_liquid=' . ($isLiquid ? 'true' : 'false');
        }
        
        $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
        $response = $this->request('GET', "/api/reports/medicines{$queryString}");
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? null
        ];
    }
    
    /**
     * Get department statistics
     */
    public function getDepartmentStats($startDate = null, $endDate = null) {
        $queryParams = [];
        if ($startDate) {
            $queryParams[] = 'start_date=' . urlencode($startDate);
        }
        if ($endDate) {
            $queryParams[] = 'end_date=' . urlencode($endDate);
        }
        
        $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
        $response = $this->request('GET', "/api/reports/departments{$queryString}");
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? null
        ];
    }
    
    /**
     * Get diagnosis statistics
     */
    public function getDiagnosisStats($startDate = null, $endDate = null, $limit = 10) {
        $queryParams = [];
        if ($startDate) {
            $queryParams[] = 'start_date=' . urlencode($startDate);
        }
        if ($endDate) {
            $queryParams[] = 'end_date=' . urlencode($endDate);
        }
        if ($limit) {
            $queryParams[] = 'limit=' . urlencode($limit);
        }
        
        $queryString = !empty($queryParams) ? '?' . implode('&', $queryParams) : '';
        $response = $this->request('GET', "/api/reports/diagnoses{$queryString}");
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? null
        ];
    }
    
    /**
     * Sync report data
     */
    public function syncReportData($type = null) {
        $queryParams = $type ? "?type={$type}" : '';
        $response = $this->request('POST', "/api/reports/sync{$queryParams}", [], true); // Use internal token
        
        return [
            'success' => $response['statusCode'] === 200,
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? 'Sync completed successfully'
        ];
    }

    /**
     * Get the status of sync operations
     * 
     * @return array The sync status data
     */
    public function getSyncStatus() {
        try {
            $url = "{$this->baseUrl}/api/reports/sync-status";
            $response = $this->request('GET', $url);
            return $response;
        } catch (Exception $e) {
            error_log("Error in getSyncStatus: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}