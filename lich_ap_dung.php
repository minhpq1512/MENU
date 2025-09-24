<?php
require 'config.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = '';

// Lấy danh sách loại thời điểm (dùng cho combobox)
$stmt = $pdo->query("SELECT * FROM LOAI_THOI_DIEM ORDER BY TenLoaiThoiDiem");
$loai_thoi_diem = $stmt->fetchAll();

// Thêm lịch áp dụng
if (isset($_POST['add'])) {
    $maLoai = $_POST['MaLoaiThoiDiem'];
    $ngayBD = $_POST['NgayBatDau'];
    $ngayKT = $_POST['NgayKetThuc'];
    try {
        $stmt = $pdo->prepare("INSERT INTO LICH_AP_DUNG (MaLoaiThoiDiem, NgayBatDau, NgayKetThuc) VALUES (?, ?, ?)");
        $stmt->execute([$maLoai, $ngayBD, $ngayKT]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa lịch áp dụng
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaLich'];
    try {
        $stmt = $pdo->prepare("DELETE FROM LICH_AP_DUNG WHERE MaLich = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=lich_ap_dung");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa lịch áp dụng
if (isset($_POST['edit'])) {
    $ma = $_POST['MaLich'];
    $maLoai = $_POST['MaLoaiThoiDiem'];
    $ngayBD = $_POST['NgayBatDau'];
    $ngayKT = $_POST['NgayKetThuc'];
    try {
        $stmt = $pdo->prepare("UPDATE LICH_AP_DUNG SET MaLoaiThoiDiem=?, NgayBatDau=?, NgayKetThuc=? WHERE MaLich=?");
        $stmt->execute([$maLoai, $ngayBD, $ngayKT, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách lịch áp dụng
if ($search != '') {
    $stmt = $pdo->prepare("SELECT LAD.*, LTD.TenLoaiThoiDiem 
                           FROM LICH_AP_DUNG LAD 
                           JOIN LOAI_THOI_DIEM LTD ON LAD.MaLoaiThoiDiem = LTD.MaLoaiThoiDiem
                           WHERE LAD.MaLich LIKE ? OR LTD.TenLoaiThoiDiem LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    $lich = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT LAD.*, LTD.TenLoaiThoiDiem 
                         FROM LICH_AP_DUNG LAD 
                         JOIN LOAI_THOI_DIEM LTD ON LAD.MaLoaiThoiDiem = LTD.MaLoaiThoiDiem
                         ORDER BY LAD.NgayBatDau DESC");
    $lich = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh mục lịch áp dụng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Danh mục lịch áp dụng</h3>
            </div>
            <div class="card-body">

                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="lich_ap_dung">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo mã lịch hoặc tên loại thời điểm..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>

                <!-- Form thêm -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <select name="MaLoaiThoiDiem" class="form-select" required>
                            <option value="">-- Chọn loại thời điểm --</option>
                            <?php foreach ($loai_thoi_diem as $ltd): ?>
                                <option value="<?= $ltd['MaLoaiThoiDiem'] ?>"><?= $ltd['TenLoaiThoiDiem'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="NgayBatDau" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="NgayKetThuc" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>

                <!-- Danh sách -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã lịch</th>
                                <th>Loại thời điểm</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lich as $lc): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $lc['MaLich']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaLich" value="<?= $lc['MaLich'] ?>"
                                                    class="form-control" readonly></td>
                                            <td>
                                                <select name="MaLoaiThoiDiem" class="form-select" required>
                                                    <?php foreach ($loai_thoi_diem as $ltd): ?>
                                                        <option value="<?= $ltd['MaLoaiThoiDiem'] ?>"
                                                            <?= ($lc['MaLoaiThoiDiem'] == $ltd['MaLoaiThoiDiem']) ? 'selected' : '' ?>>
                                                            <?= $ltd['TenLoaiThoiDiem'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="date" name="NgayBatDau" value="<?= $lc['NgayBatDau'] ?>"
                                                    class="form-control" required></td>
                                            <td><input type="date" name="NgayKetThuc" value="<?= $lc['NgayKetThuc'] ?>"
                                                    class="form-control" required></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=lich_ap_dung" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $lc['MaLich'] ?></td>
                                        <td><?= $lc['TenLoaiThoiDiem'] ?></td>
                                        <td><?= $lc['NgayBatDau'] ?></td>
                                        <td><?= $lc['NgayKetThuc'] ?></td>
                                        <td>
                                            <a href="?page=lich_ap_dung&edit=<?= $lc['MaLich'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=lich_ap_dung&delete=<?= $lc['MaLich'] ?>"
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
                            <p>Bạn có muốn xóa lịch có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                            <input type="hidden" name="MaLich" value="<?= htmlspecialchars($_GET['delete']) ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                            <a href="?page=lich_ap_dung" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Popup báo lỗi -->
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
                        <a href="?page=lich_ap_dung" class="btn btn-secondary">Đóng</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>