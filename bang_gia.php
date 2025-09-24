<?php
include 'config.php';


// Xử lý thêm
if (isset($_POST['add'])) {
    $MaTuyen = $_POST['MaTuyen'];
    $MaLoaiThoiDiem = $_POST['MaLoaiThoiDiem'];
    $GiaVe = $_POST['GiaVe'];

    $sql = "INSERT INTO BANG_GIA (MaTuyen, MaLoaiThoiDiem, GiaVe) 
            VALUES ('$MaTuyen', '$MaLoaiThoiDiem', '$GiaVe')";
    mysqli_query($conn, $sql);
    header("Location: ?page=bang_gia");
    exit();
}

// Xử lý sửa
if (isset($_POST['edit'])) {
    $MaTuyen = $_POST['MaTuyen'];
    $MaLoaiThoiDiem = $_POST['MaLoaiThoiDiem'];
    $GiaVe = $_POST['GiaVe'];

    $sql = "UPDATE BANG_GIA 
            SET GiaVe='$GiaVe' 
            WHERE MaTuyen='$MaTuyen' AND MaLoaiThoiDiem='$MaLoaiThoiDiem'";
    mysqli_query($conn, $sql);
    header("Location: ?page=bang_gia");
    exit();
}

// Xử lý xóa
if (isset($_GET['delete'])) {
    $MaTuyen = $_GET['MaTuyen'];
    $MaLoaiThoiDiem = $_GET['MaLoaiThoiDiem'];
    $sql = "DELETE FROM BANG_GIA 
            WHERE MaTuyen='$MaTuyen' AND MaLoaiThoiDiem='$MaLoaiThoiDiem'";
    mysqli_query($conn, $sql);
    header("Location: ?page=bang_gia");
    exit();
}

// Lấy dữ liệu tuyến đường
$tuyen_query = mysqli_query($conn, "SELECT * FROM TUYEN_DUONG");

// Lấy dữ liệu loại thời điểm
$thoidiem_query = mysqli_query($conn, "SELECT * FROM LOAI_THOI_DIEM");

// Lấy dữ liệu bảng giá (join để hiển thị tên dễ đọc)
$sql = "SELECT bg.MaTuyen, td.TenTuyen, bg.MaLoaiThoiDiem, ltd.TenLoaiThoiDiem, bg.GiaVe
        FROM BANG_GIA bg
        JOIN TUYEN_DUONG td ON bg.MaTuyen = td.MaTuyen
        JOIN LOAI_THOI_DIEM ltd ON bg.MaLoaiThoiDiem = ltd.MaLoaiThoiDiem";
$result = mysqli_query($conn, $sql);
?>

<h2>Quản lý Bảng Giá</h2>

<form method="POST">
    <label>Tuyến đường:</label>
    <select name="MaTuyen" required>
        <?php while ($row = mysqli_fetch_assoc($tuyen_query)) { ?>
            <option value="<?php echo $row['MaTuyen']; ?>"><?php echo $row['TenTuyen']; ?></option>
        <?php } ?>
    </select><br>

    <label>Loại thời điểm:</label>
    <select name="MaLoaiThoiDiem" required>
        <?php
        mysqli_data_seek($thoidiem_query, 0); // reset pointer
        while ($row = mysqli_fetch_assoc($thoidiem_query)) { ?>
            <option value="<?php echo $row['MaLoaiThoiDiem']; ?>"><?php echo $row['TenLoaiThoiDiem']; ?></option>
        <?php } ?>
    </select><br>

    <label>Giá vé:</label>
    <input type="number" step="0.01" name="GiaVe" required><br>

    <button type="submit" name="add">Thêm</button>
</form>

<h3>Danh sách Bảng Giá</h3>
<table boder="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Mã Tuyến</th>
        <th>Tên Tuyến</th>
        <th>Mã Loại Thời Điểm</th>
        <th>Tên Loại Thời Điểm</th>
        <th>Giá Vé</th>
        <th>Hành động</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['MaTuyen']; ?></td>
            <td><?php echo $row['TenTuyen']; ?></td>
            <td><?php echo $row['MaLoaiThoiDiem']; ?></td>
            <td><?php echo $row['TenLoaiThoiDiem']; ?></td>
            <td><?php echo $row['GiaVe']; ?></td>
            <td>
                <!-- Form sửa -->
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="MaTuyen" value="<?php echo $row['MaTuyen']; ?>">
                    <input type="hidden" name="MaLoaiThoiDiem" value="<?php echo $row['MaLoaiThoiDiem']; ?>">
                    <input type="number" step="0.01" name="GiaVe" value="<?php echo $row['GiaVe']; ?>">
                    <button type="submit" name="edit">Sửa</button>
                </form>
                <!-- Xóa -->
                <a href="?page=bang_gia&delete=1&MaTuyen=<?php echo $row['MaTuyen']; ?>&MaLoaiThoiDiem=<?php echo $row['MaLoaiThoiDiem']; ?>"
                    onclick="return confirm('Xóa giá vé này?')">Xóa</a>
            </td>
        </tr>
    <?php } ?>
</table>