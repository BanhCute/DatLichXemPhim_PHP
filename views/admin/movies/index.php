<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý phim</h2>
        <div>
            <a href="<?php echo BASE_URL; ?>admin/movies/add" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Thêm phim mới
            </a>
            <span class="badge bg-info fs-6">
                <i class="fas fa-film me-1"></i> Tổng số phim: <?php echo $totalMovies; ?>
            </span>
        </div>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>admin/movies" class="row">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text"
                            name="search"
                            class="form-control"
                            placeholder="Tìm kiếm theo tên phim..."
                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select" name="category" onchange="this.form.submit()">
                        <option value="">-- Tất cả thể loại --</option>
                        <?php if (isset($categories) && is_array($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($selectedCategory) && $selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <?php if (!empty($keyword) || !empty($selectedCategory)): ?>
                        <a href="<?php echo BASE_URL; ?>admin/movies" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Hiển thị kết quả tìm kiếm -->
    <?php if (!empty($keyword) || !empty($selectedCategory)): ?>
        <div class="alert alert-info">
            <?php if (!empty($keyword)): ?>
                <i class="fas fa-search me-2"></i>
                Kết quả tìm kiếm: "<strong><?php echo htmlspecialchars($keyword); ?></strong>"
            <?php endif; ?>

            <?php if (!empty($selectedCategory)): ?>
                <?php if (!empty($keyword)) echo ' | '; ?>
                <i class="fas fa-tag me-2"></i>
                Thể loại: <strong>
                    <?php
                    foreach ($categories as $category) {
                        if ($category['id'] == $selectedCategory) {
                            echo htmlspecialchars($category['name']);
                            break;
                        }
                    }
                    ?>
                </strong>
            <?php endif; ?>

            <span class="badge bg-secondary ms-2"><?php echo $totalMovies; ?> phim</span>
        </div>
    <?php endif; ?>

    <?php if (empty($movies)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Chưa có phim nào trong hệ thống.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($movies as $movie): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <?php if (!empty($movie['imageUrl'])): ?>
                                <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                    class="card-img-top"
                                    alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                    style="height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <i class="fas fa-film fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-primary">ID: <?php echo $movie['id']; ?></span>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-info">
                                    <i class="fas fa-clock"></i> <?php echo $movie['duration']; ?> phút
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <p class="card-text text-muted" style="height: 4.5em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                <?php echo htmlspecialchars($movie['description']); ?>
                            </p>

                            <!-- Thể loại phim -->
                            <?php if (isset($movie['categories']) && is_array($movie['categories'])): ?>
                                <div class="movie-categories">
                                    <?php foreach ($movie['categories'] as $cat): ?>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between gap-2">


                                <div>
                                    <a href="<?php echo BASE_URL; ?>admin/movies/edit?id=<?php echo $movie['id']; ?>"
                                        class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>admin/movies/delete?id=<?php echo $movie['id']; ?>"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phim này?');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <div class="d-flex justify-content-center align-items-center">
                    <ul class="pagination mb-0 align-items-center">
                        <!-- Nút Đầu trang -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-light fw-bold"
                                href="<?php echo BASE_URL; ?>admin/movies?page=1
                               <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
                               <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>

                        <!-- Nút Trước -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-light fw-bold"
                                href="<?php echo BASE_URL; ?>admin/movies?page=<?php echo ($page - 1); ?>
                               <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
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
                                    href="<?php echo BASE_URL; ?>admin/movies?page=1
                                    <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
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
                                    href="<?php echo BASE_URL; ?>admin/movies?page=<?php echo $i; ?>
                                   <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
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
                                    href="<?php echo BASE_URL; ?>admin/movies?page=<?php echo $totalPages; ?>
                                    <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
                                    <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                    <?php echo $totalPages; ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Nút Sau -->
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-light fw-bold"
                                href="<?php echo BASE_URL; ?>admin/movies?page=<?php echo ($page + 1); ?>
                               <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
                               <?php echo !empty($keyword) ? '&search=' . urlencode($keyword) : ''; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>

                        <!-- Nút Cuối trang -->
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-light fw-bold"
                                href="<?php echo BASE_URL; ?>admin/movies?page=<?php echo $totalPages; ?>
                               <?php echo $selectedCategory ? '&category=' . $selectedCategory : ''; ?>
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
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>

<style>
    /* Style cho dropdown menu */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Style cho các item trong dropdown */
    .dropdown-item {
        padding: 0.5rem 1rem;
        color: #333;
        transition: all 0.2s ease;
    }

    /* Style cho item đang được chọn (active) */
    .dropdown-item.active {
        background-color: #0d6efd !important;
        color: white !important;
    }

    /* Style cho hover state */
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }

    /* Style đặc biệt khi hover vào item đang active */
    .dropdown-item.active:hover {
        background-color: #0b5ed7 !important;
        color: white !important;
    }

    /* Thêm icon check cho item đang active */
    .dropdown-item.active::before {
        content: '✓';
        margin-right: 0.5rem;
    }

    /* Style cho dropdown toggle button */
    .nav-pills .nav-link.dropdown-toggle {
        background-color: #0d6efd;
        color: white;
    }

    .nav-pills .nav-link.dropdown-toggle:hover,
    .nav-pills .nav-link.dropdown-toggle.active {
        background-color: #0b5ed7;
        color: white;
    }

    :root {
        --primary: #0d6efd;
        --primary-dark: #0b5ed7;
        --hover-bg: #f8f9fa;
        --text-color: #333;
    }

    .dropdown-item.active {
        background-color: var(--primary) !important;
    }

    .dropdown-item:hover {
        background-color: var(--hover-bg);
        color: var(--primary);
    }
</style>