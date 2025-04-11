<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Thống kê đặt vé</h2>

    <!-- Phần thống kê -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Tổng số vé</h5>
                    <h2><?php echo $totalBookings ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Vé đã xác nhận</h5>
                    <h2><?php echo $confirmedBookings ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Vé chờ xác nhận</h5>
                    <h2><?php echo $pendingBookings ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Vé đã hủy</h5>
                    <h2><?php echo $cancelledBookings ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Bảng danh sách đặt vé -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người đặt</th>
                    <th>Phim</th>
                    <th>Suất chiếu</th>
                    <th>Phòng</th>
                    <th>Ghế</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thời gian đặt</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['userName']); ?></td>
                            <td><?php echo htmlspecialchars($booking['movieTitle']); ?></td>
                            <td><?php echo $booking['startTime']; ?></td>
                            <td><?php echo $booking['room']; ?></td>
                            <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                            <td><?php echo $booking['totalAmount']; ?></td>
                            <td>
                                <span class="badge <?php
                                                    echo match ($booking['paymentStatus']) {
                                                        'Đã xác nhận' => 'bg-success',
                                                        'Chờ xác nhận' => 'bg-warning',
                                                        'Đã hủy' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>">
                                    <?php echo $booking['paymentStatus']; ?>
                                </span>
                            </td>
                            <td><?php echo $booking['bookingDate']; ?></td>
                            <td>
                                <?php if ($booking['paymentStatus'] === 'Chờ xác nhận'): ?>
                                    <button class="btn btn-success btn-sm me-1"
                                        onclick="confirmBooking(<?php echo $booking['id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm me-1"
                                        onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-danger btn-sm"
                                    onclick="deleteBooking(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">Chưa có đơn đặt vé nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmBooking(bookingId) {
        if (confirm('Xác nhận đặt vé này?')) {
            window.location.href = '<?php echo BASE_URL; ?>admin/bookings/confirm/' + bookingId;
        }
    }

    function cancelBooking(bookingId) {
        if (confirm('Bạn có chắc muốn hủy vé này?')) {
            window.location.href = '<?php echo BASE_URL; ?>admin/bookings/cancel/' + bookingId;
        }
    }

    function deleteBooking(bookingId) {
        if (confirm('Bạn có chắc chắn muốn xóa vé này? Hành động này không thể hoàn tác!')) {
            window.location.href = '<?php echo BASE_URL; ?>admin/bookings/delete?id=' + bookingId;
        }
    }
</script>

<?php include 'views/layouts/footer.php'; ?>