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
            <h4>Mô tả</h4>
            <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>

            <?php if (!empty($showTimes)): ?>
                <h4 class="mt-4">Lịch chiếu</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Phòng</th>
                                <th>Giá vé</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($showTimes as $showTime): ?>
                                <tr>
                                    <td><?php echo date('H:i d/m/Y', strtotime($showTime['startTime'])); ?></td>
                                    <td><?php echo htmlspecialchars($showTime['room']); ?></td>
                                    <td><?php echo number_format($showTime['price'], 0, ',', '.'); ?> VND</td>
                                    <td>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="<?php echo BASE_URL; ?>booking?showtime=<?php echo $showTime['id']; ?>"
                                                class="btn btn-primary btn-sm">Đặt vé</a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>login"
                                                class="btn btn-primary btn-sm">Đăng nhập để đặt vé</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    Hiện chưa có lịch chiếu cho phim này.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>movies" class="btn btn-secondary">Quay lại danh sách phim</a>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>