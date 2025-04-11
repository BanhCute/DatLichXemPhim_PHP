<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Sao chép phần sidebar từ profile.php nhưng đổi active -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="Admin" class="rounded-circle" width="150">
                        <div class="mt-3">
                            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p class="text-muted font-size-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-group mt-4">
                <a href="<?php echo BASE_URL; ?>profile" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i>Thông tin cá nhân
                </a>
                <a href="<?php echo BASE_URL; ?>profile/change-password" class="list-group-item list-group-item-action active">
                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                </a>
                <a href="<?php echo BASE_URL; ?>my-bookings" class="list-group-item list-group-item-action">
                    <i class="fas fa-ticket-alt me-2"></i>Lịch sử đặt vé
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Đổi mật khẩu</h5>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo BASE_URL; ?>profile/update-password">
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>