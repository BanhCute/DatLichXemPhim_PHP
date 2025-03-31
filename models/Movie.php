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

    public function getAllMovies($page = 1, $perPage = 8)
    {
        try {
            $offset = ($page - 1) * $perPage;

            // Sửa lại câu truy vấn, không cần GROUP BY vì không có JOIN
            $sql = "SELECT * FROM movie 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Debug
            error_log("getAllMovies - Page: $page, Offset: $offset");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Số phim lấy được: " . count($result));

            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi lấy danh sách phim: " . $e->getMessage());
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

    public function updateMovie($id, $title, $description, $duration, $imageUrl)
    {
        try {
            // Debug log
            error_log("Updating movie with data:");
            error_log("ID: " . $id);
            error_log("Title: " . $title);
            error_log("Description: " . $description);
            error_log("Duration: " . $duration);
            error_log("ImageUrl: " . $imageUrl);

            $query = "UPDATE Movie SET title = ?, description = ?, duration = ?, imageUrl = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$title, $description, $duration, $imageUrl, $id]);

            if ($result) {
                error_log("Movie updated successfully");
                return true;
            } else {
                error_log("Failed to update movie. Error: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
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

    public function searchMovies($keyword, $page = 1, $perPage = 8)
    {
        try {
            $offset = ($page - 1) * $perPage;
            $keyword = "%$keyword%";

            // Sửa lại câu truy vấn tìm kiếm
            $sql = "SELECT * FROM movie 
                    WHERE title LIKE :keyword 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi tìm kiếm phim: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalMovies($keyword = '')
    {
        if (!empty($keyword)) {
            $sql = "SELECT COUNT(*) FROM movie WHERE title LIKE :keyword OR description LIKE :keyword";
            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$keyword}%";
            $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
        } else {
            $sql = "SELECT COUNT(*) FROM movie";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
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

    public function getLatestMovies($limit = 6)
    {
        $sql = "SELECT * FROM movie ORDER BY id DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    WHERE mc.movie_id = :movieId";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':movieId', $movieId, PDO::PARAM_INT);
            $stmt->execute();

            // Debug log
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Categories for movie $movieId: " . count($result));

            return $result;
        } catch (PDOException $e) {
            error_log("Error in getCategoriesByMovieId: " . $e->getMessage());
            return [];
        }
    }
}
