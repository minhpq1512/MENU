-- CREATE DATABASE QLVTHKDD_DB;
USE QLVTHKDD_DB;
-- Tệp SQL này chứa toàn bộ cấu trúc cơ sở dữ liệu, các ràng buộc và trigger
-- Vô hiệu hóa kiểm tra khóa ngoại để tránh lỗi khi tạo bảng
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Cấu trúc bảng
-- -----------------------------------------------------

-- Bảng LOAI_XE: Lưu thông tin loại xe
CREATE TABLE LOAI_XE (
    MaLoaiXe VARCHAR(20) PRIMARY KEY,
    TenLoaiXe VARCHAR(100) NOT NULL,
    SoGhe INT NOT NULL,
    SoNgayBaoDuongToiDa INT NOT NULL,
    SoKmGiamNgayBaoDuong INT NOT NULL
);

-- Bảng XE: Lưu thông tin các xe
CREATE TABLE XE (
    MaXe VARCHAR(20) PRIMARY KEY,
    MaLoaiXe VARCHAR(20) NOT NULL,
    BienSoXe VARCHAR(20) UNIQUE NOT NULL,
    SoKmTichLuy INT DEFAULT 0,
    NgayDangKiem DATE,
    FOREIGN KEY (MaLoaiXe) REFERENCES LOAI_XE(MaLoaiXe)
);

-- Bảng TUYEN_DUONG: Lưu thông tin các tuyến đường
CREATE TABLE TUYEN_DUONG (
    MaTuyen VARCHAR(20) PRIMARY KEY,
    DiemDau VARCHAR(100) NOT NULL,
    DiemCuoi VARCHAR(100) NOT NULL,
    DoDai DECIMAL(10, 2) NOT NULL,
    DoKho INT NOT NULL CHECK (DoKho IN (1, 2, 3)),
    DonGiaTheoKm DECIMAL(10, 2) NOT NULL
);

-- Bảng CHUYEN_XE: Lưu thông tin các chuyến xe (tuyến đường - xe)
CREATE TABLE CHUYEN_XE (
    MaChuyen VARCHAR(20) PRIMARY KEY,
    MaTuyen VARCHAR(20) NOT NULL,
    MaXe VARCHAR(20) NOT NULL,
    NgayKhoiHanh DATE NOT NULL,
    TrangThai VARCHAR(50) DEFAULT 'Dang dien ra',
    ChiPhiVanHanh DECIMAL(15, 2),
    ThuLaoChuyen DECIMAL(15, 2),
    FOREIGN KEY (MaTuyen) REFERENCES TUYEN_DUONG(MaTuyen),
    FOREIGN KEY (MaXe) REFERENCES XE(MaXe)
);

-- Bảng VE: Lưu thông tin vé đã bán
CREATE TABLE VE (
    MaVe VARCHAR(20) PRIMARY KEY,
    MaChuyen VARCHAR(20) NOT NULL,
    GiaVe DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (MaChuyen) REFERENCES CHUYEN_XE(MaChuyen)
);

-- Bảng NHAN_VIEN: Lưu thông tin nhân viên
CREATE TABLE NHAN_VIEN (
    MaNV VARCHAR(20) PRIMARY KEY,
    HoTen VARCHAR(100) NOT NULL,
    SDT VARCHAR(20) UNIQUE NOT NULL
);

-- Bảng VAI_TRO: Lưu thông tin vai trò của nhân viên
CREATE TABLE VAI_TRO (
    MaVaiTro VARCHAR(20) PRIMARY KEY,
    TenVaiTro VARCHAR(50) NOT NULL,
    HeSoLuong DECIMAL(5, 2) NOT NULL
);

-- Bảng PHAN_CONG: Lưu thông tin phân công nhân viên vào các chuyến xe
CREATE TABLE PHAN_CONG (
    MaChuyen VARCHAR(20) NOT NULL,
    MaNV VARCHAR(20) NOT NULL,
    MaVaiTro VARCHAR(20) NOT NULL,
    PRIMARY KEY (MaChuyen, MaNV, MaVaiTro),
    FOREIGN KEY (MaChuyen) REFERENCES CHUYEN_XE(MaChuyen),
    FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV),
    FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro)
);

-- Bảng LICH_SU_BAO_DUONG: Lưu lịch sử bảo dưỡng của xe
CREATE TABLE LICH_SU_BAO_DUONG (
    MaBaoDuong VARCHAR(20) PRIMARY KEY,
    MaXe VARCHAR(20) NOT NULL,
    NgayBaoDuong DATE NOT NULL,
    FOREIGN KEY (MaXe) REFERENCES XE(MaXe)
);

