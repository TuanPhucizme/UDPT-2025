<?php require_once '../views/layouts/header.php'; ?>

<div class="container py-5 mt-5">
    <h2 class="mb-4 text-center">Tìm Kiếm Lịch Khám</h2>

    <form method="GET" action="/appointments/search" class="row g-3 mb-4">
        <!-- ...existing form fields... -->
    </form>

    <h5 class="mb-3">Kết quả tìm kiếm</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Bệnh nhân</th>
                <th>Bác sĩ</th>
                <th>Ngày khám</th>
                <th>Giờ</th>
                <th>Ghi chú</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result['data'] as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['patientName']) ?></td>
                    <td><?= htmlspecialchars($appointment['doctorName']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($appointment['appointmentDate']))) ?></td>
                    <td><?= htmlspecialchars(date('H:i', strtotime($appointment['appointmentDate']))) ?></td>
                    <td><?= htmlspecialchars($appointment['notes']) ?></td>
                    <td><span class="badge bg-<?= $appointment['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars(ucfirst($appointment['status'])) ?>
                    </span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../views/layouts/footer.php'; ?>