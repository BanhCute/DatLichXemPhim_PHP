<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h1>Vé của tôi</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">
            Bạn chưa có vé nào.
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center 
                    <?php echo getStatusColorClass($booking['paymentStatus']); ?>">
                    <span>Mã vé: #<?php echo $booking['id']; ?></span>
                    <span><?php echo getStatusText($booking['paymentStatus']); ?></span>
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <span>Tên phim:</span>
                        <div style>

                            <?php echo htmlspecialchars($booking['movie_title']); ?>
                        </div>
                    </h5>
                    <div class="booking-details">
                        <p><strong>Thời gian:</strong> <?php echo date('H:i d/m/Y', strtotime($booking['startTime'])); ?></p>
                        <p><strong>Phòng:</strong> <?php echo htmlspecialchars($booking['room']); ?></p>
                        <p><strong>Ghế:</strong> <?php echo htmlspecialchars($booking['seats']); ?></p>
                        <p><strong>Tổng tiền:</strong> <?php echo number_format($booking['totalAmount'], 0) . ' VND'; ?></p>
                    </div>
                    <?php if ($booking['paymentStatus'] === 'completed'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Vui lòng đến rạp trước giờ chiếu 15-30 phút để check-in.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
function getStatusColorClass($status)
{
    return match ($status) {
        'completed' => 'bg-success text-white',
        'pending' => 'bg-warning',
        'cancelled' => 'bg-danger text-white',
        default => 'bg-secondary text-white'
    };
}

function getStatusText($status)
{
    return match ($status) {
        'completed' => 'Đã thanh toán',
        'pending' => 'Chờ thanh toán',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định'
    };
}
?>

<?php include 'views/layouts/footer.php'; ?>