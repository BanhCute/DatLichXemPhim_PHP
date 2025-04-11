<?php
require_once "config/database.php";

class Movie
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllMovies($page = 1, $itemsPerPage = 8)
    {
        try {
            // Debug
            error_log("=== Getting all movies ===");
            error_log("Page: $page");

            $offset = ($page - 1) * $itemsPerPage;

            // Đơn giản hóa câu truy vấn, chỉ lấy từ bảng movie
            $sql = "SELECT * FROM movie 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            // Debug query
            error_log("SQL Query: " . $sql);

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug results
            error_log("Found " . count($movies) . " movies");
            foreach ($movies as $movie) {
                error_log("Movie ID: {$movie['id']}, Title: {$movie['title']}");
            }

            return $movies;
        } catch (PDOException $e) {
            error_log("Error in getAllMovies: " . $e->getMessage());
            return [];
        }
    }

    public function getMovieById($id)
    {
        // Debug
        error_log("Getting movie with ID: " . $id);

        $query = "SELECT * FROM Movie WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Movie data: " . print_r($result, true));

        return $result;
    }

    public function createMovie($title, $description, $duration, $imageUrl = null)
    {
        try {
            $sql = "INSERT INTO movie (title, description, duration, imageUrl) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$title, $description, $duration, $imageUrl]);

            // Trả về ID phim vừa tạo
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Lỗi thêm phim: " . $e->getMessage());
            return false;
        }
    }

    public function updateMovie($id, $title, $description, $duration, $imageUrl, $trailer)
    {
        try {
            $sql = "UPDATE movie SET 
                    title = :title, 
                    description = :description,
                    duration = :duration,
                    imageUrl = :imageUrl,
                    trailer = :trailer
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':duration', $duration);
            $stmt->bindParam(':imageUrl', $imageUrl);
            $stmt->bindParam(':trailer', $trailer);

            // Thêm debug
            error_log("Updating movie with data: " . print_r([
                'id' => $id,
                'title' => $title,
                'description' => $description,
                'duration' => $duration,
                'imageUrl' => $imageUrl,
                'trailer' => $trailer
            ], true));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating movie: " . $e->getMessage());
            return false;
        }
    }

    public function hasBookings($movieId)
    {
        try {
            // Thêm debug log
            error_log("Kiểm tra đặt vé cho phim ID: " . $movieId);

            // Kiểm tra xem phim có suất chiếu nào không
            $sql1 = "SELECT COUNT(*) FROM showtime WHERE movie_id = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$movieId]);
            $showtimeCount = $stmt1->fetchColumn();

            error_log("Số suất chiếu của phim: " . $showtimeCount);

            if ($showtimeCount == 0) {
                // Không có suất chiếu nào, nên không thể có đặt vé
                return false;
            }

            // Kiểm tra có đặt vé nào cho các suất chiếu của phim không
            $sql2 = "SELECT COUNT(*) FROM booking b 
                    JOIN showtime s ON b.showtime_id = s.id 
                    WHERE s.movie_id = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$movieId]);
            $bookingCount = $stmt2->fetchColumn();

            error_log("Số đặt vé của phim: " . $bookingCount);

            return $bookingCount > 0;
        } catch (PDOException $e) {
            error_log("Lỗi kiểm tra booking: " . $e->getMessage());
            error_log("SQL query lỗi: " . $sql2 ?? "N/A");
            return false; // Thay đổi thành false để cho phép xóa nếu có lỗi
        }
    }

    public function deleteShowTimes($movieId)
    {
        try {
            // Kiểm tra xem có suất chiếu nào cho phim này không
            $checkSql = "SELECT COUNT(*) FROM showtime WHERE movie_id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$movieId]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // Kiểm tra xem có booking nào cho các suất chiếu của phim
                $checkBookingSql = "SELECT COUNT(*) FROM booking b 
                                    JOIN showtime s ON b.showtime_id = s.id 
                                    WHERE s.movie_id = ?";
                $checkBookingStmt = $this->db->prepare($checkBookingSql);
                $checkBookingStmt->execute([$movieId]);
                $bookingCount = $checkBookingStmt->fetchColumn();

                if ($bookingCount > 0) {
                    // Có booking, không thể xóa
                    error_log("Không thể xóa suất chiếu vì có " . $bookingCount . " đặt vé");
                    return false;
                }

                // Không có booking, xóa các suất chiếu
                $sql = "DELETE FROM showtime WHERE movie_id = ?";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([$movieId]);
                error_log("Đã xóa " . $count . " suất chiếu");
                return $result;
            }

            return true; // Không có suất chiếu nào cần xóa
        } catch (PDOException $e) {
            error_log("Lỗi xóa suất chiếu: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMovie($id)
    {
        try {
            // Xóa phim
            $sql = "DELETE FROM movie WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Lỗi xóa phim: " . $e->getMessage());
            return false;
        }
    }

    public function searchMovies($keyword = '', $categoryId = '', $page = 1)
    {
        try {
            $itemsPerPage = 8;
            $offset = ($page - 1) * $itemsPerPage;

            // Debug
            error_log("=== Searching movies ===");
            error_log("Keyword: $keyword, CategoryId: $categoryId, Page: $page");

            // Base query với DISTINCT và GROUP BY
            $sql = "SELECT DISTINCT m.* FROM movie m";
            $params = [];
            $where = [];

            // Join với bảng categories nếu cần
            if (!empty($categoryId)) {
                $sql .= " LEFT JOIN movie_categories mc ON m.id = mc.movie_id";
                $where[] = "mc.category_id = :categoryId";
                $params[':categoryId'] = $categoryId;
            }

            // Thêm điều kiện tìm kiếm
            if (!empty($keyword)) {
                $where[] = "m.title LIKE :keyword";
                $params[':keyword'] = "%$keyword%";
            }

            // Thêm WHERE nếu có điều kiện
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            // Group by để loại bỏ trùng lặp
            $sql .= " GROUP BY m.id";

            // Order và phân trang
            $sql .= " ORDER BY m.id DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $itemsPerPage;
            $params[':offset'] = $offset;

            // Debug query
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                if ($key == ':limit' || $key == ':offset' || $key == ':categoryId') {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug results
            error_log("Found " . count($movies) . " movies");
            foreach ($movies as $movie) {
                error_log("Movie ID: {$movie['id']}, Title: {$movie['title']}");
            }

            return $movies;
        } catch (PDOException $e) {
            error_log("Error in searchMovies: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số phim theo điều kiện tìm kiếm
     */
    public function getTotalMovies($keyword = '', $categoryId = '')
    {
        try {
            // Debug
            error_log("=== Getting total movies ===");
            error_log("Keyword: $keyword, CategoryId: $categoryId");

            // Base query
            $sql = "SELECT COUNT(DISTINCT m.id) FROM movie m";
            $params = [];
            $where = [];

            // Join với bảng categories nếu cần
            if (!empty($categoryId)) {
                $sql .= " LEFT JOIN movie_categories mc ON m.id = mc.movie_id";
                $where[] = "mc.category_id = :categoryId";
                $params[':categoryId'] = $categoryId;
            }

            // Thêm điều kiện tìm kiếm
            if (!empty($keyword)) {
                $where[] = "m.title LIKE :keyword";
                $params[':keyword'] = "%$keyword%";
            }

            // Thêm WHERE nếu có điều kiện
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            // Debug query
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                if ($key == ':categoryId') {
                    $stmt->bindValue($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $total = $stmt->fetchColumn();

            // Debug result
            error_log("Total movies found: " . $total);

            return $total;
        } catch (PDOException $e) {
            error_log("Error in getTotalMovies: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalMoviesSearch($search = '', $categoryId = '')
    {
        try {
            $params = [];
            $sql = "SELECT COUNT(DISTINCT m.id) as total FROM movie m ";

            if (!empty($categoryId)) {
                $sql .= "INNER JOIN movie_categories mc ON m.id = mc.movie_id ";
                $sql .= "WHERE mc.category_id = :categoryId ";
                $params[':categoryId'] = $categoryId;
            }

            if (!empty($search)) {
                $sql .= !empty($categoryId) ? "AND " : "WHERE ";
                $sql .= "m.title LIKE :keyword ";
                $params[':keyword'] = "%$search%";
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error in getTotalMoviesSearch: " . $e->getMessage());
            return 0;
        }
    }

    public function getMoviesByCategory($categoryId, $page = 1, $perPage = 8)
    {
        try {
            $offset = ($page - 1) * $perPage;

            // Sửa lại câu SQL: thay "movies m" thành "movie m" vì tên bảng là "movie"
            $sql = "SELECT DISTINCT m.* 
                    FROM movie m 
                    INNER JOIN movie_categories mc ON m.id = mc.movie_id 
                    WHERE mc.category_id = :categoryId 
                    ORDER BY m.id DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Thêm debug log
            error_log("getMoviesByCategory - CategoryID: $categoryId, Page: $page, PerPage: $perPage");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Số phim tìm được: " . count($result));

            return $result;
        } catch (PDOException $e) {
            error_log("Error in getMoviesByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function getMovieCategories($movieId)
    {
        $sql = "SELECT c.* 
                FROM categories c 
                JOIN movie_categories mc ON c.id = mc.category_id 
                WHERE mc.movie_id = :movieId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNowShowingMovies($page = 1, $perPage = 8)
    {
        $start = ($page - 1) * $perPage;
        $sql = "SELECT * FROM movie 
                WHERE status = 'now_showing' 
                AND CURRENT_DATE BETWEEN release_date AND end_date 
                ORDER BY release_date DESC 
                LIMIT :start, :perPage";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComingSoonMovies($page = 1, $perPage = 8)
    {
        $start = ($page - 1) * $perPage;
        $sql = "SELECT * FROM movie 
                WHERE status = 'coming_soon' 
                AND release_date > CURRENT_DATE 
                ORDER BY release_date ASC 
                LIMIT :start, :perPage";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalMoviesByCategory($categoryId)
    {
        $sql = "SELECT COUNT(DISTINCT m.id) 
                FROM movie m 
                JOIN movie_categories mc ON m.id = mc.movie_id 
                WHERE mc.category_id = :categoryId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getLatestMovies($limit = 8)
    {
        try {
            $sql = "SELECT * FROM movies ORDER BY id DESC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting latest movies: " . $e->getMessage());
            return [];
        }
    }

    public function getMoviesForAdmin()
    {
        $sql = "SELECT * FROM movie ORDER BY id DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi: " . $e->getMessage());
            return [];
        }
    }

    public function addMovieCategory($movieId, $categoryId)
    {
        try {
            // Kiểm tra dữ liệu đầu vào
            if (!$movieId || !$categoryId) {
                return false;
            }

            $sql = "INSERT INTO movie_categories (movie_id, category_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$movieId, $categoryId]);
        } catch (PDOException $e) {
            error_log("Lỗi thêm thể loại cho phim: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMovieCategories($movieId)
    {
        try {
            // Thêm debug log
            error_log("Đang xóa thể loại cho phim ID: " . $movieId);

            $sql = "DELETE FROM movie_categories WHERE movie_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$movieId]);

            error_log("Kết quả xóa thể loại: " . ($result ? "Thành công" : "Thất bại"));
            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi xóa thể loại của phim: " . $e->getMessage());
            return false;
        }
    }

    public function countAllMovies()
    {
        try {
            // Đơn giản hóa câu truy vấn đếm
            $sql = "SELECT COUNT(*) FROM movie";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi đếm phim: " . $e->getMessage());
            return 0;
        }
    }

    public function countMoviesByKeyword($keyword)
    {
        try {
            $keyword = "%$keyword%";
            $sql = "SELECT COUNT(DISTINCT m.id) FROM movie m WHERE m.title LIKE :keyword";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi đếm phim theo từ khóa: " . $e->getMessage());
            return 0;
        }
    }

    public function countMoviesByCategory($categoryId)
    {
        try {
            // Sửa lại câu SQL: thay "movies m" thành "movie m"
            $sql = "SELECT COUNT(DISTINCT m.id) 
                    FROM movie m 
                    INNER JOIN movie_categories mc ON m.id = mc.movie_id 
                    WHERE mc.category_id = :categoryId";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            // Thêm debug log
            $count = $stmt->fetchColumn();
            error_log("countMoviesByCategory - CategoryID: $categoryId, Count: $count");

            return $count;
        } catch (PDOException $e) {
            error_log("Error in countMoviesByCategory: " . $e->getMessage());
            return 0;
        }
    }

    public function countMoviesByCategoryAndKeyword($categoryId, $keyword)
    {
        try {
            $keyword = "%$keyword%";
            $sql = "SELECT COUNT(DISTINCT m.id) 
                    FROM movie m 
                    JOIN movie_categories mc ON m.id = mc.movie_id 
                    WHERE mc.category_id = :categoryId 
                    AND m.title LIKE :keyword";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi đếm phim theo category và keyword: " . $e->getMessage());
            return 0;
        }
    }

    public function getMoviesByCategoryAndKeyword($categoryId, $keyword, $page = 1, $perPage = 8)
    {
        try {
            $offset = ($page - 1) * $perPage;
            $keyword = "%$keyword%";

            $sql = "SELECT DISTINCT m.* 
                    FROM movie m 
                    JOIN movie_categories mc ON m.id = mc.movie_id 
                    WHERE mc.category_id = :categoryId 
                    AND m.title LIKE :keyword 
                    ORDER BY m.id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Debug
            error_log("SQL: $sql");
            error_log("CategoryId: $categoryId, Keyword: $keyword, Page: $page, PerPage: $perPage");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi tìm kiếm phim theo category và keyword: " . $e->getMessage());
            return [];
        }
    }

    // Thêm phương thức để lấy tất cả phim không phân trang (cho mục đích debug)
    public function getAllMoviesWithoutPaging()
    {
        try {
            $sql = "SELECT * FROM movie ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi lấy tất cả phim không phân trang: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoriesByMovieId($movieId)
    {
        try {
            $sql = "SELECT c.* 
                    FROM categories c
                    INNER JOIN movie_categories mc ON c.id = mc.category_id
                    WHERE mc.movie_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$movieId]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Found categories for movie $movieId: " . json_encode($categories));

            return $categories;
        } catch (PDOException $e) {
            error_log("Error getting categories for movie $movieId: " . $e->getMessage());
            return [];
        }
    }

    public function getMoviesWithFilter($keyword = '', $categoryId = '', $limit = 9, $offset = 0)
    {
        try {
            // Debug parameters
            error_log("Search params - Keyword: $keyword, CategoryId: $categoryId");

            // Base query
            $sql = "SELECT DISTINCT m.* FROM Movie m";

            // Thêm JOIN với MovieCategory nếu có categoryId
            if (!empty($categoryId)) {
                $sql .= " INNER JOIN MovieCategory mc ON m.id = mc.movieId";
            }

            // WHERE clause
            $sql .= " WHERE 1=1";
            $params = [];

            // Thêm điều kiện tìm kiếm
            if (!empty($keyword)) {
                $sql .= " AND (m.title LIKE :keyword OR m.description LIKE :keyword)";
                $params[':keyword'] = "%$keyword%";
            }

            // Thêm điều kiện thể loại
            if (!empty($categoryId)) {
                $sql .= " AND mc.categoryId = :categoryId";
                $params[':categoryId'] = $categoryId;
            }

            // Thêm ORDER BY và LIMIT
            $sql .= " ORDER BY m.id DESC LIMIT :limit OFFSET :offset";

            // Debug SQL
            error_log("SQL Query: " . $sql);
            error_log("Params: " . json_encode($params));

            $stmt = $this->db->prepare($sql);

            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key == ':categoryId') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug results
            error_log("Found " . count($movies) . " movies");

            // Lấy categories cho mỗi phim
            foreach ($movies as &$movie) {
                $movie['categories'] = $this->getCategoriesByMovieId($movie['id']);
            }

            return $movies;
        } catch (PDOException $e) {
            error_log("Error in getMoviesWithFilter: " . $e->getMessage());
            return [];
        }
    }

    public function countMoviesWithFilter($keyword = '', $categoryId = '')
    {
        try {
            // Base query
            $sql = "SELECT COUNT(DISTINCT m.id) as total FROM Movie m";

            // Thêm JOIN với MovieCategory nếu có categoryId
            if (!empty($categoryId)) {
                $sql .= " INNER JOIN MovieCategory mc ON m.id = mc.movieId";
            }

            // WHERE clause
            $sql .= " WHERE 1=1";
            $params = [];

            // Thêm điều kiện tìm kiếm
            if (!empty($keyword)) {
                $sql .= " AND (m.title LIKE :keyword OR m.description LIKE :keyword)";
                $params[':keyword'] = "%$keyword%";
            }

            // Thêm điều kiện thể loại
            if (!empty($categoryId)) {
                $sql .= " AND mc.categoryId = :categoryId";
                $params[':categoryId'] = $categoryId;
            }

            // Debug SQL
            error_log("Count SQL: " . $sql);
            error_log("Count Params: " . json_encode($params));

            $stmt = $this->db->prepare($sql);

            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key == ':categoryId') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug result
            error_log("Total count: " . $result['total']);

            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error in countMoviesWithFilter: " . $e->getMessage());
            return 0;
        }
    }
    public function getPopularMovies()
    {
        try {
            $sql = "SELECT m.title, 
                           COUNT(b.id) as ticket_count, 
                           SUM(b.totalAmount) as revenue 
                    FROM movie m 
                    LEFT JOIN showtime st ON m.id = st.movie_id 
                    LEFT JOIN booking b ON st.id = b.showtime_id 
                    WHERE b.paymentStatus = 'completed'
                    GROUP BY m.id, m.title 
                    ORDER BY ticket_count DESC 
                    LIMIT 5";

            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug log
            error_log("Popular movies data: " . json_encode($result));

            return $result;
        } catch (PDOException $e) {
            error_log("Error in getPopularMovies: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $data)
    {
        try {
            $sql = "UPDATE movie SET 
                    title = :title, 
                    description = :description, 
                    duration = :duration,
                    trailer = :trailer,
                    imageUrl = :imageUrl 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':duration', $data['duration']);
            $stmt->bindParam(':trailer', $data['trailer']);
            $stmt->bindParam(':imageUrl', $data['imageUrl']);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Lỗi cập nhật phim: " . $e->getMessage());
        }
    }

    public function create($data)
    {
        try {
            $sql = "INSERT INTO movie (title, description, duration, trailer, imageUrl) 
                    VALUES (:title, :description, :duration, :trailer, :imageUrl)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':duration', $data['duration']);
            $stmt->bindParam(':trailer', $data['trailer']);
            $stmt->bindParam(':imageUrl', $data['imageUrl']);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Lỗi thêm phim: " . $e->getMessage());
        }
    }

    public function getMovies($keyword = '', $categoryId = null, $page = 1, $itemsPerPage = 8)
    {
        try {
            // Debug log
            error_log("=== Getting movies ===");
            error_log("Page: $page, Keyword: $keyword, CategoryId: $categoryId");

            $params = [];

            // Base query - lấy tất cả phim không trùng lặp
            $sql = "SELECT DISTINCT m.* FROM movie m";

            $whereConditions = [];

            // Thêm điều kiện tìm kiếm nếu có
            if (!empty($keyword)) {
                $whereConditions[] = "m.title LIKE :keyword";
                $params[':keyword'] = "%$keyword%";
            }

            // Thêm điều kiện thể loại nếu có
            if (!empty($categoryId)) {
                $sql .= " INNER JOIN movie_categories mc ON m.id = mc.movie_id";
                $whereConditions[] = "mc.category_id = :categoryId";
                $params[':categoryId'] = $categoryId;
            }

            // Thêm WHERE nếu có điều kiện
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }

            // Thêm ORDER BY và LIMIT
            $sql .= " ORDER BY m.id DESC";

            // Debug query
            error_log("SQL before pagination: " . $sql);
            error_log("Params: " . print_r($params, true));

            // Thực hiện truy vấn để đếm tổng số phim
            $countSql = str_replace("SELECT DISTINCT m.*", "SELECT COUNT(DISTINCT m.id)", $sql);
            $countStmt = $this->db->prepare($countSql);
            foreach ($params as $key => &$val) {
                $type = (in_array($key, [':categoryId'])) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $countStmt->bindValue($key, $val, $type);
            }
            $countStmt->execute();
            $totalCount = $countStmt->fetchColumn();
            error_log("Total movies found: " . $totalCount);

            // Thêm LIMIT và OFFSET cho phân trang
            $offset = ($page - 1) * $itemsPerPage;
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $itemsPerPage;
            $params[':offset'] = $offset;

            // Debug final query
            error_log("Final SQL: " . $sql);

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                $type = (in_array($key, [':limit', ':offset', ':categoryId'])) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $val, $type);
            }

            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Found " . count($movies) . " movies for current page");
            return $movies;
        } catch (PDOException $e) {
            error_log("Error in getMovies: " . $e->getMessage());
            return [];
        }
    }
} // Make sure this is the last closing brace of the class
