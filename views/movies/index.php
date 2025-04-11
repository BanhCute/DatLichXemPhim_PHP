<?php include 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Danh sách phim</h2>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>admin/movies/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm phim mới
            </a>
        <?php endif; ?>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <div class="card shadow-sm border-0 mb-4" style="background: #f8f9fa;">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>movies" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group search-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            placeholder="Tìm kiếm phim..."
                            name="search"
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select custom-select" name="category">
                        <option value="">Tất cả thể loại</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-search w-100">
                        <i class="fas fa-search me-2"></i>Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hiển thị kết quả tìm kiếm -->
    <?php if (!empty($_GET['search']) || !empty($_GET['category'])): ?>
        <div class="alert alert-info">
            <?php if (!empty($_GET['search'])): ?>
                <i class="fas fa-search me-2"></i>
                Kết quả tìm kiếm cho: "<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>"
            <?php endif; ?>

            <?php if (!empty($_GET['category'])): ?>
                <?php if (!empty($_GET['search'])) echo ' | '; ?>
                <i class="fas fa-tag me-2"></i>
                Thể loại: <strong>
                    <?php
                    foreach ($categories as $category) {
                        if ($category['id'] == $_GET['category']) {
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

    <!-- Danh sách phim -->
    <?php if (empty($movies)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Không tìm thấy phim nào.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php
            // Thêm xử lý loại bỏ phim trùng lặp
            $uniqueMovieIds = array_unique(array_column($movies, 'id'));
            foreach ($uniqueMovieIds as $movieId):
                $movie = $movies[array_search($movieId, array_column($movies, 'id'))];
            ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm movie-card">
                        <div class="position-relative overflow-hidden">
                            <?php if (!empty($movie['imageUrl'])): ?>
                                <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                    class="card-img-top"
                                    alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                    style="height: 400px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="movie-overlay">
                                <span class="duration-badge">
                                    <i class="fas fa-clock me-1"></i><?php echo $movie['duration']; ?> phút
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($movie['title']); ?></h5>
                            <?php if (isset($movie['categories']) && !empty($movie['categories'])): ?>
                                <div class="categories-wrap mb-3">
                                    <?php foreach ($movie['categories'] as $category): ?>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($movie['trailer'])): ?>
                                <a href="<?php echo htmlspecialchars($movie['trailer']); ?>"
                                    target="_blank"
                                    class="btn btn-outline-danger btn-sm w-100 mb-2">
                                    <i class="fab fa-youtube me-1"></i>Xem Trailer
                                </a>
                            <?php endif; ?>

                            <a href="<?php echo BASE_URL; ?>movies/detail?id=<?php echo $movie['id']; ?>"
                                class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-info-circle me-1"></i>Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Nút Previous -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php
                                                    $params = $_GET;
                                                    $params['page'] = $page - 1;
                                                    echo BASE_URL . 'movies?' . http_build_query($params);
                                                    ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>

                    <!-- Các số trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php
                                                        $params = $_GET;
                                                        $params['page'] = $i;
                                                        echo BASE_URL . 'movies?' . http_build_query($params);
                                                        ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút Next -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php
                                                    $params = $_GET;
                                                    $params['page'] = $page + 1;
                                                    echo BASE_URL . 'movies?' . http_build_query($params);
                                                    ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .movie-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1) !important;
    }

    .movie-card:hover {
        transform: translateY(-5px);
    }

    .movie-overlay {
        position: absolute;
        top: 0;
        right: 0;
        padding: 10px;
    }

    .duration-badge {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .categories-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .category-badge {
        background: #f0f0f0;
        color: #666;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
    }

    .btn-primary {
        background: #0077be !important;
        border: none;
    }

    .btn-primary:hover {
        background: #005c91 !important;
    }

    .btn-outline-danger {
        border-color: #ff4444;
        color: #ff4444;
    }

    .btn-outline-danger:hover {
        background: #ff4444;
        color: white;
    }

    .card-title {
        font-size: 1.1rem;
        line-height: 1.4;
        height: 3em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .input-group-text {
        background: #f8f9fa;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
        border-color: #2196F3;
    }

    /* Thêm styles mới cho thanh tìm kiếm */
    .search-group {
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .search-group .input-group-text {
        background: white;
        border: none;
        padding-left: 20px;
    }

    .search-group .input-group-text i {
        color: #0077be;
    }

    .search-group .form-control {
        border: none;
        padding: 12px 20px;
        font-size: 1rem;
    }

    .custom-select {
        border-radius: 30px;
        padding: 12px 20px;
        border: none;
        background-color: white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-search {
        background: #0077be;
        color: white;
        border-radius: 30px;
        padding: 12px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-search:hover {
        background: #005c91;
        transform: translateY(-2px);
    }
</style>

<?php include 'views/layouts/footer.php'; ?>