-- Bảng LOAI_THOI_DIEM: Định nghĩa các loại thời điểm (Ngày thường, Lễ tết)
CREATE TABLE LOAI_THOI_DIEM (
    MaLoaiThoiDiem VARCHAR(20) PRIMARY KEY,
    TenLoaiThoiDiem VARCHAR(50) NOT NULL
);

-- Bảng LICH_AP_DUNG: Lưu lịch áp dụng cho các loại thời điểm
CREATE TABLE LICH_AP_DUNG (
    MaLich VARCHAR(20) PRIMARY KEY,
    MaLoaiThoiDiem VARCHAR(20) NOT NULL,
    NgayBatDau DATE NOT NULL,
    NgayKetThuc DATE NOT NULL,
    FOREIGN KEY (MaLoaiThoiDiem) REFERENCES LOAI_THOI_DIEM(MaLoaiThoiDiem)
);

-- Bảng BANG_GIA: Lưu giá vé theo tuyến đường và loại thời điểm
CREATE TABLE BANG_GIA (
    MaTuyen VARCHAR(20) NOT NULL,
    MaLoaiThoiDiem VARCHAR(20) NOT NULL,
    GiaVe DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (MaTuyen, MaLoaiThoiDiem),
    FOREIGN KEY (MaTuyen) REFERENCES TUYEN_DUONG(MaTuyen),
    FOREIGN KEY (MaLoaiThoiDiem) REFERENCES LOAI_THOI_DIEM(MaLoaiThoiDiem)
);

-- -----------------------------------------------------
-- Các Trigger để thực hiện logic nghiệp vụ

-- Trigger 1: Kiểm tra số ghế đã bán trước khi thêm vé mới
-- Yêu cầu: Số ghế bán ra < Số ghế trên xe - 2
DELIMITER $$
CREATE TRIGGER TRIGGER_CHECK_SO_GHE
BEFORE INSERT ON VE
FOR EACH ROW
BEGIN
    DECLARE so_ghe_tren_xe INT;
    DECLARE so_ghe_da_ban INT;

    -- Lấy số ghế trên xe
    SELECT lx.SoGhe INTO so_ghe_tren_xe
    FROM CHUYEN_XE cx
    JOIN XE x ON cx.MaXe = x.MaXe
    JOIN LOAI_XE lx ON x.MaLoaiXe = lx.MaLoaiXe
    WHERE cx.MaChuyen = NEW.MaChuyen;

    -- Đếm số vé đã bán cho chuyến xe này (bao gồm cả vé đang được thêm)
    SELECT COUNT(*) INTO so_ghe_da_ban
    FROM VE
    WHERE MaChuyen = NEW.MaChuyen;

    -- Kiểm tra điều kiện
    IF (so_ghe_da_ban + 1) > (so_ghe_tren_xe - 2) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lỗi: Số ghế bán ra không được vượt quá số ghế trên xe - 2';
    END IF;
END$$
DELIMITER ;

-- Trigger 2: Kiểm tra phân công nhân viên vào chuyến xe
-- Yêu cầu: Mỗi chuyến chỉ có 1 lái xe và 1 phụ xe.
DELIMITER $$
CREATE TRIGGER TRIGGER_CHECK_PHAN_CONG
BEFORE INSERT ON PHAN_CONG
FOR EACH ROW
BEGIN
    DECLARE count_roles INT;
    DECLARE role_name VARCHAR(50);
    DECLARE error_message VARCHAR(100);
    
    SELECT TenVaiTro INTO role_name FROM VAI_TRO WHERE MaVaiTro = NEW.MaVaiTro;

    -- Kiểm tra nếu vai trò là 'Lái xe' hoặc 'Phụ xe'
    IF role_name IN ('Lái xe', 'Phụ xe') THEN
        -- Đếm số lượng nhân viên đã được phân công với cùng vai trò trên cùng chuyến xe
        SELECT COUNT(*) INTO count_roles
        FROM PHAN_CONG pc
        JOIN VAI_TRO vt ON pc.MaVaiTro = vt.MaVaiTro
        WHERE pc.MaChuyen = NEW.MaChuyen AND vt.TenVaiTro = role_name;

        -- Nếu đã có người được phân công vai trò này, đưa ra cảnh báo
        IF count_roles > 0 THEN
            SET error_message = CONCAT('Lỗi: Chuyến xe này đã có ', role_name, '.');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_message;
        END IF;
    END IF;
END$$
DELIMITER ;

