<?php include 'views/layouts/header.php'; ?>

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
            <h2 class="display-6 fw-bold text-primary">Phim Mới Nhất</h2>
            <div class="section-divider"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($latestMovies as $movie): ?>
                <div class="col-md-3">
                    <div class="movie-card">
                        <div class="movie-poster">
                            <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <div class="movie-overlay">
                                <a href="<?php echo BASE_URL; ?>movies/detail?id=<?php echo $movie['id']; ?>"
                                    class="btn btn-primary">Chi tiết</a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <?php if (isset($movie['categories']) && !empty($movie['categories'])): ?>
                                <div class="movie-categories mb-2">
                                    <?php foreach ($movie['categories'] as $cat): ?>
                                        <span class="badge bg-primary me-1">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <p class="movie-duration">
                                <i class="fas fa-clock me-2"></i><?php echo $movie['duration']; ?> phút
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>movies" class="btn btn-outline-primary btn-lg">
                Xem Tất Cả Phim
            </a>
        </div>
    </div>
</section>



<!-- Phim Theo Thể Loại Section - Cải tiến -->
<section class="categories-section py-5">
    <div class="container">
        <?php foreach ($moviesByCategory as $categoryId => $category): ?>
            <?php if (!empty($category['movies'])): ?>
                <div class="category-block mb-5">
                    <div class="section-header d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title">
                            <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($category['name']); ?>
                        </h2>
                        <a href="<?php echo BASE_URL; ?>movies?category=<?php echo $categoryId; ?>"
                            class="btn btn-outline-primary">
                            Xem thêm <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <div class="row g-4">
                        <?php foreach (array_slice($category['movies'], 0, 4) as $movie): ?>
                            <div class="col-md-3">
                                <div class="movie-card">
                                    <div class="movie-poster">
                                        <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                                            alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                        <div class="movie-overlay">
                                            <div class="movie-actions d-flex flex-column gap-2">

                                                <a href="<?php echo BASE_URL; ?>movies/detail?id=<?php echo $movie['id']; ?>"
                                                    class="btn btn-outline-light btn-sm">
                                                    <i class="fas fa-info-circle me-2"></i>Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="movie-info">
                                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                        <?php if (isset($movie['categories']) && !empty($movie['categories'])): ?>
                                            <div class="movie-categories">
                                                <?php foreach ($movie['categories'] as $cat): ?>
                                                    <span class="badge bg-primary">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="movie-meta">
                                            <span class="duration">
                                                <i class="fas fa-clock me-1"></i><?php echo $movie['duration']; ?> phút
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
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
        width: 80px;
        height: 4px;
        background: var(--primary-color);
        margin: 20px auto;
        border-radius: 2px;
    }

    .movie-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .movie-card:hover {
        transform: translateY(-10px);
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
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .movie-card:hover .movie-overlay {
        opacity: 1;
    }

    .movie-info {
        padding: 20px;
    }

    .movie-title {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: var(--primary-dark);
    }

    .movie-duration {
        color: #666;
        font-size: 0.9rem;
        margin: 0;
    }

    .btn-primary {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: white;
    }

    .movie-categories {
        margin: 8px 0;
    }

    .movie-categories .badge {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 12px;
        background-color: var(--primary-color);
        transition: all 0.3s ease;
    }

    .movie-categories .badge:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
    }

    .movie-info {
        padding: 15px;
    }

    .movie-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--primary-dark);
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .movie-duration {
        font-size: 0.9rem;
        color: #666;
        margin: 0;
        display: flex;
        align-items: center;
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