<?php
// app/services/NotificationApi.php
require_once __DIR__ . '/../../configuration/config.php';

class NotificationApi {
  private static function base() {
    // đổi ví dụ: getenv('NOTI_API') khi bạn có ENV
    return 'http://localhost:3004/api/notifications';
  }

  private static function headers(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $h = ['Content-Type: application/json'];
    if (!empty($_SESSION['access_token'])) {
      $h[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
    }
    return $h;
  }

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

  // GET /:patientId?page=&limit=&type=&isRead=
  public static function listByPatient(string $patientId, array $query = []): array {
    $qs = http_build_query(array_filter($query, fn($v) => $v !== null && $v !== ''));
    $url = rtrim(self::base(), '/') . '/' . urlencode($patientId) . ($qs ? ('?' . $qs) : '');
    return self::request('GET', $url);
  }

  // PUT /read/:id
  public static function markRead(string $id): array {
    $url = rtrim(self::base(), '/') . '/read/' . urlencode($id);
    return self::request('PUT', $url, []); // body rỗng
  }

  // PUT /read-all/:patientId
  public static function markAllRead(string $patientId): array {
    $url = rtrim(self::base(), '/') . '/read-all/' . urlencode($patientId);
    return self::request('PUT', $url, []); // body rỗng
  }
}
