<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh Sách Bệnh Nhân</h2>
        <a href="/patients/create" class="btn btn-primary">Thêm Bệnh Nhân</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ Tên</th>
                    <th>Ngày Sinh</th>
                    <th>Giới Tính</th>
                    <th>Số Điện Thoại</th>
                    <th>Địa Chỉ</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?= htmlspecialchars($patient['id']) ?></td>
                        <td><?= htmlspecialchars($patient['hoten_bn']) ?></td>
                        <td><?= (new DateTime($patient['dob']))->format("M d, Y"); ?></td>
                        <td><?= htmlspecialchars($patient['gender']) ?></td>
                        <td><?= htmlspecialchars($patient['sdt']) ?></td>
                        <td><?= htmlspecialchars($patient['diachi']) ?></td>
                        <td>
                            <a href="/patients/edit/<?= $patient['id'] ?>" class="btn btn-sm btn-primary">Sửa</a>
                            <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-sm btn-info">Chi tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>