-- Mỗi xe chạy nhiều chuyến trên một tuyến đường:
SELECT
    cx.MaXe,
    x.BienSoXe,
    cx.MaTuyen,
    td.DiemDau,
    td.DiemCuoi,
    COUNT(cx.MaChuyen) AS SoLuongChuyen
FROM
    CHUYEN_XE cx
JOIN
    XE x ON cx.MaXe = x.MaXe
JOIN
    TUYEN_DUONG td ON cx.MaTuyen = td.MaTuyen
GROUP BY
    cx.MaXe, x.BienSoXe, cx.MaTuyen, td.DiemDau, td.DiemCuoi
HAVING
    COUNT(cx.MaChuyen) > 1
ORDER BY
    SoLuongChuyen DESC;
    
SELECT * FROM CHUYEN_XE;
    
-- Mỗi chuyến xe được chỉ định 1 lái xe và 1 phụ xe cố định trong suốt hành trình:
SELECT 
    pc.MaChuyen, 
    nv.HoTen, 
    vt.TenVaiTro 
FROM 
    PHAN_CONG pc
JOIN 
    NHAN_VIEN nv ON pc.MaNV = nv.MaNV
JOIN 
    VAI_TRO vt ON pc.MaVaiTro = vt.MaVaiTro
WHERE 
    pc.MaChuyen = 'CX001';
-- Cố gắng thêm 'Lê Văn Cường' (NV003) làm LÁI XE (VT001) cho chuyến CX001
INSERT INTO PHAN_CONG (MaChuyen, MaNV, MaVaiTro) VALUES ('CX001', 'NV003', 'VT001');

-- Hien thi lai xe va luong cua ho
USE QLVTHKDD_DB;

-- BƯỚC 1: THÊM MỘT CHUYẾN XE ĐÃ HOÀN THÀNH VÀ CÓ THÙ LAO
-- Chuyến này sẽ tự động nhận mã là 'CX005' vì đã có 4 chuyến trước đó.
INSERT INTO CHUYEN_XE (MaTuyen, MaXe, NgayKhoiHanh, TrangThai, ChiPhiVanHanh, ThuLaoChuyen) VALUES
('TD001', 'XE001', '2025-10-05', 'Hoan thanh', 800000, 180000);


-- BƯỚC 2: PHÂN CÔNG LÁI XE CHO CÁC CHUYẾN ĐÃ HOÀN THÀNH
-- Việc này là cần thiết để liên kết thù lao chuyến xe với lái xe.
INSERT INTO PHAN_CONG (MaChuyen, MaNV, MaVaiTro) VALUES
('CX004', 'NV005', 'VT001'), -- Phân công lái xe 'Hoàng Văn Em' cho chuyến CX004 (Tháng 9)
('CX005', 'NV001', 'VT001'); -- Phân công lái xe 'Nguyễn Văn An' cho chuyến CX005 (Tháng 10)
SELECT
    nv.HoTen AS TenLaiXe,
    DATE_FORMAT(cx.NgayKhoiHanh, '%Y-%m') AS Thang,
    SUM(cx.ThuLaoChuyen) AS TongLuongThang,
    COUNT(cx.MaChuyen) AS SoChuyenDaChay
FROM
    NHAN_VIEN nv
JOIN
    PHAN_CONG pc ON nv.MaNV = pc.MaNV
JOIN
    VAI_TRO vt ON pc.MaVaiTro = vt.MaVaiTro
JOIN
    CHUYEN_XE cx ON pc.MaChuyen = cx.MaChuyen
WHERE
    vt.TenVaiTro = 'Lái xe'
    AND cx.ThuLaoChuyen IS NOT NULL -- Chỉ tính các chuyến có ghi nhận thù lao
    AND cx.TrangThai = 'Hoan thanh' -- Chỉ tính lương cho các chuyến đã hoàn thành
GROUP BY
    nv.MaNV,
    nv.HoTen,
    DATE_FORMAT(cx.NgayKhoiHanh, '%Y-%m')
ORDER BY
    Thang DESC,
    TongLuongThang DESC;
    
-- Bao duong xe
-- Sử dụng Common Table Expression (CTE) để lấy ngày bảo dưỡng cuối cùng của mỗi xe
WITH LastMaintenance AS (
    SELECT
        MaXe,
        MAX(NgayBaoDuong) AS NgayBaoDuongCuoiCung
    FROM
        LICH_SU_BAO_DUONG
    GROUP BY
        MaXe
)
-- Truy vấn chính để tính toán và hiển thị cảnh báo
SELECT
    x.MaXe,
    x.BienSoXe,
    lx.TenLoaiXe,
    x.SoKmTichLuy,
    -- Xác định ngày bắt đầu tính chu kỳ (ngày bảo dưỡng cuối hoặc ngày đăng kiểm)
    COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem) AS NgayBatDauChuKy,
    -- Tính toán ngày đến hạn bảo dưỡng dựa trên quy tắc nghiệp vụ
    DATE_ADD(
        COALESCE(lm.NgayBaoDuongCuoiCung, x.NgayDangKiem),
        INTERVAL (lx.SoNgayBaoDuongToiDa - FLOOR(x.SoKmTichLuy / lx.SoKmGiamNgayBaoDuong)) DAY
    ) AS NgayDenHanBaoDuong,
    -- Đưa ra cảnh báo dựa trên ngày đến hạn so với ngày hiện tại
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
ORDER BY
    -- Sắp xếp để các xe cần ưu tiên bảo dưỡng hiển thị lên đầu
    FIELD(TrangThaiBaoDuong, 'Cần bảo dưỡng ngay', 'Sắp đến hạn bảo dưỡng', 'Bình thường'),
    NgayDenHanBaoDuong ASC;
-- bao cao doanh thu theo xe
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
    -- Thay đổi '2025-09' thành tháng bạn muốn xem báo cáo
    DATE_FORMAT(cx.NgayKhoiHanh, '%Y-%m') = '2025-09'
GROUP BY
    x.MaXe,
    x.BienSoXe,
    Thang
ORDER BY
    TongDoanhThu DESC;