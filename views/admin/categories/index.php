<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý thể loại phim</h2>
        <a href="<?php echo BASE_URL; ?>admin/categories/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm thể loại mới
        </a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Chưa có thể loại nào trong hệ thống.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="40%">Tên thể loại</th>
                                <th width="30%">Slug</th>
                                <th width="25%" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>admin/categories/edit?id=<?php echo $category['id']; ?>"
                                            class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/categories/delete?id=<?php echo $category['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa thể loại này?');">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>