<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-danger mb-4"><?= $statusCode ?? '500' ?></h1>
            <h2 class="mb-4"><?= $error ?? 'Đã xảy ra lỗi' ?></h2>
            <p class="lead mb-4">Xin lỗi, đã xảy ra lỗi trong quá trình xử lý yêu cầu của bạn.</p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> Về Trang Chủ
            </a>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>