<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Quản lý người dùng</h2>

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
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                <?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                                <a href="<?php echo BASE_URL; ?>admin/users/promote?id=<?php echo $user['id']; ?>"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn thăng cấp người dùng này thành admin?');">
                                    <i class="fas fa-arrow-up"></i> Thăng cấp
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>admin/users/demote?id=<?php echo $user['id']; ?>"
                                    class="btn btn-warning btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn hạ cấp admin này thành người dùng thường?');">
                                    <i class="fas fa-arrow-down"></i> Hạ cấp
                                </a>
                            <?php endif; ?>

                            <a href="<?php echo BASE_URL; ?>admin/users/delete?id=<?php echo $user['id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>