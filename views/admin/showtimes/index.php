<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý suất chiếu</h2>
        <a href="<?php echo BASE_URL; ?>admin/showtimes/add" class="btn btn-primary">Thêm suất chiếu</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
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

    <?php if (empty($showTimes)): ?>
        <div class="alert alert-info">
            Chưa có suất chiếu nào trong hệ thống.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phim</th>
                        <th>Thời gian bắt đầu</th>
                        <th>Thời gian kết thúc</th>
                        <th>Phòng</th>
                        <th>Giá vé</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($showTimes as $showTime): ?>
                        <tr>
                            <td><?php echo $showTime['id']; ?></td>
                            <td><?php echo htmlspecialchars($showTime['movie_title']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($showTime['startTime'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($showTime['endTime'])); ?></td>
                            <td><?php echo htmlspecialchars($showTime['room']); ?></td>
                            <td><?php echo number_format($showTime['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/showtimes/edit?id=<?php echo $showTime['id']; ?>"
                                    class="btn btn-sm btn-primary">Sửa</a>
                                <a href="<?php echo BASE_URL; ?>admin/showtimes/delete?id=<?php echo $showTime['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa suất chiếu này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>