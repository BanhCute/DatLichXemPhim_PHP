<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống đặt vé xem phim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0066cc;
            --primary-dark: #004d99;
            --primary-light: #e6f0ff;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark)) !important;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand i {
            font-size: 1.8rem;
        }

        .nav-link {
            font-weight: 500;
            padding: 8px 15px !important;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-item.active .nav-link {
            background: rgba(255, 255, 255, 0.2);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .dropdown-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item i {
            color: var(--primary-blue);
        }

        .dropdown-item:hover {
            background: var(--primary-light);
        }

        .user-welcome {
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-buttons .nav-link {
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 0 5px;
        }

        .auth-buttons .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Custom Dropdown Menu Styles */
        .custom-dropdown {
            padding: 0.5rem 0;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            min-width: 200px;
        }

        .custom-dropdown .dropdown-item {
            padding: 0.75rem 1rem;
            color: #495057;
            font-weight: normal;
            transition: all 0.2s ease;
        }

        .custom-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .custom-dropdown .dropdown-item.active {
            background-color: #e7f1ff;
            color: #0d6efd;
            font-weight: 500;
        }

        .custom-dropdown .dropdown-item.active:hover {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        /* Nav Pills Styles */
        .nav-pills .nav-link {
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-right: 0.5rem;
        }

        .nav-pills .nav-link:hover {
            color: #0d6efd;
            background-color: #e7f1ff;
        }

        .nav-pills .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .nav-pills .nav-link.dropdown-toggle.active {
            background-color: #0d6efd;
            color: #fff;
        }

        /* Icon styles */
        .dropdown-item.active i {
            color: #0d6efd;
        }
    </style>
    <!-- Bootstrap JS và các dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-film"></i>
                MovieTickets
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>movies">
                            <i class="fas fa-ticket-alt me-2"></i>Danh sách phim
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-shield me-2"></i>Quản lý
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/movies">
                                        <i class="fas fa-film"></i>Quản lý phim</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/categories">
                                        <i class="fas fa-tags"></i>Quản lý thể loại</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/showtimes">
                                        <i class="fas fa-clock"></i>Quản lý suất chiếu</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/bookings">
                                        <i class="fas fa-chart-bar"></i>Thống kê đặt vé</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/users">
                                        <i class="fas fa-users"></i>Quản lý người dùng</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>my-bookings">
                                <i class="fas fa-ticket me-2"></i>Vé của tôi
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link user-welcome">
                                <i class="fas fa-user-circle"></i>
                                <?php echo $_SESSION['user_name']; ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>login">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>register">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>

</html>