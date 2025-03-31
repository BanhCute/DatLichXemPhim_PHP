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

    public function showBookingForm()
    {
        // Đặt múi giờ cho Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $showTimeId = isset($_GET['showtime']) ? $_GET['showtime'] : null;
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

        error_log("Raw showtime data: " . print_r($showTime, true));

        // Kiểm tra thời gian chiếu
        try {
            $startTimeStr = $showTime['startTime'];
            error_log("Start time string from DB: " . $startTimeStr);

            $startTime = new DateTime($startTimeStr);
            $now = new DateTime();

            error_log("Debug time info:");
            error_log("- Current timezone: " . date_default_timezone_get());
            error_log("- Server time: " . date('Y-m-d H:i:s'));
            error_log("- Start time: " . $startTime->format('Y-m-d H:i:s'));
            error_log("- Now: " . $now->format('Y-m-d H:i:s'));

            // Tính khoảng cách thời gian theo phút
            $diffInMinutes = ($startTime->getTimestamp() - $now->getTimestamp()) / 60;
            error_log("Difference in minutes: " . $diffInMinutes);

            // Kiểm tra nếu suất chiếu đã qua
            if ($startTime < $now) {
                error_log("Booking rejected: Show time has passed");
                $_SESSION['error'] = 'Suất chiếu này đã diễn ra vui lòng chọn suất chiếu khác';
                header('Location: ' . BASE_URL . 'movies');
                exit;
            }

            // Lấy danh sách ghế đã đặt
            $bookedSeats = $this->bookingModel->getBookedSeats($showTimeId);
            $showTime['booked_seats'] = $bookedSeats;

            error_log("Show time data: " . print_r($showTime, true));
            error_log("Booked seats: " . print_r($bookedSeats, true));

            require_once "views/booking/form.php";
        } catch (Exception $e) {
            error_log("Error processing show time: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý thông tin suất chiếu';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }
    }

    public function create()
    {
        // Đặt múi giờ cho Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        $showTimeId = $_POST['showtime_id'];
        $seats = $_POST['seats'];
        $totalAmount = $_POST['total_amount'];

        // Kiểm tra suất chiếu
        $showTime = $this->showTimeModel->getShowTimeById($showTimeId);
        if (!$showTime) {
            $_SESSION['error'] = 'Không tìm thấy suất chiếu';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        // Kiểm tra thời gian chiếu
        $startTime = new DateTime($showTime['startTime']);
        $now = new DateTime();

        // Kiểm tra nếu suất chiếu đã qua
        if ($startTime < $now) {
            $_SESSION['error'] = 'Suất chiếu này đã diễn ra vui lòng chọn suất chiếu khác';
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        // Kiểm tra ghế đã đặt
        $bookedSeats = $this->bookingModel->getBookedSeats($showTimeId);
        $selectedSeats = is_array($seats) ? $seats : explode(',', $seats);
        $conflictSeats = array_intersect($selectedSeats, $bookedSeats);

        if (!empty($conflictSeats)) {
            $_SESSION['error'] = 'Ghế ' . implode(', ', $conflictSeats) . ' đã được đặt. Vui lòng chọn ghế khác.';
            header('Location: ' . BASE_URL . 'booking?showtime=' . $showTimeId);
            exit;
        }

        // Debug: Kiểm tra dữ liệu trước khi tạo booking
        error_log("Creating booking with data: " . json_encode([
            'userId' => $_SESSION['user_id'],
            'showTimeId' => $showTimeId,
            'seats' => $seats,
            'totalAmount' => $totalAmount,
            'showTime' => $showTime
        ]));

        // Tạo booking với trạng thái pending
        $bookingId = $this->bookingModel->createBooking(
            $_SESSION['user_id'],
            $showTimeId,
            $seats,
            $totalAmount
        );

        if (!$bookingId) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi đặt vé. Vui lòng thử lại sau.';
            header('Location: ' . BASE_URL . 'booking?showtime=' . $showTimeId);
            exit;
        }

        $_SESSION['success'] = 'Đặt vé thành công! Vui lòng thanh toán để hoàn tất đặt vé.';
        header('Location: ' . BASE_URL . 'my-bookings');
        exit;
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
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];
            $bookings = $this->bookingModel->getBookingsByUserId($userId);

            // Debug để xem dữ liệu
            error_log("Bookings data from controller: " . json_encode($bookings));

            require 'views/booking/my-bookings.php';
        } catch (Exception $e) {
            error_log("Error in myBookings: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi lấy thông tin vé';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function cancel()
    {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header('Location: ' . BASE_URL . 'movies');
            exit;
        }

        $bookingId = $_GET['id'];
        $booking = $this->bookingModel->getBookingById($bookingId);

        if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Không thể hủy vé này';
            header('Location: ' . BASE_URL . 'my-bookings');
            exit;
        }

        if ($booking['paymentStatus'] === 'completed') {
            $_SESSION['error'] = 'Không thể hủy vé đã thanh toán';
            header('Location: ' . BASE_URL . 'my-bookings');
            exit;
        }

        if ($this->bookingModel->cancelBooking($bookingId)) {
            $_SESSION['success'] = 'Hủy vé thành công';
        } else {
            $_SESSION['error'] = 'Không thể hủy vé này';
        }
        header('Location: ' . BASE_URL . 'my-bookings');
        exit;
    }
}
