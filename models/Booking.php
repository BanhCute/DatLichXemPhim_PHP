<?php
require_once "config/database.php";

class Booking
{
    private $conn;
    private $table_name = "Booking";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllBookings()
    {
        $query = "SELECT b.*, u.name as user_name, m.title as movie_title, st.startTime, st.room, st.price as total_price,
                    CASE 
                        WHEN b.paymentStatus = 'completed' THEN 'Đã xác nhận'
                        WHEN b.paymentStatus = 'pending' THEN 'Chờ xác nhận'
                        WHEN b.paymentStatus = 'cancelled' THEN 'Đã hủy'
                    END as status
                 FROM " . $this->table_name . " b
                 JOIN User u ON b.userId = u.id
                 JOIN ShowTime st ON b.showTimeId = st.id
                 JOIN Movie m ON st.movieId = m.id
                 ORDER BY b.bookingDate DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByUserId($userId)
    {
        try {
            // Sửa lại tên bảng và cột cho đúng (chú ý chữ hoa/thường)
            $sql = "SELECT 
                    b.id,
                    b.userId,
                    b.showTimeId,
                    b.seats,
                    b.totalAmount,
                    b.paymentStatus,
                    b.createdAt,
                    s.startTime,
                    s.room,
                    s.movieId,
                    m.title as movieTitle
                    FROM Booking b  -- Chữ B hoa
                    INNER JOIN ShowTime s ON b.showTimeId = s.id  -- Chữ S và T hoa
                    INNER JOIN Movie m ON s.movieId = m.id  -- Chữ M hoa
                    WHERE b.userId = :userId";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);

            // Debug trước khi execute
            error_log("Executing query for user ID: " . $userId);

            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug kết quả
            error_log("Found " . count($bookings) . " bookings");
            foreach ($bookings as $booking) {
                error_log(json_encode([
                    'booking_id' => $booking['id'],
                    'showtime_id' => $booking['showTimeId'],
                    'movie_title' => $booking['movieTitle'] ?? 'NULL'
                ]));
            }

            return $bookings;
        } catch (PDOException $e) {
            error_log("Error in getBookingsByUserId: " . $e->getMessage());
            return [];
        }
    }

    public function createBooking($userId, $showTimeId, $seats, $totalAmount, $paymentStatus = 'pending')
    {
        try {
            error_log("Start creating booking with data: " . json_encode([
                'userId' => $userId,
                'showTimeId' => $showTimeId,
                'seats' => $seats,
                'totalAmount' => $totalAmount
            ]));

            // Chuyển mảng seats thành chuỗi, phân cách bằng dấu phẩy
            $seatsString = is_array($seats) ? implode(',', $seats) : $seats;
            error_log("Seats string: " . $seatsString);

            // Kiểm tra suất chiếu tồn tại và còn hiệu lực
            $checkShowTime = "SELECT id, startTime FROM ShowTime 
                            WHERE id = ? AND startTime > NOW()";
            $stmt = $this->conn->prepare($checkShowTime);
            $stmt->execute([$showTimeId]);
            $showTime = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$showTime) {
                error_log("ShowTime $showTimeId not found or expired");
                return false;
            }
            error_log("Found valid showtime: " . json_encode($showTime));

            $this->conn->beginTransaction();
            error_log("Started transaction");

            // Tạo booking mới
            $query = "INSERT INTO " . $this->table_name . " 
                     (userId, showTimeId, seats, totalAmount, paymentStatus, bookingDate) 
                     VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $userId,
                $showTimeId,
                $seatsString,
                $totalAmount,
                $paymentStatus
            ]);

            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Failed to create booking. Error: " . json_encode($error));
                $this->conn->rollBack();
                return false;
            }

            $bookingId = $this->conn->lastInsertId();
            $this->conn->commit();

            error_log("Successfully created booking #$bookingId");
            return $bookingId;
        } catch (Exception $e) {
            error_log("Error creating booking: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function cancelExpiredBookings()
    {
        try {
            // Lấy danh sách booking hết hạn
            $query = "SELECT id FROM " . $this->table_name . "
                     WHERE paymentStatus = 'pending' 
                     AND bookingDate < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $expiredBookings = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($expiredBookings)) {
                error_log("No expired bookings found");
                return true;
            }

            error_log("Found expired bookings: " . implode(', ', $expiredBookings));

            // Hủy các booking hết hạn
            $sql = "UPDATE " . $this->table_name . "
                   SET paymentStatus = 'cancelled' 
                   WHERE id IN (" . implode(',', $expiredBookings) . ")";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();

            if ($result) {
                error_log("Successfully cancelled expired bookings");
            } else {
                error_log("Failed to cancel expired bookings");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error cancelling expired bookings: " . $e->getMessage());
            return false;
        }
    }

    public function cancelBooking($bookingId, $userId = null)
    {
        try {
            $this->conn->beginTransaction();

            // Lấy thông tin booking
            $query = "SELECT b.*, st.startTime 
                     FROM " . $this->table_name . " b
                     JOIN ShowTime st ON b.showTimeId = st.id
                     WHERE b.id = ? AND b.paymentStatus = 'pending'";
            $params = [$bookingId];

            if ($userId !== null) {
                $query .= " AND b.userId = ?";
                $params[] = $userId;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Canceling booking: " . print_r($booking, true));

            if (!$booking) {
                error_log("Booking not found or not in pending status");
                $this->conn->rollBack();
                return false;
            }

            // Kiểm tra thời gian chiếu
            $showTime = new DateTime($booking['startTime']);
            $now = new DateTime();
            if ($showTime < $now) {
                error_log("Cannot cancel booking: Show time has passed");
                $this->conn->rollBack();
                return false;
            }

            // Cập nhật trạng thái booking
            $query = "UPDATE " . $this->table_name . " 
                     SET paymentStatus = 'cancelled' 
                     WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$bookingId]);

            if (!$result) {
                error_log("Failed to update booking status");
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            error_log("Successfully cancelled booking #$bookingId");
            return true;
        } catch (Exception $e) {
            error_log("Error canceling booking: " . $e->getMessage());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function confirmBooking($id)
    {
        $query = "UPDATE " . $this->table_name . " SET paymentStatus = 'completed' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getBookingById($id)
    {
        $query = "SELECT b.*, st.startTime, st.room, m.title as movieTitle, u.email 
                 FROM " . $this->table_name . " b
                 JOIN ShowTime st ON b.showTimeId = st.id
                 JOIN Movie m ON st.movieId = m.id
                 JOIN User u ON b.userId = u.id
                 WHERE b.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePaymentStatus($id, $status)
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET paymentStatus = ? 
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function getUserBookings($userId)
    {
        $query = "SELECT b.*, st.startTime, st.room, m.title as movie_title, 
                         CASE 
                             WHEN b.paymentStatus = 'completed' THEN 'Đã xác nhận'
                             WHEN b.paymentStatus = 'pending' THEN 'Chờ xác nhận'
                             WHEN b.paymentStatus = 'cancelled' THEN 'Đã hủy'
                         END as status 
                  FROM " . $this->table_name . " b
                  JOIN ShowTime st ON b.showTimeId = st.id
                  JOIN Movie m ON st.movieId = m.id
                  WHERE b.userId = ?
                  ORDER BY b.bookingDate DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookedSeats($showTimeId)
    {
        try {
            // Hủy các vé hết hạn trước
            $this->cancelExpiredBookings();

            // Lấy danh sách ghế đã đặt (pending hoặc completed)
            $query = "SELECT b.seats, b.paymentStatus, b.bookingDate 
                     FROM " . $this->table_name . " b
                     WHERE b.showTimeId = ? 
                     AND b.paymentStatus IN ('pending', 'completed')";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$showTimeId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gộp tất cả các ghế đã đặt thành một mảng
            $bookedSeats = [];
            foreach ($results as $booking) {
                $seatArray = explode(',', $booking['seats']);
                foreach ($seatArray as $seat) {
                    $bookedSeats[] = [
                        'seat' => trim($seat),
                        'status' => $booking['paymentStatus']
                    ];
                }
            }

            error_log("Detailed booked seats for showtime $showTimeId: " . print_r($bookedSeats, true));

            // Chuyển đổi sang mảng đơn giản chỉ chứa số ghế
            $simpleBookedSeats = array_map(function ($item) {
                return $item['seat'];
            }, $bookedSeats);

            return array_unique($simpleBookedSeats);
        } catch (Exception $e) {
            error_log("Error in getBookedSeats: " . $e->getMessage());
            return [];
        }
    }

    public function getDetailedBookedSeats($showTimeId)
    {
        try {
            // Hủy các vé hết hạn trước
            $this->cancelExpiredBookings();

            // Lấy danh sách ghế đã đặt với thông tin chi tiết
            $query = "SELECT b.seats, b.paymentStatus, b.bookingDate 
                     FROM " . $this->table_name . " b
                     WHERE b.showTimeId = ? 
                     AND b.paymentStatus IN ('pending', 'completed')";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$showTimeId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gộp tất cả các ghế đã đặt thành một mảng với thông tin chi tiết
            $bookedSeats = [];
            foreach ($results as $booking) {
                $seatArray = explode(',', $booking['seats']);
                foreach ($seatArray as $seat) {
                    $bookedSeats[trim($seat)] = [
                        'status' => $booking['paymentStatus'],
                        'bookingDate' => $booking['bookingDate']
                    ];
                }
            }

            error_log("Detailed booked seats info for showtime $showTimeId: " . print_r($bookedSeats, true));
            return $bookedSeats;
        } catch (Exception $e) {
            error_log("Error in getDetailedBookedSeats: " . $e->getMessage());
            return [];
        }
    }

    public function debugBookingInfo($bookingId)
    {
        try {
            // Kiểm tra từng bước một
            // 1. Kiểm tra booking
            $sql1 = "SELECT * FROM booking WHERE id = :bookingId";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bindValue(':bookingId', $bookingId);
            $stmt1->execute();
            $booking = $stmt1->fetch(PDO::FETCH_ASSOC);
            error_log("Booking data: " . json_encode($booking));

            if ($booking) {
                // 2. Kiểm tra showtime
                $sql2 = "SELECT * FROM showtime WHERE id = :showTimeId";
                $stmt2 = $this->conn->prepare($sql2);
                $stmt2->bindValue(':showTimeId', $booking['showTimeId']);
                $stmt2->execute();
                $showtime = $stmt2->fetch(PDO::FETCH_ASSOC);
                error_log("Showtime data: " . json_encode($showtime));

                if ($showtime) {
                    // 3. Kiểm tra movie
                    $sql3 = "SELECT * FROM movie WHERE id = :movieId";
                    $stmt3 = $this->conn->prepare($sql3);
                    $stmt3->bindValue(':movieId', $showtime['movieId']);
                    $stmt3->execute();
                    $movie = $stmt3->fetch(PDO::FETCH_ASSOC);
                    error_log("Movie data: " . json_encode($movie));
                }
            }
        } catch (PDOException $e) {
            error_log("Error in debugBookingInfo: " . $e->getMessage());
        }
    }
}
