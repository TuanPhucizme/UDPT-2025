<?php
// app/services/ReportApi.php
require_once __DIR__ . '/../../configuration/config.php';

class ReportApi {
  private static function base() {
    // URL gốc của report-service
    return getenv('REPORT_API') ?: 'http://localhost:3005/api/reports';
  }

  private static function headers(): array {
    // Nếu bạn có lưu token sau login trong $_SESSION['access_token'], thêm vào đây
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $h = ['Content-Type: application/json'];
    if (!empty($_SESSION['access_token'])) {
      $h[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
    }
    return $h;
  }

  private static function get(string $path): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => rtrim(self::base(), '/') . '/' . ltrim($path, '/'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => self::headers(),
      CURLOPT_TIMEOUT => 10,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) throw new Exception("HTTP error: $err");
    if ($code >= 400) throw new Exception("HTTP $code: $res");
    return json_decode($res, true) ?: [];
  }

  public static function prescriptions(): array { return self::get('prescriptions'); }
  public static function patients(): array { return self::get('patients'); }
  // (nếu sau này bạn có /prescriptions/raw): public static function prescriptionsRaw(): array { return self::get('prescriptions/raw'); }
}
