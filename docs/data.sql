-- ============================================================
-- DATA MẪU — Hệ thống Trung tâm Đào tạo Ngoại ngữ Five Genius
-- Chạy SAU khi đã migrate: php artisan migrate
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 1. Tài khoản (users)
-- ────────────────────────────────────────────────────────────
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin Five Genius',   'admin@fivegenius.vn',    '$2y$12$YvzON/FsNPpFkSrh4lW5O.3M5DLGm7b6AOwv0aXMQj01j2MMnNmAi', 3, NOW(), NOW()),
(2, 'Nguyễn Thị Lan',     'giaovien1@fivegenius.vn','$2y$12$YvzON/FsNPpFkSrh4lW5O.3M5DLGm7b6AOwv0aXMQj01j2MMnNmAi', 2, NOW(), NOW()),
(3, 'Trần Văn Nam',       'hocvien1@gmail.com',     '$2y$12$YvzON/FsNPpFkSrh4lW5O.3M5DLGm7b6AOwv0aXMQj01j2MMnNmAi', 1, NOW(), NOW()),
(4, 'Lê Thị Hoa',         'hocvien2@gmail.com',     '$2y$12$YvzON/FsNPpFkSrh4lW5O.3M5DLGm7b6AOwv0aXMQj01j2MMnNmAi', 1, NOW(), NOW()),
(5, 'Phạm Minh Đức',      'nhanvien1@fivegenius.vn','$2y$12$YvzON/FsNPpFkSrh4lW5O.3M5DLGm7b6AOwv0aXMQj01j2MMnNmAi', 2, NOW(), NOW());
-- Mật khẩu mặc định: password

