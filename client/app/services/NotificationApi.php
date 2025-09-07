<?php
/**
 * Notification API Service
 * Integrates with notification service via HTTP/RabbitMQ
 */
require_once __DIR__ . '/../../configuration/config.php';
require_once __DIR__ . '/BaseService.php';

class NotificationApi extends BaseService {
  /**
   * Get notification service base URL
   * @return string Base URL
   */
  private static function base() {
    global $config;
    return $config['services']['notification_service'] ?? 'http://localhost:3004/api/notifications';
  }

  /**
   * Get request headers
   * @return array Headers
   */
  private static function headers(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $h = ['Content-Type: application/json'];
    if (!empty($_SESSION['access_token'])) {
      $h[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
    }
    return $h;
  }

  /**
   * Make HTTP request to notification service
   * @param string $method HTTP method
   * @param string $url Request URL
   * @param array|null $body Request body
   * @return array Response data
   * @throws Exception If request fails
   */
  private static function request(string $method, string $url, array $body = null): array {
    $ch = curl_init();
    $opts = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => self::headers(),
      CURLOPT_TIMEOUT => 10,
      CURLOPT_CUSTOMREQUEST => $method
    ];
    if ($body !== null) {
      $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    }
    curl_setopt_array($ch, $opts);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("HTTP error: $err");
    if ($code >= 400) throw new Exception("HTTP $code: $res");
    return json_decode($res, true) ?: [];
  }

  /**
   * Get notifications for a patient
   * @param string $patientId Patient ID
   * @param array $query Query parameters
   * @return array Notifications
   */
  public static function listByPatient(string $patientId, array $query = []): array {
    try {
      $qs = http_build_query(array_filter($query, fn($v) => $v !== null && $v !== ''));
      $url = rtrim(self::base(), '/') . '/' . urlencode($patientId) . ($qs ? ('?' . $qs) : '');
      return self::request('GET', $url);
    } catch (Exception $e) {
      self::logError("Failed to get notifications: " . $e->getMessage());
      return [
        'items' => [],
        'pagination' => [
          'page' => 1,
          'limit' => 10,
          'total' => 0,
          'pages' => 0
        ],
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Mark a notification as read
   * @param string $id Notification ID
   * @return array Updated notification
   */
  public static function markRead(string $id): array {
    try {
      $url = rtrim(self::base(), '/') . '/read/' . urlencode($id);
      return self::request('PUT', $url, []);
    } catch (Exception $e) {
      self::logError("Failed to mark notification as read: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Mark all notifications as read for a patient
   * @param string $patientId Patient ID
   * @return array Result
   */
  public static function markAllRead(string $patientId): array {
    try {
      $url = rtrim(self::base(), '/') . '/read-all/' . urlencode($patientId);
      return self::request('PUT', $url, []);
    } catch (Exception $e) {
      self::logError("Failed to mark all notifications as read: " . $e->getMessage());
      throw $e;
    }
  }
  
  /**
   * Create a new notification
   * @param array $data Notification data
   * @return array Created notification
   */
  public static function createNotification(array $data): array {
    try {
      $url = rtrim(self::base(), '/');
      return self::request('POST', $url, $data);
    } catch (Exception $e) {
      self::logError("Failed to create notification: " . $e->getMessage());
      throw $e;
    }
  }
  
  /**
   * Queue a notification for asynchronous processing
   * @param array $data Notification data
   * @return array Queue result
   */
  public static function queueNotification(array $data): array {
    try {
      $url = rtrim(self::base(), '/') . '/queue';
      return self::request('POST', $url, $data);
    } catch (Exception $e) {
      self::logError("Failed to queue notification: " . $e->getMessage());
      
      // Try direct creation as fallback
      try {
        self::logInfo("Attempting direct notification creation as fallback");
        return self::createNotification($data);
      } catch (Exception $fallbackError) {
        self::logError("Fallback notification creation also failed: " . $fallbackError->getMessage());
        throw $fallbackError;
      }
    }
  }
  
  /**
   * Send appointment notification
   * @param array $appointmentData Appointment data
   * @return array Queue result
   */
  public static function sendAppointmentNotification(array $appointmentData): array {
    $data = [
      'type' => 'appointment',
      'patientId' => $appointmentData['patientId'],
      'patientName' => $appointmentData['patientName'],
      'appointmentId' => $appointmentData['id'],
      'appointmentDate' => $appointmentData['date'],
      'doctorName' => $appointmentData['doctorName'],
      'email' => $appointmentData['patientEmail'] ?? null,
      'phone' => $appointmentData['patientPhone'] ?? null,
      'message' => sprintf(
        'You have an appointment scheduled on %s with Dr. %s.',
        date('Y-m-d H:i', strtotime($appointmentData['date'])),
        $appointmentData['doctorName']
      )
    ];
    
    return self::queueNotification($data);
  }
  
  /**
   * Send prescription notification
   * @param array $prescriptionData Prescription data
   * @return array Queue result
   */
  public static function sendPrescriptionNotification(array $prescriptionData): array {
    $data = [
      'type' => 'prescription',
      'patientId' => $prescriptionData['patientId'],
      'patientName' => $prescriptionData['patientName'],
      'prescriptionId' => $prescriptionData['id'],
      'doctorName' => $prescriptionData['doctorName'],
      'email' => $prescriptionData['patientEmail'] ?? null,
      'phone' => $prescriptionData['patientPhone'] ?? null
    ];
    
    return self::queueNotification($data);
  }
  
  /**
   * Send record notification
   * @param array $recordData Record data
   * @return array Queue result
   */
  public static function sendRecordNotification(array $recordData): array {
    $data = [
      'type' => 'record',
      'patientId' => $recordData['patientId'],
      'patientName' => $recordData['patientName'],
      'recordId' => $recordData['id'],
      'recordType' => $recordData['type'],
      'doctorName' => $recordData['doctorName'],
      'email' => $recordData['patientEmail'] ?? null,
      'phone' => $recordData['patientPhone'] ?? null
    ];
    
    return self::queueNotification($data);
  }
}
