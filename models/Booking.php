<?php
require_once 'config/Database.php';

class Booking
{
    private $db;
    private $table_name = "Booking";

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();

        // Kiểm tra kết nối
        if (!$this->db) {
            throw new Exception("Không thể kết nối database");
        }
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
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingsByUserId($userId)
    {
        try {
            $sql = "SELECT 
                    b.id,
                    b.userId,
                    b.showTimeId,
                    b.seats,
                    b.totalAmount,
                    b.paymentStatus,
                    b.bookingDate,
                    b.createdAt,
                    st.startTime,
                    st.room,
                    m.title as movie_title,
                    CASE 
                        WHEN b.paymentStatus = 'completed' THEN 'Đã thanh toán'
                        WHEN b.paymentStatus = 'pending' THEN 'Chờ thanh toán'
                        WHEN b.paymentStatus = 'cancelled' THEN 'Đã hủy'
                        ELSE 'Không xác định'
                    END as status
                    FROM booking b
                    INNER JOIN showtime st ON b.showTimeId = st.id
                    INNER JOIN movie m ON st.movieId = m.id
                    WHERE b.userId = ?
                    ORDER BY b.createdAt DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bookings as &$booking) {
                $booking['startTime'] = date('H:i d/m/Y', strtotime($booking['startTime']));
                $booking['totalAmount'] = number_format((float)$booking['totalAmount'], 0) . ' VND';
            }

            return $bookings;
        } catch (PDOException $e) {
            error_log("Error in getBookingsByUserId: " . $e->getMessage());
            throw $e;
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
            $stmt = $this->db->prepare($checkShowTime);
            $stmt->execute([$showTimeId]);
            $showTime = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$showTime) {
                error_log("ShowTime $showTimeId not found or expired");
                return false;
            }
            error_log("Found valid showtime: " . json_encode($showTime));

            $this->db->beginTransaction();
            error_log("Started transaction");