-- ────────────────────────────────────────────────────────────
-- 2. Hồ sơ người dùng (hosonguoidung)
-- ────────────────────────────────────────────────────────────
INSERT INTO `hosonguoidung` (`taiKhoanId`, `hoTen`, `soDienThoai`, `ngaySinh`, `gioiTinh`, `created_at`, `updated_at`) VALUES
(1, 'Admin Five Genius',  '0900000000', '1990-01-01', 'nam',  NOW(), NOW()),
(2, 'Nguyễn Thị Lan',    '0912345678', '1995-05-15', 'nu',   NOW(), NOW()),
(3, 'Trần Văn Nam',       '0987654321', '2000-08-20', 'nam',  NOW(), NOW()),
(4, 'Lê Thị Hoa',         '0901122334', '2001-03-10', 'nu',   NOW(), NOW()),
(5, 'Phạm Minh Đức',      '0933445566', '1993-11-22', 'nam',  NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 3. Tỉnh/Thành phố (tinhthanh)
-- ────────────────────────────────────────────────────────────
INSERT INTO `tinhthanh` (`tinhThanhId`, `tenTinhThanh`, `maApi`, `created_at`, `updated_at`) VALUES
(1, 'TP. Hồ Chí Minh', '79', NOW(), NOW()),
(2, 'Hà Nội',           '01', NOW(), NOW()),
(3, 'Đà Nẵng',          '48', NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 4. Cơ sở đào tạo (cosodaotao)
-- ────────────────────────────────────────────────────────────
INSERT INTO `cosodaotao` (`coSoId`, `tenCoSo`, `diaChi`, `tinhThanhId`, `soDienThoai`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Cơ sở Quận 1',     '123 Nguyễn Trãi, Phường 3, Quận 1', 1, '028.1234.5678', 1, NOW(), NOW()),
(2, 'Cơ sở Bình Thạnh', '456 Đinh Bộ Lĩnh, P.24, Bình Thạnh', 1, '028.8765.4321', 1, NOW(), NOW()),
(3, 'Cơ sở Hà Nội',     '789 Hoàng Quốc Việt, Cầu Giấy', 2, '024.3344.5566', 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 5. Phòng học (phonghoc)
-- ────────────────────────────────────────────────────────────
INSERT INTO `phonghoc` (`phongHocId`, `coSoId`, `tenPhong`, `sucChua`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, 'Phòng A101', 20, 1, NOW(), NOW()),
(2, 1, 'Phòng A102', 15, 1, NOW(), NOW()),
(3, 2, 'Phòng B201', 25, 1, NOW(), NOW()),
(4, 3, 'Phòng HN01', 20, 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 6. Nhân sự (nhansu) — Giáo viên & Nhân viên
-- ────────────────────────────────────────────────────────────
INSERT INTO `nhansu` (`nhanSuId`, `taiKhoanId`, `coSoId`, `chuyenMon`, `moTa`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Tiếng Anh IELTS', 'Giáo viên 10 năm kinh nghiệm, chứng chỉ IELTS 8.5', NOW(), NOW()),
(2, 5, 1, 'Hành chính',      'Nhân viên tư vấn tuyển sinh', NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 7. Ca học (cahoc)
-- ────────────────────────────────────────────────────────────
INSERT INTO `cahoc` (`caHocId`, `tenCa`, `gioBatDau`, `gioKetThuc`, `thu`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Ca sáng T2-T4-T6',  '08:00:00', '10:00:00', '2,4,6',   1, NOW(), NOW()),
(2, 'Ca chiều T3-T5-T7', '14:00:00', '16:00:00', '3,5,7',   1, NOW(), NOW()),
(3, 'Ca tối T2-T4',      '18:00:00', '20:00:00', '2,4',     1, NOW(), NOW()),
(4, 'Cuối tuần T7-CN',   '09:00:00', '12:00:00', '7,1',     1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 8. Danh mục khóa học dạng cây (danhmuckhoahoc)
-- ────────────────────────────────────────────────────────────
INSERT INTO `danhmuckhoahoc` (`danhMucId`, `tenDanhMuc`, `slug`, `moTa`, `parent_id`, `trangThai`, `created_at`, `updated_at`) VALUES
-- Cấp 1 (root)
(1, 'Tiếng Anh',  'tieng-anh',  'Các khóa học tiếng Anh',  NULL, 1, NOW(), NOW()),
(2, 'Tiếng Nhật', 'tieng-nhat', 'Các khóa học tiếng Nhật', NULL, 1, NOW(), NOW()),
(3, 'Tiếng Hàn',  'tieng-han',  'Các khóa học tiếng Hàn',  NULL, 1, NOW(), NOW()),
-- Cấp 2 (con của Tiếng Anh)
(4, 'IELTS',           'ielts',            'Luyện thi IELTS',        1, 1, NOW(), NOW()),
(5, 'TOEIC',           'toeic',            'Luyện thi TOEIC',        1, 1, NOW(), NOW()),
(6, 'Giao tiếp',       'giao-tiep',        'Tiếng Anh giao tiếp',    1, 1, NOW(), NOW()),
(7, 'Trẻ em',          'tieng-anh-tre-em', 'Tiếng Anh cho trẻ em',   1, 1, NOW(), NOW()),
-- Cấp 3 (con của IELTS)
(8, 'IELTS Cơ bản',    'ielts-co-ban',     'IELTS band 4.5–5.5',     4, 1, NOW(), NOW()),
(9, 'IELTS Nâng cao',  'ielts-nang-cao',   'IELTS band 6.0–7.0',     4, 1, NOW(), NOW()),
-- Cấp 2 (con của Tiếng Nhật)
(10, 'N5 - Sơ cấp',   'tieng-nhat-n5',    'Tiếng Nhật N5',          2, 1, NOW(), NOW()),
(11, 'N4 - Sơ trung',  'tieng-nhat-n4',    'Tiếng Nhật N4',          2, 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 9. Khóa học (khoahoc)
-- ────────────────────────────────────────────────────────────
INSERT INTO `khoahoc` (`khoaHocId`, `tenKhoaHoc`, `danhMucId`, `moTa`, `slug`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'IELTS Cơ bản 4.5 → 5.5', 8,  'Khóa học IELTS cơ bản giúp bạn đạt band 5.5 trong 3 tháng', 'ielts-co-ban-4-5-5-5', 1, NOW(), NOW()),
(2, 'IELTS Nâng cao 6.0+',     9,  'Nâng trình IELTS lên 6.0 và 7.0 với phương pháp học thực chiến', 'ielts-nang-cao-6-0', 1, NOW(), NOW()),
(3, 'TOEIC 600+',              5,  'Luyện thi TOEIC đạt 600 điểm trở lên trong 2 tháng', 'toeic-600', 1, NOW(), NOW()),
(4, 'Tiếng Anh giao tiếp',    6,  'Tự tin giao tiếp tiếng Anh trong công việc và cuộc sống', 'tieng-anh-giao-tiep', 1, NOW(), NOW()),
(5, 'Tiếng Nhật N5',           10, 'Tiếng Nhật cơ bản N5 từ con số 0', 'tieng-nhat-n5', 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 10. Lop hoc (lophoc)
-- ────────────────────────────────────────────────────────────
INSERT INTO `lophoc` (`lopHocId`, `khoaHocId`, `coSoId`, `phongHocId`, `nhanSuId`, `caHocId`, `tenLop`, `slug`, `ngayBatDau`, `ngayKetThuc`, `siSo`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 1, 'IELTS CB-01/2026', 'ielts-cb-01-2026', '2026-03-10', '2026-06-10', 15, 'dang_hoc',    NOW(), NOW()),
(2, 2, 1, 2, 1, 3, 'IELTS NC-01/2026', 'ielts-nc-01-2026', '2026-03-15', '2026-07-15', 12, 'sap_khai_giang', NOW(), NOW()),
(3, 3, 2, 3, 1, 2, 'TOEIC-01/2026',    'toeic-01-2026',    '2026-04-01', '2026-06-01', 20, 'sap_khai_giang', NOW(), NOW()),
(4, 5, 3, 4, 1, 4, 'NHT-N5-01/2026',   'nht-n5-01-2026',   '2026-03-20', '2026-09-20', 18, 'dang_hoc',    NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 11. Chinh sach gia lop hoc (lophoc_chinhsachgia)
-- ────────────────────────────────────────────────────────────
INSERT INTO `lophoc_chinhsachgia` (`lopHocChinhSachGiaId`, `lopHocId`, `loaiThu`, `hocPhiNiemYet`, `soBuoiCamKet`, `ghiChuChinhSach`, `hieuLucTu`, `hieuLucDen`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 3500000, 24, 'Hoc phi tron goi lop IELTS co ban', NOW(), NULL, 1, NOW(), NOW()),
(2, 2, 0, 4500000, 30, 'Hoc phi tron goi lop IELTS nang cao', NOW(), NULL, 1, NOW(), NOW()),
(3, 3, 0, 2800000, 20, 'Hoc phi tron goi lop TOEIC', NOW(), NULL, 1, NOW(), NOW()),
(4, 4, 0, 3000000, 28, 'Hoc phi tron goi lop N5', NOW(), NULL, 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 12. Dot thu cua lop (lophoc_dotthu)
-- ────────────────────────────────────────────────────────────
INSERT INTO `lophoc_dotthu` (`lopHocDotThuId`, `lopHocChinhSachGiaId`, `tenDotThu`, `thuTu`, `soTien`, `hanThanhToan`, `batBuoc`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dat coc giu cho', 1, 1000000, '2026-03-08', 1, 1, NOW(), NOW()),
(2, 1, 'Thanh toan khai giang', 2, 2500000, '2026-03-15', 1, 1, NOW(), NOW()),
(3, 2, 'Thanh toan tron khoa', 1, 4500000, '2026-03-15', 1, 1, NOW(), NOW()),
(4, 3, 'Thanh toan tron khoa', 1, 2800000, '2026-04-01', 1, 1, NOW(), NOW()),
(5, 4, 'Thanh toan tron khoa', 1, 3000000, '2026-03-20', 1, 1, NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 13. Dang ky lop hoc + Hoa don
-- ────────────────────────────────────────────────────────────
INSERT INTO `dangkylophoc` (`dangKyLopHocId`, `lopHocId`, `taiKhoanId`, `lopHocChinhSachGiaId`, `loaiThuSnapshot`, `hocPhiNiemYetSnapshot`, `giamGiaSnapshot`, `hocPhiPhaiThuSnapshot`, `soBuoiCamKetSnapshot`, `ghiChuGiaSnapshot`, `ngayDangKy`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 0, 3500000, 0, 3500000, 24, 'Chot gia tai thoi diem dang ky', NOW(), 'da_xac_nhan', NOW(), NOW()),
(2, 1, 4, 1, 0, 3500000, 0, 3500000, 24, 'Chot gia tai thoi diem dang ky', NOW(), 'da_xac_nhan', NOW(), NOW()),
(3, 4, 3, 4, 0, 3000000, 0, 3000000, 28, 'Chot gia tai thoi diem dang ky', NOW(), 'cho_xac_nhan', NOW(), NOW());

INSERT INTO `hoadon` (`hoaDonId`, `dangKyLopHocId`, `lopHocDotThuId`, `tongTien`, `soTienCon`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 3500000, 0,       'da_thanh_toan', NOW(), NOW()),
(2, 2, NULL, 3500000, 3500000, 'chua_thanh_toan', NOW(), NOW()),
(3, 3, NULL, 3000000, 3000000, 'chua_thanh_toan', NOW(), NOW());

INSERT INTO `phieuthu` (`phieuThuId`, `hoaDonId`, `soTien`, `ngayThu`, `hinhThucTT`, `ghiChu`, `created_at`, `updated_at`) VALUES
(1, 1, 3500000, NOW(), 'tien_mat', 'Thanh toán đủ khi nhập học', NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 14. Liên hệ tư vấn (lienhe)
-- ────────────────────────────────────────────────────────────
INSERT INTO `lienhe` (`lienHeId`, `hoTen`, `soDienThoai`, `email`, `khoaHocQuan`, `ghiChu`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', 'IELTS', 'Muốn học buổi tối', 'moi',        NOW(), NOW()),
(2, 'Lê Thị B',     '0912345678', 'lethib@gmail.com',     'TOEIC', NULL,                 'dang_xu_ly', NOW(), NOW());

-- ────────────────────────────────────────────────────────────
-- 15. Bài viết (baiviet)
-- ────────────────────────────────────────────────────────────
INSERT INTO `danhmucbaiviet` (`danhMucId`, `tenDanhMuc`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Kinh nghiệm học', 'kinh-nghiem-hoc', NOW(), NOW()),
(2, 'Tin tức',         'tin-tuc',          NOW(), NOW());

INSERT INTO `baiviet` (`baiVietId`, `tieuDe`, `slug`, `tomTat`, `noiDung`, `danhMucId`, `taiKhoanId`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Phân biệt TOEIC, IELTS và TOEFL', 'phan-biet-toeic-ielts-va-toefl',
   'Ba chứng chỉ tiếng Anh phổ biến nhất khác nhau như thế nào?',
   '<p>Nội dung bài viết chi tiết...</p>', 1, 1, 1, NOW(), NOW()),
(2, 'Khai giảng khóa mới tháng 4/2026', 'khai-giang-thang-4-2026',
   'Trung tâm Five Genius thông báo khai giảng các lớp mới tháng 4.',
   '<p>Nội dung thông báo khai giảng...</p>', 2, 1, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF DATA.SQL
-- Chú ý:
-- • Mật khẩu mặc định cho tất cả tài khoản: "password"
-- • File nay da duoc cap nhat theo mo hinh hoc phi theo lop hoc sau migration 2026_03_14_150000
-- • Dieu chinh coSoId, nhanSuId cho phu hop voi du lieu thuc
-- ============================================================
