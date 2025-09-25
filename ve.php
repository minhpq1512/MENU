<?php
require 'config.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errorMsg = '';

// Cấu hình phân trang
$records_per_page = 5; // Thay đổi từ 50 xuống 25
$current_page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
$current_page = max(1, $current_page); // Đảm bảo trang >= 1
$offset = ($current_page - 1) * $records_per_page;

// Lấy danh sách chuyến xe (dùng cho combobox)
$stmt = $pdo->query("SELECT * FROM CHUYEN_XE ORDER BY MaChuyen");
$chuyen_xe = $stmt->fetchAll();

// Thêm vé
if (isset($_POST['add'])) {
    $maChuyen = $_POST['MaChuyen'];
    $giaVe = $_POST['GiaVe'];
    try {
        $stmt = $pdo->prepare("INSERT INTO VE (MaChuyen, GiaVe) VALUES (?, ?)");
        $stmt->execute([$maChuyen, $giaVe]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Xóa vé
if (isset($_POST['confirm_delete'])) {
    $ma = $_POST['MaVe'];
    try {
        $stmt = $pdo->prepare("DELETE FROM VE WHERE MaVe = ?");
        $stmt->execute([$ma]);
        header("Location: ?page=ve");
        exit;
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Sửa vé
if (isset($_POST['edit'])) {
    $maVe = $_POST['MaVe'];
    $maChuyen = $_POST['MaChuyen'];
    $giaVe = $_POST['GiaVe'];
    try {
        $stmt = $pdo->prepare("UPDATE VE SET MaChuyen=?, GiaVe=? WHERE MaVe=?");
        $stmt->execute([$maChuyen, $giaVe, $maVe]);
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
    }
}

// Lấy danh sách vé với phân trang
if ($search != '') {
    // Đếm tổng số bản ghi tìm kiếm
    $count_stmt = $pdo->prepare("SELECT COUNT(*) 
                                FROM VE 
                                JOIN CHUYEN_XE CX ON VE.MaChuyen = CX.MaChuyen
                                WHERE VE.MaVe LIKE ? OR VE.MaChuyen LIKE ?");
    $count_stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    $total_records = $count_stmt->fetchColumn();
    
    // Lấy dữ liệu với LIMIT
    $stmt = $pdo->prepare("SELECT VE.*, CX.NgayKhoiHanh 
                           FROM VE 
                           JOIN CHUYEN_XE CX ON VE.MaChuyen = CX.MaChuyen
                           WHERE VE.MaVe LIKE ? OR VE.MaChuyen LIKE ?
                           ORDER BY VE.MaVe ASC
                           LIMIT ? OFFSET ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', $records_per_page, $offset]);
    $ve = $stmt->fetchAll();
} else {
    // Đếm tổng số bản ghi
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM VE");
    $total_records = $count_stmt->fetchColumn();
    
    // Lấy dữ liệu với LIMIT
    $stmt = $pdo->prepare("SELECT VE.*, CX.NgayKhoiHanh 
                          FROM VE 
                          JOIN CHUYEN_XE CX ON VE.MaChuyen = CX.MaChuyen
                          ORDER BY VE.MaVe ASC
                          LIMIT ? OFFSET ?");
    $stmt->execute([$records_per_page, $offset]);
    $ve = $stmt->fetchAll();
}

// Tính tổng số trang
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý vé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Danh mục vé</h3>
            </div>
            <div class="card-body">

                <!-- Vùng nhập tìm kiếm -->
                <form method="get" class="row g-2 mb-4">
                    <input type="hidden" name="page" value="ve">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo mã vé hoặc mã chuyến..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-end">
                            <span class="text-muted">
                                Hiển thị <?= min($offset + 1, $total_records) ?> -
                                <?= min($offset + $records_per_page, $total_records) ?>
                                trong tổng số <?= $total_records ?> bản ghi (<?= $records_per_page ?> bản ghi/trang)
                            </span>
                        </div>
                    </div>
                </form>

                <!-- Form thêm -->
                <form method="post" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <select name="MaChuyen" class="form-select" required>
                            <option value="">-- Chọn chuyến xe --</option>
                            <?php foreach ($chuyen_xe as $cx): ?>
                            <option value="<?= $cx['MaChuyen'] ?>">
                                <?= $cx['MaChuyen'] ?> (<?= $cx['NgayKhoiHanh'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="GiaVe" class="form-control" step="0.01" placeholder="Giá vé"
                            required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" name="add" class="btn btn-success">Thêm vé</button>
                    </div>
                </form>

                <!-- Danh sách -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã vé</th>
                                <th>Mã chuyến</th>
                                <th>Ngày khởi hành</th>
                                <th>Giá vé (VND)</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ve)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    <?php if ($search): ?>
                                    Không tìm thấy vé nào với từ khóa
                                    "<strong><?= htmlspecialchars($search) ?></strong>"
                                    <?php else: ?>
                                    Chưa có vé nào được tạo
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($ve as $index => $v): ?>
                            <tr>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $v['MaVe']): ?>
                                <form method="post">
                                    <td>
                                        <input type="text" name="MaVe" value="<?= $v['MaVe'] ?>" class="form-control"
                                            readonly>
                                        <small class="text-muted">Mã tự động</small>
                                    </td>
                                    <td>
                                        <select name="MaChuyen" class="form-select" required>
                                            <?php foreach ($chuyen_xe as $cx): ?>
                                            <option value="<?= $cx['MaChuyen'] ?>"
                                                <?= ($v['MaChuyen'] == $cx['MaChuyen']) ? 'selected' : '' ?>>
                                                <?= $cx['MaChuyen'] ?> (<?= $cx['NgayKhoiHanh'] ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><?= $v['NgayKhoiHanh'] ?></td>
                                    <td>
                                        <input type="number" name="GiaVe" value="<?= $v['GiaVe'] ?>"
                                            class="form-control" step="0.01" required>
                                    </td>
                                    <td>
                                        <button type="submit" name="edit" class="btn btn-primary btn-sm">Lưu</button>
                                        <a href="<?= buildPaginationUrl($current_page, $search) ?>"
                                            class="btn btn-secondary btn-sm">Hủy</a>
                                    </td>
                                </form>
                                <?php else: ?>
                                <td>
                                    <strong><?= $v['MaVe'] ?></strong>
                                    <small class="text-muted d-block">#<?= $offset + $index + 1 ?></small>
                                </td>
                                <td><?= $v['MaChuyen'] ?></td>
                                <td><?= $v['NgayKhoiHanh'] ?></td>
                                <td><strong><?= number_format($v['GiaVe'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <a href="<?= buildPaginationUrl($current_page, $search) ?>&edit=<?= $v['MaVe'] ?>"
                                        class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="<?= buildPaginationUrl($current_page, $search) ?>&delete=<?= $v['MaVe'] ?>"
                                        class="btn btn-danger btn-sm">Xóa</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thông tin tổng quan và Phân trang -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>Trang hiện tại:</strong> <?= $current_page ?>/<?= max(1, $total_pages) ?>
                            | <strong>Tổng số vé:</strong> <?= $total_records ?> vé
                            | <strong>Bản ghi/trang:</strong> <?= $records_per_page ?>
                            <?php if (!empty($ve)): ?>
                            <br><strong>Doanh thu trang này:</strong>
                            <?= number_format(array_sum(array_column($ve, 'GiaVe')), 0, ',', '.') ?> VND
                            <?php endif; ?>
                        </div>

                        <!-- DEBUG INFO - Xóa sau khi test -->
                        <div class="alert alert-warning">
                            <small>
                                <strong>Debug:</strong>
                                Total Records: <?= $total_records ?> |
                                Total Pages: <?= $total_pages ?> |
                                Current Page: <?= $current_page ?> |
                                Offset: <?= $offset ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- PHÂN TRANG - Luôn hiển thị khi có > 50 bản ghi -->
                        <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-end">
                            <nav aria-label="Phân trang" class="mb-3">
                                <ul class="pagination">
                                    <!-- Trang đầu -->
                                    <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl(1, $search) ?>"
                                            title="Trang đầu">
                                            &laquo;&laquo;
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="<?= buildPaginationUrl($current_page - 1, $search) ?>"
                                            title="Trang trước">
                                            &laquo;
                                        </a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;&laquo;</span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;</span>
                                    </li>
                                    <?php endif; ?>

                                    <!-- Các trang xung quanh -->
                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);
                                    
                                    // Hiển thị ít nhất 5 trang nếu có thể
                                    if ($end_page - $start_page < 4) {
                                        if ($start_page == 1) {
                                            $end_page = min($total_pages, $start_page + 4);
                                        } else {
                                            $start_page = max(1, $end_page - 4);
                                        }
                                    }
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl($i, $search) ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>

                                    <!-- Trang cuối -->
                                    <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="<?= buildPaginationUrl($current_page + 1, $search) ?>"
                                            title="Trang sau">
                                            &raquo;
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl($total_pages, $search) ?>"
                                            title="Trang cuối">
                                            &raquo;&raquo;
                                        </a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">&raquo;</span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">&raquo;&raquo;</span>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php else: ?>
                        <!-- Hiển thị khi chỉ có 1 trang -->
                        <div class="d-flex justify-content-end">
                            <div class="alert alert-light mb-0">
                                <small class="text-muted">Chỉ có 1 trang dữ liệu</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- PHÂN TRANG DƯ PHÒNG - Nếu phần trên không hiện -->
                <?php if ($total_records > $records_per_page): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center">
                            <nav aria-label="Pagination">
                                <ul class="pagination pagination-lg">
                                    <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl(1, $search) ?>">Trang đầu</a>
                                    </li>
                                    <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="<?= buildPaginationUrl(max(1, $current_page - 1), $search) ?>">Trước</a>
                                    </li>

                                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl($i, $search) ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="<?= buildPaginationUrl(min($total_pages, $current_page + 1), $search) ?>">Sau</a>
                                    </li>
                                    <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="<?= buildPaginationUrl($total_pages, $search) ?>">Trang cuối</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>

                        <!-- Thông tin bổ sung -->
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Chuyển nhanh đến trang:
                                <select onchange="location.href=this.value" class="form-select d-inline-block"
                                    style="width: auto;">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <option value="<?= buildPaginationUrl($i, $search) ?>"
                                        <?= $i == $current_page ? 'selected' : '' ?>>
                                        Trang <?= $i ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Hàm tạo URL phân trang - Phiên bản tuyệt đối
                function buildPaginationUrl($page, $search = '') {
                    $base_url = $_SERVER['PHP_SELF']; // Lấy đường dẫn hiện tại
                    
                    $params = [
                        'page' => 've',
                        'pg' => $page
                    ];
                    
                    if (!empty($search)) {
                        $params['search'] = $search;
                    }
                    
                    return $base_url . '?' . http_build_query($params);
                }

                // DEBUG: Kiểm tra URL được tạo
                if (isset($_GET['debug'])) {
                    echo "<div class='alert alert-warning'>";
                    echo "<strong>DEBUG URLs:</strong><br>";
                    echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
                    echo "Page 2 URL: " . buildPaginationUrl(2, $search) . "<br>";
                    echo "GET params: " . print_r($_GET, true);
                    echo "</div>";
                }
                ?>

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
                        <p>Bạn có muốn xóa vé có mã <strong><?= htmlspecialchars($_GET['delete']) ?></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Lưu ý:</strong> Thao tác này không thể hoàn tác!
                        </div>
                        <input type="hidden" name="MaVe" value="<?= htmlspecialchars($_GET['delete']) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Đồng ý xóa</button>
                        <a href="<?= buildPaginationUrl($current_page, $search) ?>" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Popup báo lỗi -->
    <?php if ($errorMsg): ?>
    <div class="modal show" tabindex="-1" id="errorModal"
        style="display:block; background:rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Lỗi</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeErrorModal()"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Chi tiết lỗi:</strong><br>
                        <?= htmlspecialchars($errorMsg) ?>
                    </div>
                    <?php if (strpos($errorMsg, 'Giá vé phải lớn hơn 0') !== false): ?>
                    <div class="alert alert-info">
                        <strong>Gợi ý:</strong> Vui lòng nhập giá vé lớn hơn 0 VND.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeErrorModal()">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Hàm đóng modal lỗi mà không chuyển trang
    function closeErrorModal() {
        const errorModal = document.getElementById('errorModal');
        if (errorModal) {
            errorModal.style.opacity = '0';
            errorModal.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                errorModal.style.display = 'none';
            }, 300);
        }
    }

    // Đóng modal khi click bên ngoài
    document.addEventListener('click', function(event) {
        const errorModal = document.getElementById('errorModal');
        if (errorModal && event.target === errorModal) {
            closeErrorModal();
        }
    });

    // Đóng modal khi nhấn phím Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const errorModal = document.getElementById('errorModal');
            if (errorModal && errorModal.style.display !== 'none') {
                closeErrorModal();
            }
        }
    });

    // Auto-hide success alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-info)');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-success')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 5000);

    // Auto-focus vào trường đầu tiên khi có lỗi
    <?php if ($errorMsg): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('form input[type="number"], form select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 500);
        }
    });
    <?php endif; ?>
    </script>
</body>

</html>