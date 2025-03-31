<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Thanh toán vé xem phim</h1>

    <div class="row">
        <!-- Thông tin đặt vé -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thông tin đặt vé:</h5>
                    <table class="table">
                        <tr>
                            <th>Phim:</th>
                            <td><?php echo htmlspecialchars($booking['movieTitle']); ?></td>
                        </tr>
                        <tr>
                            <th>Suất chiếu:</th>
                            <td><?php echo htmlspecialchars($booking['startTime']); ?></td>
                        </tr>
                        <tr>
                            <th>Phòng:</th>
                            <td><?php echo htmlspecialchars($booking['room']); ?></td>
                        </tr>
                        <tr>
                            <th>Ghế:</th>
                            <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                        </tr>
                        <tr>
                            <th>Tổng tiền:</th>
                            <td><?php echo number_format($booking['totalAmount'], 0, ',', '.'); ?> VND</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Mã QR -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Quét mã QR để thanh toán</h5>
                    <img src="<?php echo $qrImageUrl; ?>" alt="QR Code" class="img-fluid mb-3">
                    <div id="countdown" class="mb-3">05:00</div>
                    <button id="confirmPayment" class="btn btn-success">
                        <i class="fas fa-check"></i> Đang xử lý...
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thanh toán thành công</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                    <p class="mt-3">Thanh toán đã được xác nhận thành công!</p>
                    <p>Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn.</p>
                    <p>Tự động chuyển hướng sau <span id="redirectCountdown">10</span> giây...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmPayment');
        const countdownEl = document.getElementById('countdown');
        let countdown = 300; // 5 phút

        // Countdown timer
        const timer = setInterval(() => {
            countdown--;
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            countdownEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?php echo BASE_URL; ?>my-bookings';
            }
        }, 1000);

        // Xử lý nút xác nhận thanh toán
        confirmBtn.addEventListener('click', function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            fetch('<?php echo BASE_URL; ?>confirm-payment/<?php echo $booking['id']; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hiển thị modal thành công
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();

                        // Countdown chuyển hướng
                        let redirectCountdown = 10;
                        const redirectTimer = setInterval(() => {
                            redirectCountdown--;
                            document.getElementById('redirectCountdown').textContent = redirectCountdown;

                            if (redirectCountdown <= 0) {
                                clearInterval(redirectTimer);
                                window.location.href = '<?php echo BASE_URL; ?>my-bookings';
                            }
                        }, 1000);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Đã thanh toán';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check"></i> Đã thanh toán';
                });
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>