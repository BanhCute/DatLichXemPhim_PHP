<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-edit me-2"></i>Sửa phim</h3>
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

                    <form action="<?php echo BASE_URL; ?>admin/movies/edit?id=<?php echo $movie['id']; ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tên phim</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="trailer" class="form-label">Link Trailer (YouTube)</label>
                            <input type="text" class="form-control" id="trailer" name="trailer"
                                value="<?php echo htmlspecialchars($movie['trailer'] ?? ''); ?>"
                                placeholder="Ví dụ: https://www.youtube.com/watch?v=...">
                            <div class="form-text">Nhập link YouTube của trailer phim</div>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Thời lượng (phút)</label>
                            <input type="number" class="form-control" id="duration" name="duration" value="<?php echo $movie['duration']; ?>" required min="1">
                        </div>

                        <!-- Thêm phần chọn thể loại -->
                        <div class="mb-3">
                            <label class="form-label">Thể loại</label>
                            <div class="row">
                                <?php
                                // Lấy tất cả thể loại
                                require_once 'models/Category.php';
                                $categoryModel = new Category();
                                $categories = $categoryModel->getAllCategories();

                                // Lấy thể loại của phim hiện tại
                                $movieCategories = $categoryModel->getCategoriesByMovieId($movie['id']);
                                $movieCategoryIds = array_column($movieCategories, 'id');

                                foreach ($categories as $category):
                                    $checked = in_array($category['id'], $movieCategoryIds) ? 'checked' : '';
                                ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]"
                                                value="<?php echo $category['id']; ?>" <?php echo $checked; ?>
                                                id="category<?php echo $category['id']; ?>">
                                            <label class="form-check-label" for="category<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Phần hiển thị và xem trước ảnh -->
                        <div class="mb-4">
                            <label class="form-label d-block">Hình ảnh phim</label>

                            <div class="row">
                                <!-- Ảnh hiện tại -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">Ảnh hiện tại</div>
                                        <div class="card-body text-center">
                                            <?php if ($movie['imageUrl']): ?>
                                                <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                                    alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                                    class="img-thumbnail"
                                                    style="max-height: 200px;">
                                            <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="fas fa-image fa-5x"></i>
                                                    <p>Chưa có ảnh</p>
                                                </div>
                                            <?php endif; ?>
                                            <!-- Trường ẩn để lưu đường dẫn ảnh hiện tại -->
                                            <input type="hidden" name="current_image" value="<?php echo $movie['imageUrl']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Xem trước ảnh mới -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">Ảnh mới (xem trước)</div>
                                        <div class="card-body text-center" id="image-preview-container">
                                            <div class="text-muted">
                                                <i class="fas fa-upload fa-5x"></i>
                                                <p>Chưa chọn ảnh mới</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="image" class="form-label">Chọn ảnh mới</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                                <div class="form-text">Chấp nhận file JPG, JPEG & PNG (tối đa 5MB). Để trống nếu không muốn thay đổi ảnh.</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Cập nhật
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
                    <i class="fas fa-upload fa-5x"></i>
                    <p>Chưa chọn ảnh mới</p>
                </div>`;
            }
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>