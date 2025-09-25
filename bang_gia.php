<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = '';

// ================== Thêm ==================
if (isset($_POST['add'])) {
    $MaTuyen = $_POST['MaTuyen'];
    $MaLoaiThoiDiem = $_POST['MaLoaiThoiDiem'];
    $GiaVe = $_POST['GiaVe'];

    try {
        $stmt = $pdo->prepare("INSERT INTO BANG_GIA (MaTuyen, MaLoaiThoiDiem, GiaVe) 
                               VALUES (?, ?, ?)");
        $stmt->execute([$MaTuyen, $MaLoaiThoiDiem, $GiaVe]);
        header("Location: ?page=bang_gia");
        exit();
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// ================== Sửa ==================
if (isset($_POST['edit'])) {
    $MaTuyen = $_POST['MaTuyen'];
    $MaLoaiThoiDiem = $_POST['MaLoaiThoiDiem'];
    $GiaVe = $_POST['GiaVe'];

    try {
        $stmt = $pdo->prepare("UPDATE BANG_GIA 
                               SET GiaVe = ? 
                               WHERE MaTuyen = ? AND MaLoaiThoiDiem = ?");
        $stmt->execute([$GiaVe, $MaTuyen, $MaLoaiThoiDiem]);
        header("Location: ?page=bang_gia");
        exit();
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// ================== Xóa ==================
if (isset($_POST['confirm_delete'])) {
    $MaTuyen = $_POST['MaTuyen'];
    $MaLoaiThoiDiem = $_POST['MaLoaiThoiDiem'];

    try {
        $stmt = $pdo->prepare("DELETE FROM BANG_GIA 
                               WHERE MaTuyen = ? AND MaLoaiThoiDiem = ?");
        $stmt->execute([$MaTuyen, $MaLoaiThoiDiem]);
        header("Location: ?page=bang_gia");
        exit();
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// ================== Lấy dữ liệu ==================
$tuyen_stmt = $pdo->query("SELECT * FROM TUYEN_DUONG");
$tuyen_list = $tuyen_stmt->fetchAll(PDO::FETCH_ASSOC);

$thoidiem_stmt = $pdo->query("SELECT * FROM LOAI_THOI_DIEM");
$thoidiem_list = $thoidiem_stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT BG.MaTuyen, 
               CONCAT(TD.DiemDau, ' → ', TD.DiemCuoi) AS TenTuyen,
               BG.MaLoaiThoiDiem, LTD.TenLoaiThoiDiem, BG.GiaVe
        FROM BANG_GIA BG
        JOIN TUYEN_DUONG TD ON BG.MaTuyen = TD.MaTuyen
        JOIN LOAI_THOI_DIEM LTD ON BG.MaLoaiThoiDiem = LTD.MaLoaiThoiDiem";
$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bảng giá vé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow mt-2">
            <div class="card-header bg-secondary text-white">
                <h3 class="mb-0">Danh mục Bảng giá vé</h3>
            </div>
            <div class="card-body">
                <!-- Form thêm -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <select name="MaTuyen" class="form-control" required>
                            <option value="">-- Chọn tuyến --</option>
                            <?php foreach ($tuyen_list as $row): ?>
                                <option value="<?= $row['MaTuyen'] ?>">
                                    <?= $row['MaTuyen'] . " - " . $row['DiemDau'] . " → " . $row['DiemCuoi'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="MaLoaiThoiDiem" class="form-control" required>
                            <option value="">-- Chọn thời điểm --</option>
                            <?php foreach ($thoidiem_list as $row): ?>
                                <option value="<?= $row['MaLoaiThoiDiem'] ?>">
                                    <?= $row['MaLoaiThoiDiem'] . " - " . $row['TenLoaiThoiDiem'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="GiaVe" class="form-control" placeholder="Giá vé"
                            required>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>

                <!-- Danh sách -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>Mã Tuyến</th>
                                <th>Tên Tuyến</th>
                                <th>Mã Loại Thời Điểm</th>
                                <th>Tên Loại Thời Điểm</th>
                                <th>Giá Vé</th>
                                <th width="150">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <?php if (
                                        isset($_GET['edit'])
                                        && $_GET['MaTuyen'] == $row['MaTuyen']
                                        && $_GET['MaLoaiThoiDiem'] == $row['MaLoaiThoiDiem']
                                    ): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaTuyen" value="<?= $row['MaTuyen'] ?>"
                                                    class="form-control" readonly></td>
                                            <td><?= $row['TenTuyen'] ?></td>
                                            <td><input type="text" name="MaLoaiThoiDiem" value="<?= $row['MaLoaiThoiDiem'] ?>"
                                                    class="form-control" readonly></td>
                                            <td><?= $row['TenLoaiThoiDiem'] ?></td>
                                            <td><input type="number" step="0.01" name="GiaVe" value="<?= $row['GiaVe'] ?>"
                                                    class="form-control" required></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=bang_gia" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $row['MaTuyen'] ?></td>
                                        <td><?= $row['TenTuyen'] ?></td>
                                        <td><?= $row['MaLoaiThoiDiem'] ?></td>
                                        <td><?= $row['TenLoaiThoiDiem'] ?></td>
                                        <td><?= $row['GiaVe'] ?></td>
                                        <td>
                                            <a href="?page=bang_gia&edit=1&MaTuyen=<?= $row['MaTuyen'] ?>&MaLoaiThoiDiem=<?= $row['MaLoaiThoiDiem'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=bang_gia&delete=1&MaTuyen=<?= $row['MaTuyen'] ?>&MaLoaiThoiDiem=<?= $row['MaLoaiThoiDiem'] ?>"
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

    <!-- Popup lỗi -->
    <?php if ($errorMsg): ?>
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
                        <a href="?page=bang_gia" class="btn btn-secondary">Đóng</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Popup xác nhận xóa -->
    <?php if (isset($_GET['delete'])): ?>
        <div class="modal show" tabindex="-1"
            style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Xác nhận xóa</h5>
                        </div>
                        <div class="modal-body">
                            <p>Bạn có chắc muốn xóa giá vé của tuyến
                                <strong><?= htmlspecialchars($_GET['MaTuyen']) ?></strong> - loại
                                <strong><?= htmlspecialchars($_GET['MaLoaiThoiDiem']) ?></strong>?
                            </p>
                            <input type="hidden" name="MaTuyen" value="<?= htmlspecialchars($_GET['MaTuyen']) ?>">
                            <input type="hidden" name="MaLoaiThoiDiem"
                                value="<?= htmlspecialchars($_GET['MaLoaiThoiDiem']) ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                            <a href="?page=bang_gia" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>