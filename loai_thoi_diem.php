<?php
require 'config.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = '';

// Thêm loại thời điểm
if (isset($_POST['add'])) {
    $ten = $_POST['TenLoaiThoiDiem'];
    try {
        $stmt = $pdo->prepare("INSERT INTO LOAI_THOI_DIEM (TenLoaiThoiDiem) VALUES (?)");
        $stmt->execute([$ten]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa loại thời điểm
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaLoaiThoiDiem'];
    try {
        $stmt = $pdo->prepare("DELETE FROM LOAI_THOI_DIEM WHERE MaLoaiThoiDiem = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=loai_thoi_diem");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa loại thời điểm
if (isset($_POST['edit'])) {
    $ma = $_POST['MaLoaiThoiDiem'];
    $ten = $_POST['TenLoaiThoiDiem'];
    try {
        $stmt = $pdo->prepare("UPDATE LOAI_THOI_DIEM SET TenLoaiThoiDiem=? WHERE MaLoaiThoiDiem=?");
        $stmt->execute([$ten, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách loại thời điểm
if ($search != '') {
    $stmt = $pdo->prepare("SELECT * FROM LOAI_THOI_DIEM WHERE TenLoaiThoiDiem LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $loai_td = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM LOAI_THOI_DIEM");
    $loai_td = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh mục loại thời điểm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Danh mục loại thời điểm</h3>
            </div>
            <div class="card-body">

                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="loai_thoi_diem">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo tên loại thời điểm..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>

                <!-- Form thêm -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="TenLoaiThoiDiem" class="form-control" placeholder="Tên loại thời điểm"
                            required>
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
                                <th>Mã loại thời điểm</th>
                                <th>Tên loại thời điểm</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loai_td as $td): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $td['MaLoaiThoiDiem']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaLoaiThoiDiem" value="<?= $td['MaLoaiThoiDiem'] ?>"
                                                    class="form-control" readonly></td>
                                            <td><input type="text" name="TenLoaiThoiDiem" value="<?= $td['TenLoaiThoiDiem'] ?>"
                                                    class="form-control" required></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=loai_thoi_diem" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $td['MaLoaiThoiDiem'] ?></td>
                                        <td><?= $td['TenLoaiThoiDiem'] ?></td>
                                        <td>
                                            <a href="?page=loai_thoi_diem&edit=<?= $td['MaLoaiThoiDiem'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=loai_thoi_diem&delete=<?= $td['MaLoaiThoiDiem'] ?>"
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
                            <p>Bạn có muốn xóa loại thời điểm có mã
                                <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?
                            </p>
                            <input type="hidden" name="MaLoaiThoiDiem" value="<?= htmlspecialchars($_GET['delete']) ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                            <a href="?page=loai_thoi_diem" class="btn btn-secondary">Hủy</a>
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
                        <a href="?page=loai_thoi_diem" class="btn btn-secondary">Đóng</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>