<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Sửa suất chiếu</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>admin/showtimes/edit?id=<?php echo $showTime['id']; ?>" method="POST">
                        <div class="mb-3">
                            <label for="movieId" class="form-label">Phim</label>
                            <select class="form-select" id="movieId" name="movieId" required>
                                <option value="">Chọn phim</option>
                                <?php foreach ($movies as $movie): ?>
                                    <option value="<?php echo $movie['id']; ?>"
                                        <?php echo ($movie['id'] == $showTime['movieId']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($movie['title']); ?> (<?php echo $movie['duration']; ?> phút)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="startTime" class="form-label">Thời gian bắt đầu</label>
                            <input type="datetime-local" class="form-control" id="startTime" name="startTime"
                                value="<?php echo date('Y-m-d\TH:i', strtotime($showTime['startTime'])); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="room" class="form-label">Phòng chiếu</label>
                            <select class="form-select" id="room" name="room" required>
                                <option value="">Chọn phòng</option>
                                <option value="Phòng 1" <?php echo ($showTime['room'] == 'Phòng 1') ? 'selected' : ''; ?>>Phòng 1</option>
                                <option value="Phòng 2" <?php echo ($showTime['room'] == 'Phòng 2') ? 'selected' : ''; ?>>Phòng 2</option>
                                <option value="Phòng 3" <?php echo ($showTime['room'] == 'Phòng 3') ? 'selected' : ''; ?>>Phòng 3</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Giá vé (VNĐ)</label>
                            <input type="number" class="form-control" id="price" name="price"
                                value="<?php echo $showTime['price']; ?>" required min="0" step="1000">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="<?php echo BASE_URL; ?>admin/showtimes" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>