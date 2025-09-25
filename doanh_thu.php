<?php
require 'config.php'; // $pdo phải được khởi tạo trong file này  

// Lấy tham số tháng từ GET (format YYYY-MM). Nếu không có, dùng tháng hiện tại.  
$thang = isset($_GET['thang']) && preg_match('/^\d{4}-\d{2}$/', $_GET['thang']) ? $_GET['thang'] : date('Y-m');

// Truy vấn doanh thu theo tháng (theo yêu cầu của bạn)  
$sql = "  
SELECT  
    x.MaXe,  
    x.BienSoXe,  
    DATE_FORMAT(cx.NgayKhoiHanh, '%Y-%m') AS Thang,  
    SUM(v.GiaVe) AS TongDoanhThu,  
    COUNT(DISTINCT cx.MaChuyen) AS SoChuyenThucHien,  
    COUNT(v.MaVe) AS SoVeBanDuoc  
FROM  
    VE v  
JOIN  
    CHUYEN_XE cx ON v.MaChuyen = cx.MaChuyen  
JOIN  
    XE x ON cx.MaXe = x.MaXe  
WHERE  
    DATE_FORMAT(cx.NgayKhoiHanh, '%Y-%m') = :thang  
GROUP BY  
    x.MaXe,  
    x.BienSoXe,  
    Thang  
ORDER BY  
    TongDoanhThu DESC  
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':thang', $thang);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chuẩn bị dữ liệu cho biểu đồ  
$labels = [];
$values = [];
$so_chuyen = [];
$so_ve = [];
foreach ($data as $row) {
    $labels[] = $row['BienSoXe'] . ' (' . $row['MaXe'] . ')';
    $values[] = (float) $row['TongDoanhThu'];
    $so_chuyen[] = (int) $row['SoChuyenThucHien'];
    $so_ve[] = (int) $row['SoVeBanDuoc'];
}

// Tổng doanh thu tổng thể, tổng số chuyến, tổng vé  
$tongDoanhThu = array_sum($values);
$tongChuyen = array_sum($so_chuyen);
$tongVe = array_sum($so_ve);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo Doanh Thu - Tháng <?php echo htmlspecialchars($thang); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Báo cáo Doanh Thu - Tháng <?php echo htmlspecialchars($thang); ?></h3>
            <form class="d-flex" method="GET" action="doanh_thu.php">
                <input type="month" name="thang" class="form-control form-control-sm me-2"
                    value="<?php echo htmlspecialchars($thang); ?>">
                <button class="btn btn-primary btn-sm" type="submit">Xem</button>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">Tổng Doanh Thu</h5>
                        <p class="card-text fs-4"><?php echo number_format($tongDoanhThu, 0, ',', '.'); ?> đ</p>
                        <small>Tháng: <?php echo htmlspecialchars($thang); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-dark bg-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title">Tổng Số Chuyến</h5>
                        <p class="card-text fs-4"><?php echo number_format($tongChuyen); ?></p>
                        <small>Xe thực hiện chuyến</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">Tổng Số Vé Bán</h5>
                        <p class="card-text fs-4"><?php echo number_format($tongVe); ?></p>
                        <small>Trong tháng</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong>Biểu đồ doanh thu theo xe</strong>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="90"></canvas>
            </div>
        </div>

        <!-- Bảng chi tiết -->
        <div class="card">
            <div class="card-header bg-light">
                <strong>Chi tiết theo xe</strong>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Mã Xe</th>
                            <th>Biển Số</th>
                            <th>Tháng</th>
                            <th>Tổng Doanh Thu (đ)</th>
                            <th>Số Chuyến</th>
                            <th>Số Vé Bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không có dữ liệu cho tháng này</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1;
                            foreach ($data as $row): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['MaXe']); ?></td>
                                    <td><?php echo htmlspecialchars($row['BienSoXe']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Thang']); ?></td>
                                    <td><?php echo number_format($row['TongDoanhThu'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['SoChuyenThucHien']); ?></td>
                                    <td><?php echo htmlspecialchars($row['SoVeBanDuoc']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Dữ liệu biểu đồ lấy từ PHP  
        const labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
        const dataValues = <?php echo json_encode($values); ?>;
        const secondaryValues = <?php echo json_encode($so_chuyen); ?>;
        const ctx = document.getElementById('revenueChart').getContext('2d');

        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tổng Doanh Thu (đ)',
                    data: dataValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'Số chuyến',
                    data: <?php echo json_encode($so_chuyen); ?>,
                    type: 'line',
                    backgroundColor: 'rgba(255, 159, 64, 0.4)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y1',
                }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        ticks: {
                            // Format số lớn với dấu ngăn cách
                            callback: function (value) {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        },
                        title: {
                            display: true,
                            text: 'Doanh Thu (đ)',
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Số chuyến',
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    if (context.dataset.label.includes('Doanh Thu')) {
                                        label += context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                            ".") + ' đ';
                                    } else {
                                        label += context.parsed.y;
                                    }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>