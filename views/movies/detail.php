<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <?php if ($movie['imageUrl']): ?>
                <img src="<?php echo BASE_URL . $movie['imageUrl']; ?>"
                    class="img-fluid rounded"
                    alt="<?php echo htmlspecialchars($movie['title']); ?>">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center rounded"
                    style="height: 400px;">
                    <span class="text-muted">No image</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
            <p class="text-muted">Thời lượng: <?php echo $movie['duration']; ?> phút</p>

            <!-- Thêm nút xem trailer -->
            <?php if (!empty($movie['trailer'])): ?>
                <a href="<?php echo htmlspecialchars($movie['trailer']); ?>"
                    target="_blank"
                    class="btn btn-danger mb-3">
                    <i class="fab fa-youtube"></i> Xem Trailer
                </a>
            <?php endif; ?>

            <h4>Mô tả</h4>
            <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>

            <div class="showtimes mt-4">
                <h3>Lịch chiếu</h3>
                <!-- Debug -->
                <?php
                echo "<!-- Số lượng suất chiếu: " . count($showTimes) . " -->\n";
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Phòng</th>
                                <th>Giá vé</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($showTimes as $index => $showTime): ?>
                                <?php
                                // Debug
                                echo "<!-- Đang xử lý suất chiếu thứ {$index} - ID: {$showTime['id']} -->\n";
                                echo "<!-- Thời gian: {$showTime['startTime']} - Phòng: {$showTime['room']} -->\n";
                                ?>
                                <tr>
                                    <td><?php echo date('H:i d/m/Y', strtotime($showTime['startTime'])); ?></td>
                                    <td><?php echo htmlspecialchars($showTime['room']); ?></td>
                                    <td><?php echo number_format($showTime['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <?php if (isset($showTime['can_book']) && $showTime['can_book']): ?>
                                            <span class="badge bg-success">Có thể đặt vé</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <?php echo isset($showTime['message']) ? htmlspecialchars($showTime['message']) : 'Không thể đặt vé'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($showTime['can_book']) && $showTime['can_book']): ?>
                                            <a href="<?php echo BASE_URL; ?>booking/form/<?php echo $showTime['id']; ?>"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-ticket-alt"></i> Đặt vé
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>movies" class="btn btn-secondary">Quay lại danh sách phim</a>
    </div>
</div>

<!-- Thêm modal thông báo -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Thêm JavaScript -->
<script>
    function showBookingError(message) {
        document.getElementById('errorMessage').textContent = message;
        new bootstrap.Modal(document.getElementById('errorModal')).show();
    }

    // Kiểm tra nếu có thông báo lỗi từ session
    <?php if (isset($_SESSION['error'])): ?>
        showBookingError('<?php echo htmlspecialchars($_SESSION['error']); ?>');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</script>

<?php include 'views/layouts/footer.php'; ?>