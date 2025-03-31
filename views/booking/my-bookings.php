<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Vé của tôi</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($bookings)): ?>
        <?php foreach ($bookings as $booking): ?>
            <div class="card mb-3">
                <div class="card-header <?php echo $booking['paymentStatus'] == 'cancelled' ? 'bg-danger' : 'bg-success'; ?> text-white">
                    Mã vé: #<?php echo $booking['id']; ?>
                    <?php if ($booking['paymentStatus'] == 'cancelled'): ?>
                        <span class="float-end">Đã hủy</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php
                    // Debug để xem dữ liệu
                    error_log("Booking data: " . json_encode($booking));
                    ?>

                    <h5 class="card-title">
                        <?php echo isset($booking['movie_title']) ? htmlspecialchars($booking['movie_title']) : (isset($booking['movieTitle']) ? htmlspecialchars($booking['movieTitle']) : 'Không có tên phim'); ?>
                    </h5>

                    <p class="card-text">
                        <i class="fas fa-calendar"></i> <?php echo date('H:i d/m/Y', strtotime($booking['startTime'])); ?><br>
                        <i class="fas fa-door-open"></i> Phòng: <?php echo htmlspecialchars($booking['room']); ?><br>
                        <i class="fas fa-chair"></i> Ghế: <?php echo htmlspecialchars($booking['seats']); ?><br>
                        <strong>Tổng tiền:</strong> <?php echo number_format($booking['totalAmount'], 0, ',', '.'); ?> VNĐ
                    </p>

                    <?php if ($booking['paymentStatus'] == 'cancelled'): ?>
                        <div class="alert alert-danger mt-2 mb-0">
                            Vé này đã bị hủy bởi người dùng!!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">
            Bạn chưa có vé nào. <a href="<?php echo BASE_URL; ?>movies">Đặt vé ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>