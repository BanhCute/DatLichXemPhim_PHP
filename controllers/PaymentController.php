<?php
require_once 'models/Booking.php';
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PaymentController
{
    private $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
    }

    public function showPayment($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Không tìm thấy đơn đặt vé!';
            header('Location: ' . BASE_URL . 'my-bookings');
            exit;
        }

        // Tạo mã QR cho thanh toán
        $qrData = [
            'amount' => $booking['totalAmount'],
            'booking_id' => $booking['id'],
            'movie' => $booking['movieTitle'],
            'showtime' => $booking['startTime'],
            'seats' => $booking['seats']
        ];
        $qrContent = json_encode($qrData);
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrContent);

        require 'views/payment/qr-payment.php';
    }

    public function confirmPayment($bookingId)
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || $booking['userId'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn đặt vé']);
            return;
        }

        if ($this->bookingModel->confirmBooking($bookingId)) {
            // Gửi email xác nhận
            $this->sendConfirmationEmail($booking);

            echo json_encode([
                'success' => true,
                'message' => 'Thanh toán thành công! Vui lòng kiểm tra email của bạn.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận thanh toán'
            ]);
        }
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
            $mail->Subject = 'Xác nhận đặt vé xem phim thành công';

            // Nội dung email
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
                <p>Vui lòng đến rạp trước giờ chiếu 15 phút, xuất trình email và mã đặt vé: <strong>{$booking['id']}</strong></p>
            ";

            $mail->send();
            error_log("Confirmation email sent to {$booking['email']}");
        } catch (Exception $e) {
            error_log("Failed to send confirmation email: {$mail->ErrorInfo}");
        }
    }
}
