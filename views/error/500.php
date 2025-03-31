<?php include 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-1 text-danger">500</h1>
                    <h2 class="mb-4">Đã có lỗi xảy ra</h2>
                    <p class="lead">Xin lỗi, đã có lỗi xảy ra trong quá trình xử lý yêu cầu của bạn.</p>
                    <p>Vui lòng thử lại sau hoặc liên hệ với quản trị viên nếu lỗi vẫn tiếp tục.</p>
                    <?php if (isset($e)): ?>
                        <div class="alert alert-danger mt-4">
                            <strong>Chi tiết lỗi:</strong><br>
                            <?php echo $e->getMessage(); ?>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary mt-4">Về trang chủ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>