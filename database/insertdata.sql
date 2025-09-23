-- ===================================================================================
-- TỆP DỮ LIỆU MẪU CHO CƠ SỞ DỮ LIỆU QUẢN LÝ VẬN TẢI HÀNH KHÁCH
-- Dữ liệu được sắp xếp theo thứ tự để đảm bảo tính toàn vẹn của khóa ngoại.
-- Các trigger tự động sinh mã (MaLoaiXe, MaXe,...) sẽ được kích hoạt.
-- ===================================================================================

USE QLVTHKDD_DB;

-- Tạm thời vô hiệu hóa kiểm tra khóa ngoại để chèn dữ liệu hàng loạt
SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu cũ trong các bảng (theo thứ tự ngược lại của sự phụ thuộc)
TRUNCATE TABLE BANG_GIA;
TRUNCATE TABLE LICH_AP_DUNG;
TRUNCATE TABLE LOAI_THOI_DIEM;
TRUNCATE TABLE LICH_SU_BAO_DUONG;
TRUNCATE TABLE PHAN_CONG;
TRUNCATE TABLE VAI_TRO;
TRUNCATE TABLE NHAN_VIEN;
TRUNCATE TABLE VE;
TRUNCATE TABLE CHUYEN_XE;
TRUNCATE TABLE TUYEN_DUONG;
TRUNCATE TABLE XE;
TRUNCATE TABLE LOAI_XE;

-- Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;


-- ===================================================================================
-- BƯỚC 1: CHÈN DỮ LIỆU CHO CÁC BẢNG KHÔNG CÓ KHÓA NGOẠI
-- ===================================================================================

-- 1.1. Bảng LOAI_XE: Thông tin các loại xe
-- Trigger 'before_insert_loai_xe' sẽ tự động tạo MaLoaiXe (LX001, LX002,...)
INSERT INTO LOAI_XE (TenLoaiXe, SoGhe, SoNgayBaoDuongToiDa, SoKmGiamNgayBaoDuong) VALUES
('Xe khach 45 cho', 45, 120, 10),
('Xe khach 30 cho', 30, 90, 8),
('Xe khach 16 cho', 16, 60, 5),
('Xe Limousine 9 cho', 9, 45, 3);

-- 1.2. Bảng TUYEN_DUONG: Thông tin các tuyến đường
-- Trigger 'before_insert_tuyen_duong' sẽ tự động tạo MaTuyen (TD001, TD002,...)
INSERT INTO TUYEN_DUONG (DiemDau, DiemCuoi, DoDai, DoKho, DonGiaTheoKm) VALUES
('Hà Nội', 'Hải Phòng', 120.0, 1, 1000),
('Hà Nội', 'Sapa', 320.0, 3, 1500),
('TP. Hồ Chí Minh', 'Đà Lạt', 300.0, 2, 1200),
('Đà Nẵng', 'Hội An', 30.0, 1, 800);

-- 1.3. Bảng NHAN_VIEN: Thông tin nhân viên
-- Trigger 'before_insert_nhan_vien' sẽ tự động tạo MaNV (NV001, NV002,...)
INSERT INTO NHAN_VIEN (HoTen, SDT) VALUES
('Nguyễn Văn An', '0912345678'),
('Trần Thị Bình', '0987654321'),
('Lê Văn Cường', '0905112233'),
('Phạm Thị Dung', '0934556677'),
('Hoàng Văn Em', '0978123456');

-- 1.4. Bảng VAI_TRO: Các vai trò của nhân viên
-- Trigger 'before_insert_vai_tro' sẽ tự động tạo MaVaiTro (VT001, VT002,...)
INSERT INTO VAI_TRO (TenVaiTro, HeSoLuong) VALUES
('Lái xe', 2.5),
('Phụ xe', 1.8);

-- 1.5. Bảng LOAI_THOI_DIEM: Các loại thời điểm áp dụng giá vé khác nhau
-- Trigger 'before_insert_loai_thoi_diem' sẽ tự động tạo MaLoaiThoiDiem (LT001, LT002,...)
INSERT INTO LOAI_THOI_DIEM (TenLoaiThoiDiem) VALUES
('Ngày thường'),
('Lễ tết'),
('Cao điểm hè');

-- ===================================================================================
-- BƯỚC 2: CHÈN DỮ LIỆU CHO CÁC BẢNG PHỤ THUỘC (CẤP 1)
-- ===================================================================================

-- 2.1. Bảng XE: Thông tin xe, phụ thuộc vào LOAI_XE
-- Trigger 'before_insert_xe' sẽ tự động tạo MaXe (XE001, XE002,...)
-- MaLoaiXe 'LX001', 'LX002',... được tạo tự động ở bước 1.1
INSERT INTO XE (MaLoaiXe, BienSoXe, SoKmTichLuy, NgayDangKiem) VALUES
('LX001', '29B-123.45', 5000, '2026-01-15'),
('LX001', '30A-543.21', 2000, '2025-11-20'),
('LX002', '51F-987.65', 10000, '2025-09-01'),
('LX004', '92A-111.22', 1500, '2026-05-10');

