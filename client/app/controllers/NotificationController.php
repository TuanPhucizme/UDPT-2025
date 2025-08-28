<?php
// app/controllers/NotificationController.php
require_once __DIR__ . '/../services/NotificationApi.php';

class NotificationController {
  public function index() {
    session_start();
    $user = $_SESSION['user'] ?? [];
    $role = $user['role'] ?? '';
    $patientId = $user['id'] ?? null;

    // Admin/Lễ tân xem: /notifications/index?patientId=123
    if (($role === 'admin' || $role === 'letan') && !empty($_GET['patientId'])) {
      $patientId = $_GET['patientId'];
    }
    if (!$patientId) { http_response_code(400); echo 'Thiếu patientId'; return; }

    $page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $type  = $_GET['type']  ?? '';   // 'appointment' | 'prescription'
    $isRead= $_GET['isRead']?? '';   // 'true' | 'false' | ''

    $query = [
      'page'  => $page,
      'limit' => $limit,
      'type'  => $type,
      'isRead'=> $isRead
    ];

    try {
      $items = NotificationApi::listByPatient((string)$patientId, $query);
      $notifications = $items;
      $filters = $query;
      $filters['patientId'] = $patientId;
      require __DIR__ . '/../views/notifications/notifications_list.php';
    } catch (Exception $e) {
      http_response_code(500);
      echo 'Lỗi lấy thông báo: ' . htmlspecialchars($e->getMessage());
    }
  }

  public function read() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); return; }
    $id = $_POST['id'] ?? '';
    if (!$id) { http_response_code(400); echo 'Thiếu id'; return; }

    try {
      NotificationApi::markRead($id);
      // quay lại trang hiện tại
      header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/notifications/index')); 
    } catch (Exception $e) {
      http_response_code(500);
      echo 'Lỗi markRead: ' . htmlspecialchars($e->getMessage());
    }
  }

  public function readAll() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); return; }
    session_start();
    $patientId = $_POST['patientId'] ?? ($_SESSION['user']['id'] ?? null);
    if (!$patientId) { http_response_code(400); echo 'Thiếu patientId'; return; }

    try {
      NotificationApi::markAllRead((string)$patientId);
      header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/notifications/index'));
    } catch (Exception $e) {
      http_response_code(500);
      echo 'Lỗi markAllRead: ' . htmlspecialchars($e->getMessage());
    }
  }
}
