<?php
require_once "models/Booking.php";
require_once "models/ShowTime.php";
require_once "models/Movie.php";
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class BookingController
{
    private $bookingModel;
    private $showTimeModel;
    private $movieModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->showTimeModel = new ShowTime();
        $this->movieModel = new Movie();
    }

    private function checkAndCancelExpiredBookings()
    {
        // Hủy tất cả các vé chưa thanh toán sau 5 phút
        $this->bookingModel->cancelExpiredBookings();
    }

    public function index()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $showTimeId = isset($_GET['showtime']) ? (int)$_GET['showtime'] : 0;
            if (!$showTimeId) {
                $_SESSION['error'] = 'Không tìm thấy suất chiếu';
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            // Lấy thông tin suất chiếu
            $showTime = $this->showTimeModel->getShowTimeById($showTimeId);
            if (!$showTime) {
                $_SESSION['error'] = 'Không tìm thấy suất chiếu';
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            // Kiểm tra có thể đặt vé không
            $bookingStatus = $this->showTimeModel->canBookShowTime($showTimeId);
            if (!$bookingStatus['can_book']) {
                $_SESSION['error'] = $bookingStatus['message'];
                header('Location: ' . BASE_URL . 'movies/detail?id=' . $showTime['movieId']);
                exit;
            }

            // Lấy danh sách ghế đã đặt
            $bookedSeats = $this->bookingModel->getBookedSeatsByShowTime($showTimeId);
            $showTime['booked_seats'] = $bookedSeats;

            require 'views/booking/form.php';
        } catch (Exception $e) {
            error_log("Error in BookingController::index - " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải form đặt vé';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }

    public function create()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            // Lấy dữ liệu từ form
            $showTimeId = $_POST['showtime_id'] ?? 0;
            $seats = $_POST['seats'] ?? [];
            $totalAmount = $_POST['total_amount'] ?? 0;

            // Kiểm tra dữ liệu
            if (empty($showTimeId) || empty($seats)) {
                $_SESSION['error'] = 'Vui lòng chọn ghế ngồi';
                header('Location: ' . BASE_URL . 'booking/form/' . $showTimeId);
                exit;
            }

            // Chuyển mảng ghế thành chuỗi
            $seatsString = is_array($seats) ? implode(',', $seats) : $seats;

            // Tạo booking với trạng thái pending
            $bookingId = $this->bookingModel->createBooking(
                $_SESSION['user_id'],
                $showTimeId,
                $seatsString,
                $totalAmount
            );

            if (!$bookingId) {
                $_SESSION['error'] = 'Có lỗi xảy ra khi đặt vé';
                header('Location: ' . BASE_URL . 'booking/form/' . $showTimeId);
                exit;
            }

            // Debug log
            error_log("Created booking ID: " . $bookingId);

            // Chuyển hướng đến trang thanh toán
            header('Location: ' . BASE_URL . 'payment/' . $bookingId);
            exit;
        } catch (Exception $e) {
            error_log("Error in BookingController::create - " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi đặt vé';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }

    public function showPayment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        $bookingId = $_GET['id'];
        $booking = $this->bookingModel->getBookingById($bookingId);

        if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        require_once "views/booking/payment.php";
    }

    public function confirmPayment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        $bookingId = $_GET['id'];
        $booking = $this->bookingModel->getBookingById($bookingId);

        if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        // Cập nhật trạng thái thanh toán
        $this->bookingModel->updatePaymentStatus($bookingId, 'completed');

        // Gửi email xác nhận
        $this->sendConfirmationEmail($booking);

        $_SESSION['success'] = 'Đặt vé thành công! Vui lòng kiểm tra email của bạn.';
        header('Location: ' . BASE_URL . 'my-bookings');
        exit;
    }

    private function sendConfirmationEmail($booking)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'truongnga252003@gmail.com';
            $mail->Password = 'psgm fxee eodl wkrz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('truongnga252003@gmail.com', 'Hệ thống đặt vé xem phim');
            $mail->addAddress($booking['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Xác nhận đặt vé xem phim - ' . $booking['movieTitle'];

            $mail->Body = "
                <h2>Xác nhận đặt vé thành công</h2>
                <p>Cảm ơn bạn đã đặt vé xem phim tại hệ thống của chúng tôi.</p>
                <h3>Chi tiết đặt vé:</h3>
                <ul>
                    <li>Phim: {$booking['movieTitle']}</li>
                    <li>Suất chiếu: {$booking['startTime']}</li>
                    <li>Phòng: {$booking['room']}</li>
                    <li>Ghế: {$booking['seats']}</li>
                    <li>Tổng tiền: " . number_format($booking['totalAmount'], 0, ',', '.') . " VNĐ</li>
                </ul>
                <p>Vui lòng đến rạp trước giờ chiếu 15 phút và xuất trình mã đặt vé: <strong>{$booking['id']}</strong></p>
            ";

            $mail->send();
            error_log("Confirmation email sent to {$booking['email']}");
            return true;
        } catch (Exception $e) {
            error_log("Failed to send confirmation email: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function myBookings()
    {
        try {
            // Kiểm tra đăng nhập
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để xem vé của bạn';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $userId = $_SESSION['user_id'];

            // Debug
            error_log("Getting bookings for user ID: " . $userId);

            // Lấy danh sách vé
            $bookings = $this->bookingModel->getBookingsByUserId($userId);

            // Debug
            error_log("Found " . count($bookings) . " bookings");
            if (count($bookings) > 0) {
                error_log("First booking: " . json_encode($bookings[0]));
            }

            // Load view
            require_once 'views/booking/my-bookings.php';
        } catch (Exception $e) {
            error_log("Error in myBookings: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải danh sách vé';
            require_once 'views/booking/my-bookings.php';
        }
    }

    public function cancel()
    {
        try {
            if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            $bookingId = $_GET['id'];
            $booking = $this->bookingModel->getBookingById($bookingId);

            // Debug log
            error_log("Cancelling booking: " . json_encode($booking));

            // Kiểm tra quyền hủy vé
            if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
                $_SESSION['error'] = 'Không thể hủy vé này';
                header('Location: ' . BASE_URL . 'my-bookings');
                exit;
            }

            // Chỉ cho phép hủy vé chưa thanh toán
            if ($booking['paymentStatus'] != 'pending') {
                $_SESSION['error'] = 'Chỉ có thể hủy vé chưa thanh toán';
                header('Location: ' . BASE_URL . 'my-bookings');
                exit;
            }

            // Thực hiện hủy vé
            if ($this->bookingModel->cancelBooking($bookingId)) {
                $_SESSION['success'] = 'Hủy vé thành công';
            } else {
                $_SESSION['error'] = 'Không thể hủy vé này';
            }

            header('Location: ' . BASE_URL . 'my-bookings');
            exit;
        } catch (Exception $e) {
            error_log("Error in cancel booking: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi hủy vé';
            header('Location: ' . BASE_URL . 'my-bookings');
            exit;
        }
    }

    public function form($showTimeId)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            // Lấy thông tin suất chiếu
            $showTime = $this->showTimeModel->getShowTimeById($showTimeId);
            if (!$showTime) {
                $_SESSION['error'] = 'Không tìm thấy suất chiếu';
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            // Debug log
            error_log("Show time data: " . json_encode($showTime));

            // Kiểm tra có thể đặt vé không
            $bookingStatus = $this->showTimeModel->canBookShowTime($showTimeId);
            if (!$bookingStatus['can_book']) {
                $_SESSION['error'] = $bookingStatus['message'];
                header('Location: ' . BASE_URL . 'movies/detail?id=' . $showTime['movieId']);
                exit;
            }

            // Lấy danh sách ghế đã đặt
            $bookedSeats = $this->bookingModel->getBookedSeats($showTimeId);
            $showTime['booked_seats'] = $bookedSeats;

            // Debug log
            error_log("Booked seats: " . json_encode($bookedSeats));

            require 'views/booking/form.php';
        } catch (Exception $e) {
            error_log("Error in BookingController::form - " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải form đặt vé';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }
}
