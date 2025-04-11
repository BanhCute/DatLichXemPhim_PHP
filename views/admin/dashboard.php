<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fas fa-chart-line me-2"></i>Thống kê tổng quan
    </h2>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-film me-2"></i>Tổng số phim
                    </h5>
                    <h2 class="card-text"><?php echo $totalMovies; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-ticket-alt me-2"></i>Vé đã bán
                    </h5>
                    <h2 class="card-text"><?php echo $totalBookings; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-users me-2"></i>Người dùng
                    </h5>
                    <h2 class="card-text"><?php echo $totalUsers; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-clock me-2"></i>Suất chiếu
                    </h5>
                    <h2 class="card-text"><?php echo $totalShowtimes; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Movie Categories Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Phân bố thể loại phim</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Bookings Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Số lượng đặt vé theo ngày</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Movies Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-star me-2"></i>Phim được đặt vé nhiều nhất</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Số lượng vé</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularMovies as $movie): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td><?php echo $movie['ticket_count']; ?></td>
                            <td><?php echo number_format($movie['revenue'], 0, ',', '.'); ?> VNĐ</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Statistics -->
    <!-- Remove or comment out this section since we're using the chart instead -->
    <!--
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Phân bố thể loại phim</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($categoryStats)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Thể loại</th>
                                <th>Số lượng phim</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoryStats as $stat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                    <td><?php echo (int)$stat['movie_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Chưa có dữ liệu thống kê thể loại phim.
                </div>
            <?php endif; ?>
        </div>
    </div>
    -->
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($categoryStats, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categoryStats, 'movie_count')); ?>,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#2ECC71', '#3498DB', '#9B59B6', '#E74C3C'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            },
            title: {
                display: true,
                text: 'Phân bố thể loại phim',
                font: {
                    size: 16
                }
            }
        }
    }
});

// Booking Chart
const bookingCtx = document.getElementById('bookingChart').getContext('2d');
new Chart(bookingCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($bookingStats, 'date')); ?>,
        datasets: [{
            label: 'Số lượng đặt vé',
            data: <?php echo json_encode(array_column($bookingStats, 'count')); ?>,
            backgroundColor: '#36A2EB'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>