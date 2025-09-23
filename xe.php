<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Thêm xe
if (isset($_POST['add'])) {
    $maLoaiXe = $_POST['MaLoaiXe'];
    $bienSoXe = $_POST['BienSoXe'];
    $soKm = $_POST['SoKmTichLuy'];
    $ngayDK = $_POST['NgayDangKiem'];
    // Không truyền MaXe, để trigger tự sinh
    $stmt = $pdo->prepare("INSERT INTO XE (MaLoaiXe, BienSoXe, SoKmTichLuy, NgayDangKiem) VALUES (?, ?, ?, ?)");
    $stmt->execute([$maLoaiXe, $bienSoXe, $soKm, $ngayDK]);
}

// Xóa xe
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaXe'];
    $stmt = $pdo->prepare("DELETE FROM XE WHERE MaXe = ?");
    $stmt->execute([$ma]);
    header("Location: ?page=danh_muc_xe");
    exit;
}

// Sửa xe
if (isset($_POST['edit'])) {
    $ma = $_POST['MaXe'];
    $maLoaiXe = $_POST['MaLoaiXe'];
    $bienSoXe = $_POST['BienSoXe'];
    $soKm = $_POST['SoKmTichLuy'];
    $ngayDK = $_POST['NgayDangKiem'];
    $stmt = $pdo->prepare("UPDATE XE SET MaLoaiXe=?, BienSoXe=?, SoKmTichLuy=?, NgayDangKiem=? WHERE MaXe=?");
    $stmt->execute([$maLoaiXe, $bienSoXe, $soKm, $ngayDK, $ma]);
}

// Lấy danh sách loại xe cho dropdown
$dsLoaiXe = $pdo->query("SELECT MaLoaiXe, TenLoaiXe FROM LOAI_XE")->fetchAll();

// Lấy danh sách xe
if ($search != '') {
    $stmt = $pdo->prepare("SELECT XE.*, LOAI_XE.TenLoaiXe FROM XE JOIN LOAI_XE ON XE.MaLoaiXe = LOAI_XE.MaLoaiXe WHERE XE.BienSoXe LIKE ? OR LOAI_XE.TenLoaiXe LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    $xe = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT XE.*, LOAI_XE.TenLoaiXe FROM XE JOIN LOAI_XE ON XE.MaLoaiXe = LOAI_XE.MaLoaiXe");
    $xe = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh mục xe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">Danh mục xe</h3>
            </div>
            <div class="card-body">
                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="danh_muc_xe">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo biển số hoặc loại xe..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>
                <!-- Form thêm xe -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <select name="MaLoaiXe" class="form-select" required>
                            <option value="">--Chọn loại xe--</option>
                            <?php foreach ($dsLoaiXe as $lx): ?>
                                <option value="<?= $lx['MaLoaiXe'] ?>"><?= $lx['TenLoaiXe'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="BienSoXe" class="form-control" placeholder="Biển số xe" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="SoKmTichLuy" class="form-control" placeholder="Số km tích lũy" min="0" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="NgayDangKiem" class="form-control" placeholder="Ngày đăng kiểm">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm xe</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>Mã xe</th>
                                <th>Loại xe</th>
                                <th>Biển số xe</th>
                                <th>Số km tích lũy</th>
                                <th>Ngày đăng kiểm</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($xe as $x): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $x['MaXe']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaXe" value="<?= $x['MaXe'] ?>" class="form-control" readonly></td>
                                            <td>
                                                <select name="MaLoaiXe" class="form-select" required>
                                                    <?php foreach ($dsLoaiXe as $lx): ?>
                                                        <option value="<?= $lx['MaLoaiXe'] ?>" <?= $lx['MaLoaiXe'] == $x['MaLoaiXe'] ? 'selected' : '' ?>>
                                                            <?= $lx['TenLoaiXe'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="BienSoXe" value="<?= $x['BienSoXe'] ?>" class="form-control" required></td>
                                            <td><input type="number" name="SoKmTichLuy" value="<?= $x['SoKmTichLuy'] ?>" class="form-control" min="0" required></td>
                                            <td><input type="date" name="NgayDangKiem" value="<?= $x['NgayDangKiem'] ?>" class="form-control"></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=danh_muc_xe" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $x['MaXe'] ?></td>
                                        <td><?= $x['TenLoaiXe'] ?></td>
                                        <td><?= $x['BienSoXe'] ?></td>
                                        <td><?= $x['SoKmTichLuy'] ?></td>
                                        <td><?= $x['NgayDangKiem'] ?></td>
                                        <td>
                                            <a href="?page=danh_muc_xe&edit=<?= $x['MaXe'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=danh_muc_xe&delete=<?= $x['MaXe'] ?>" class="btn btn-danger btn-sm">Xóa</a>
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
                        <input type="hidden" name="MaXe" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                        <a href="?page=danh_muc_xe" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>