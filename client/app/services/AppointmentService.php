<?php
require_once __DIR__ . '/../../configuration/services.php';
require_once __DIR__ . '/BaseService.php';

class AppointmentService extends BaseService {
    private $authServiceUrl;
    
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = APPOINTMENT_SERVICE_PORT;
        $this->authServiceUrl = AUTH_SERVICE_URL;
    }
    public function createAppointment($data) {
        return $this->request('POST', '/api/appointments/book', $data);
    }
    
    public function getAppointments($filters = []) {
        $query = http_build_query($filters);
        return $this->request('GET', '/api/appointments?' . $query);
    }
    
    public function proposeTime($id, $data) {
        return $this->request('PUT', "/api/appointments/{$id}/propose", $data);
    }

    public function declineAppointment($id, $data) {
        return $this->request('PUT', "/api/appointments/{$id}/cancel", $data);
    }

    /**
     * Get all departments from auth service
     */
    public function getDepartments() {
        try {
            $response = $this->makeRequest(
                'GET',
                $this->authServiceUrl . '/api/departments',
                [],
                true // Use internal token
            );
            
            if ($response['statusCode'] === 200) {
                return $response['data'];
            }
            
            throw new Exception($response['message'] ?? 'Could not fetch departments');
        } catch (Exception $e) {
            error_log('Error getting departments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get doctors by department
     */
    public function getDoctorsByDepartment($departmentId) {
        return $this->makeRequest(
            'GET',
            $this->authServiceUrl . "/api/departments/{$departmentId}/staff",
            [],
            true
        );
    }

    /**
     * Get doctor's schedule
     */
    public function getDoctorSchedule($doctorId, $date) {
        $query = http_build_query(['doctor_id' => $doctorId,'date' => $date]);
        return $this->request('GET', "/api/appointments/doctor-schedule?{$query}");
    }

    /**
     * Get available time slots
     */
    public function getAvailableSlots($doctorId, $date) {
        $query = http_build_query(['doctor_id' => $doctorId,'date' => $date]);
        return $this->request('GET', "/api/appointments/doctor-slots?{$query}");
    }

    public function getAppointmentById($id) {
        return $this->request('GET', "/api/appointments/{$id}");
    }

    public function confirmAppointment($id) {
        return $this->request('PUT', "/api/appointments/{$id}/confirm", []);
    }

    public function cancelAppointment($id, $data) {
        return $this->request('PUT', "/api/appointments/{$id}/cancel", $data);
    }
}