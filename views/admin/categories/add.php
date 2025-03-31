<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm thể loại mới</h4>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>admin/categories/add" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Tên thể loại <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug (để trống sẽ tự động tạo)</label>
                    <input type="text" class="form-control" id="slug" name="slug">
                    <div class="form-text">Slug là phiên bản thân thiện với URL của tên, ví dụ: "hanh-dong"</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu thể loại
                    </button>
                    <a href="<?php echo BASE_URL; ?>admin/categories" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('name').addEventListener('input', function() {
        // Tự động tạo slug khi nhập tên
        const nameValue = this.value;
        const slug = nameValue
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Bỏ dấu tiếng Việt
            .replace(/[^a-z0-9\s]/g, '') // Chỉ giữ lại chữ cái và số
            .replace(/\s+/g, '-') // Thay khoảng trắng bằng dấu gạch ngang
            .replace(/-+/g, '-') // Loại bỏ nhiều dấu gạch ngang liên tiếp
            .trim();

        document.getElementById('slug').value = slug;
    });
</script>

<?php include 'views/layouts/footer.php'; ?>