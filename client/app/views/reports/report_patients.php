<?php /* app/views/report_patients.php */ ?>
<link rel="stylesheet" href="/css/report.css">
<div class="container" style="padding:16px">
  <h2>Báo cáo bệnh nhân theo tháng</h2>
  <canvas id="patChart" height="110"></canvas>
  <hr>
  <h3>Chi tiết</h3>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead><tr><th>Tháng</th><th>Số bệnh nhân</th></tr></thead>
      <tbody>
        <?php foreach (($stats ?? []) as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['month_year'] ?? '') ?></td>
            <td><?= (int)($row['patient_count'] ?? 0) ?></td>
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
  new Chart(document.getElementById('patChart').getContext('2d'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Số bệnh nhân', data, tension: .3 }] },
    options: { responsive: true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
  });
</script>
