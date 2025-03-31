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
        // Debug
        error_log("Getting showtimes for movie ID: " . $movieId);

        $query = "SELECT st.*, m.title as movie_title 
                 FROM " . $this->table_name . " st 
                 JOIN Movie m ON st.movieId = m.id 
                 WHERE st.movieId = ?
                 ORDER BY st.startTime";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$movieId]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Found " . count($result) . " showtimes");

        return $result;
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

    public function canBookShowTime($id)
    {
        $showTime = $this->getShowTimeById($id);
        if (!$showTime) {
            return [
                'can_book' => false,
                'message' => 'Không tìm thấy suất chiếu'
            ];
        }

        $now = new DateTime();
        $startTime = new DateTime($showTime['startTime']);
        $timeUntilShow = $startTime->getTimestamp() - $now->getTimestamp();
        $minutesUntilShow = floor($timeUntilShow / 60);

        if ($timeUntilShow < 0) {
            return [
                'can_book' => false,
                'message' => 'Suất chiếu đã bắt đầu'
            ];
        }

        if ($minutesUntilShow <= 30) {
            return [
                'can_book' => false,
                'message' => 'Không thể đặt vé trước giờ chiếu ít hơn 30 phút'
            ];
        }

        return [
            'can_book' => true,
            'message' => ''
        ];
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
}
