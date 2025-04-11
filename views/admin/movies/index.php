<?php include 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold text-dark">Quản lý phim</h2>
        <div>
            <a href="<?php echo BASE_URL; ?>admin/movies/add" class="btn btn-primary me-2 shadow-sm">
                <i class="fas fa-plus"></i> Thêm phim mới
            </a>
            <span class="badge bg-info fs-6 shadow-sm">
                <i class="fas fa-film me-1"></i> Tổng số phim: <?php echo isset($totalMovies) ? $totalMovies : 0; ?>
            </span>
        </div>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <div class="card mb-5 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>admin/movies" class="row g-3" id="searchForm">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text"
                            name="search"
                            class="form-control border-0 shadow-sm"
                            placeholder="Tìm kiếm theo tên phim..."
                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <select class="form-select border-0 shadow-sm" name="category" id="categorySelect">
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

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2 shadow-sm">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if (!empty($keyword) || !empty($selectedCategory)): ?>
                        <a href="<?php echo BASE_URL; ?>admin/movies" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Hiển thị kết quả tìm kiếm -->
    <?php if (!empty($keyword) || !empty($selectedCategory)): ?>
        <div class="alert alert-info shadow-sm">
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

    <!-- Thêm debug info -->
    <?php
    error_log("View received movies: " . (isset($movies) ? count($movies) : 'null'));
    error_log("View received totalMovies: " . (isset($totalMovies) ? $totalMovies : 'null'));
    ?>

    <!-- Hiển thị danh sách phim -->
    <?php if (empty($movies)): ?>
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle"></i> Chưa có phim nào trong hệ thống.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            // Tạo mảng lưu trữ phim đã hiển thị
            $displayedMovies = [];

            foreach ($movies as $movie):
                // Kiểm tra nếu phim chưa được hiển thị
                if (!in_array($movie['id'], $displayedMovies)):
                    // Thêm ID phim vào mảng đã hiển thị
                    $displayedMovies[] = $movie['id'];
            ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm movie-card">
                            <div class="position-relative">
                                <?php if (!empty($movie['imageUrl'])): ?>
                                    <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                        class="card-img-top"
                                        alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                        style="height: 300px; object-fit: cover; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 300px; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                                        <i class="fas fa-film fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-primary shadow-sm">ID: <?php echo $movie['id']; ?></span>
                                </div>

                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-info shadow-sm">
                                        <i class="fas fa-clock"></i> <?php echo $movie['duration']; ?> phút
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                <div class="category-container mb-2">
                                    <?php if (!empty($movie['categories'])): ?>
                                        <?php foreach ($movie['categories'] as $category): ?>
                                            <span class="badge bg-secondary me-1 category-badge">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary me-1 category-badge">
                                            <i class="fas fa-tag me-1"></i>Chưa phân loại
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text text-muted">
                                    <?php if (!empty($movie['description'])): ?>
                                        <?php echo htmlspecialchars(substr($movie['description'], 0, 100)) . '...'; ?>
                                    <?php endif; ?>
                                </p>

                                <!-- Thêm nút xem trailer -->
                                <?php if (!empty($movie['trailer'])): ?>
                                    <a href="<?php echo htmlspecialchars($movie['trailer']); ?>"
                                        target="_blank"
                                        class="btn btn-outline-danger btn-sm mb-2 shadow-sm trailer-btn">
                                        <i class="fab fa-youtube"></i> Xem Trailer
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer bg-transparent border-0">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo BASE_URL; ?>admin/movies/edit?id=<?php echo $movie['id']; ?>"
                                        class="btn btn-primary btn-sm shadow-sm">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>admin/movies/delete?id=<?php echo $movie['id']; ?>"
                                        class="btn btn-danger btn-sm shadow-sm"
                                        onclick="return confirm('Bạn có chắc muốn xóa phim này?');">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>

        <!-- Phân trang -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Nút Previous -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-sm" href="<?php
                                                                $prevPage = $page - 1;
                                                                $params = $_GET;
                                                                $params['page'] = $prevPage;
                                                                echo BASE_URL . 'admin/movies?' . http_build_query($params);
                                                                ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>

                    <!-- Các số trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link shadow-sm" href="<?php
                                                                    $params = $_GET;
                                                                    $params['page'] = $i;
                                                                    echo BASE_URL . 'admin/movies?' . http_build_query($params);
                                                                    ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút Next -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-sm" href="<?php
                                                                $nextPage = $page + 1;
                                                                $params = $_GET;
                                                                $params['page'] = $nextPage;
                                                                echo BASE_URL . 'admin/movies?' . http_build_query($params);
                                                                ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>

<style>
    :root {
        --primary: #0d6efd;
        --primary-dark: #0b5ed7;
        --secondary: #6c757d;
        --danger: #dc3545;
        --info: #17a2b8;
        --light: #f8f9fa;
        --dark: #343a40;
        --hover-bg: #f1f3f5;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 6px 12px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease;
    }

    body {
        background-color: var(--light);
        font-family: 'Roboto', sans-serif;
    }

    .container {
        max-width: 1200px;
    }

    h2 {
        font-size: 2rem;
        letter-spacing: 1px;
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        transition: var(--transition);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-outline-secondary {
        border-color: var(--secondary);
        color: var(--secondary);
        transition: var(--transition);
    }

    .btn-outline-secondary:hover {
        background-color: var(--secondary);
        color: white;
        transform: translateY(-2px);
    }

    .badge {
        padding: 0.5em 1em;
        font-weight: 500;
    }

    .category-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .category-badge {
        background-color: var(--secondary);
        color: white;
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
    }

    .card {
        transition: var(--transition);
    }

    .movie-card {
        border-radius: 10px;
        overflow: hidden;
        background-color: white;
    }

    .movie-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .card-img-top {
        transition: var(--transition);
    }

    .movie-card:hover .card-img-top {
        opacity: 0.9;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .card-text {
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .trailer-btn {
        border-color: var(--danger);
        color: var(--danger);
        transition: var(--transition);
    }

    .trailer-btn:hover {
        background-color: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    .card-footer {
        padding: 1rem 1.5rem;
    }

    .btn-sm {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }

    .btn-danger {
        background-color: var(--danger);
        border: none;
        transition: var(--transition);
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
    }

    .pagination .page-link {
        color: var(--primary);
        border: none;
        margin: 0 5px;
        transition: var(--transition);
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .pagination .page-link:hover {
        background-color: var(--hover-bg);
        color: var(--primary-dark);
    }

    .alert {
        border-radius: 8px;
        border: none;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 0.75rem;
    }

    .input-group-text {
        border-radius: 8px 0 0 8px;
    }
</style>

<!-- Thêm JavaScript để xử lý form -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const categorySelect = document.getElementById('categorySelect');

        // Tự động submit form khi thay đổi category
        categorySelect.addEventListener('change', function() {
            searchForm.submit();
        });
    });
</script>