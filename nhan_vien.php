<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$errorMsg = '';

// Thêm nhân viên
if (isset($_POST['add'])) {
    $ten = $_POST['HoTen'];
    $sdt = $_POST['SDT'];
    try {
        $stmt = $pdo->prepare("INSERT INTO NHAN_VIEN (HoTen, SDT) VALUES (?, ?)");
        $stmt->execute([$ten, $sdt]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa nhân viên
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaNV'];
    try {
        $stmt = $pdo->prepare("DELETE FROM NHAN_VIEN WHERE MaNV = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=nhan_vien");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa nhân viên
if (isset($_POST['edit'])) {
    $ma = $_POST['MaNV'];
    $ten = $_POST['HoTen'];
    $sdt = $_POST['SDT'];
    try {
        $stmt = $pdo->prepare("UPDATE NHAN_VIEN SET HoTen=?, SDT=? WHERE MaNV=?");
        $stmt->execute([$ten, $sdt, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách nhân viên
if ($search != '') {
    $stmt = $pdo->prepare("SELECT * FROM NHAN_VIEN WHERE HoTen LIKE ? OR SDT LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    $nhanvien = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM NHAN_VIEN");
    $nhanvien = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh mục nhân viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow mt-2">
            <div class="card-header bg-secondary text-white">
                <h3 class="mb-0">Danh mục nhân viên</h3>
            </div>
            <div class="card-body">
                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="nhan_vien">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo tên hoặc số điện thoại..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>
                <!-- Form thêm nhân viên -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-5">
                        <input type="text" name="HoTen" class="form-control" placeholder="Họ tên nhân viên" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="SDT" class="form-control" placeholder="Số điện thoại" required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm nhân viên</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Mã NV</th>
                                <th>Họ tên</th>
                                <th>Số điện thoại</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nhanvien as $nv): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $nv['MaNV']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaNV" value="<?= $nv['MaNV'] ?>" class="form-control"
                                                    readonly></td>
                                            <td><input type="text" name="HoTen" value="<?= $nv['HoTen'] ?>" class="form-control"
                                                    required></td>
                                            <td><input type="text" name="SDT" value="<?= $nv['SDT'] ?>" class="form-control"
                                                    required></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=nhan_vien" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $nv['MaNV'] ?></td>
                                        <td><?= $nv['HoTen'] ?></td>
                                        <td><?= $nv['SDT'] ?></td>
                                        <td>
                                            <a href="?page=nhan_vien&edit=<?= $nv['MaNV'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=nhan_vien&delete=<?= $nv['MaNV'] ?>"
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php if ($errorMsg): ?>
    <!-- Popup báo lỗi -->
    <div class="modal show" tabindex="-1"
        style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Lỗi</h5>
                </div>
                <div class="modal-body">
                    <p><?= htmlspecialchars($errorMsg) ?></p>
                </div>
                <div class="modal-footer">
                    <a href="?page=nhan_vien" class="btn btn-secondary">Đóng</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

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
                        <p>Bạn có muốn xóa nhân viên có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                        <input type="hidden" name="MaNV" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                        <a href="?page=nhan_vien" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>