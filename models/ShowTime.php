    <?php
    require_once "config/database.php";

    class ShowTime
    {
        private $conn;
        private $table_name = "ShowTime";

        public function __construct()
        {
            $database = new Database();
            $this->conn = $database->getConnection();
        }

        public function getAllShowTimes()
        {
            $query = "SELECT st.*, m.title as movie_title 
                 FROM " . $this->table_name . " st 
                 JOIN Movie m ON st.movieId = m.id 
                 ORDER BY st.startTime";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getShowTimesByMovieId($movieId)
        {
            try {
                // Debug log
                error_log("Getting showtimes for movie ID: " . $movieId);

                $sql = "SELECT DISTINCT st.* 
                        FROM showtime st 
                        WHERE st.movieId = ? 
                        ORDER BY st.startTime ASC";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$movieId]);
                $showtimes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Debug log kết quả
                error_log("Found showtimes: " . print_r($showtimes, true));

                return $showtimes;
            } catch (PDOException $e) {
                error_log("Error getting showtimes: " . $e->getMessage());
                return [];
            }
        }

        public function getShowTimeById($id)
        {
            error_log("Getting showtime with ID: " . $id);

            $query = "SELECT st.*, m.title as movie_title, m.duration 
                 FROM " . $this->table_name . " st 
                 JOIN Movie m ON st.movieId = m.id 
                 WHERE st.id = ?";

            error_log("Query: " . $query);

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Showtime data from DB: " . print_r($result, true));

            return $result;
        }

        public function canBookShowTime($showTimeId)
        {
            try {
                $showtime = $this->getShowTimeById($showTimeId);
                if (!$showtime) {
                    return [
                        'can_book' => false,
                        'message' => 'Không tìm thấy suất chiếu'
                    ];
                }

                // Lấy thời gian hiện tại
                $now = new DateTime();
                $startTime = new DateTime($showtime['startTime']);

                // Debug
                error_log("Checking showtime ID: " . $showTimeId);
                error_log("Start time: " . $startTime->format('Y-m-d H:i:s'));
                error_log("Current time: " . $now->format('Y-m-d H:i:s'));

                // Kiểm tra xem suất chiếu đã qua chưa
                if ($startTime <= $now) {
                    return [
                        'can_book' => false,
                        'message' => 'Suất chiếu này đã diễn ra vui lòng chọn suất chiếu khác'
                    ];
                }

                // Nếu mọi điều kiện đều ok
                return [
                    'can_book' => true,
                    'message' => 'Có thể đặt vé'
                ];
            } catch (Exception $e) {
                error_log("Error in canBookShowTime: " . $e->getMessage());
                return [
                    'can_book' => false,
                    'message' => 'Có lỗi xảy ra'
                ];
            }
        }

        public function createShowTime($movieId, $startTime, $endTime, $room, $price)
        {
            // Kiểm tra xem phòng có trống trong khoảng thời gian này không
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                 WHERE room = ? AND 
                 ((startTime BETWEEN ? AND ?) OR 
                  (endTime BETWEEN ? AND ?) OR 
                  (startTime <= ? AND endTime >= ?))";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$room, $startTime, $endTime, $startTime, $endTime, $startTime, $endTime]);
            if ($stmt->fetchColumn() > 0) {
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . " 
                 (movieId, startTime, endTime, room, price) 
                 VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$movieId, $startTime, $endTime, $room, $price]);
        }

        public function updateShowTime($id, $movieId, $startTime, $endTime, $room, $price)
        {
            // Kiểm tra xem phòng có trống trong khoảng thời gian này không (trừ suất chiếu hiện tại)
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                 WHERE room = ? AND id != ? AND
                 ((startTime BETWEEN ? AND ?) OR 
                  (endTime BETWEEN ? AND ?) OR 
                  (startTime <= ? AND endTime >= ?))";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$room, $id, $startTime, $endTime, $startTime, $endTime, $startTime, $endTime]);
            if ($stmt->fetchColumn() > 0) {
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                 SET movieId = ?, startTime = ?, endTime = ?, room = ?, price = ? 
                 WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$movieId, $startTime, $endTime, $room, $price, $id]);
        }

        public function deleteShowTime($id)
        {
            // Kiểm tra xem có vé nào đã được đặt cho suất chiếu này không
            $query = "SELECT COUNT(*) FROM Booking WHERE showTimeId = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        }

        public function getTotalShowtimes()
        {
            try {
                $sql = "SELECT COUNT(*) FROM " . $this->table_name;
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $result = $stmt->fetchColumn();
                error_log("Total showtimes count: " . $result);

                return (int)$result;
            } catch (PDOException $e) {
                error_log("Error in getTotalShowtimes: " . $e->getMessage());
                return 0;
            }
        }

        public function checkShowTimeConflict($startTime, $endTime, $room, $id = null)
        {
            $sql = "SELECT COUNT(*) FROM showtime 
                WHERE room = ? 
                AND ((startTime BETWEEN ? AND ?) 
                OR (endTime BETWEEN ? AND ?))";

            if ($id !== null) {
                $sql .= " AND id != ?";
            }

            $stmt = $this->conn->prepare($sql);

            if ($id !== null) {
                $stmt->execute([$room, $startTime, $endTime, $startTime, $endTime, $id]);
            } else {
                $stmt->execute([$room, $startTime, $endTime, $startTime, $endTime]);
            }

            return $stmt->fetchColumn() > 0;
        }

        public function addShowTime($movieId, $startTime, $endTime, $room, $price)
        {
            try {
                // Debug log
                error_log("=== Kiểm tra thêm suất chiếu mới ===");
                error_log("MovieId: " . $movieId);
                error_log("Start time: " . $startTime);
                error_log("End time: " . $endTime);
                error_log("Room: " . $room);
                error_log("Price: " . $price);

                // Kiểm tra trùng lịch - đơn giản hóa logic kiểm tra
                $sql = "SELECT COUNT(*) FROM showtime 
                        WHERE room = ? 
                        AND (
                            (startTime <= ? AND endTime >= ?) OR
                            (startTime <= ? AND endTime >= ?) OR
                            (startTime >= ? AND startTime <= ?)
                        )";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $room,
                    $startTime,
                    $startTime,  // Kiểm tra thời gian bắt đầu
                    $endTime,
                    $endTime,      // Kiểm tra thời gian kết thúc
                    $startTime,
                    $endTime     // Kiểm tra thời gian ở giữa
                ]);

                $count = $stmt->fetchColumn();
                error_log("Số suất chiếu trùng: " . $count);

                if ($count > 0) {
                    // Debug: In ra các suất chiếu trùng
                    $debugSql = "SELECT * FROM showtime 
                                WHERE room = ? 
                                AND (
                                    (startTime <= ? AND endTime >= ?) OR
                                    (startTime <= ? AND endTime >= ?) OR
                                    (startTime >= ? AND startTime <= ?)
                                )";
                    $debugStmt = $this->conn->prepare($debugSql);
                    $debugStmt->execute([
                        $room,
                        $startTime,
                        $startTime,
                        $endTime,
                        $endTime,
                        $startTime,
                        $endTime
                    ]);
                    $conflicts = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("Suất chiếu trùng: " . print_r($conflicts, true));
                    return false;
                }

                // Thêm suất chiếu mới
                $sql = "INSERT INTO showtime (movieId, startTime, endTime, room, price) 
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $this->conn->prepare($sql);
                $result = $stmt->execute([$movieId, $startTime, $endTime, $room, $price]);

                error_log("Kết quả thêm: " . ($result ? "Thành công" : "Thất bại"));
                return $result;
            } catch (PDOException $e) {
                error_log("Lỗi trong addShowTime: " . $e->getMessage());
                return false;
            }
        }
    }
