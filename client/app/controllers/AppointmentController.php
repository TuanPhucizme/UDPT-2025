<?php
require_once '../services/AppointmentService.php';
require_once '../models/Appointment.php';

class AppointmentController {
    private $appointmentService;
    
    public function __construct() {
        $this->appointmentService = new AppointmentService();
    }
    
    public function search() {
        $filters = [
            'keyword' => $_GET['keyword'] ?? '',
            'date' => $_GET['ngay_kham'] ?? '',
            'status' => $_GET['trang_thai'] ?? ''
        ];
        
        $result = $this->appointmentService->getAppointments($filters);
        
        require '../views/appointments/search.php';
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentData = [
                'patientId' => $_POST['patient_id'],
                'doctorId' => $_POST['doctor_id'],
                'appointmentDate' => $_POST['appointment_date'],
                'notes' => $_POST['notes']
            ];
            
            $appointment = new Appointment($appointmentData);
            $result = $this->appointmentService->createAppointment($appointment->toArray());
            
            if ($result['statusCode'] === 201) {
                header('Location: /appointments/search');
                exit;
            }
        }
        
        require '../views/appointments/create.php';
    }
}