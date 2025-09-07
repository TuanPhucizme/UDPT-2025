<?php
require_once __DIR__ . '/../services/AppointmentService.php';
require_once __DIR__ . '/../models/Appointment.php';

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
        try {
            // Get departments for dropdown
            $departments = $this->appointmentService->getDepartments();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $appointmentData = [
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $_POST['doctor_id'],
                    'department_id' => $_POST['department_id'],
                    'thoi_gian_hen' => $_POST['requested_time'],
                    'lydo' => $_POST['lydo'],
                    'note' => $_POST['note'],
                    'receptionist_id' => $_SESSION['user']['id']
                ];
                
                $result = $this->appointmentService->createAppointment($appointmentData);
                
                if ($result['statusCode'] === 201) {
                    $_SESSION['success'] = 'Đã tạo lịch hẹn thành công';
                    header('Location: /appointments');
                    exit;
                } else {
                    $error = $result['message'] ?? 'Không thể tạo lịch hẹn';
                }
            }
            
            require '../app/views/appointments/create.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/appointments/create.php';
        }
    }

    /**
     * AJAX endpoint for doctor selection by department
     */
    public function getDoctorsByDepartment($departmentId) {
        try {
            $result = $this->appointmentService->getDoctorsByDepartment($departmentId);
            header('Content-Type: application/json');
            echo json_encode($result['data'] ?? []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * AJAX endpoint for doctor schedule
     */
    public function getDoctorSchedule($doctorId) {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            
            if (!$doctorId) {
                throw new Exception('Doctor ID is required');
            }
            $result = $this->appointmentService->getDoctorSchedule($doctorId, $date);
            header('Content-Type: application/json');
            echo json_encode($result['data'] ?? []);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * AJAX endpoint for available time slots
     */
    public function getAvailableSlots($doctorId) {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            
            if (!$doctorId) {
                throw new Exception('Doctor ID is required');
            }
            
            $result = $this->appointmentService->getAvailableSlots($doctorId, $date);
            header('Content-Type: application/json');
            echo json_encode($result['data'] ?? []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function pending() {
        try {
            $filters = ['status' => 'pending'];
            if ($_SESSION['user']['role'] === 'bacsi') {
                $filters['doctor_id'] = $_SESSION['user']['id'];
            }
            
            $result = $this->appointmentService->getAppointments($filters);
            $appointments = $result['data'] ?? [];
            
            require '../app/views/appointments/pending.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }

    public function propose($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $result = $this->appointmentService->proposeTime($id, [
                'proposed_time' => $_POST['proposed_time']
            ]);

            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đã đề xuất thời gian thành công';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Không thể đề xuất thời gian';
            }
            
            header('Location: /appointments/pending');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /appointments/pending');
            exit;
        }
    }

    public function decline($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $result = $this->appointmentService->declineAppointment($id, [
                'reason' => $_POST['reason']
            ]);

            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đã từ chối lịch hẹn';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Không thể từ chối lịch hẹn';
            }
            
            header('Location: /appointments/pending');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /appointments/pending');
            exit;
        }
    }

    public function index() {
        try {
            $filters = [
                'keyword' => $_GET['keyword'] ?? '',
                'date' => $_GET['date'] ?? '',
                'status' => $_GET['status'] ?? ''
            ];

            if ($_SESSION['user']['role'] === 'bacsi') {
                $filters['doctor_id'] = $_SESSION['user']['id'];
            }
            
            $result = $this->appointmentService->getAppointments($filters);
            $appointments = $result['data'] ?? [];
            
            require '../app/views/appointments/index.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require '../app/views/error.php';
        }
    }

    public function view($id) {
        try {
            $result = $this->appointmentService->getAppointmentById($id);
            
            if ($result['statusCode'] === 404) {
                throw new Exception('Không tìm thấy lịch hẹn');
            }
            
            $appointment = $result['data'];
            // Check permission
            if ($_SESSION['user']['role'] === 'bacsi' && $appointment['doctor_id'] !== $_SESSION['user']['id']) {
                throw new Exception('Bạn không có quyền xem lịch hẹn này');
            }
            
            require '../app/views/appointments/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /appointments');
            exit;
        }
    }

    public function confirm($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Check if the doctor has permission
            $appointmentResult = $this->appointmentService->getAppointmentById($id);
            if ($appointmentResult['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy lịch hẹn');
            }

            $appointment = $appointmentResult['data'];
            if ($_SESSION['user']['role'] === 'bacsi' && $appointment['doctor_id'] !== $_SESSION['user']['id']) {
                throw new Exception('Bạn không có quyền xác nhận lịch hẹn này');
            }

            $result = $this->appointmentService->confirmAppointment($id);

            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đã xác nhận lịch hẹn thành công';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Không thể xác nhận lịch hẹn';
            }
            
            // Redirect back to the appointment view
            header('Location: /appointments/view/' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /appointments/view/' . $id);
            exit;
        }
    }

    public function cancel($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Check if the doctor has permission
            $appointmentResult = $this->appointmentService->getAppointmentById($id);
            if ($appointmentResult['statusCode'] !== 200) {
                throw new Exception('Không tìm thấy lịch hẹn');
            }

            $appointment = $appointmentResult['data'];
            if ($_SESSION['user']['role'] === 'bacsi' && $appointment['doctor_id'] !== $_SESSION['user']['id']) {
                throw new Exception('Bạn không có quyền hủy lịch hẹn này');
            }

            $result = $this->appointmentService->cancelAppointment($id, [
                'reason' => $_POST['reason'] ?? ''
            ]);

            if ($result['statusCode'] === 200) {
                $_SESSION['success'] = 'Đã hủy lịch hẹn thành công';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Không thể hủy lịch hẹn';
            }
            
            // Redirect back to the appointment view
            header('Location: /appointments/view/' . $id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /appointments/view/' . $id);
            exit;
        }
    }
}