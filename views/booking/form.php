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

    <h2>Đặt vé xem phim: <?php echo htmlspecialchars($showTime['movie_title']); ?></h2>

    <?php
    $bookingStatus = $this->showTimeModel->canBookShowTime($showTime['id']);
    if (!$bookingStatus['can_book']):
    ?>
        <div class="alert alert-warning">
            <h4 class="alert-heading">Không thể đặt vé!</h4>
            <p><?php echo $bookingStatus['message']; ?></p>
            <hr>
            <p class="mb-0">Vui lòng chọn suất chiếu khác.</p>
            <a href="<?php echo BASE_URL; ?>movies" class="btn btn-primary mt-3">Xem danh sách phim khác</a>
            <a href="<?php echo BASE_URL; ?>detail/<?php echo $showTime['movie_id']; ?>" class="btn btn-primary mt-3">Xem lịch chiếu phim</a>

        </div>
    <?php else: ?>
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thông tin suất chiếu</h5>
                        <p><strong>Thời gian:</strong> <?php echo date('H:i d/m/Y', strtotime($showTime['startTime'])); ?></p>
                        <p><strong>Phòng:</strong> <?php echo htmlspecialchars($showTime['room']); ?></p>
                        <p><strong>Giá vé:</strong> <?php echo number_format($showTime['price'], 0, ',', '.'); ?> VND</p>

                        <form action="<?php echo BASE_URL; ?>booking/create" method="POST" id="bookingForm">
                            <input type="hidden" name="showtime_id" value="<?php echo $showTime['id']; ?>">
                            <input type="hidden" name="total_amount" id="totalAmount" value="0">

                            <div class="form-group">
                                <label>Chọn ghế:</label>
                                <div class="seat-container mt-3">
                                    <?php
                                    // Giả sử có 6 hàng (A-F), mỗi hàng 8 ghế (1-8)
                                    $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
                                    foreach ($rows as $row) {
                                        echo '<div class="row mb-2">';
                                        for ($i = 1; $i <= 8; $i++) {
                                            $seatNumber = $row . $i;
                                            echo '<div class="col">';
                                            echo '<div class="form-check">';
                                            echo '<input class="form-check-input seat-checkbox" type="checkbox" name="seats[]" value="' . $seatNumber . '" id="seat' . $seatNumber . '">';
                                            echo '<label class="form-check-label" for="seat' . $seatNumber . '">' . $seatNumber . '</label>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <h5>Tổng tiền: <span id="totalPrice">0</span> VND</h5>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Tiếp tục</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Chú thích</h5>
                        <div class="mt-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="seat-example available"></div>
                                <span class="ms-2">Ghế trống</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="seat-example selected"></div>
                                <span class="ms-2">Ghế đã chọn</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="seat-example occupied"></div>
                                <span class="ms-2">Ghế đã đặt</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .seat-container .form-check {
        text-align: center;
        margin-bottom: 10px;
    }

    .seat-checkbox {
        display: none;
    }

    .form-check-label {
        display: inline-block;
        width: 35px;
        height: 35px;
        line-height: 35px;
        text-align: center;
        background-color: #e9ecef;
        border-radius: 5px;
        cursor: pointer;
        margin: 0;
    }

    .seat-checkbox:checked+.form-check-label {
        background-color: #007bff;
        color: white;
    }

    .seat-example {
        width: 35px;
        height: 35px;
        border-radius: 5px;
    }

    .seat-example.available {
        background-color: #e9ecef;
    }

    .seat-example.selected {
        background-color: #007bff;
    }

    .seat-example.occupied {
        background-color: #dc3545;
    }

    .seat {
        width: 40px;
        height: 40px;
        margin: 5px;
        border: none;
        border-radius: 5px;
        background-color: #e0e0e0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .seat:hover:not(.booked) {
        background-color: #4CAF50;
        color: white;
    }

    .seat.selected {
        background-color: #4CAF50;
        color: white;
    }

    .seat.booked {
        background-color: #f44336;
        color: white;
        cursor: not-allowed;
    }

    .screen {
        width: 100%;
        height: 50px;
        background-color: #ccc;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 5px;
    }

    .seat-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .seat-row {
        display: flex;
        align-items: center;
    }

    .row-label {
        width: 30px;
        text-align: center;
        font-weight: bold;
    }

    .seat-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookedSeats = <?php echo json_encode($showTime['booked_seats']); ?>;
        const checkboxes = document.querySelectorAll('.seat-checkbox');
        const totalPriceElement = document.getElementById('totalPrice');
        const totalAmountInput = document.getElementById('totalAmount');
        const pricePerSeat = <?php echo $showTime['price']; ?>;
        const bookingForm = document.getElementById('bookingForm');

        // Đánh dấu ghế đã đặt
        checkboxes.forEach(checkbox => {
            if (bookedSeats.includes(checkbox.value)) {
                checkbox.disabled = true;
                const label = checkbox.nextElementSibling;
                label.style.backgroundColor = '#dc3545';
                label.style.color = 'white';
                label.style.cursor = 'not-allowed';
            }
        });

        // Xử lý sự kiện khi chọn/bỏ chọn ghế
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateTotalPrice();
            });
        });

        // Cập nhật tổng tiền
        function updateTotalPrice() {
            const selectedSeats = document.querySelectorAll('.seat-checkbox:checked').length;
            const totalPrice = selectedSeats * pricePerSeat;
            totalPriceElement.textContent = totalPrice.toLocaleString('vi-VN');
            totalAmountInput.value = totalPrice;
        }

        // Kiểm tra form trước khi submit
        bookingForm.addEventListener('submit', function(e) {
            const selectedSeats = document.querySelectorAll('.seat-checkbox:checked').length;
            if (selectedSeats === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một ghế');
            }
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>