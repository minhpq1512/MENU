<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$errorMsg = '';

// Thêm tuyến đường
if (isset($_POST['add'])) {
    $diemDau = $_POST['DiemDau'];
    $diemCuoi = $_POST['DiemCuoi'];
    $doDai = $_POST['DoDai'];
    $doKho = $_POST['DoKho'];
    $donGia = $_POST['DonGiaTheoKm'];
    try {
        $stmt = $pdo->prepare("INSERT INTO TUYEN_DUONG (DiemDau, DiemCuoi, DoDai, DoKho, DonGiaTheoKm) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$diemDau, $diemCuoi, $doDai, $doKho, $donGia]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa tuyến đường
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaTuyen'];
    try {
        $stmt = $pdo->prepare("DELETE FROM TUYEN_DUONG WHERE MaTuyen = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=tuyen_duong");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa tuyến đường
if (isset($_POST['edit'])) {
    $ma = $_POST['MaTuyen'];
    $diemDau = $_POST['DiemDau'];
    $diemCuoi = $_POST['DiemCuoi'];
    $doDai = $_POST['DoDai'];
    $doKho = $_POST['DoKho'];
    $donGia = $_POST['DonGiaTheoKm'];
    try {
        $stmt = $pdo->prepare("UPDATE TUYEN_DUONG SET DiemDau=?, DiemCuoi=?, DoDai=?, DoKho=?, DonGiaTheoKm=? WHERE MaTuyen=?");
        $stmt->execute([$diemDau, $diemCuoi, $doDai, $doKho, $donGia, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách tuyến đường
if ($search != '') {
    $stmt = $pdo->prepare("SELECT * FROM TUYEN_DUONG WHERE DiemDau LIKE ? OR DiemCuoi LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    $tuyen = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM TUYEN_DUONG");
    $tuyen = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh mục tuyến đường</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="card shadow mt-2">
        <div class="card-header bg-info text-white">
            <h3 class="mb-0">Danh mục tuyến đường</h3>
        </div>
        <div class="card-body">
            <!-- Vùng nhập tìm kiếm -->
            <form method="get" class="row g-2 mb-4">
                <input type="hidden" name="page" value="tuyen_duong">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo điểm đầu hoặc điểm cuối..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-info">Tìm kiếm</button>
                </div>
            </form>
            <!-- Form thêm tuyến đường -->
            <form method="post" class="row g-2 mb-4">
                <div class="col-md-2">
                    <input type="text" name="DiemDau" class="form-control" placeholder="Điểm đầu" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="DiemCuoi" class="form-control" placeholder="Điểm cuối" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="DoDai" class="form-control" placeholder="Độ dài (km)"  required>
                </div>
                <div class="col-md-2">
                    <select name="DoKho" class="form-select" required>
                        <option value="">Độ khó</option>
                        <option value="1">1 - Dễ</option>
                        <option value="2">2 - Trung bình</option>
                        <option value="3">3 - Khó</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="DonGiaTheoKm" class="form-control" placeholder="Đơn giá/km"  required>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" name="add" class="btn btn-success">Thêm tuyến</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-info">
                        <tr>
                            <th>Mã tuyến</th>
                            <th>Điểm đầu</th>
                            <th>Điểm cuối</th>
                            <th>Độ dài (km)</th>
                            <th>Độ khó</th>
                            <th>Đơn giá/km</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tuyen as $t): ?>
                        <tr>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $t['MaTuyen']): ?>
                            <form method="post">
                                <td><input type="text" name="MaTuyen" value="<?= $t['MaTuyen'] ?>" class="form-control" readonly></td>
                                <td><input type="text" name="DiemDau" value="<?= $t['DiemDau'] ?>" class="form-control" required></td>
                                <td><input type="text" name="DiemCuoi" value="<?= $t['DiemCuoi'] ?>" class="form-control" required></td>
                                <td><input type="number" step="0.01" name="DoDai" value="<?= $t['DoDai'] ?>" class="form-control" required></td>
                                <td>
                                    <select name="DoKho" class="form-select" required>
                                        <option value="1" <?= $t['DoKho']==1?'selected':'' ?>>1 - Dễ</option>
                                        <option value="2" <?= $t['DoKho']==2?'selected':'' ?>>2 - Trung bình</option>
                                        <option value="3" <?= $t['DoKho']==3?'selected':'' ?>>3 - Khó</option>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="DonGiaTheoKm" value="<?= $t['DonGiaTheoKm'] ?>" class="form-control" required></td>
                                <td>
                                    <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                    <a href="?page=tuyen_duong" class="btn btn-secondary btn-sm">Hủy</a>
                                </td>
                            </form>
                        <?php else: ?>
                            <td><?= $t['MaTuyen'] ?></td>
                            <td><?= $t['DiemDau'] ?></td>
                            <td><?= $t['DiemCuoi'] ?></td>
                            <td><?= $t['DoDai'] ?></td>
                            <td><?= $t['DoKho'] ?></td>
                            <td><?= $t['DonGiaTheoKm'] ?></td>
                            <td>
                                <a href="?page=tuyen_duong&edit=<?= $t['MaTuyen'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="?page=tuyen_duong&delete=<?= $t['MaTuyen'] ?>" class="btn btn-danger btn-sm">Xóa</a>
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
<div class="modal show" tabindex="-1" style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Lỗi</h5>
            </div>
            <div class="modal-body">
                <p><?= htmlspecialchars($errorMsg) ?></p>
            </div>
            <div class="modal-footer">
                <a href="?page=tuyen_duong" class="btn btn-secondary">Đóng</a>
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
                    <p>Bạn có muốn xóa tuyến đường có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                    <input type="hidden" name="MaTuyen" value="<?= htmlspecialchars($_GET['delete']) ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                    <a href="?page=tuyen_duong" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>