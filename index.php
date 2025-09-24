<?php
$page = isset($_GET['page']) ? $_GET['page'] : '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý vận tải</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .menu {
            width: 250px;
            float: left;
            background: #2c3e50;
            min-height: 100vh;
            color: #fff;
        }

        .menu h2 {
            text-align: center;
            padding: 15px 0;
            background: #1abc9c;
            margin: 0;
        }

        .menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu ul li {
            border-bottom: 1px solid #34495e;
        }

        .menu ul li button {
            width: 100%;
            background: none;
            border: none;
            color: #ecf0f1;
            text-align: left;
            padding: 10px 15px;
            font-size: 15px;
            cursor: pointer;
        }

        .menu ul li button:hover {
            background: #16a085;
        }

        .submenu {
            display: none;
            background: #34495e;
        }

        .submenu a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 30px;
        }

        .submenu a:hover {
            background: #1abc9c;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        h3 {
            color: #2c3e50;
        }
    </style>
    <script>
        // Chỉ ẩn/hiện submenu khi click vào menu mẹ
        function toggleMenu(id) {
            var submenu = document.getElementById(id);
            // Toggle submenu khi click vào menu mẹ
            submenu.style.display = (submenu.style.display === "block") ? "none" : "block";
        }
    </script>
</head>

<body>
    <div class="menu">
        <h2>Quản lý vận tải</h2>
        <ul>
            <li>
                <button onclick="toggleMenu('submenu1')">Thông tin xe</button>
                <ul class="submenu" id="submenu1">
                    <li><a href="?page=loai_xe">Danh mục loại xe</a></li>
                    <li><a href="?page=danh_muc_xe">Danh mục xe</a></li>
                </ul>
            </li>
            <li>
                <button onclick="toggleMenu('submenu2')">Nhân sự</button>
                <ul class="submenu" id="submenu2">
                    <li><a href="?page=nhan_vien">Danh mục nhân viên</a></li>
                    <li><a href="?page=vai_tro">Vai Trò</a></li>
                    <li><a href="?page=phan_cong">Lịch phân công</a></li>
                    <li><a href="?page=loai_thoi_diem">Danh mục ngày nghỉ</a></li>
                    <li><a href="?page=lich_ap_dung">Lịch Áp dụng</a></li>
                </ul>
            </li>
            <li>
                <button onclick="toggleMenu('submenu3')">Di chuyển</button>
                <ul class="submenu" id="submenu3">
                    <li><a href="?page=tuyen_duong">Danh mục tuyến đường</a></li>
                    <li><a href="?page=hanh_khach">Danh mục hành khách</a></li>
                    <li><a href="?page=bang_gia">Bảng giá</a></li>
                    <li><a href="?page=chuyen_xe">Quản lý chuyến xe</a></li>
                    <li><a href="?page=ban_ve">Bán vé</a></li>
                </ul>
            </li>
            <li>
                <button onclick="toggleMenu('submenu4')">Báo cáo</button>
                <ul class="submenu" id="submenu4">
                    <li><a href="?page=doanh_thu">Báo cáo doanh thu</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="content">
        <?php
        switch ($page) {
            case 'loai_xe':
                include 'loai_xe.php';
                break;
            case 'danh_muc_xe':
                include 'xe.php';
                break;
            case 'nhan_vien':
                include 'nhan_vien.php';
                break;
            case 'phan_cong':
                echo "<h3>Lịch phân công</h3><p>Nội dung lịch phân công...</p>";
                break;
            case 'vai_tro':
                include 'vai_tro.php';
                break;
            case 'loai_thoi_diem':
                include 'loai_thoi_diem.php';
                break;
            case 'lich_ap_dung':
                include 'lich_ap_dung.php';
                break;
            case 'tuyen_duong':
                include 'tuyen_duong.php';
                break;
            case 'hanh_khach':
                echo "<h3>Danh mục hành khách</h3><p>Nội dung hành khách...</p>";
                break;
            case 'bang_gia':
                include 'bang_gia.php';
                break;
            case 'chuyen_xe':
                include 'chuyen_xe.php';
                break;
            case 'ban_ve':
                echo "<h3>Bán vé</h3><p>Nội dung bán vé...</p>";
                break;
            case 'doanh_thu':
                echo "<h3>Báo cáo doanh thu</h3><p>Nội dung báo cáo doanh thu...</p>";
                break;
            default:
                echo "<h3>Chào mừng đến hệ thống quản lý vận tải</h3><p>Chọn menu bên trái để thao tác.</p>";
        }
        ?>
    </div>
</body>

</html>