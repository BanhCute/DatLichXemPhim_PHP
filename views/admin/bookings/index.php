<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Thống kê đặt vé</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Tổng số vé</h5>
                    <p class="card-text display-6"><?php echo count($bookings); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Vé đã xác nhận</h5>
                    <p class="card-text display-6">
                        <?php echo count(array_filter($bookings, function ($booking) {
                            return $booking['paymentStatus'] === 'completed';
                        })); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Vé chờ xác nhận</h5>
                    <p class="card-text display-6">
                        <?php echo count(array_filter($bookings, function ($booking) {
                            return $booking['paymentStatus'] === 'pending';
                        })); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Vé đã hủy</h5>
                    <p class="card-text display-6">
                        <?php echo count(array_filter($bookings, function ($booking) {
                            return $booking['paymentStatus'] === 'cancelled';
                        })); ?>
                    </p>
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

    <div class="table-responsive">
        <table class="table table-striped">
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
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                        <td><?php
                            $startTime = new DateTime($booking['startTime']);
                            echo $startTime->format('H:i d/m/Y');
                            ?></td>
                        <td><?php echo htmlspecialchars($booking['room']); ?></td>
                        <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                        <td><?php echo number_format($booking['totalAmount'], 0, ',', '.'); ?> VNĐ</td>
                        <td>
                            <?php
                            $statusClass = '';
                            switch ($booking['paymentStatus']) {
                                case 'completed':
                                    $statusClass = 'bg-success';
                                    break;
                                case 'pending':
                                    $statusClass = 'bg-warning text-dark';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-danger';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($booking['paymentStatus'] === 'pending'): ?>
                                <a href="<?php echo BASE_URL; ?>admin/bookings/confirm?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Xác nhận
                                </a>
                                <a href="<?php echo BASE_URL; ?>admin/bookings/cancel?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn hủy vé này?');">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>