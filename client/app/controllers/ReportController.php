<?php
// app/controllers/ReportController.php
require_once __DIR__ . '/../services/ReportApi.php';

class ReportController {
  public function prescriptions() {
    $stats = ReportApi::prescriptions();
    $labels = array_map(fn($r) => substr(($r['report_date'] ?? ''), 0, 10), $stats);
    $counts = array_map(fn($r) => (int)($r['total_prescriptions'] ?? 0), $stats);
    // nạp view
    $data = compact('stats','labels','counts');
    extract($data);
    require __DIR__ . '/../views/report_prescriptions.php';
  }

  public function patients() {
    $stats = ReportApi::patients();
    $labels = array_map(fn($r) => ($r['month_year'] ?? ''), $stats);
    $counts = array_map(fn($r) => (int)($r['patient_count'] ?? 0), $stats);
    $data = compact('stats','labels','counts');
    extract($data);
    require __DIR__ . '/../views/report_patients.php';
  }
}
