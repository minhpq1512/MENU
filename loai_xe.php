<?php
require 'config.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Thêm loại xe
if (isset($_POST['add'])) {
    $ten = $_POST['TenLoaiXe'];
    $soghe = $_POST['SoGhe'];
    $songay = $_POST['SoNgayBaoDuongToiDa'];
    $sokm = $_POST['SoKmGiamNgayBaoDuong'];
    // Không truyền MaLoaiXe, để trigger tự sinh
    $stmt = $pdo->prepare("INSERT INTO LOAI_XE (TenLoaiXe, SoGhe, SoNgayBaoDuongToiDa, SoKmGiamNgayBaoDuong) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ten, $soghe, $songay, $sokm]);
}

// Xóa loại xe
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaLoaiXe'];
    $stmt = $pdo->prepare("DELETE FROM LOAI_XE WHERE MaLoaiXe = ?");
    $stmt->execute([$ma]);
    // Sau khi xóa, chuyển về trang danh sách
    header("Location: ?page=loai_xe");
    exit;
}

// Sửa loại xe
if (isset($_POST['edit'])) {
    $ma = $_POST['MaLoaiXe'];
    $ten = $_POST['TenLoaiXe'];
    $soghe = $_POST['SoGhe'];
    $songay = $_POST['SoNgayBaoDuongToiDa'];
    $sokm = $_POST['SoKmGiamNgayBaoDuong'];
    $stmt = $pdo->prepare("UPDATE LOAI_XE SET TenLoaiXe=?, SoGhe=?, SoNgayBaoDuongToiDa=?, SoKmGiamNgayBaoDuong=? WHERE MaLoaiXe=?");
    $stmt->execute([$ten, $soghe, $songay, $sokm, $ma]);
}

// Lấy danh sách loại xe
if ($search != '') {
    $stmt = $pdo->prepare("SELECT * FROM LOAI_XE WHERE TenLoaiXe LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $loai_xe = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM LOAI_XE");
    $loai_xe = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh mục loại xe</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Danh mục loại xe</h3>
            </div>
            <div class="card-body">
                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="loai_xe">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên loại xe..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <input type="text" name="TenLoaiXe" class="form-control" placeholder="Tên loại xe" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="SoGhe" class="form-control" placeholder="Số ghế" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="SoNgayBaoDuongToiDa" class="form-control"
                            placeholder="Số ngày bảo dưỡng tối đa" required min="1">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="SoKmGiamNgayBaoDuong" class="form-control"
                            placeholder="Số km giảm ngày bảo dưỡng" required min="1">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã loại xe</th>
                                <th>Tên loại xe</th>
                                <th>Số ghế</th>
                                <th>Số ngày bảo dưỡng tối đa</th>
                                <th>Số km giảm ngày bảo dưỡng</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loai_xe as $lx): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $lx['MaLoaiXe']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaLoaiXe" value="<?= $lx['MaLoaiXe'] ?>"
                                                    class="form-control" readonly></td>
                                            <td><input type="text" name="TenLoaiXe" value="<?= $lx['TenLoaiXe'] ?>"
                                                    class="form-control" required></td>
                                            <td><input type="number" name="SoGhe" value="<?= $lx['SoGhe'] ?>"
                                                    class="form-control" required></td>
                                            <td><input type="number" name="SoNgayBaoDuongToiDa"
                                                    value="<?= $lx['SoNgayBaoDuongToiDa'] ?>" class="form-control" required>
                                            </td>
                                            <td><input type="number" name="SoKmGiamNgayBaoDuong"
                                                    value="<?= $lx['SoKmGiamNgayBaoDuong'] ?>" class="form-control" required>
                                            </td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=loai_xe" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $lx['MaLoaiXe'] ?></td>
                                        <td><?= $lx['TenLoaiXe'] ?></td>
                                        <td><?= $lx['SoGhe'] ?></td>
                                        <td><?= $lx['SoNgayBaoDuongToiDa'] ?></td>
                                        <td><?= $lx['SoKmGiamNgayBaoDuong'] ?></td>
                                        <td>
                                            <a href="?page=loai_xe&edit=<?= $lx['MaLoaiXe'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=loai_xe&delete=<?= $lx['MaLoaiXe'] ?>"
                                                class="btn btn-danger btn-sm">Xóa</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional, for modal or advanced features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</div>
<?php if (isset($_GET['delete'])): ?>
    <!-- Popup xác nhận xóa -->
    <div class="modal show" tabindex="-1"
        style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Xác nhận xóa</h5>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có muốn xóa xe có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                        <input type="hidden" name="MaLoaiXe" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                        <a href="?page=loai_xe" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>