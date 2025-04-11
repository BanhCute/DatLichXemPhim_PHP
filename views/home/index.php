<?php include 'views/layouts/header.php'; ?>

<?php
// Debug data nhận được
error_log("View received data: " . json_encode([
    'hasLatestMovies' => isset($latestMovies),
    'movieCount' => isset($latestMovies) ? count($latestMovies) : 0,
    'hasCategories' => isset($categories),
    'categoryCount' => isset($categories) ? count($categories) : 0
]));
?>

<!-- Hero Section với Background Video/Image -->
<div class="hero-banner position-relative">
    <div class="overlay"></div>
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-md-6">
                <div class="hero-content text-white">
                    <h1 class="display-4 fw-bold mb-4">Đặt Vé Xem Phim Online</h1>
                    <p class="lead mb-4">Trải nghiệm đặt vé xem phim trực tuyến dễ dàng, nhanh chóng và tiện lợi. Không cần xếp hàng, không lo hết vé!</p>
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>movies" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-film me-2"></i>Xem Danh Sách Phim
                        </a>
                        <a href="#latest-movies" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-star me-2"></i>Phim Mới Nhất
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-cards">
                    <div class="feature-card">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Đặt Vé Dễ Dàng</h3>
                        <p>Chỉ với vài click chuột</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-couch"></i>
                        <h3>Chọn Ghế Ưa Thích</h3>
                        <p>Thoải mái lựa chọn vị trí</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-clock"></i>
                        <h3>Tiết Kiệm Thời Gian</h3>
                        <p>Không cần xếp hàng chờ đợi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Categories Section -->
<section class="quick-categories py-4 bg-light">
    <div class="container">
        <div class="category-pills d-flex justify-content-center flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>movies" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-film me-2"></i>Tất cả phim
            </a>
            <?php if (isset($categories) && is_array($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>movies?category=<?php echo $category['id']; ?>"
                        class="btn btn-outline-primary rounded-pill">
                        <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Phim Mới Nhất Section -->
<section id="latest-movies" class="latest-movies py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="display-6 fw-bold">Phim Mới Nhất</h2>
            <div class="section-divider"></div>
            <p class="text-muted">Cập nhật những bộ phim mới và hấp dẫn nhất</p>
        </div>

        <?php
        // Debug data
        error_log("View data: " . json_encode([
            'hasMovies' => isset($latestMovies),
            'movieCount' => isset($latestMovies) ? count($latestMovies) : 0
        ]));
        ?>

        <?php if (empty($latestMovies)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>Chưa có phim nào.
            </div>
        <?php else: ?>
            <!-- Debug info -->
            <?php error_log("Rendering movies in view: " . count($latestMovies)); ?>

            <div class="row g-4">
                <?php
                // Sử dụng array_unique để đảm bảo không có phim trùng lặp
                $uniqueMovies = array_unique(array_column($latestMovies, 'id'));
                foreach ($uniqueMovies as $movieId):
                    $movie = $latestMovies[array_search($movieId, array_column($latestMovies, 'id'))];
                ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="movie-card">
                            <div class="movie-poster">
                                <?php if (!empty($movie['imageUrl'])): ?>
                                    <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                        alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                        class="img-fluid">
                                <?php else: ?>
                                    <div class="no-poster">
                                        <i class="fas fa-film"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="movie-overlay">
                                    <div class="overlay-content">
                                        <?php if (!empty($movie['trailer'])): ?>
                                            <a href="<?php echo htmlspecialchars($movie['trailer']); ?>"
                                                class="btn btn-danger btn-sm mb-2" target="_blank">
                                                <i class="fab fa-youtube me-2"></i>Xem Trailer
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>movies/detail?id=<?php echo $movie['id']; ?>"
                                            class="btn btn-light btn-sm">
                                            <i class="fas fa-info-circle me-2"></i>Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>

                                <!-- Hiển thị categories -->
                                <?php if (!empty($movie['categories'])): ?>
                                    <div class="movie-categories mb-2">
                                        <?php foreach ($movie['categories'] as $category): ?>
                                            <span class="category-tag">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="movie-meta">
                                    <span class="duration">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo $movie['duration']; ?> phút
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Nút xem thêm -->
            <div class="text-center mt-5">
                <a href="<?php echo BASE_URL; ?>movies" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-film me-2"></i>Xem tất cả phim
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CSS Styles -->
<style>
    :root {
        --primary-color: #0066cc;
        --primary-dark: #004d99;
        --primary-light: #e6f0ff;
    }

    .hero-banner {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        min-height: 600px;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .hero-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('path_to_your_pattern.png');
        opacity: 0.1;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .feature-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .feature-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        color: white;
    }

    .feature-card i {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: var(--primary-light);
    }

    .section-divider {
        width: 60px;
        height: 4px;
        background: var(--primary-color);
        margin: 1rem auto;
        border-radius: 2px;
    }

    .movie-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }

    .movie-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .movie-poster {
        position: relative;
        padding-top: 150%;
        overflow: hidden;
        background: #f8f9fa;
    }

    .movie-poster img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .movie-card:hover .movie-poster img {
        transform: scale(1.05);
    }

    .movie-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .movie-card:hover .movie-overlay {
        opacity: 1;
    }

    .movie-info {
        padding: 1.5rem;
    }

    .movie-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #2c3e50;
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .movie-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .category-tag {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: #f0f2f5;
        color: #4a5568;
        transition: all 0.2s ease;
    }

    .category-tag:hover {
        background: #e2e8f0;
        color: #2d3748;
    }

    .movie-meta {
        display: flex;
        align-items: center;
        color: #718096;
        font-size: 0.9rem;
    }

    .duration {
        display: flex;
        align-items: center;
    }

    .btn-danger {
        background: #e53e3e;
        border: none;
    }

    .btn-danger:hover {
        background: #c53030;
    }

    .btn-light {
        background: rgba(255, 255, 255, 0.9);
        color: #2d3748;
    }

    .btn-light:hover {
        background: #ffffff;
        color: #1a202c;
    }

    .section-header {
        margin-bottom: 3rem;
    }

    .section-divider {
        width: 60px;
        height: 4px;
        background: linear-gradient(to right, #3182ce, #63b3ed);
        margin: 1rem auto;
        border-radius: 2px;
    }

    .overlay-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
    }

    .no-poster {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .no-poster i {
        font-size: 3rem;
        color: #999;
    }

    /* Quick Categories */
    .quick-categories {
        border-bottom: 1px solid #eee;
    }

    .category-pills .btn {
        transition: all 0.3s ease;
    }

    .category-pills .btn:hover {
        transform: translateY(-2px);
    }

    /* Featured Movies */
    .featured-movies {
        background: linear-gradient(to right, #1a1a1a, #333);
    }

    .featured-movie-card {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .movie-actions {
        text-align: center;
    }

    /* Category Blocks */
    .category-block {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-dark);
        margin: 0;
    }

    /* Movie Cards Enhancement */
    .movie-card {
        border: none;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .movie-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .movie-poster {
        position: relative;
        padding-top: 150%;
    }

    .movie-poster img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .movie-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .movie-card:hover .movie-overlay {
        opacity: 1;
    }

    .movie-info {
        padding: 15px;
    }

    .movie-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .movie-categories {
        margin-bottom: 8px;
    }

    .movie-categories .badge {
        font-size: 0.75rem;
        padding: 5px 10px;
        margin-right: 5px;
        margin-bottom: 5px;
        border-radius: 12px;
    }

    .movie-meta {
        font-size: 0.85rem;
        color: #666;
    }
</style>

<?php include 'views/layouts/footer.php'; ?>