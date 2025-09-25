<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$errorMsg = '';

// Thêm phân công
if (isset($_POST['add'])) {
    $maChuyen = $_POST['MaChuyen'];
    $maNV     = $_POST['MaNV'];
    $maVaiTro = $_POST['MaVaiTro'];
    try {
        $stmt = $pdo->prepare("INSERT INTO PHAN_CONG (MaChuyen, MaNV, MaVaiTro) VALUES (?, ?, ?)");
        $stmt->execute([$maChuyen, $maNV, $maVaiTro]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa phân công
if (isset($_POST['confirm_delete'])) {
    $maChuyen = $_POST['MaChuyen'];
    $maNV     = $_POST['MaNV'];
    $maVaiTro = $_POST['MaVaiTro'];
    try {
        $stmt = $pdo->prepare("DELETE FROM PHAN_CONG WHERE MaChuyen=? AND MaNV=? AND MaVaiTro=?");
        $stmt->execute([$maChuyen, $maNV, $maVaiTro]);
        header("Location: ?page=phan_cong");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa phân công
if (isset($_POST['edit'])) {
    $oldChuyen = $_POST['OldMaChuyen'];
    $oldNV     = $_POST['OldMaNV'];
    $oldVaiTro = $_POST['OldMaVaiTro'];

    $maChuyen = $_POST['MaChuyen'];
    $maNV     = $_POST['MaNV'];
    $maVaiTro = $_POST['MaVaiTro'];

    try {
        $stmt = $pdo->prepare("UPDATE PHAN_CONG 
                               SET MaChuyen=?, MaNV=?, MaVaiTro=?
                               WHERE MaChuyen=? AND MaNV=? AND MaVaiTro=?");
        $stmt->execute([$maChuyen, $maNV, $maVaiTro, $oldChuyen, $oldNV, $oldVaiTro]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách phân công (join để có tên NV + vai trò)
if ($search != '') {
    $stmt = $pdo->prepare("
        SELECT pc.*, nv.HoTen, vt.TenVaiTro, cx.MaChuyen 
        FROM PHAN_CONG pc
        JOIN NHAN_VIEN nv ON pc.MaNV = nv.MaNV
        JOIN VAI_TRO vt ON pc.MaVaiTro = vt.MaVaiTro
        JOIN CHUYEN_XE cx ON pc.MaChuyen = cx.MaChuyen
        WHERE nv.HoTen LIKE ? OR vt.TenVaiTro LIKE ? OR cx.MaChuyen LIKE ?");
    $stmt->execute(['%'.$search.'%', '%'.$search.'%', '%'.$search.'%']);
    $phancong = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT pc.*, nv.HoTen, vt.TenVaiTro, cx.MaChuyen 
        FROM PHAN_CONG pc
        JOIN NHAN_VIEN nv ON pc.MaNV = nv.MaNV
        JOIN VAI_TRO vt ON pc.MaVaiTro = vt.MaVaiTro
        JOIN CHUYEN_XE cx ON pc.MaChuyen = cx.MaChuyen");
    $phancong = $stmt->fetchAll();
}

// Lấy dữ liệu cho dropdown
$dsNV      = $pdo->query("SELECT MaNV, HoTen FROM NHAN_VIEN")->fetchAll();
$dsVaiTro  = $pdo->query("SELECT MaVaiTro, TenVaiTro FROM VAI_TRO")->fetchAll();
$dsChuyen  = $pdo->query("SELECT MaChuyen FROM CHUYEN_XE")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phân công nhân viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow mt-2">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Phân công nhân viên</h3>
            </div>
            <div class="card-body">
                <!-- Vùng tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="phan_cong">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo mã chuyến, tên nhân viên hoặc vai trò..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>

                <!-- Form thêm -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <select name="MaChuyen" class="form-select" required>
                            <option value="">--Chọn chuyến xe--</option>
                            <?php foreach ($dsChuyen as $c): ?>
                            <option value="<?= $c['MaChuyen'] ?>"><?= $c['MaChuyen'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="MaNV" class="form-select" required>
                            <option value="">--Chọn nhân viên--</option>
                            <?php foreach ($dsNV as $nv): ?>
                            <option value="<?= $nv['MaNV'] ?>"><?= $nv['HoTen'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="MaVaiTro" class="form-select" required>
                            <option value="">--Chọn vai trò--</option>
                            <?php foreach ($dsVaiTro as $vt): ?>
                            <option value="<?= $vt['MaVaiTro'] ?>"><?= $vt['TenVaiTro'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm phân công</button>
                    </div>
                </form>

                <!-- Bảng -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã chuyến</th>
                                <th>Nhân viên</th>
                                <th>Vai trò</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phancong as $pc): ?>
                            <tr>
                                <?php if (isset($_GET['edit']) 
                                && $_GET['edit'] == $pc['MaChuyen'].'_'.$pc['MaNV'].'_'.$pc['MaVaiTro']): ?>
                                <form method="post">
                                    <input type="hidden" name="OldMaChuyen" value="<?= $pc['MaChuyen'] ?>">
                                    <input type="hidden" name="OldMaNV" value="<?= $pc['MaNV'] ?>">
                                    <input type="hidden" name="OldMaVaiTro" value="<?= $pc['MaVaiTro'] ?>">

                                    <td>
                                        <select name="MaChuyen" class="form-select" required>
                                            <?php foreach ($dsChuyen as $c): ?>
                                            <option value="<?= $c['MaChuyen'] ?>"
                                                <?= $c['MaChuyen']==$pc['MaChuyen']?'selected':'' ?>>
                                                <?= $c['MaChuyen'] ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="MaNV" class="form-select" required>
                                            <?php foreach ($dsNV as $nv): ?>
                                            <option value="<?= $nv['MaNV'] ?>"
                                                <?= $nv['MaNV']==$pc['MaNV']?'selected':'' ?>>
                                                <?= $nv['HoTen'] ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="MaVaiTro" class="form-select" required>
                                            <?php foreach ($dsVaiTro as $vt): ?>
                                            <option value="<?= $vt['MaVaiTro'] ?>"
                                                <?= $vt['MaVaiTro']==$pc['MaVaiTro']?'selected':'' ?>>
                                                <?= $vt['TenVaiTro'] ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                        <a href="?page=phan_cong" class="btn btn-secondary btn-sm">Hủy</a>
                                    </td>
                                </form>
                                <?php else: ?>
                                <td><?= $pc['MaChuyen'] ?></td>
                                <td><?= $pc['HoTen'] ?></td>
                                <td><?= $pc['TenVaiTro'] ?></td>
                                <td>
                                    <a href="?page=phan_cong&edit=<?= $pc['MaChuyen'].'_'.$pc['MaNV'].'_'.$pc['MaVaiTro'] ?>"
                                        class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="?page=phan_cong&delete=<?= $pc['MaChuyen'].'_'.$pc['MaNV'].'_'.$pc['MaVaiTro'] ?>"
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
    style="display:block; background:rgba(0,0,0,0.5);position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Lỗi</h5>
            </div>
            <div class="modal-body">
                <p><?= htmlspecialchars($errorMsg) ?></p>
            </div>
            <div class="modal-footer">
                <a href="?page=phan_cong" class="btn btn-secondary">Đóng</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['delete'])): ?>
<!-- Popup xác nhận xóa -->
<div class="modal show" tabindex="-1"
    style="display:block; background:rgba(0,0,0,0.5);position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xác nhận xóa</h5>
                </div>
                <div class="modal-body">
                    <p>Bạn có muốn xóa phân công này?</p>
                    <?php
                        $parts = explode('_', $_GET['delete']);
                    ?>
                    <input type="hidden" name="MaChuyen" value="<?= htmlspecialchars($parts[0]) ?>">
                    <input type="hidden" name="MaNV" value="<?= htmlspecialchars($parts[1]) ?>">
                    <input type="hidden" name="MaVaiTro" value="<?= htmlspecialchars($parts[2]) ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                    <a href="?page=phan_cong" class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>