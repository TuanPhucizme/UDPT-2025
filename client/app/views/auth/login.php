<?php require_once '../app/views/layouts/header.php'; ?>

<style>
    body {
        background: linear-gradient(135deg, #2196f3, #21cbf3);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeInUp 0.6s ease;
    }
    .login-card .card-header {
        background: #fff;
        border-bottom: none;
        text-align: center;
        padding: 25px 20px 10px;
    }
    .login-card .card-header h4 {
        font-weight: 700;
        color: #0d6efd;
    }
    .form-control {
        border-radius: 12px;
        padding-left: 40px;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }
    .input-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
    }
    .btn-login {
        border-radius: 12px;
        font-weight: 600;
        padding: 12px;
        transition: all 0.3s ease;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13,110,253,0.3);
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card login-card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-hospital-user me-2"></i>Đăng Nhập</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $fieldError): ?>
                                    <li><?= htmlspecialchars($fieldError) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/auth/login">
                        <div class="mb-3 position-relative">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input type="text" 
                                   class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Tên đăng nhập"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="mb-4 position-relative">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Mật khẩu"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Đăng Nhập
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
