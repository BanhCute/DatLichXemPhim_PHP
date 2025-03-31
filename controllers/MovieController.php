<?php
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

    public function __construct()
    {
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
            $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
            $perPage = 2;

            // Debug
            error_log("Search params - Keyword: $keyword, Category: $categoryId, Page: $page");

            // Lấy danh sách phim theo điều kiện
            if (!empty($keyword) && $categoryId > 0) {
                $totalMovies = $this->movieModel->countMoviesByCategoryAndKeyword($categoryId, $keyword);
                $movies = $this->movieModel->getMoviesByCategoryAndKeyword($categoryId, $keyword, $page, $perPage);
            } elseif (!empty($keyword)) {
                $totalMovies = $this->movieModel->countMoviesByKeyword($keyword);
                $movies = $this->movieModel->searchMovies($keyword, $page, $perPage);
            } elseif ($categoryId > 0) {
                $totalMovies = $this->movieModel->countMoviesByCategory($categoryId);
                $movies = $this->movieModel->getMoviesByCategory($categoryId, $page, $perPage);
            } else {
                $totalMovies = $this->movieModel->countAllMovies();
                $movies = $this->movieModel->getAllMovies($page, $perPage);
            }

            // Debug số lượng phim trước khi xử lý categories
            error_log("Số phim trước khi xử lý categories: " . count($movies));

            // Lấy categories cho mỗi phim - Sửa lại phần này
            if (!empty($movies)) {
                $uniqueMovies = [];
                foreach ($movies as $movie) {
                    if (!isset($uniqueMovies[$movie['id']])) {
                        $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
                        $uniqueMovies[$movie['id']] = $movie;
                    }
                }
                $movies = array_values($uniqueMovies);
            }

            // Debug số lượng phim sau khi xử lý categories
            error_log("Số phim sau khi xử lý categories: " . count($movies));

            // Tính số trang
            $totalPages = ceil($totalMovies / $perPage);
            if ($page > $totalPages) $page = $totalPages;

            // Debug phân trang
            error_log("Tổng số trang: $totalPages, Trang hiện tại: $page");

            // Lấy danh sách categories
            $categories = $this->categoryModel->getAllCategories();

            require_once 'views/movies/index.php';
        } catch (Exception $e) {
            error_log("Lỗi controller: " . $e->getMessage());
            // Xử lý lỗi
        }
    }

    public function detail($id)
    {
        // Debug
        error_log("Loading movie detail for ID: " . $id);

        $movie = $this->movieModel->getMovieById($id);
        if (!$movie) {
            $_SESSION['error'] = 'Không tìm thấy phim này';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        // Lấy thể loại của phim
        $movie['categories'] = $this->categoryModel->getCategoriesByMovieId($id);

        // Lấy suất chiếu
        $showTimes = $this->showTimeModel->getShowTimesByMovieId($id);

        require_once "views/movies/detail.php";
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

            if ($this->movieModel->createMovie($title, $description, $duration, $imageUrl)) {
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

            if ($this->movieModel->updateMovie($id, $title, $description, $duration, $imageUrl)) {
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
