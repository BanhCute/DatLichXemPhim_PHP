<?php
require_once "models/Movie.php";
require_once "models/Category.php";
require_once "models/ShowTime.php";
require_once "config/database.php";

class HomeController
{
    private $movieModel;
    private $categoryModel;
    private $showTimeModel;
    private $db;

    public function __construct()
    {
        try {
            // Khởi tạo kết nối database
            $database = new Database();
            $this->db = $database->getConnection();

            if (!$this->db) {
                throw new Exception("Không thể kết nối database");
            }

            // Debug kết nối thành công
            error_log("Database connected successfully in HomeController");

            // Khởi tạo các model
            $this->movieModel = new Movie();
            $this->categoryModel = new Category();
            $this->showTimeModel = new ShowTime();
        } catch (Exception $e) {
            error_log("Error in HomeController constructor: " . $e->getMessage());
            die("Lỗi kết nối: " . $e->getMessage());
        }
    }

    public function index()
    {
        try {
            // Sửa lại query đơn giản hơn
            $sql = "SELECT * FROM movie ORDER BY createdAt DESC LIMIT 8";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $latestMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug để kiểm tra dữ liệu
            error_log("Raw movies data: " . print_r($latestMovies, true));

            // Lấy categories cho mỗi phim
            if ($latestMovies) {
                foreach ($latestMovies as &$movie) {
                    $sql = "SELECT DISTINCT c.* 
                           FROM categories c 
                           INNER JOIN movie_categories mc ON c.id = mc.category_id 
                           WHERE mc.movie_id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$movie['id']]);
                    $movie['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Debug cho từng phim
                    error_log("Movie {$movie['id']} - {$movie['title']}: " .
                        count($movie['categories']) . " categories");
                }
            }

            // Lấy categories cho menu
            $sql = "SELECT * FROM categories ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug trước khi render view
            error_log("Total movies to display: " . count($latestMovies));

            require_once 'views/home/index.php';
        } catch (Exception $e) {
            error_log("Error in HomeController: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải dữ liệu';
            $latestMovies = [];
            $categories = [];
            require_once 'views/home/index.php';
        }
    }

    private function getMoviesByCategory()
    {
        // Lấy danh sách tất cả thể loại
        $categories = $this->movieModel->getAllCategories();

        $moviesByCategory = [];

        // Lấy phim cho từng thể loại (tối đa 4 phim mỗi thể loại)
        foreach ($categories as $category) {
            $categoryId = $category['id'];
            $movies = $this->movieModel->getMoviesByCategory($categoryId, 1, 4); // Sử dụng getMoviesByCategory

            // Lấy thể loại cho từng phim
            if (!empty($movies)) {
                foreach ($movies as &$movie) {
                    $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
                }
            }

            $moviesByCategory[$categoryId] = [
                'name' => $category['name'],
                'movies' => $movies
            ];
        }

        return $moviesByCategory;
    }

    public function movies()
    {
        // Chuyển hướng đến MovieController
        header('Location: ' . BASE_URL . 'movies');
        exit;
    }

    public function movieDetail()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        $movie = $this->movieModel->getMovieById($id);
        if (!$movie) {
            $_SESSION['error'] = 'Không tìm thấy phim';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        // Lấy danh sách suất chiếu của phim
        $showTimes = $this->showTimeModel->getShowTimesByMovieId($id);
        require_once "views/movies/detail.php";
    }

    public function detail($id)
    {
        try {
            // Lấy thông tin phim
            $movie = $this->movieModel->getMovieById($id);
            if (!$movie) {
                throw new Exception("Không tìm thấy phim");
            }

            // Lấy danh sách suất chiếu của phim
            $showTimes = $this->showTimeModel->getShowTimesByMovieId($id);

            // Debug
            error_log("Movie detail loaded - ID: $id, ShowTimes: " . count($showTimes));

            require_once "views/movies/detail.php";
        } catch (Exception $e) {
            error_log("Error in detail(): " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải thông tin phim';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }
}