-- Trigger 3: Cập nhật Km tích lũy sau mỗi chuyến xe hoàn thành
DELIMITER $$
CREATE TRIGGER TRIGGER_UPDATE_KM_TICH_LUY
AFTER UPDATE ON CHUYEN_XE
FOR EACH ROW
BEGIN
    DECLARE effective_km INT;
    
    -- Kiểm tra nếu trạng thái chuyến xe chuyển thành 'Hoan thanh'
    IF NEW.TrangThai = 'Hoan thanh' AND OLD.TrangThai != 'Hoan thanh' THEN
        -- Tính Km hiệu quả
        SELECT FLOOR(td.DoDai * td.DoKho) INTO effective_km
        FROM TUYEN_DUONG td
        WHERE td.MaTuyen = NEW.MaTuyen;

        -- Cập nhật SoKmTichLuy cho xe
        UPDATE XE
        SET SoKmTichLuy = SoKmTichLuy + effective_km
        WHERE MaXe = NEW.MaXe;
    END IF;
END$$
DELIMITER ;

-- Trigger 4: Reset Km tích lũy khi xe được bảo dưỡng
DELIMITER $$
CREATE TRIGGER TRIGGER_RESET_KM_TICH_LUY
AFTER INSERT ON LICH_SU_BAO_DUONG
FOR EACH ROW
BEGIN
    UPDATE XE
    SET SoKmTichLuy = 0
    WHERE MaXe = NEW.MaXe;
END$$
DELIMITER ;
---
-- Trigger cho bảng LOAI_XE
---
DELIMITER $$
CREATE TRIGGER before_insert_loai_xe
BEFORE INSERT ON LOAI_XE
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);

    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaLoaiXe, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM LOAI_XE;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaLoaiXe = CONCAT('LX', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng XE
---
DELIMITER $$
CREATE TRIGGER before_insert_xe
BEFORE INSERT ON XE
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);

    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaXe, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM XE;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaXe = CONCAT('XE', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng TUYEN_DUONG
---
DELIMITER $$
CREATE TRIGGER before_insert_tuyen_duong
BEFORE INSERT ON TUYEN_DUONG
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaTuyen, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM TUYEN_DUONG;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaTuyen = CONCAT('TD', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng CHUYEN_XE
---
DELIMITER $$
CREATE TRIGGER before_insert_chuyen_xe
BEFORE INSERT ON CHUYEN_XE
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaChuyen, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM CHUYEN_XE;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaChuyen = CONCAT('CX', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng VE
---
DELIMITER $$
CREATE TRIGGER before_insert_ve
BEFORE INSERT ON VE
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaVe, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM VE;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaVe = CONCAT('VE', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng NHAN_VIEN
---
DELIMITER $$
CREATE TRIGGER before_insert_nhan_vien
BEFORE INSERT ON NHAN_VIEN
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaNV, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM NHAN_VIEN;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaNV = CONCAT('NV', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng VAI_TRO
---
DELIMITER $$
CREATE TRIGGER before_insert_vai_tro
BEFORE INSERT ON VAI_TRO
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaVaiTro, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM VAI_TRO;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaVaiTro = CONCAT('VT', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng LICH_SU_BAO_DUONG
---
DELIMITER $$
CREATE TRIGGER before_insert_lich_su_bao_duong
BEFORE INSERT ON LICH_SU_BAO_DUONG
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaBaoDuong, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM LICH_SU_BAO_DUONG;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaBaoDuong = CONCAT('BD', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng LOAI_THOI_DIEM
---
DELIMITER $$
CREATE TRIGGER before_insert_loai_thoi_diem
BEFORE INSERT ON LOAI_THOI_DIEM
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaLoaiThoiDiem, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM LOAI_THOI_DIEM;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaLoaiThoiDiem = CONCAT('LT', new_id_str);
END$$
DELIMITER ;

---
-- Trigger cho bảng LICH_AP_DUNG
---
DELIMITER $$
CREATE TRIGGER before_insert_lich_ap_dung
BEFORE INSERT ON LICH_AP_DUNG
FOR EACH ROW
BEGIN
    DECLARE max_id INT;
    DECLARE new_id_str VARCHAR(10);
    
    SELECT 
        IFNULL(MAX(CAST(SUBSTRING(MaLich, 3) AS UNSIGNED)), 0) + 1
    INTO max_id
    FROM LICH_AP_DUNG;
    
    SET new_id_str = LPAD(max_id, 3, '0');
    SET NEW.MaLich = CONCAT('LA', new_id_str);
END$$
DELIMITER ;

-- Kích hoạt lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;
