<?php
require_once 'config/database.php';
require_once "models/Movie.php";
require_once "models/ShowTime.php";
require_once "models/Category.php";

class MovieController
{
    private $movieModel;
    private $showTimeModel;
    private $categoryModel;
    private $uploadDir;
    private $uploadPath = 'public/uploads/movies/'; // Đường dẫn tương đối cho web
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->movieModel = new Movie();
        $this->showTimeModel = new ShowTime();
        $this->categoryModel = new Category();

        // Đường dẫn tuyệt đối cho việc lưu file
        $this->uploadDir = __DIR__ . '/../' . $this->uploadPath;

        // Tạo thư mục uploads nếu chưa tồn tại
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function index()
    {
        try {
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
            $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

            // Debug
            error_log("=== MovieController::index ===");
            error_log("Page: $page, Keyword: $keyword, CategoryId: $categoryId");

            // Sử dụng phương thức getMovies duy nhất
            $movies = $this->movieModel->getMovies($keyword, $categoryId, $page);

            // Đếm tổng số phim để phân trang đúng
            $totalMovies = $this->movieModel->getTotalMovies($keyword, $categoryId);
            $itemsPerPage = 8;
            $totalPages = ceil($totalMovies / $itemsPerPage);

            // Lấy categories cho mỗi phim
            if ($movies) {
                foreach ($movies as &$movie) {
                    $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
                }
            }

            // Lấy danh sách categories cho form tìm kiếm
            $categories = $this->categoryModel->getAllCategories();

            require 'views/movies/index.php';
        } catch (Exception $e) {
            error_log("Error in MovieController::index - " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải danh sách phim';
            require 'views/movies/index.php';
        }
    }

    public function detail()
    {
        try {
            $movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            // Debug
            error_log("=== Movie Detail ===");
            error_log("Movie ID: " . $movieId);

            $movie = $this->movieModel->getMovieById($movieId);
            if (!$movie) {
                $_SESSION['error'] = 'Không tìm thấy phim';
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            // Lấy danh sách suất chiếu
            $showTimes = $this->showTimeModel->getShowTimesByMovieId($movieId);
            error_log("Số lượng suất chiếu: " . count($showTimes));

            // Debug mỗi suất chiếu
            foreach ($showTimes as $index => $showTime) {
                error_log("Suất chiếu {$index}:");
                error_log("ID: " . $showTime['id']);
                error_log("Thời gian: " . $showTime['startTime']);
                error_log("Phòng: " . $showTime['room']);

                $bookingStatus = $this->showTimeModel->canBookShowTime($showTime['id']);
                $showTimes[$index]['can_book'] = $bookingStatus['can_book'];
                $showTimes[$index]['message'] = $bookingStatus['message'];
            }

            require 'views/movies/detail.php';
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }

    public function uploadImage($file)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . basename($file['name']);
            $targetPath = $this->uploadDir . $fileName;

            // Debug
            error_log("Uploading file to: " . $targetPath);

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return $fileName;
            }

            error_log("Failed to move uploaded file");
        }
        return null;
    }

    private function downloadImage($url)
    {
        // Tạo tên file mới
        $imageFileType = pathinfo($url, PATHINFO_EXTENSION);
        if (!$imageFileType) {
            $imageFileType = 'jpg'; // Mặc định là jpg nếu không xác định được
        }
        $newFileName = uniqid() . '.' . $imageFileType;
        $target_file = $this->uploadDir . $newFileName;

        // Tải ảnh về
        $image = file_get_contents($url);
        if ($image === false) {
            return ["success" => false, "message" => "Không thể tải ảnh từ URL."];
        }

        // Lưu ảnh
        if (file_put_contents($target_file, $image)) {
            return ["success" => true, "path" => $this->uploadPath . $newFileName];
        } else {
            return ["success" => false, "message" => "Không thể lưu ảnh."];
        }
    }

    public function addMovie()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $duration = $_POST['duration'];
            $trailer = $_POST['trailer'] ?? '';

            $imageUrl = null;
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                // Upload file từ máy tính
                $uploadResult = $this->uploadImage($_FILES["image"]);
                if ($uploadResult) {
                    $imageUrl = $uploadResult;
                } else {
                    $_SESSION['error'] = 'Có lỗi khi upload file';
                    header('Location: ' . BASE_URL . 'admin/movies/add');
                    exit;
                }
            } elseif (isset($_POST['imageUrl']) && !empty($_POST['imageUrl'])) {
                // Tải ảnh từ URL
                $downloadResult = $this->downloadImage($_POST['imageUrl']);
                if ($downloadResult["success"]) {
                    $imageUrl = $downloadResult["path"];
                } else {
                    $_SESSION['error'] = $downloadResult["message"];
                    header('Location: ' . BASE_URL . 'admin/movies/add');
                    exit;
                }
            }

            if ($this->movieModel->createMovie($title, $description, $duration, $imageUrl, $trailer)) {
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
        $movie = $this->movieModel->getMovieById($id);
        if (!$movie) {
            $_SESSION['error'] = 'Không tìm thấy phim';
            header('Location: ' . BASE_URL . 'admin/movies');
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $duration = $_POST['duration'];
            $trailer = $_POST['trailer'] ?? '';
            $imageUrl = $movie['imageUrl']; // Giữ nguyên ảnh cũ nếu không upload ảnh mới

            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                // Upload file từ máy tính
                $uploadResult = $this->uploadImage($_FILES["image"]);
                if ($uploadResult) {
                    // Xóa ảnh cũ nếu tồn tại
                    $oldImagePath = __DIR__ . '/../' . $movie['imageUrl'];
                    if ($movie['imageUrl'] && file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $imageUrl = $uploadResult;
                } else {
                    $_SESSION['error'] = 'Có lỗi khi upload file';
                    header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                    exit;
                }
            } elseif (isset($_POST['imageUrl']) && !empty($_POST['imageUrl']) && $_POST['imageUrl'] !== $movie['imageUrl']) {
                // Tải ảnh từ URL mới
                $downloadResult = $this->downloadImage($_POST['imageUrl']);
                if ($downloadResult["success"]) {
                    // Xóa ảnh cũ nếu tồn tại
                    $oldImagePath = __DIR__ . '/../' . $movie['imageUrl'];
                    if ($movie['imageUrl'] && file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $imageUrl = $downloadResult["path"];
                } else {
                    $_SESSION['error'] = $downloadResult["message"];
                    header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
                    exit;
                }
            }

            if ($this->movieModel->updateMovie($id, $title, $description, $duration, $imageUrl, $trailer)) {
                $_SESSION['success'] = 'Cập nhật phim thành công';
                header('Location: ' . BASE_URL . 'admin/movies');
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật phim';
                header('Location: ' . BASE_URL . 'admin/movies/edit?id=' . $id);
            }
            exit;
        }

        require_once "views/admin/movies/edit.php";
    }
}
