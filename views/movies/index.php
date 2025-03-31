<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <!-- Form tìm kiếm -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form method="GET" action="<?php echo BASE_URL; ?>movies" class="d-flex gap-2">
                <input type="text"
                    name="search"
                    class="form-control"
                    placeholder="Tìm kiếm phim..."
                    value="<?php echo htmlspecialchars($keyword ?? ''); ?>">

                <!-- Giữ lại thể loại khi tìm kiếm -->
                <?php if ($categoryId): ?>
                    <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                <?php endif; ?>

                <input type="hidden" name="page" value="1">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                </button>

                <!-- Thêm nút Xem tất cả khi đang tìm kiếm -->
                <?php if (!empty($keyword)): ?>
                    <a href="<?php echo BASE_URL; ?>movies<?php echo $categoryId ? '?category=' . $categoryId : ''; ?>"
                        class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>Xem tất cả
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Hiển thị kết quả tìm kiếm -->
    <?php if (!empty($keyword)): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-search me-2"></i>
                Kết quả tìm kiếm cho: "<strong><?php echo htmlspecialchars($keyword); ?></strong>"
                (<?php echo $totalMovies; ?> phim)
            </div>
            <a href="<?php echo BASE_URL; ?>movies<?php echo $categoryId ? '?category=' . $categoryId : ''; ?>"
                class="btn btn-outline-info btn-sm">
                <i class="fas fa-times me-2"></i>Xóa tìm kiếm
            </a>
        </div>
    <?php endif; ?>

    <!-- Menu phân loại -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-pills">
                <!-- Tất cả phim -->
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($categoryId) ? 'active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>movies<?php echo !empty($keyword) ? '?search=' . urlencode($keyword) : ''; ?>">
                        <i class="fas fa-film me-2"></i>Tất cả
                    </a>
                </li>

                <!-- Dropdown thể loại -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo !empty($categoryId) ? 'active' : ''; ?>"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button">
                        <i class="fas fa-tags me-2"></i>Thể loại
                    </a>
                    <ul class="dropdown-menu custom-dropdown">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>"
                                    href="<?php echo BASE_URL; ?>movies?category=<?php echo $category['id']; ?><?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                    <?php if ($categoryId == $category['id']): ?>
                                        <i class="fas fa-check me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <!-- Hiển thị thể loại đang chọn -->
    <?php if ($categoryId): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-tag me-2"></i>
                Thể loại: <strong><?php
                                    foreach ($categories as $category) {
                                        if ($category['id'] == $categoryId) {
                                            echo htmlspecialchars($category['name']);
                                            break;
                                        }
                                    }
                                    ?></strong>
                (<?php echo $totalMovies; ?> phim)
            </div>
            <a href="<?php echo BASE_URL; ?>movies<?php echo !empty($keyword) ? '?search=' . urlencode($keyword) : ''; ?>"
                class="btn btn-outline-info btn-sm">
                <i class="fas fa-times me-2"></i>Xóa bộ lọc
            </a>
        </div>
    <?php endif; ?>

    <!-- Danh sách phim -->
    <div class="row">
        <?php if (!empty($movies)): ?>
            <?php
            error_log("Số phim trong view: " . count($movies));
            foreach ($movies as $movie) {
                error_log("Movie ID: " . $movie['id'] . ", Title: " . $movie['title']);
            }
            ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php
                        // Kiểm tra và hiển thị hình ảnh
                        if (!empty($movie['imageUrl'])):
                        ?>
                            <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                style="height: 300px; object-fit: cover;">
                        <?php else: ?>
                            <!-- Hình ảnh mặc định nếu không có ảnh -->
                            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center"
                                style="height: 300px;">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <?php if (isset($movie['categories']) && !empty($movie['categories'])): ?>
                                <div class="mb-2">
                                    <?php foreach ($movie['categories'] as $cat): ?>
                                        <span class="badge bg-primary me-1">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <p class="card-text">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                <span class="text-muted"><?php echo $movie['duration']; ?> phút</span>
                            </p>

                            <a href="<?php echo BASE_URL . 'movies/detail?id=' . $movie['id']; ?>"
                                class="btn btn-primary w-100">
                                <i class="fas fa-info-circle me-2"></i>Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Không tìm thấy phim nào
                    <?php echo !empty($keyword) ? ' cho từ khóa "' . htmlspecialchars($keyword) . '"' : ''; ?>
                    <?php echo $categoryId ? ' trong thể loại đã chọn' : ''; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Phân trang đã sửa -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <div class="d-flex justify-content-center align-items-center">
                <ul class="pagination mb-0 align-items-center">
                    <!-- Nút Đầu trang -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 bg-light fw-bold"
                            href="<?php echo BASE_URL; ?>movies?page=1
                           <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                           <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>

                    <!-- Nút Trước -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 bg-light fw-bold"
                            href="<?php echo BASE_URL; ?>movies?page=<?php echo ($page - 1); ?>
                           <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                           <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>

                    <!-- Các số trang -->
                    <?php
                    // Hiển thị tối đa 5 trang
                    $startPage = max(1, min($page - 2, $totalPages - 4));
                    $endPage = min($totalPages, max(5, $page + 2));

                    // Hiển thị "..." nếu không bắt đầu từ trang 1
                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link border-0 bg-light"
                                href="<?php echo BASE_URL; ?>movies?page=1
                                <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                                <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                1
                            </a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link border-0">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link border-0 <?php echo $i == $page ? 'bg-primary text-white' : 'bg-light'; ?>"
                                href="<?php echo BASE_URL; ?>movies?page=<?php echo $i; ?>
                               <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                               <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Hiển thị "..." nếu không kết thúc ở trang cuối -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link border-0">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link border-0 bg-light"
                                href="<?php echo BASE_URL; ?>movies?page=<?php echo $totalPages; ?>
                                <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                                <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                <?php echo $totalPages; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Nút Sau -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 bg-light fw-bold"
                            href="<?php echo BASE_URL; ?>movies?page=<?php echo ($page + 1); ?>
                           <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                           <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>

                    <!-- Nút Cuối trang -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 bg-light fw-bold"
                            href="<?php echo BASE_URL; ?>movies?page=<?php echo $totalPages; ?>
                           <?php echo $categoryId ? '&category=' . $categoryId : ''; ?>
                           <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>

                    <!-- Tổng số trang -->
                    <li class="page-item disabled ms-3">
                        <span class="page-link border-0 bg-transparent">
                            Trang <?php echo $page; ?> / <?php echo $totalPages; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>