            // Tạo booking mới
            $query = "INSERT INTO " . $this->table_name . " 
                     (userId, showTimeId, seats, totalAmount, paymentStatus, bookingDate) 
                     VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($query);
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
                $this->db->rollBack();
                return false;
            }

            $bookingId = $this->db->lastInsertId();
            $this->db->commit();

            error_log("Successfully created booking #$bookingId");
            return $bookingId;
        } catch (Exception $e) {
            error_log("Error creating booking: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
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
            $stmt = $this->db->prepare($query);
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

            $stmt = $this->db->prepare($sql);
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
            $this->db->beginTransaction();

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

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Canceling booking: " . print_r($booking, true));

            if (!$booking) {
                error_log("Booking not found or not in pending status");
                $this->db->rollBack();
                return false;
            }

            // Kiểm tra thời gian chiếu
            $showTime = new DateTime($booking['startTime']);
            $now = new DateTime();
            if ($showTime < $now) {
                error_log("Cannot cancel booking: Show time has passed");
                $this->db->rollBack();
                return false;
            }

            // Cập nhật trạng thái booking
            $query = "UPDATE " . $this->table_name . " 
                     SET paymentStatus = 'cancelled' 
                     WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$bookingId]);

            if (!$result) {
                error_log("Failed to update booking status");
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            error_log("Successfully cancelled booking #$bookingId");
            return true;
        } catch (Exception $e) {
            error_log("Error canceling booking: " . $e->getMessage());
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function confirmBooking($id)
    {
        $query = "UPDATE " . $this->table_name . " SET paymentStatus = 'completed' WHERE id = ?";
        $stmt = $this->db->prepare($query);
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

        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePaymentStatus($id, $status)
    {
        $query = "UPDATE " . $this->table_name . " 
                 SET paymentStatus = ? 
                 WHERE id = ?";

        $stmt = $this->db->prepare($query);
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

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookedSeats($showTimeId)
    {
        return $this->getBookedSeatsByShowTime($showTimeId);
    }

    public function getBookedSeatsByShowTime($showTimeId)
    {
        try {
            // Lấy tất cả ghế đã đặt cho suất chiếu này
            $sql = "SELECT seats FROM Booking 
                    WHERE showTimeId = ? 
                    AND paymentStatus != 'cancelled'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$showTimeId]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tạo mảng chứa tất cả ghế đã đặt
            $bookedSeats = [];
            foreach ($bookings as $booking) {
                // Ghế có thể được lưu dưới dạng chuỗi phân cách bởi dấu phẩy
                $seats = explode(',', $booking['seats']);
                $bookedSeats = array_merge($bookedSeats, $seats);
            }

            // Debug log
            error_log("Booked seats for showtime $showTimeId: " . json_encode($bookedSeats));

            return array_unique($bookedSeats); // Loại bỏ các ghế trùng lặp
        } catch (PDOException $e) {
            error_log("Error in getBookedSeatsByShowTime: " . $e->getMessage());
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

            $stmt = $this->db->prepare($query);
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
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->bindValue(':bookingId', $bookingId);
            $stmt1->execute();
            $booking = $stmt1->fetch(PDO::FETCH_ASSOC);
            error_log("Booking data: " . json_encode($booking));

            if ($booking) {
                // 2. Kiểm tra showtime
                $sql2 = "SELECT * FROM showtime WHERE id = :showTimeId";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->bindValue(':showTimeId', $booking['showTimeId']);
                $stmt2->execute();
                $showtime = $stmt2->fetch(PDO::FETCH_ASSOC);
                error_log("Showtime data: " . json_encode($showtime));

                if ($showtime) {
                    // 3. Kiểm tra movie
                    $sql3 = "SELECT * FROM movie WHERE id = :movieId";
                    $stmt3 = $this->db->prepare($sql3);
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

    public function getBookingStats()
    {
        try {
            // Kiểm tra kết nối trước khi query
            if (!$this->db) {
                throw new Exception("Mất kết nối database");
            }

            // Query đơn giản để test
            $sql = "SELECT COUNT(*) as total FROM booking";
            $stmt = $this->db->query($sql);

            if ($stmt === false) {
                throw new Exception("Lỗi truy vấn: " . print_r($this->db->errorInfo(), true));
            }

            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Query để đếm theo trạng thái
            $sql = "SELECT 
                    paymentStatus,
                    COUNT(*) as count
                    FROM booking 
                    GROUP BY paymentStatus";

            $stmt = $this->db->query($sql);
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Khởi tạo mảng kết quả
            $stats = [
                'total' => (int)$total,
                'confirmed' => 0,
                'pending' => 0,
                'cancelled' => 0
            ];

            // Phân loại theo trạng thái
            foreach ($statusCounts as $row) {
                switch ($row['paymentStatus']) {
                    case 'completed':
                        $stats['confirmed'] = (int)$row['count'];
                        break;
                    case 'pending':
                        $stats['pending'] = (int)$row['count'];
                        break;
                    case 'cancelled':
                        $stats['cancelled'] = (int)$row['count'];
                        break;
                }
            }


            return $stats;
        } catch (Exception $e) {
            error_log("Lỗi trong getBookingStats: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllBookingsWithDetails()
    {
        try {
            $sql = "SELECT 
                    b.id,
                    b.userId,
                    b.showTimeId,
                    b.seats,
                    b.totalAmount,
                    b.paymentStatus,
                    b.bookingDate,
                    b.createdAt,
                    u.name as userName,
                    m.title as movieTitle,
                    s.startTime,
                    s.room
                    FROM booking b
                    LEFT JOIN user u ON b.userId = u.id
                    LEFT JOIN showtime s ON b.showTimeId = s.id
                    LEFT JOIN movie m ON s.movieId = m.id
                    ORDER BY b.createdAt DESC";

            $stmt = $this->db->query($sql);
            if (!$stmt) {
                throw new Exception("Query failed: " . print_r($this->db->errorInfo(), true));
            }

            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Xử lý dữ liệu trước khi trả về
            foreach ($bookings as &$booking) {
                // Format lại các trường dữ liệu
                $booking['userName'] = $booking['userName'] ?? 'N/A';
                $booking['movieTitle'] = $booking['movieTitle'] ?? 'N/A';
                $booking['startTime'] = !empty($booking['startTime']) ?
                    date('H:i d/m/Y', strtotime($booking['startTime'])) : 'N/A';
                $booking['room'] = $booking['room'] ? 'Phòng ' . $booking['room'] : 'N/A';
                $booking['totalAmount'] = number_format($booking['totalAmount'], 0) . ' VND';
                $booking['bookingDate'] = date('H:i d/m/Y', strtotime($booking['bookingDate']));

                // Chuyển đổi trạng thái sang tiếng Việt
                $booking['paymentStatus'] = match ($booking['paymentStatus']) {
                    'completed' => 'Đã xác nhận',
                    'pending' => 'Chờ xác nhận',
                    'cancelled' => 'Đã hủy',
                    default => 'Không xác định'
                };
            }

            return $bookings;
        } catch (Exception $e) {
            error_log("Lỗi trong getAllBookingsWithDetails: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalBookings()
    {
        try {
            $sql = "SELECT COUNT(*) FROM booking WHERE paymentStatus = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetchColumn();
            error_log("Total completed bookings: " . $result); // Debug log

            return (int)$result;
        } catch (PDOException $e) {
            error_log("Error in getTotalBookings: " . $e->getMessage());
            return 0;
        }
    }

    public function getBookingStatistics()
    {
        try {
            $sql = "SELECT DATE(bookingDate) as date, COUNT(*) as count 
                    FROM booking 
                    WHERE bookingDate >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                    AND paymentStatus = 'completed'
                    GROUP BY DATE(bookingDate) 
                    ORDER BY date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            // Get results
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug log
            error_log("Booking statistics: " . json_encode($results));

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getBookingStatistics: " . $e->getMessage());
            return [];
        }
    }

    public function deleteBooking($id)
    {
        try {
            $sql = "DELETE FROM booking WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting booking: " . $e->getMessage());
            return false;
        }
    }
}
