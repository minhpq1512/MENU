<?php
require 'config.php';  // $pdo từ config.php

// ================== Xử lý tìm kiếm ==================
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$trangThai = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
$from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

// Câu SQL chính (giữ logic gốc, thêm điều kiện động)
$sql = "
    WITH LastMaintenance AS (
        SELECT
            MaXe,
            MAX(NgayBaoDuong) AS NgayBaoDuongCuoiCung
        FROM
            LICH_SU_BAO_DUONG
        GROUP BY
            MaXe
    )
    SELECT
        x.MaXe,
        x.BienSoXe,
        lx.TenLoaiXe,
        x.SoKmTichLuy,
        COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem) AS NgayBatDauChuKy,
        DATE_ADD(
            COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
            INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
        ) AS NgayDenHanBaoDuong,
        CASE
            WHEN DATEDIFF(
                DATE_ADD(
                    COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
                    INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
                ),
                CURDATE()
            ) < 0 THEN 'Cần bảo dưỡng ngay'
            WHEN DATEDIFF(
                DATE_ADD(
                    COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
                    INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
                ),
                CURDATE()
            ) <= 30 THEN 'Sắp đến hạn bảo dưỡng'
            ELSE 'Bình thường'
        END AS TrangThaiBaoDuong
    FROM
        XE x
    JOIN
        LOAI_XE lx ON x.MaLoaiXe = lx.MaLoaiXe
    LEFT JOIN
        LastMaintenance lm ON x.MaXe = lm.MaXe
    WHERE 1=1
";

// Tạo mảng tham số động
$params = [];

// Tìm kiếm theo keyword (MaXe, BienSoXe, TenLoaiXe)
if ($keyword !== '') {
    $sql .= " AND (x.MaXe LIKE :kw OR x.BienSoXe LIKE :kw OR lx.TenLoaiXe LIKE :kw) ";
    $params[':kw'] = "%$keyword%";
}

// Lọc theo trạng thái
if ($trangThai !== '') {
    // Chỉ cho phép 3 trạng thái hợp lệ để tránh injection logic
    $allowed = ['Cần bảo dưỡng ngay', 'Sắp đến hạn bảo dưỡng', 'Bình thường'];
    if (in_array($trangThai, $allowed, true)) {
        $sql .= " AND (
            CASE
                WHEN DATEDIFF(
                    DATE_ADD(
                        COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
                        INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
                    ),
                    CURDATE()
                ) < 0 THEN 'Cần bảo dưỡng ngay'
                WHEN DATEDIFF(
                    DATE_ADD(
                        COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
                        INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
                    ),
                    CURDATE()
                ) <= 30 THEN 'Sắp đến hạn bảo dưỡng'
                ELSE 'Bình thường'
            END
        ) = :trang_thai ";
        $params[':trang_thai'] = $trangThai;
    }
}

// Lọc theo khoảng ngày bắt đầu chu kỳ (NgayBatDauChuKy)
if ($from_date !== '') {
    $sql .= " AND COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem) >= :from_date ";
    $params[':from_date'] = $from_date;
}
if ($to_date !== '') {
    $sql .= " AND COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem) <= :to_date ";
    $params[':to_date'] = $to_date;
}

$sql .= "
    ORDER BY
        FIELD(TrangThaiBaoDuong, 'Cần bảo dưỡng ngay', 'Sắp đến hạn bảo dưỡng', 'Bình thường'),
        NgayDenHanBaoDuong ASC
";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử bảo dưỡng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Danh sách bảo dưỡng xe</h3>

            </div>
            <div class="card-body">

                <!-- Form tìm kiếm nâng cao
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm mã xe, biển số, loại xe..."
                            value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>

                    <div class="col-md-2">
                        <select name="trang_thai" class="form-select">
                            <option value="">-- Trạng thái --</option>
                            <option value="Cần bảo dưỡng ngay" <?php if ($trangThai === 'Cần bảo dưỡng ngay')
                                echo 'selected'; ?>>Cần bảo dưỡng ngay
                            </option>
                            <option value="Sắp đến hạn bảo dưỡng" <?php if ($trangThai === 'Sắp đến hạn bảo dưỡng')
                                echo 'selected'; ?>>Sắp đến hạn bảo dưỡng
                            </option>
                            <option value="Bình thường" <?php if ($trangThai === 'Bình thường')
                                echo 'selected'; ?>>Bình
                                thường</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" placeholder="Từ ngày bắt đầu"
                            value="<?php echo htmlspecialchars($from_date); ?>">
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" placeholder="Đến ngày bắt đầu"
                            value="<?php echo htmlspecialchars($to_date); ?>">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-info">Tìm kiếm</button>
                    </div>
                </form> -->

                <!-- Bảng danh sách -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã Xe</th>
                                <th>Biển Số Xe</th>
                                <th>Loại Xe</th>
                                <th>Số Km Tích Lũy</th>
                                <th>Ngày Bắt Đầu Chu Kỳ</th>
                                <th>Ngày Đến Hạn</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($result) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($result as $row) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['MaXe']); ?></td>
                                        <td><?= htmlspecialchars($row['BienSoXe']); ?></td>
                                        <td><?= htmlspecialchars($row['TenLoaiXe']); ?></td>
                                        <td><?= htmlspecialchars($row['SoKmTichLuy']); ?></td>
                                        <td><?= htmlspecialchars($row['NgayBatDauChuKy']); ?></td>
                                        <td><?= htmlspecialchars($row['NgayDenHanBaoDuong']); ?></td>
                                        <td>
                                            <?php
                                            if ($row['TrangThaiBaoDuong'] == 'Cần bảo dưỡng ngay') {
                                                echo "<span class='badge bg-danger p-2'>" . htmlspecialchars($row['TrangThaiBaoDuong']) . "</span>";
                                            } elseif ($row['TrangThaiBaoDuong'] == 'Sắp đến hạn bảo dưỡng') {
                                                echo "<span class='badge bg-warning text-dark p-2'>" . htmlspecialchars($row['TrangThaiBaoDuong']) . "</span>";
                                            } else {
                                                echo "<span class='badge bg-primary p-2'>" . htmlspecialchars($row['TrangThaiBaoDuong']) . "</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php endif; ?>
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