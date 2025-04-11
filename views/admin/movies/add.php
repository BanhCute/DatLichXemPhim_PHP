<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-film me-2"></i>Thêm phim mới</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>admin/movies/add" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tên phim</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="trailer">Trailer URL</label>
                            <input type="text" class="form-control" id="trailer" name="trailer" placeholder="Nhập link trailer Youtube">
                            <small class="form-text text-muted">Ví dụ: https://www.youtube.com/watch?v=abcd123</small>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Thời lượng (phút)</label>
                            <input type="number" class="form-control" id="duration" name="duration" required min="1">
                        </div>

                        <!-- Thêm phần chọn thể loại -->
                        <div class="mb-3">
                            <label class="form-label">Thể loại</label>
                            <div class="d-flex align-items-center mb-2">
                                <button type="button" class="btn btn-sm btn-secondary me-2" id="clearCategories">
                                    <i class="fas fa-times"></i> Bỏ chọn tất cả
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="selectAllCategories">
                                    <i class="fas fa-check-square"></i> Chọn tất cả
                                </button>
                            </div>
                            <div class="row">
                                <?php
                                // Lấy tất cả thể loại
                                require_once 'models/Category.php';
                                $categoryModel = new Category();
                                $categories = $categoryModel->getAllCategories();

                                foreach ($categories as $category):
                                ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]"
                                                value="<?php echo $category['id']; ?>" id="category<?php echo $category['id']; ?>">
                                            <label class="form-check-label" for="category<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Phần hình ảnh với xem trước -->
                        <div class="mb-4">
                            <label class="form-label">Hình ảnh phim</label>

                            <div class="card mb-3">
                                <div class="card-header">Xem trước ảnh</div>
                                <div class="card-body text-center" id="image-preview-container">
                                    <div class="text-muted">
                                        <i class="fas fa-image fa-5x"></i>
                                        <p>Chưa chọn ảnh</p>
                                    </div>
                                </div>
                            </div>

                            <label for="image" class="form-label">Chọn ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                            <div class="form-text">Chấp nhận file JPG, JPEG & PNG (tối đa 5MB)</div>
                        </div>



                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Thêm phim
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/movies" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thêm JavaScript để xem trước ảnh -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('image-preview-container');

        imageInput.addEventListener('change', function() {
            // Xóa nội dung hiện tại
            previewContainer.innerHTML = '';

            if (this.files && this.files[0]) {
                const file = this.files[0];

                // Kiểm tra kích thước file (tối đa 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    previewContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        File quá lớn! Vui lòng chọn file nhỏ hơn 5MB.
                    </div>`;
                    this.value = ''; // Xóa file đã chọn
                    return;
                }

                // Kiểm tra loại file
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    previewContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Loại file không hợp lệ! Chỉ chấp nhận JPG, JPEG, PNG.
                    </div>`;
                    this.value = ''; // Xóa file đã chọn
                    return;
                }

                // Tạo URL để xem trước
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" 
                         style="max-height: 200px;" alt="Xem trước">
                    <p class="mt-2 text-success">
                        <i class="fas fa-check-circle"></i> Ảnh hợp lệ
                    </p>`;
                }
                reader.readAsDataURL(file);
            } else {
                // Nếu không có file được chọn
                previewContainer.innerHTML = `
                <div class="text-muted">
                    <i class="fas fa-image fa-5x"></i>
                    <p>Chưa chọn ảnh</p>
                </div>`;
            }
        });
    });
</script>

<!-- Add this JavaScript before the closing </script> tag -->
<script>
    // Existing image preview code...

    // Add category selection functionality
    document.addEventListener('DOMContentLoaded', function() {
        const clearBtn = document.getElementById('clearCategories');
        const selectAllBtn = document.getElementById('selectAllCategories');
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox');

        clearBtn.addEventListener('click', function() {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        selectAllBtn.addEventListener('click', function() {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>