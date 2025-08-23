<?php
require_once __DIR__ . '/../../configuration/services.php';
require_once __DIR__ . '/BaseService.php';

class AppointmentService extends BaseService {
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->port = APPOINTMENT_SERVICE_PORT;
    }
    public function createAppointment($data) {
        return $this->request('POST', '/api/appointments', $data);
    }
    
    public function getAppointments($filters = []) {
        $query = http_build_query($filters);
        return $this->request('GET', '/api/appointments?' . $query);
    }
    
    public function proposeTime($id, $data) {
        return $this->request('PUT', "/api/appointments/{$id}/propose", $data);
    }

    public function declineAppointment($id, $data) {
        return $this->request('PUT', "/api/appointments/{$id}/decline", $data);
    }
}