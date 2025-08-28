<?php /* app/views/report_prescriptions.php */ ?>
<link rel="stylesheet" href="/css/report.css">
<div class="container" style="padding:16px">
  <h2>Báo cáo đơn thuốc theo ngày</h2>
  <canvas id="presChart" height="110"></canvas>
  <hr>
  <h3>Chi tiết</h3>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead><tr><th>Ngày</th><th>Tổng đơn</th></tr></thead>
      <tbody>
        <?php foreach (($stats ?? []) as $row): ?>
          <tr>
            <td><?= htmlspecialchars(substr($row['report_date'] ?? '', 0, 10)) ?></td>
            <td><?= (int)($row['total_prescriptions'] ?? 0) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const labels = <?= json_encode($labels ?? []) ?>;
  const data = <?= json_encode($counts ?? []) ?>;
  new Chart(document.getElementById('presChart').getContext('2d'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Số đơn thuốc', data }] },
    options: { responsive: true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
  });
</script>
