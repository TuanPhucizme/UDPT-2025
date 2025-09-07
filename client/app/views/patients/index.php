<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="fas fa-users text-primary"></i> Danh Sách Bệnh Nhân
        </h2>
        <div class="d-flex">
            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                <a href="/patients/create" class="btn btn-success shadow-sm px-3 me-2">
                    <i class="fas fa-plus-circle"></i> Thêm Bệnh Nhân
                </a>
            <?php endif; ?>
                
            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin', 'bacsi'])): ?>
                <a href="/patients/search" class="btn btn-outline-info shadow-sm px-3 me-2">
                    <i class="fas fa-search"></i> Tìm Kiếm
                </a>
                
                <a href="/" class="btn btn-outline-secondary shadow-sm px-3">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="card shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Họ Tên</th>
                            <th scope="col">Ngày Sinh</th>
                            <th scope="col">Giới Tính</th>
                            <th scope="col">Số Điện Thoại</th>
                            <th scope="col">Địa Chỉ</th>
                            <th scope="col" class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($patient['id']) ?></span></td>
                                <td class="fw-semibold"><?= htmlspecialchars($patient['hoten_bn']) ?></td>
                                <td><?= (new DateTime($patient['dob']))->format("d/m/Y"); ?></td>
                                <td><?= htmlspecialchars($patient['gender']) ?></td>
                                <td><?= htmlspecialchars($patient['sdt']) ?></td>
                                <td><?= htmlspecialchars($patient['diachi']) ?></td>
                                <td class="text-center">
                                    <a href="/patients/view/<?= $patient['id'] ?>" 
                                       class="btn btn-sm btn-outline-info me-2">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                    <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                                    <a href="/patients/edit/<?= $patient['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
