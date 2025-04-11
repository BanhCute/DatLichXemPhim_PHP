<?php
// Hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Định nghĩa hằng số BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_name);

// Autoload các file trong thư mục models
spl_autoload_register(function ($class_name) {
    $file = "models/$class_name.php";
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include các controller
require_once "controllers/HomeController.php";
require_once "controllers/MovieController.php";
require_once "controllers/UserController.php";
require_once "controllers/AdminController.php";
require_once "controllers/BookingController.php";
require_once "controllers/PaymentController.php";

// Lấy URL từ $_GET['url']
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// Định nghĩa các route
// Replace or update these routes
$routes = [
    // Trang chủ
    '' => ['HomeController', 'index'],
    'movies' => ['MovieController', 'index'],
    'movies/detail' => ['MovieController', 'detail'],

    // Đăng nhập, đăng ký, đăng xuất
    'login' => ['UserController', 'showLoginForm'],
    'do-login' => ['UserController', 'login'],
    'register' => ['UserController', 'showRegisterForm'],
    'do-register' => ['UserController', 'register'],
    'logout' => ['UserController', 'logout'],

    // Admin - Quản lý phim
    'admin/movies' => ['AdminController', 'movies'],
    'admin/movies/add' => ['AdminController', 'addMovie'],
    'admin/movies/edit' => ['AdminController', 'editMovie'],
    'admin/movies/delete' => ['AdminController', 'deleteMovie'],

    // Admin - Quản lý suất chiếu
    'admin/showtimes' => ['AdminController', 'showTimes'],
    'admin/showtimes/add' => ['AdminController', 'addShowTime'],
    'admin/showtimes/edit' => ['AdminController', 'editShowTime'],
    'admin/showtimes/delete' => ['AdminController', 'deleteShowTime'],
    //Admin - Quản lý thể loại
    'admin/categories' => ['AdminController', 'categories'],
    'admin/categories/add' => ['AdminController', 'addCategory'],
    'admin/categories/edit' => ['AdminController', 'editCategory'],
    'admin/categories/delete' => ['AdminController', 'deleteCategory'],

    // Admin - Quản lý đặt vé
    'admin/bookings' => ['AdminController', 'bookings'],
    'admin/bookings/confirm' => ['AdminController', 'confirmBooking'],
    'admin/bookings/cancel' => ['AdminController', 'cancelBooking'],
    'admin/bookings/delete' => ['AdminController', 'deleteBooking'],

    // Admin - Quản lý người dùng
    'admin/users' => ['AdminController', 'users'],
    'admin/users/promote' => ['AdminController', 'promoteUser'],
    'admin/users/demote' => ['AdminController', 'demoteUser'],
    'admin/users/delete' => ['AdminController', 'deleteUser'],

    // User - Đặt vé
    'booking/form/([0-9]+)' => ['BookingController', 'form'],
    'booking/create' => ['BookingController', 'create'],
    'booking/confirm' => ['BookingController', 'confirm'],
    'booking/cancel' => ['BookingController', 'cancel'],
    'my-bookings' => ['UserController', 'myBookings'],
    'payment/([0-9]+)' => ['PaymentController', 'showPayment'],
    'confirm-payment/([0-9]+)' => ['PaymentController', 'confirmPayment'],
    'profile' => ['UserController', 'showProfile'],
    'profile/change-password' => ['UserController', 'showChangePassword'],
    'profile/update' => ['UserController', 'updateProfile'],
    'profile/update-password' => ['UserController', 'updatePassword'],
    'admin/dashboard' => ['AdminController', 'dashboard'], // Add this line
];

// Kiểm tra route có tồn tại không
$matched = false;
foreach ($routes as $pattern => $handler) {
    // Chuyển đổi pattern route thành regex pattern
    $pattern = str_replace('/', '\/', $pattern);
    if (preg_match('/^' . $pattern . '$/', $url, $matches)) {
        $controller_name = $handler[0];
        $action = $handler[1];

        // Tạo instance của controller
        $controller = new $controller_name();

        // Nếu có ID trong matches (từ regex), sử dụng nó
        if (count($matches) > 1) {
            $controller->$action($matches[1]);
        } else {
            // Không có ID trong URL pattern, kiểm tra query string
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if ($id !== null) {
                $controller->$action($id);
            } else {
                $controller->$action();
            }
        }

        $matched = true;
        break;
    }
}

if (!$matched) {
    // Debug
    error_log("Route not found: " . $url);
    error_log("Available routes: " . print_r(array_keys($routes), true));

    // Chuyển hướng về trang chủ
    header('Location: ' . BASE_URL);
    exit;
}
