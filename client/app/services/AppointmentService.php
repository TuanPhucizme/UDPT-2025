<?php
require_once 'BaseService.php';

class AppointmentService extends BaseService {
    public function __construct() {
        $this->baseUrl = APPOINTMENT_SERVICE_URL;
    }
    
    public function createAppointment($data) {
        return $this->request('POST', '/appointments', $data);
    }
    
    public function getAppointments($filters = []) {
        $query = http_build_query($filters);
        return $this->request('GET', '/appointments?' . $query);
    }
}