<?php
require 'config.php'; // phải có $pdo = new PDO(...)

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = '';

// Thêm vai trò (không nhập MaVaiTro vì trigger tự sinh)
if (isset($_POST['add'])) {
    $ten = $_POST['TenVaiTro'];
    $heso = $_POST['HeSoLuong'];
    try {
        $stmt = $pdo->prepare("INSERT INTO VAI_TRO (TenVaiTro, HeSoLuong) VALUES (?, ?)");
        $stmt->execute([$ten, $heso]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa vai trò
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaVaiTro'];
    try {
        $stmt = $pdo->prepare("DELETE FROM VAI_TRO WHERE MaVaiTro = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=vai_tro");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa vai trò
if (isset($_POST['edit'])) {
    $ma = $_POST['MaVaiTro'];
    $ten = $_POST['TenVaiTro'];
    $heso = $_POST['HeSoLuong'];
    try {
        $stmt = $pdo->prepare("UPDATE VAI_TRO SET TenVaiTro=?, HeSoLuong=? WHERE MaVaiTro=?");
        $stmt->execute([$ten, $heso, $ma]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách vai trò
try {
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT * FROM VAI_TRO WHERE TenVaiTro LIKE ?");
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM VAI_TRO");
    }
    $vai_tro = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMsg = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh mục Vai trò</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Danh mục Vai trò</h3>
            </div>
            <div class="card-body">

                <!-- Form tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="vai_tro">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên vai trò..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form>

                <!-- Form thêm (không có MaVaiTro) -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="TenVaiTro" class="form-control" placeholder="Tên vai trò" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="HeSoLuong" class="form-control" placeholder="Hệ số lương"
                            required>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>

                <!-- Bảng danh sách -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã vai trò</th>
                                <th>Tên vai trò</th>
                                <th>Hệ số lương</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vai_tro as $vt): ?>
                                <tr>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $vt['MaVaiTro']): ?>
                                        <form method="post">
                                            <td><input type="text" name="MaVaiTro" value="<?= $vt['MaVaiTro'] ?>"
                                                    class="form-control" readonly></td>
                                            <td><input type="text" name="TenVaiTro" value="<?= $vt['TenVaiTro'] ?>"
                                                    class="form-control" required></td>
                                            <td><input type="number" step="0.01" name="HeSoLuong"
                                                    value="<?= $vt['HeSoLuong'] ?>" class="form-control" required></td>
                                            <td>
                                                <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                                <a href="?page=vai_tro" class="btn btn-secondary btn-sm">Hủy</a>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td><?= $vt['MaVaiTro'] ?></td>
                                        <td><?= $vt['TenVaiTro'] ?></td>
                                        <td><?= $vt['HeSoLuong'] ?></td>
                                        <td>
                                            <a href="?page=vai_tro&edit=<?= $vt['MaVaiTro'] ?>"
                                                class="btn btn-warning btn-sm">Sửa</a>
                                            <a href="?page=vai_tro&delete=<?= $vt['MaVaiTro'] ?>"
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php if (isset($_GET['delete'])): ?>
    <!-- Modal xác nhận xóa -->
    <div class="modal show" tabindex="-1"
        style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Xác nhận xóa</h5>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có muốn xóa vai trò có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                        <input type="hidden" name="MaVaiTro" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý</button>
                        <a href="?page=vai_tro" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <!-- Modal báo lỗi -->
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
                    <a href="?page=vai_tro" class="btn btn-secondary">Đóng</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>