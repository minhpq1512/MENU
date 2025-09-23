<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$errorMsg = '';

// Lấy danh sách tuyến đường và xe cho dropdown
$dsTuyen = $pdo->query("SELECT MaTuyen, DiemDau, DiemCuoi FROM TUYEN_DUONG")->fetchAll();
$dsXe = $pdo->query("SELECT MaXe, BienSoXe FROM XE")->fetchAll();

// Thêm chuyến xe
if (isset($_POST['add'])) {
    $maTuyen = $_POST['MaTuyen'];
    $maXe = $_POST['MaXe'];
    $ngayKhoiHanh = $_POST['NgayKhoiHanh'];
    $trangThai = $_POST['TrangThai'];
    $chiPhi = $_POST['ChiPhiVanHanh'];
    $thuLao = $_POST['ThuLaoChuyen'];
    try {
        $stmt = $pdo->prepare("INSERT INTO CHUYEN_XE (MaTuyen, MaXe, NgayKhoiHanh, TrangThai, ChiPhiVanHanh, ThuLaoChuyen) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$maTuyen, $maXe, $ngayKhoiHanh, $trangThai, $chiPhi, $thuLao]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa chuyến xe
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaChuyen'];
    try {
        $stmt = $pdo->prepare("DELETE FROM CHUYEN_XE WHERE MaChuyen = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=chuyen_xe");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa chuyến xe
if (isset($_POST['edit'])) {
    $ma = $_POST['MaChuyen'];
    $maTuyen = $_POST['MaTuyen'];
    $maXe = $_POST['MaXe'];
    $ngayKhoiHanh = $_POST['NgayKhoiHanh'];
    $trangThai = $_POST['TrangThai'];
    $chiPhi = $_POST['ChiPhiVanHanh'];
    $thuLao = $_POST['ThuLaoChuyen'];
    try {
        $stmt = $pdo->prepare("UPDATE CHUYEN_XE SET MaTuyen=?, MaXe=?, NgayKhoiHanh=?, TrangThai=?, ChiPhiVanHanh=?, ThuLaoChuyen=? WHERE MaChuyen=?");
        $stmt->execute([$maTuyen, $maXe, $ngayKhoiHanh, $trangThai, $chiPhi, $thuLao, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách chuyến xe
if ($search != '') {
    $stmt = $pdo->prepare("SELECT CX.*, TD.DiemDau, TD.DiemCuoi, XE.BienSoXe 
        FROM CHUYEN_XE CX 
        JOIN TUYEN_DUONG TD ON CX.MaTuyen = TD.MaTuyen 
        JOIN XE ON CX.MaXe = XE.MaXe 
        WHERE CX.MaChuyen LIKE ? OR XE.BienSoXe LIKE ? OR TD.DiemDau LIKE ? OR TD.DiemCuoi LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    $chuyen = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT CX.*, TD.DiemDau, TD.DiemCuoi, XE.BienSoXe 
        FROM CHUYEN_XE CX 
        JOIN TUYEN_DUONG TD ON CX.MaTuyen = TD.MaTuyen 
        JOIN XE ON CX.MaXe = XE.MaXe");
    $chuyen = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý chuyến xe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow mt-2">
            <div class="card-header bg-warning text-dark">
                <h3 class="mb-0">Quản lý chuyến xe</h3>
            </div>
            <div class="card-body">
                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="chuyen_xe">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo mã chuyến, biển số, điểm đầu/cuối..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>
                <!-- Form thêm chuyến xe -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-2">
                        <select name="MaTuyen" class="form-select" required>
                            <option value="">--Chọn tuyến--</option>
                            <?php foreach ($dsTuyen as $td): ?>
                                <option value="<?= $td['MaTuyen'] ?>">
                                    <?= $td['MaTuyen'] ?> (<?= $td['DiemDau'] ?> - <?= $td['DiemCuoi'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="MaXe" class="form-select" required>
                            <option value="">--Chọn xe--</option>
                            <?php foreach ($dsXe as $xe): ?>
                                <option value="<?= $xe['MaXe'] ?>"><?= $xe['BienSoXe'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="NgayKhoiHanh" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <select name="TrangThai" class="form-select" required>
                            <option value="Dang dien ra">Dang dien ra</option>
                            <option value="Sap dien ra">Sap dien ra</option>
                            <option value="Hoan thanh">Hoan thanh</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="ChiPhiVanHanh" class="form-control"
                            placeholder="Chi phí vận hành">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="ThuLaoChuyen" class="form-control"
                            placeholder="Thù lao chuyến">
                    </div>
                    <div class="col-md-2 d-grid mt-2">
                        <button type="submit" name="add" class="btn btn-success">Thêm chuyến</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-warning">
                            <tr>
                                <th>Mã chuyến</th>
                                <th>Tuyến</th>
                                <th>Xe</th>
                                <th>Ngày khởi hành</th>
                                <th>Trạng thái</th>
                                <th>Chi phí vận hành</th>
                                <th>Thù lao chuyến</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chuyen as $c): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $c['MaChuyen']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaChuyen" value="<?= $c['MaChuyen'] ?>"
                                                    class="form-control" readonly></td>
                                            <td>
                                                <select name="MaTuyen" class="form-select" required>
                                                    <?php foreach ($dsTuyen as $td): ?>
                                                        <option value="<?= $td['MaTuyen'] ?>" <?= $td['MaTuyen'] == $c['MaTuyen'] ? 'selected' : '' ?>>
                                                            <?= $td['MaTuyen'] ?> (<?= $td['DiemDau'] ?> - <?= $td['DiemCuoi'] ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="MaXe" class="form-select" required>
                                                    <?php foreach ($dsXe as $xe): ?>
                                                        <option value="<?= $xe['MaXe'] ?>" <?= $xe['MaXe'] == $c['MaXe'] ? 'selected' : '' ?>>
                                                            <?= $xe['BienSoXe'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="date" name="NgayKhoiHanh" value="<?= $c['NgayKhoiHanh'] ?>"
                                                    class="form-control" required></td>
<td>
    <select name="TrangThai" class="form-select" required>
        <option value="Dang dien ra" <?= $c['TrangThai']=='Dang dien ra'?'selected':'' ?>>Dang dien ra</option>
        <option value="Sap dien ra" <?= $c['TrangThai']=='Sap dien ra'?'selected':'' ?>>Sap dien ra</option>
        <option value="Hoan thanh" <?= $c['TrangThai']=='Hoan thanh'?'selected':'' ?>>Hoan thanh</option>
    </select>
</td>
                                            <td><input type="number" step="0.01" name="ChiPhiVanHanh"
                                                    value="<?= $c['ChiPhiVanHanh'] ?>" class="form-control"></td>
                                            <td><input type="number" step="0.01" name="ThuLaoChuyen"
                                                    value="<?= $c['ThuLaoChuyen'] ?>" class="form-control"></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=chuyen_xe" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $c['MaChuyen'] ?></td>
                                        <td><?= $c['MaTuyen'] ?> (<?= $c['DiemDau'] ?> - <?= $c['DiemCuoi'] ?>)</td>
                                        <td><?= $c['BienSoXe'] ?></td>
                                        <td><?= $c['NgayKhoiHanh'] ?></td>
                                        <td><?= $c['TrangThai'] ?></td>
                                        <td><?= $c['ChiPhiVanHanh'] ?></td>
                                        <td><?= $c['ThuLaoChuyen'] ?></td>
                                        <td>
                                            <a href="?page=chuyen_xe&edit=<?= $c['MaChuyen'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=chuyen_xe&delete=<?= $c['MaChuyen'] ?>"
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
                    <a href="?page=chuyen_xe" class="btn btn-secondary">Đóng</a>
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
                        <p>Bạn có muốn xóa chuyến xe có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                        <input type="hidden" name="MaChuyen" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                        <a href="?page=chuyen_xe" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>