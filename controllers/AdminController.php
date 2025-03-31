<?php
require_once "models/Movie.php";
require_once "models/ShowTime.php";
require_once "models/User.php";
require_once "models/Booking.php";
require_once "models/Category.php";

class AdminController
{
    private $movieModel;
    private $showTimeModel;
    private $userModel;
    private $bookingModel;
    private $categoryModel;

    public function __construct()
    {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Khởi tạo các model đúng cách
        $this->movieModel = new Movie();
        $this->showTimeModel = new ShowTime();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->categoryModel = new Category();
    }

    // Thêm phương thức render để thay thế view()
    private function render($viewPath, $data = [])
    {
        // Extract data thành các biến riêng lẻ
        extract($data);

        // Include view file
        $viewFile = 'views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View file not found: " . $viewFile);
        }
    }

    // Quản lý phim
    public function movies()
    {
        // Lấy các tham số tìm kiếm
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 8;

        // Debug log
        error_log("Admin Movies - Search Params: keyword=$keyword, categoryId=$categoryId, page=$page");

        try {
            $movies = [];
            $totalMovies = 0;

            if ($categoryId > 0) {
                // Nếu có chọn thể loại
                if (!empty($keyword)) {
                    // Có cả keyword và category
                    $movies = $this->movieModel->getMoviesByCategoryAndKeyword($categoryId, $keyword, $page, $perPage);
                    $totalMovies = $this->movieModel->countMoviesByCategoryAndKeyword($categoryId, $keyword);
                } else {
                    // Chỉ có category
                    $movies = $this->movieModel->getMoviesByCategory($categoryId, $page, $perPage);
                    $totalMovies = $this->movieModel->countMoviesByCategory($categoryId);
                }
            } else {
                // Không chọn thể loại
                if (!empty($keyword)) {
                    // Chỉ có keyword
                    $movies = $this->movieModel->searchMovies($keyword, $page, $perPage);
                    $totalMovies = $this->movieModel->countMoviesByKeyword($keyword);
                } else {
                    // Không có điều kiện lọc
                    $movies = $this->movieModel->getAllMovies($page, $perPage);
                    $totalMovies = $this->movieModel->countAllMovies();
                }
            }

            // Lấy categories cho mỗi phim
            if ($movies) {
                foreach ($movies as &$movie) {
                    $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
                }
            }

            $totalPages = ceil($totalMovies / $perPage);
            $page = min($page, $totalPages);

            // Lấy danh sách categories cho dropdown
            $categories = $this->categoryModel->getAllCategories();

            error_log("Total movies found: $totalMovies, Total pages: $totalPages, Current page: $page");

            // Sử dụng render thay vì view
            $this->render('admin/movies/index', [
                'movies' => $movies,
                'categories' => $categories,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalMovies' => $totalMovies,
                'keyword' => $keyword,
                'selectedCategory' => $categoryId
            ]);
        } catch (Exception $e) {
            error_log("Error in AdminController::movies - " . $e->getMessage());
            // Xử lý lỗi phù hợp
        }
    }

    public function addMovie()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $duration = $_POST['duration'] ?? 0;

            // Đảm bảo categories là một mảng
            $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? $_POST['categories'] : [];

            if (empty($title) || empty($description) || empty($duration)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'admin/movies/add');
                exit;
            }

            // Xử lý upload ảnh
            $imageUrl = null;
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                // Kiểm tra thư mục upload tồn tại
                $uploadDir = 'public/uploads/movies/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Xử lý tên file
                $fileName = basename($_FILES["image"]["name"]);
                $targetFilePath = $uploadDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Kiểm tra loại file
                $allowTypes = array('jpg', 'jpeg', 'png');
                if (in_array(strtolower($fileType), $allowTypes)) {
                    // Upload file
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $imageUrl = $targetFilePath;
                    } else {
                        $_SESSION['error'] = 'Có lỗi xảy ra khi upload file.';
                        header('Location: ' . BASE_URL . 'admin/movies/add');
                        exit;
                    }
                } else {
                    $_SESSION['error'] = 'Chỉ chấp nhận file JPG, JPEG & PNG.';
                    header('Location: ' . BASE_URL . 'admin/movies/add');
                    exit;
                }
            }

            // Tạo phim mới - đảm bảo phương thức trả về ID phim
            $movieId = $this->movieModel->createMovie($title, $description, $duration, $imageUrl);

            if ($movieId) {
                // Thêm thể loại cho phim
                if (!empty($categories)) {
                    foreach ($categories as $categoryId) {
                        $this->movieModel->addMovieCategory($movieId, $categoryId);
                    }
                }

                $_SESSION['success'] = 'Thêm phim thành công';
                header('Location: ' . BASE_URL . 'admin/movies');
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi thêm phim';
                header('Location: ' . BASE_URL . 'admin/movies/add');
            }
            exit;
        }

        require_once "views/admin/movies/add.php";
    }

    public function editMovie($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $duration = $_POST['duration'] ?? 0;

            // Đảm bảo categories là một mảng
            $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? $_POST['categories'] : [];

            // Lấy ảnh hiện tại
            $current_image = $_POST['current_image'] ?? '';

            if (empty($title) || empty($description) || empty($duration)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                exit;
            }

            // Xử lý upload ảnh mới
            $imageUrl = $current_image; // Mặc định giữ ảnh hiện tại
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                // Kiểm tra thư mục upload tồn tại
                $uploadDir = 'public/uploads/movies/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Xử lý tên file
                $fileName = basename($_FILES["image"]["name"]);
                $targetFilePath = $uploadDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Kiểm tra loại file
                $allowTypes = array('jpg', 'jpeg', 'png');
                if (in_array(strtolower($fileType), $allowTypes)) {
                    // Upload file
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        // Xóa ảnh cũ nếu có
                        if (!empty($current_image) && file_exists($current_image)) {
                            unlink($current_image);
                        }
                        $imageUrl = $targetFilePath;
                    } else {
                        $_SESSION['error'] = 'Có lỗi xảy ra khi upload file.';
                        header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                        exit;
                    }
                } else {
                    $_SESSION['error'] = 'Chỉ chấp nhận file JPG, JPEG & PNG.';
                    header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                    exit;
                }
            }

            // Cập nhật thông tin phim
            if ($this->movieModel->updateMovie($id, $title, $description, $duration, $imageUrl)) {
                // Cập nhật thể loại
                try {
                    // Xóa các thể loại cũ
                    $this->movieModel->deleteMovieCategories($id);

                    // Thêm thể loại mới
                    if (!empty($categories)) {
                        foreach ($categories as $categoryId) {
                            $this->movieModel->addMovieCategory($id, $categoryId);
                        }
                    }

                    $_SESSION['success'] = 'Cập nhật phim thành công';
                    header('Location: ' . BASE_URL . 'admin/movies');
                } catch (Exception $e) {
                    error_log("Lỗi cập nhật thể loại: " . $e->getMessage());
                    $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật thể loại phim';
                    header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                }
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật phim';
                header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
            }
            exit;
        }

        $movie = $this->movieModel->getMovieById($id);
        if (!$movie) {
            $_SESSION['error'] = 'Không tìm thấy phim';
            header('Location: ' . BASE_URL . 'admin/movies');
            exit;
        }

        require_once "views/admin/movies/edit.php";
    }

    public function deleteMovie($id)
    {
        try {
            // Kiểm tra xem phim có tồn tại không
            $movie = $this->movieModel->getMovieById($id);
            if (!$movie) {
                $_SESSION['error'] = 'Không tìm thấy phim';
                header('Location: ' . BASE_URL . 'admin/movies');
                exit;
            }

            // Debug: Ghi log thông tin phim
            error_log("Đang xóa phim: " . json_encode($movie));

            // Kiểm tra xem có đặt vé nào cho các suất chiếu của phim không
            // Nếu cần, có thể tạm thời bỏ qua kiểm tra này
            $hasBookings = $this->movieModel->hasBookings($id);
            error_log("Phim có đặt vé không: " . ($hasBookings ? "Có" : "Không"));

            if ($hasBookings) {
                $_SESSION['error'] = 'Không thể xóa phim này vì đã có người đặt vé xem';
                header('Location: ' . BASE_URL . 'admin/movies');
                exit;
            }

            // Xóa các mối quan hệ trước
            // 1. Xóa các thể loại của phim
            $deleteCategories = $this->movieModel->deleteMovieCategories($id);
            error_log("Kết quả xóa thể loại: " . ($deleteCategories ? "Thành công" : "Thất bại"));

            // 2. Xóa tất cả suất chiếu của phim
            $deleteShowtimes = $this->movieModel->deleteShowTimes($id);
            error_log("Kết quả xóa suất chiếu: " . ($deleteShowtimes ? "Thành công" : "Thất bại"));

            // 3. Xóa ảnh của phim nếu có
            if (!empty($movie['imageUrl'])) {
                $imagePath = $movie['imageUrl'];
                if (file_exists($imagePath)) {
                    $deleteImage = unlink($imagePath);
                    error_log("Kết quả xóa ảnh: " . ($deleteImage ? "Thành công" : "Thất bại"));
                }
            }

            // 4. Xóa phim
            $deleteMovie = $this->movieModel->deleteMovie($id);
            error_log("Kết quả xóa phim: " . ($deleteMovie ? "Thành công" : "Thất bại"));

            if ($deleteMovie) {
                $_SESSION['success'] = 'Xóa phim thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa phim';
            }
        } catch (Exception $e) {
            error_log("Lỗi xóa phim: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa phim: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'admin/movies');
        exit;
    }

    // Quản lý suất chiếu
    public function showTimes()
    {
        $showTimes = $this->showTimeModel->getAllShowTimes();
        require_once "views/admin/showtimes/index.php";
    }

    public function addShowTime()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movieId = $_POST['movieId'] ?? 0;
            $startTime = $_POST['startTime'] ?? '';
            $room = $_POST['room'] ?? '';
            $price = $_POST['price'] ?? 0;

            if (empty($movieId) || empty($startTime) || empty($room) || empty($price)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'admin/showtimes/add');
                exit;
            }

            // Tính endTime dựa trên thời lượng phim
            $movie = $this->movieModel->getMovieById($movieId);
            $startDateTime = new DateTime($startTime);
            $endDateTime = clone $startDateTime;
            $endDateTime->add(new DateInterval('PT' . $movie['duration'] . 'M'));
            $endTime = $endDateTime->format('Y-m-d H:i:s');

            if ($this->showTimeModel->createShowTime($movieId, $startTime, $endTime, $room, $price)) {
                $_SESSION['success'] = 'Thêm suất chiếu thành công';
                header('Location: ' . BASE_URL . 'admin/showtimes');
            } else {
                $_SESSION['error'] = 'Phòng đã được đặt trong khoảng thời gian này';
                header('Location: ' . BASE_URL . 'admin/showtimes/add');
            }
            exit;
        }

        $movies = $this->movieModel->getAllMovies();
        require_once "views/admin/showtimes/add.php";
    }

    public function editShowTime($id)
    {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $showTime = $this->showTimeModel->getShowTimeById($id);
        if (!$showTime) {
            $_SESSION['error'] = 'Không tìm thấy suất chiếu';
            header('Location: ' . BASE_URL . 'admin/showtimes');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movieId = $_POST['movieId'] ?? 0;
            $startTime = $_POST['startTime'] ?? '';
            $room = $_POST['room'] ?? '';
            $price = $_POST['price'] ?? 0;

            if (empty($movieId) || empty($startTime) || empty($room) || empty($price)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'admin/showtimes/edit?id=' . $id);
                exit;
            }

            // Tính endTime dựa trên thời lượng phim
            $movie = $this->movieModel->getMovieById($movieId);
            if (!$movie) {
                $_SESSION['error'] = 'Không tìm thấy phim';
                header('Location: ' . BASE_URL . 'admin/showtimes/edit?id=' . $id);
                exit;
            }

            $startDateTime = new DateTime($startTime);
            $endDateTime = clone $startDateTime;
            $endDateTime->add(new DateInterval('PT' . $movie['duration'] . 'M'));
            $endTime = $endDateTime->format('Y-m-d H:i:s');

            if ($this->showTimeModel->updateShowTime($id, $movieId, $startTime, $endTime, $room, $price)) {
                $_SESSION['success'] = 'Cập nhật suất chiếu thành công';
                header('Location: ' . BASE_URL . 'admin/showtimes');
            } else {
                $_SESSION['error'] = 'Phòng đã được đặt trong khoảng thời gian này';
                header('Location: ' . BASE_URL . 'admin/showtimes/edit?id=' . $id);
            }
            exit;
        }

        $movies = $this->movieModel->getAllMovies();
        require_once "views/admin/showtimes/edit.php";
    }

    public function deleteShowTime($id)
    {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($this->showTimeModel->deleteShowTime($id)) {
            $_SESSION['success'] = 'Xóa suất chiếu thành công';
        } else {
            $_SESSION['error'] = 'Không thể xóa suất chiếu này vì đã có người đặt vé';
        }
        header('Location: ' . BASE_URL . 'admin/showtimes');
        exit;
    }

    // Thống kê đặt vé
    public function bookings()
    {
        $bookings = $this->bookingModel->getAllBookings();
        require_once "views/admin/bookings/index.php";
    }

    public function confirmBooking($id)
    {
        if ($this->bookingModel->confirmBooking($id)) {
            $_SESSION['success'] = 'Xác nhận vé thành công';
        } else {
            $_SESSION['error'] = 'Không thể xác nhận vé này';
        }
        header('Location: ' . BASE_URL . 'admin/bookings');
        exit;
    }

    public function cancelBooking($id)
    {
        if ($this->bookingModel->cancelBooking($id, null)) {
            $_SESSION['success'] = 'Hủy vé thành công';
        } else {
            $_SESSION['error'] = 'Không thể hủy vé này';
        }
        header('Location: ' . BASE_URL . 'admin/bookings');
        exit;
    }

    // Quản lý người dùng
    public function users()
    {
        $users = $this->userModel->getAllUsers();
        require_once "views/admin/users/index.php";
    }

    public function promoteUser($id)
    {
        if ($this->userModel->updateUserRole($id, 'ADMIN')) {
            $_SESSION['success'] = 'Thăng cấp người dùng thành công';
        } else {
            $_SESSION['error'] = 'Không thể thăng cấp người dùng này';
        }
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    public function demoteUser($id)
    {
        if ($this->userModel->updateUserRole($id, 'USER')) {
            $_SESSION['success'] = 'Hạ cấp người dùng thành công';
        } else {
            $_SESSION['error'] = 'Không thể hạ cấp người dùng này';
        }
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    public function deleteUser($id)
    {
        if ($this->userModel->deleteUser($id)) {
            $_SESSION['success'] = 'Xóa người dùng thành công';
        } else {
            $_SESSION['error'] = 'Không thể xóa người dùng này';
        }
        header('Location: ' . BASE_URL . 'admin/users');
        exit;
    }

    // Thêm phương thức để quản lý categories
    public function categories()
    {
        try {
            $categories = $this->categoryModel->getAllCategories();
            require 'views/admin/categories/index.php';
        } catch (Exception $e) {
            error_log("Lỗi trong phương thức categories(): " . $e->getMessage());
            $_SESSION['error'] = "Đã xảy ra lỗi: " . $e->getMessage();
            require 'views/admin/categories/index.php';
        }
    }

    public function addCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';

            if (empty($name)) {
                $_SESSION['error'] = 'Tên thể loại không được để trống';
                header('Location: ' . BASE_URL . 'admin/categories/add');
                exit;
            }

            if (empty($slug)) {
                // Tạo slug từ tên nếu không nhập
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }

            $result = $this->categoryModel->addCategory($name, $slug);

            if ($result) {
                $_SESSION['success'] = 'Thêm thể loại thành công';
                header('Location: ' . BASE_URL . 'admin/categories');
            } else {
                $_SESSION['error'] = 'Thêm thể loại thất bại';
                header('Location: ' . BASE_URL . 'admin/categories/add');
            }
            exit;
        }

        require 'views/admin/categories/add.php';
    }

    public function editCategory()
    {
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $category = $this->categoryModel->getCategoryById($id);

        if (!$category) {
            $_SESSION['error'] = 'Thể loại không tồn tại';
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';

            if (empty($name)) {
                $_SESSION['error'] = 'Tên thể loại không được để trống';
                header('Location: ' . BASE_URL . 'admin/categories/edit?id=' . $id);
                exit;
            }

            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }

            $result = $this->categoryModel->updateCategory($id, $name, $slug);

            if ($result) {
                $_SESSION['success'] = 'Cập nhật thể loại thành công';
                header('Location: ' . BASE_URL . 'admin/categories');
            } else {
                $_SESSION['error'] = 'Cập nhật thể loại thất bại';
                header('Location: ' . BASE_URL . 'admin/categories/edit?id=' . $id);
            }
            exit;
        }

        require 'views/admin/categories/edit.php';
    }

    public function deleteCategory()
    {
        $id = $_GET['id'] ?? 0;

        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/categories');
            exit;
        }

        $result = $this->categoryModel->deleteCategory($id);

        if ($result) {
            $_SESSION['success'] = 'Xóa thể loại thành công';
        } else {
            $_SESSION['error'] = 'Xóa thể loại thất bại';
        }

        header('Location: ' . BASE_URL . 'admin/categories');
        exit;
    }
}