-- 2.2. Bảng LICH_AP_DUNG: Lịch áp dụng cho loại thời điểm, phụ thuộc vào LOAI_THOI_DIEM
-- Trigger 'before_insert_lich_ap_dung' sẽ tự động tạo MaLich (LA001, LA002,...)
INSERT INTO LICH_AP_DUNG (MaLoaiThoiDiem, NgayBatDau, NgayKetThuc) VALUES
('LT002', '2026-01-28', '2026-02-10'), -- Lễ tết 2026
('LT003', '2025-06-01', '2025-08-15'); -- Cao điểm hè 2025

-- 2.3. Bảng BANG_GIA: Bảng giá vé, phụ thuộc vào TUYEN_DUONG và LOAI_THOI_DIEM
-- Bảng này không có trigger sinh mã tự động
INSERT INTO BANG_GIA (MaTuyen, MaLoaiThoiDiem, GiaVe) VALUES
('TD001', 'LT001', 100000), -- HN - HP ngày thường
('TD001', 'LT002', 150000), -- HN - HP lễ tết
('TD002', 'LT001', 250000), -- HN - Sapa ngày thường
('TD002', 'LT002', 350000), -- HN - Sapa lễ tết
('TD003', 'LT001', 220000), -- HCM - Đà Lạt ngày thường
('TD003', 'LT003', 280000); -- HCM - Đà Lạt cao điểm hè

-- ===================================================================================
-- BƯỚC 3: CHÈN DỮ LIỆU CHO BẢNG CHUYEN_XE
-- ===================================================================================

-- 3.1. Bảng CHUYEN_XE: Thông tin chuyến xe, phụ thuộc vào TUYEN_DUONG và XE
-- Trigger 'before_insert_chuyen_xe' sẽ tự động tạo MaChuyen (CX001, CX002,...)
INSERT INTO CHUYEN_XE (MaTuyen, MaXe, NgayKhoiHanh, TrangThai) VALUES
('TD001', 'XE001', '2025-09-25', 'Dang dien ra'),
('TD002', 'XE002', '2025-09-26', 'Sap dien ra'),
('TD003', 'XE003', '2025-09-20', 'Dang dien ra'); -- Chuyến này sẽ được dùng để test trigger update km
INSERT INTO CHUYEN_XE (MaTuyen, MaXe, NgayKhoiHanh, TrangThai, ChiPhiVanHanh, ThuLaoChuyen) VALUES
('TD004', 'XE004', '2025-09-18', 'Hoan thanh', 1000000, 200000); -- Chuyến đã hoàn thành
INSERT INTO CHUYEN_XE (MaTuyen, MaXe, NgayKhoiHanh, TrangThai, ChiPhiVanHanh, ThuLaoChuyen) VALUES
('TD004', 'XE004', '2025-09-18', 'Hoan thanh', 1000000, 200000), -- Chuyến đã hoàn thành
('TD001', 'XE001', '2025-10-05', 'Hoan thanh', 800000, 180000); -- Thêm chuyến hoàn thành để có dữ liệu lương


-- ===================================================================================
-- BƯỚC 4: CHÈN DỮ LIỆU CHO CÁC BẢNG PHỤ THUỘC CUỐI CÙNG
-- ===================================================================================

-- 4.1. Bảng PHAN_CONG: Phân công nhân viên, phụ thuộc CHUYEN_XE, NHAN_VIEN, VAI_TRO
-- Trigger 'TRIGGER_CHECK_PHAN_CONG' sẽ đảm bảo mỗi chuyến chỉ có 1 lái xe, 1 phụ xe.
INSERT INTO PHAN_CONG (MaChuyen, MaNV, MaVaiTro) VALUES
('CX001', 'NV001', 'VT001'), -- Chuyến 1: Lái xe An
('CX001', 'NV002', 'VT002'), -- Chuyến 1: Phụ xe Bình
('CX002', 'NV003', 'VT001'), -- Chuyến 2: Lái xe Cường
('CX002', 'NV004', 'VT002'), -- Chuyến 2: Phụ xe Dung
('CX003', 'NV001', 'VT001'), -- Chuyến 3: Lái xe An
('CX003', 'NV003', 'VT002'); -- Chuyến 3: Phụ xe Cường

-- 4.2. Bảng VE: Thông tin vé, phụ thuộc CHUYEN_XE
-- Trigger 'TRIGGER_CHECK_SO_GHE' sẽ kiểm tra số lượng vé bán ra.
-- Chuyến CX001 dùng xe XE001 có 45 ghế. Số vé tối đa được bán là 45 - 2 = 43 vé.
-- Ta sẽ thêm 2 vé cho chuyến này.
INSERT INTO VE (MaChuyen, GiaVe) VALUES
('CX001', 100000),
('CX001', 100000);

-- Chuyến CX002 dùng xe XE002 có 45 ghế. Số vé tối đa được bán là 43.
-- Ta sẽ thêm 3 vé cho chuyến này.
INSERT INTO VE (MaChuyen, GiaVe) VALUES
('CX002', 250000),
('CX002', 250000),
('CX002', 250000);