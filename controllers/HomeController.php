<?php
require_once "models/Movie.php";
require_once "models/ShowTime.php";

class HomeController
{
    private $movieModel;
    private $showTimeModel;
    private $categoryModel;

    public function __construct()
    {
        $this->movieModel = new Movie();
        $this->showTimeModel = new ShowTime();
        $this->categoryModel = new Category();
    }

    public function index()
    {
        // Lấy danh sách phim mới nhất
        $latestMovies = $this->movieModel->getAllMovies(1, 8);

        // Lấy categories cho mỗi phim mới nhất
        if ($latestMovies) {
            foreach ($latestMovies as &$movie) {
                $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
            }
        }

        // Lấy danh sách categories
        $categories = $this->categoryModel->getAllCategories();

        // Lấy phim theo từng category
        $moviesByCategory = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoryMovies = $this->movieModel->getMoviesByCategory($category['id'], 1, 4);

                // Lấy categories cho từng phim trong mỗi thể loại
                if ($categoryMovies) {
                    foreach ($categoryMovies as &$movie) {
                        $movie['categories'] = $this->movieModel->getCategoriesByMovieId($movie['id']);
                    }
                }

                $moviesByCategory[$category['id']] = [
                    'name' => $category['name'],
                    'movies' => $categoryMovies
                ];
            }
        }

        // Debug log
        error_log("Số phim mới nhất: " . count($latestMovies));
        error_log("Số thể loại: " . count($categories));

        require_once 'views/home/index.php';
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
}
