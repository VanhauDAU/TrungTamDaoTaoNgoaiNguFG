-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th2 27, 2026 lúc 07:57 AM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `DoAnChuyenNganhCNPM_TrungTamNN`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `BaiThi`
--

CREATE TABLE `BaiThi` (
  `baiThiId` int(11) NOT NULL,
  `khoaHocId` int(11) DEFAULT NULL,
  `tenBaiThi` varchar(255) DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `ngayThi` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `BaiViet`
--

CREATE TABLE `BaiViet` (
  `baiVietId` int(11) NOT NULL,
  `tieuDe` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tomTat` text DEFAULT NULL,
  `noiDung` longtext NOT NULL,
  `anhDaiDien` varchar(255) DEFAULT NULL,
  `taiKhoanId` int(11) NOT NULL,
  `luotXem` int(11) DEFAULT 0,
  `trangThai` tinyint(4) DEFAULT 0 COMMENT '0: Ẩn, 1: Công khai',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `BaiViet`
--

INSERT INTO `BaiViet` (`baiVietId`, `tieuDe`, `slug`, `tomTat`, `noiDung`, `anhDaiDien`, `taiKhoanId`, `luotXem`, `trangThai`, `published_at`, `created_at`, `updated_at`) VALUES
(1, '5 bí quyết đạt IELTS 7.0 trong 6 tháng', '5-bi-quyet-dat-ielts-7-trong-6-thang', 'Chia sẻ lộ trình và phương pháp giúp bạn đạt IELTS 7.0.', 'Nội dung chi tiết về lộ trình học IELTS, kỹ năng Listening, Reading, Writing và Speaking...', 'anh-demo.jpg', 1, 120, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:47'),
(2, 'Cách học từ vựng tiếng Anh nhớ lâu', 'cach-hoc-tu-vung-nho-lau', 'Phương pháp học từ vựng hiệu quả cho người mới bắt đầu.', 'Bài viết chia sẻ các phương pháp học từ vựng như Spaced Repetition, Flashcard...', 'anh-demo.jpg', 1, 85, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:49'),
(3, 'Trải nghiệm học viên sau khóa IELTS tại trung tâm', 'trai-nghiem-hoc-vien-ielts', 'Chia sẻ cảm nhận của học viên sau khi hoàn thành khóa học.', 'Học viên A đã cải thiện band điểm từ 5.5 lên 7.0 chỉ sau 4 tháng...', 'anh-demo.jpg', 1, 60, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:51'),
(4, 'Thông báo khai giảng khóa TOEIC tháng 3', 'thong-bao-khai-giang-toeic-thang-3', 'Trung tâm chính thức khai giảng khóa TOEIC tháng 3.', 'Khóa TOEIC tháng 3 sẽ khai giảng vào ngày 15/03 với nhiều ưu đãi...', 'anh-demo.jpg', 1, 40, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:52'),
(13, '5 bí quyết đạt IELTS 7.0 trong 6 tháng 2', '5-bi-quyet-dat-ielts-7-trong-6-thang-2', 'Chia sẻ lộ trình và phương pháp giúp bạn đạt IELTS 7.0.', 'Nội dung chi tiết về lộ trình học IELTS, kỹ năng Listening, Reading, Writing và Speaking...', 'anh-demo.jpg', 1, 120, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:47'),
(14, 'Cách học từ vựng tiếng Anh nhớ lâu nha', 'cach-hoc-tu-vung-nho-lau-nha', 'Phương pháp học từ vựng hiệu quả cho người mới bắt đầu.', 'Bài viết chia sẻ các phương pháp học từ vựng như Spaced Repetition, Flashcard...', 'anh-demo.jpg', 1, 85, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:49'),
(15, 'Trải nghiệm học viên sau khóa IELTS tại trung tâm 2', 'trai-nghiem-hoc-vien-ielts-2', 'Chia sẻ cảm nhận của học viên sau khi hoàn thành khóa học.', 'Học viên A đã cải thiện band điểm từ 5.5 lên 7.0 chỉ sau 4 tháng...', 'anh-demo.jpg', 1, 60, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:51'),
(16, 'Thông báo khai giảng khóa TOEIC tháng 12', 'thong-bao-khai-giang-toeic-thang-12', 'Trung tâm chính thức khai giảng khóa TOEIC tháng 3.', 'Khóa TOEIC tháng 3 sẽ khai giảng vào ngày 15/03 với nhiều ưu đãi...', 'anh-demo.jpg', 1, 40, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:52'),
(17, 'Trải nghiệm học viên sau khóa IELTS tại trung tâm 2', 'trai-nghiem-hoc-vien-ielts-21', 'Chia sẻ cảm nhận của học viên sau khi hoàn thành khóa học.', 'Học viên A đã cải thiện band điểm từ 5.5 lên 7.0 chỉ sau 4 tháng...', 'anh-demo.jpg', 1, 60, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:51'),
(18, 'Thông báo khai giảng khóa TOEIC tháng 12', 'thong-bao-khai-giang-toeic-thang-12-1', 'Trung tâm chính thức khai giảng khóa TOEIC tháng 3.', 'Khóa TOEIC tháng 3 sẽ khai giảng vào ngày 15/03 với nhiều ưu đãi...', 'anh-demo.jpg', 1, 40, 1, '2026-01-27 13:11:43', '2026-01-27 06:11:43', '2026-01-27 10:56:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `BaiViet_DanhMuc`
--

CREATE TABLE `BaiViet_DanhMuc` (
  `baiVietId` int(11) NOT NULL,
  `danhMucId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `BaiViet_DanhMuc`
--

INSERT INTO `BaiViet_DanhMuc` (`baiVietId`, `danhMucId`) VALUES
(1, 1),
(2, 2),
(3, 4),
(4, 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `BaiViet_Tag`
--

CREATE TABLE `BaiViet_Tag` (
  `baiVietId` int(11) NOT NULL,
  `tagId` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `BaiViet_Tag`
--

INSERT INTO `BaiViet_Tag` (`baiVietId`, `tagId`) VALUES
(1, 1),
(1, 7),
(1, 9),
(2, 4),
(2, 5),
(3, 1),
(3, 7),
(4, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `BuoiHoc`
--

CREATE TABLE `BuoiHoc` (
  `buoiHocId` int(11) NOT NULL,
  `lopHocId` int(11) DEFAULT NULL,
  `tenBuoiHoc` varchar(255) DEFAULT NULL,
  `ngayHoc` date DEFAULT NULL,
  `caHocId` int(11) DEFAULT NULL,
  `phongHocId` int(11) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL COMMENT 'Giáo viên dạy',
  `ghiChu` text DEFAULT NULL,
  `daDiemDanh` tinyint(4) DEFAULT 0,
  `daHoanThanh` tinyint(4) DEFAULT 0,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `BuoiHoc`
--

INSERT INTO `BuoiHoc` (`buoiHocId`, `lopHocId`, `tenBuoiHoc`, `ngayHoc`, `caHocId`, `phongHocId`, `taiKhoanId`, `ghiChu`, `daDiemDanh`, `daHoanThanh`, `trangThai`, `created_at`, `updated_at`) VALUES
(4, 6, 'Buổi 2: Lớp tiếng anh giao tiếp 123', '2026-02-28', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(5, 6, 'Buổi 3: Lớp tiếng anh giao tiếp 123', '2026-03-03', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(6, 6, 'Buổi 4: Lớp tiếng anh giao tiếp 123', '2026-03-05', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(7, 6, 'Buổi 5: Lớp tiếng anh giao tiếp 123', '2026-03-07', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(8, 6, 'Buổi 6: Lớp tiếng anh giao tiếp 123', '2026-03-10', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(9, 6, 'Buổi 7: Lớp tiếng anh giao tiếp 123', '2026-03-12', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(10, 6, 'Buổi 8: Lớp tiếng anh giao tiếp 123', '2026-03-14', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(11, 6, 'Buổi 9: Lớp tiếng anh giao tiếp 123', '2026-03-17', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(12, 6, 'Buổi 10: Lớp tiếng anh giao tiếp 123', '2026-03-19', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(13, 6, 'Buổi 11: Lớp tiếng anh giao tiếp 123', '2026-03-21', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(14, 6, 'Buổi 12: Lớp tiếng anh giao tiếp 123', '2026-03-24', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(15, 6, 'Buổi 13: Lớp tiếng anh giao tiếp 123', '2026-03-26', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:49:58', '2026-02-26 14:49:58'),
(16, 6, 'Buổi 13: Lớp tiếng anh giao tiếp 123', '2026-02-26', 1, NULL, 26, NULL, 0, 0, 0, '2026-02-26 14:51:29', '2026-02-26 14:51:29'),
(17, 7, 'Buổi 1: Lớp tiếng anh giao tiếp 123', '2026-02-27', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(18, 7, 'Buổi 2: Lớp tiếng anh giao tiếp 123', '2026-03-03', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(19, 7, 'Buổi 3: Lớp tiếng anh giao tiếp 123', '2026-03-06', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(20, 7, 'Buổi 4: Lớp tiếng anh giao tiếp 123', '2026-03-10', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(21, 7, 'Buổi 5: Lớp tiếng anh giao tiếp 123', '2026-03-13', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(22, 7, 'Buổi 6: Lớp tiếng anh giao tiếp 123', '2026-03-17', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(23, 7, 'Buổi 7: Lớp tiếng anh giao tiếp 123', '2026-03-20', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(24, 7, 'Buổi 8: Lớp tiếng anh giao tiếp 123', '2026-03-24', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(25, 7, 'Buổi 9: Lớp tiếng anh giao tiếp 123', '2026-03-27', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(26, 7, 'Buổi 10: Lớp tiếng anh giao tiếp 123', '2026-03-31', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(27, 7, 'Buổi 11: Lớp tiếng anh giao tiếp 123', '2026-04-03', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(28, 7, 'Buổi 12: Lớp tiếng anh giao tiếp 123', '2026-04-07', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(29, 7, 'Buổi 13: Lớp tiếng anh giao tiếp 123', '2026-04-10', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(30, 7, 'Buổi 14: Lớp tiếng anh giao tiếp 123', '2026-04-14', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(31, 7, 'Buổi 15: Lớp tiếng anh giao tiếp 123', '2026-04-17', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(32, 7, 'Buổi 16: Lớp tiếng anh giao tiếp 123', '2026-04-21', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(33, 7, 'Buổi 17: Lớp tiếng anh giao tiếp 123', '2026-04-24', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(34, 7, 'Buổi 18: Lớp tiếng anh giao tiếp 123', '2026-04-28', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(35, 7, 'Buổi 19: Lớp tiếng anh giao tiếp 123', '2026-05-01', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(36, 7, 'Buổi 20: Lớp tiếng anh giao tiếp 123', '2026-05-05', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(37, 7, 'Buổi 21: Lớp tiếng anh giao tiếp 123', '2026-05-08', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(38, 7, 'Buổi 22: Lớp tiếng anh giao tiếp 123', '2026-05-12', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(39, 7, 'Buổi 23: Lớp tiếng anh giao tiếp 123', '2026-05-15', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(40, 7, 'Buổi 24: Lớp tiếng anh giao tiếp 123', '2026-05-19', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(41, 7, 'Buổi 25: Lớp tiếng anh giao tiếp 123', '2026-05-22', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(42, 7, 'Buổi 26: Lớp tiếng anh giao tiếp 123', '2026-05-26', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(43, 7, 'Buổi 27: Lớp tiếng anh giao tiếp 123', '2026-05-29', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(44, 7, 'Buổi 28: Lớp tiếng anh giao tiếp 123', '2026-06-02', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(45, 7, 'Buổi 29: Lớp tiếng anh giao tiếp 123', '2026-06-05', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(46, 7, 'Buổi 30: Lớp tiếng anh giao tiếp 123', '2026-06-09', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(47, 7, 'Buổi 31: Lớp tiếng anh giao tiếp 123', '2026-06-12', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(48, 7, 'Buổi 32: Lớp tiếng anh giao tiếp 123', '2026-06-16', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(50, 7, 'Buổi 34: Lớp tiếng anh giao tiếp 123', '2026-06-23', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(51, 7, 'Buổi 35: Lớp tiếng anh giao tiếp 123', '2026-06-26', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04'),
(52, 7, 'Buổi 36: Lớp tiếng anh giao tiếp 123', '2026-06-30', 2, 3, 26, NULL, 0, 0, 0, '2026-02-26 15:32:04', '2026-02-26 15:32:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-admin|::1', 'i:1;', 1771553959),
('laravel-cache-admin|::1:timer', 'i:1771553959;', 1771553959),
('laravel-cache-levanhau1@gmail.com|127.0.0.1', 'i:1;', 1771552084),
('laravel-cache-levanhau1@gmail.com|127.0.0.1:timer', 'i:1771552084;', 1771552084),
('laravel-cache-levanhauadmin114|::1', 'i:1;', 1771553974),
('laravel-cache-levanhauadmin114|::1:timer', 'i:1771553974;', 1771553974),
('laravel-cache-levanhaum@gmail.com|127.0.0.1', 'i:1;', 1771558245),
('laravel-cache-levanhaum@gmail.com|127.0.0.1:timer', 'i:1771558245;', 1771558245),
('trung-tam-ngoai-ngu-cache-admiadmin@admin.com|127.0.0.1', 'i:1;', 1771641489),
('trung-tam-ngoai-ngu-cache-admiadmin@admin.com|127.0.0.1:timer', 'i:1771641489;', 1771641489),
('trung-tam-ngoai-ngu-cache-levanhau1@gmail.com|127.0.0.1', 'i:3;', 1771639455),
('trung-tam-ngoai-ngu-cache-levanhau1@gmail.com|127.0.0.1:timer', 'i:1771639455;', 1771639455);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `CaHoc`
--

CREATE TABLE `CaHoc` (
  `caHocId` int(11) NOT NULL,
  `tenCa` varchar(50) DEFAULT NULL,
  `gioBatDau` time DEFAULT NULL,
  `gioKetThuc` time DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `CaHoc`
--

INSERT INTO `CaHoc` (`caHocId`, `tenCa`, `gioBatDau`, `gioKetThuc`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Ca Sáng 1', '08:00:00', '10:00:00', 1, '2026-01-23 20:07:20', '2026-01-23 20:07:20'),
(2, 'Ca Tối 2', '18:00:00', '20:00:00', 1, '2026-01-23 20:07:20', '2026-01-23 20:07:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `CoSoDaoTao`
--

CREATE TABLE `CoSoDaoTao` (
  `coSoId` int(11) NOT NULL,
  `maCoSo` varchar(20) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `tenCoSo` varchar(255) NOT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `banDoGoogle` text DEFAULT NULL,
  `tinhThanhId` int(11) DEFAULT NULL,
  `maPhuongXa` int(10) UNSIGNED DEFAULT NULL,
  `tenPhuongXa` varchar(150) DEFAULT NULL,
  `viDo` decimal(10,7) DEFAULT NULL,
  `kinhDo` decimal(10,7) DEFAULT NULL,
  `ngayKhaiTruong` date DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `CoSoDaoTao`
--

INSERT INTO `CoSoDaoTao` (`coSoId`, `maCoSo`, `slug`, `tenCoSo`, `diaChi`, `soDienThoai`, `email`, `banDoGoogle`, `tinhThanhId`, `maPhuongXa`, `tenPhuongXa`, `viDo`, `kinhDo`, `ngayKhaiTruong`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'CS01', 'chi-nhanh-quan-1', 'Chi nhánh Quận 1', '123 Lê Lợi, Q1, HCM', '02838123456', 'q1@center.com', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.641108987922!2d108.21948517688925!3d16.032187484641973!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314219ee598df9c5%3A0xaadb53409be7c909!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBLaeG6v24gdHLDumMgxJDDoCBO4bq1bmc!5e0!3m2!1svi!2s!4v1769507200475!5m2!1svi!2s', 217, 26743, 'Phường Bến Thành', 10.7714230, 106.6984710, '2024-01-01', 1, '2026-01-23 20:07:12', '2026-02-21 23:46:16'),
(13, 'CS001', 'chi-nhanh-nguyen-van-linh', 'Chi nhánh Nguyễn Văn Linh', '370 Nguyễn Văn Linh', '0777464347', 'fgnguyenvanlinh@gmail.com', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.1240200285793!2d108.20496117539444!3d16.05905283970972!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3142192e08f910c5%3A0x676e0cb3f8b52221!2zQ2VsbHBob25lUyAtIFRydW5nIHTDom0gTGFwdG9wLCBTbWFydCBIb21lIGNow61uaCBow6NuZywgZ2nDoSB04buRdA!5e0!3m2!1svi!2s!4v1771730749397!5m2!1svi!2s', 210, 20209, 'Phường Thanh Khê', 16.0590528, 108.2049612, '2026-02-23', 1, '2026-02-22 10:26:29', '2026-02-22 10:26:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `DangKyLopHoc`
--

CREATE TABLE `DangKyLopHoc` (
  `dangKyLopHocId` int(11) NOT NULL,
  `taiKhoanId` int(11) DEFAULT NULL COMMENT 'Học viên',
  `lopHocId` int(11) DEFAULT NULL,
  `ngayDangKy` date DEFAULT NULL,
  `trangThai` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `DanhGiaGiaoVien`
--

CREATE TABLE `DanhGiaGiaoVien` (
  `danhGiaId` int(11) NOT NULL,
  `giaoVienId` int(11) DEFAULT NULL,
  `hocVienId` int(11) DEFAULT NULL,
  `lopHocId` int(11) DEFAULT NULL,
  `soSao` tinyint(4) DEFAULT NULL,
  `noiDung` text DEFAULT NULL,
  `ngayDanhGia` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `DanhMucBaiViet`
--

CREATE TABLE `DanhMucBaiViet` (
  `danhMucId` int(11) NOT NULL,
  `tenDanhMuc` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `moTa` text DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `DanhMucBaiViet`
--

INSERT INTO `DanhMucBaiViet` (`danhMucId`, `tenDanhMuc`, `slug`, `moTa`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Kinh nghiệm học IELTS', 'kinh-nghiem-hoc-ielts', 'Chia sẻ kinh nghiệm luyện thi IELTS', 1, '2026-01-27 06:11:29', '2026-01-27 06:11:29'),
(2, 'Mẹo học tiếng Anh', 'meo-hoc-tieng-anh', 'Các mẹo học tiếng Anh hiệu quả', 1, '2026-01-27 06:11:29', '2026-01-27 06:11:29'),
(3, 'Tin tức trung tâm', 'tin-tuc-trung-tam', 'Thông báo và tin tức từ trung tâm', 1, '2026-01-27 06:11:29', '2026-01-27 06:11:29'),
(4, 'Chia sẻ học viên', 'chia-se-hoc-vien', 'Câu chuyện và trải nghiệm học viên', 1, '2026-01-27 06:11:29', '2026-01-27 06:11:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `DiemBaiThi`
--

CREATE TABLE `DiemBaiThi` (
  `diemThiId` int(11) NOT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `baiThiId` int(11) DEFAULT NULL,
  `diemSo` decimal(4,2) DEFAULT NULL,
  `ghiChu` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `DiemDanh`
--

CREATE TABLE `DiemDanh` (
  `diemDanhId` varchar(50) NOT NULL,
  `taiKhoanId` int(11) DEFAULT NULL COMMENT 'Học viên',
  `buoiHocId` int(11) DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL COMMENT '0: vắng, 1: có mặt, 2: trễ',
  `ghiChu` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `HoaDon`
--

CREATE TABLE `HoaDon` (
  `hoaDonId` int(11) NOT NULL,
  `ngayLap` date DEFAULT NULL,
  `tongTien` decimal(15,2) DEFAULT NULL,
  `daTra` decimal(15,2) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `dangKyLopHocId` int(11) DEFAULT NULL,
  `phuongThucThanhToan` int(11) DEFAULT NULL,
  `coSoId` int(11) DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL COMMENT '0: Chưa thanh toán\r\n1: Đã thanh toán một phần\r\n2: Đã thanh toán đủ',
  `ghiChu` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `HocPhi`
--

CREATE TABLE `HocPhi` (
  `hocPhiId` bigint(20) NOT NULL,
  `khoaHocId` int(11) DEFAULT NULL,
  `soBuoi` int(11) DEFAULT NULL,
  `donGia` decimal(15,2) DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `HocPhi`
--

INSERT INTO `HocPhi` (`hocPhiId`, `khoaHocId`, `soBuoi`, `donGia`, `trangThai`, `created_at`, `updated_at`) VALUES
(3, 4, 20, 15000.00, 1, '2026-02-26 15:10:48', '2026-02-26 15:10:48'),
(4, 4, 25, 20000.00, 1, '2026-02-26 15:11:30', '2026-02-26 15:11:30'),
(5, 3, 35, 50000.00, 1, '2026-02-26 15:28:05', '2026-02-26 15:28:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `HoSoNguoiDung`
--

CREATE TABLE `HoSoNguoiDung` (
  `taiKhoanId` int(11) NOT NULL,
  `hoTen` varchar(100) NOT NULL,
  `soDienThoai` varchar(15) DEFAULT NULL,
  `zalo` varchar(20) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` tinyint(4) DEFAULT NULL COMMENT '0: nữ, 1: nam, 2: khác',
  `diaChi` varchar(255) DEFAULT NULL,
  `nguoiGiamHo` varchar(100) DEFAULT NULL,
  `sdtGuardian` varchar(20) DEFAULT NULL,
  `moiQuanHe` varchar(50) DEFAULT NULL,
  `trinhDoHienTai` varchar(30) DEFAULT NULL,
  `ngonNguMucTieu` varchar(50) DEFAULT NULL,
  `nguonBietDen` varchar(50) DEFAULT NULL,
  `ghiChu` text DEFAULT NULL,
  `cccd` varchar(20) DEFAULT NULL,
  `anhDaiDien` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `HoSoNguoiDung`
--

INSERT INTO `HoSoNguoiDung` (`taiKhoanId`, `hoTen`, `soDienThoai`, `zalo`, `ngaySinh`, `gioiTinh`, `diaChi`, `nguoiGiamHo`, `sdtGuardian`, `moiQuanHe`, `trinhDoHienTai`, `ngonNguMucTieu`, `nguonBietDen`, `ghiChu`, `cccd`, `anhDaiDien`, `created_at`, `updated_at`) VALUES
(1, 'Lê Văn Hậu', '0901234567', NULL, '1985-01-01', 1, 'HCM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123456789001', 'van-hau.png', '2026-01-23 20:07:12', '2026-02-04 03:22:48'),
(4, 'John Smith', '0904234567', NULL, '1988-10-10', 1, 'USA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123456789004', 'van-hau.png', '2026-01-23 20:07:12', '2026-01-26 19:55:38'),
(5, 'Nguyễn Văn An', '0907234567', NULL, '2005-03-20', 1, 'Q1, HCM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123456789007', 'hoang-le.png', '2026-01-23 20:07:12', '2026-01-26 19:57:12'),
(22, 'Lê Văn Hậu HV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-21 11:30:47', '2026-02-21 11:30:47'),
(23, 'Hậu Lê Văn', '0777464347', '0816548150', '2004-10-14', 1, 'Số 55, xã điện phong, thị xã điện bàn, tỉnh quảng nam', NULL, NULL, NULL, 'Elementary (Sơ cấp)', 'Tiếng Anh', 'Bạn bè giới thiệu', NULL, '049204011849', NULL, '2026-02-21 11:45:30', '2026-02-21 11:45:30'),
(26, 'Lê Văn Giáo Viên', '0816548150', NULL, '2000-10-14', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '049204011848', NULL, '2026-02-25 18:37:41', '2026-02-25 18:37:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `KhoaHoc`
--

CREATE TABLE `KhoaHoc` (
  `khoaHocId` int(11) NOT NULL,
  `loaiKhoaHocId` int(11) DEFAULT NULL,
  `tenKhoaHoc` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `anhKhoaHoc` varchar(255) DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `doiTuong` text DEFAULT NULL COMMENT 'Đối tượng học viên',
  `ketQuaDatDuoc` text DEFAULT NULL COMMENT 'Kết quả sau khóa học',
  `yeuCauDauVao` text DEFAULT NULL COMMENT 'Yêu cầu kiến thức đầu vào',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `KhoaHoc`
--

INSERT INTO `KhoaHoc` (`khoaHocId`, `loaiKhoaHocId`, `tenKhoaHoc`, `slug`, `anhKhoaHoc`, `moTa`, `trangThai`, `created_at`, `updated_at`, `doiTuong`, `ketQuaDatDuoc`, `yeuCauDauVao`, `deleted_at`) VALUES
(3, 1, 'IELTS Intensive 6.5+ 2', 'ielts-intensive-2', 'khoa-hoc/MSskHWqVdeFmSTZlVmKoJkciJzymNuQlOVTnek1d.jpg', 'Khóa học cấp tốc 3 tháng', 1, '2026-01-23 20:07:20', '2026-02-26 15:23:28', NULL, NULL, NULL, NULL),
(4, 1, 'Ielts cơ bản 1', 'ielts-co-ban-1', 'khoa-hoc/Fz8LGvPkv2JAvtNlsGBSSVWgrRng4FOJDMrXvT43.jpg', 'giới thiệu 01', 1, '2026-02-26 14:59:04', '2026-02-26 15:25:44', 'Người bắt đầu học', 'sẽ đạt được chứng chỉ ielts', 'yêu cầu học viên có kỹ năng thành thạo', '2026-02-26 08:25:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `LienHe`
--

CREATE TABLE `LienHe` (
  `lienHeId` int(11) NOT NULL,
  `hoTen` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `soDienThoai` varchar(15) DEFAULT NULL,
  `tieuDe` varchar(255) DEFAULT NULL,
  `noiDung` text DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `LienHe`
--

INSERT INTO `LienHe` (`lienHeId`, `hoTen`, `email`, `soDienThoai`, `tieuDe`, `noiDung`, `trangThai`, `taiKhoanId`, `created_at`, `updated_at`) VALUES
(1, 'Lê Hậu', 'Levanhaum@gmail.com', NULL, 'Đăng ký tư vấn miễn phí', 'Đăng ký tư vấn miễn phí\nKhóa học quan tâm: ielts-intensive\n', 0, 1, '2026-02-06 21:38:30', '2026-02-06 21:38:30'),
(2, 'Lê Văn Hậu', NULL, '0777464347', 'Đăng ký tư vấn miễn phí', 'Đăng ký tư vấn miễn phí\nKhóa học quan tâm: ielts-intensive\n', 0, 1, '2026-02-06 21:43:58', '2026-02-06 21:43:58'),
(3, 'Lê Văn Sỹ', 'Levanhaum@gmail.com', '0777464347', 'Đăng ký tư vấn miễn phí', 'Đăng ký tư vấn miễn phí\nKhóa học quan tâm: English for Beginners\nCơ sở: Chi nhánh Đà Nẵng 1\n', 0, 1, '2026-02-06 21:46:26', '2026-02-06 21:46:26'),
(4, 'Hậu Lê Văn', 'levanhaum@gmail.com', '0777464347', 'Đăng ký tư vấn miễn phí', 'Đăng ký tư vấn miễn phí\nKhóa học quan tâm: IELTS Intensive 6.5+\nCơ sở: Chi nhánh Quận 1\n', 0, 1, '2026-02-21 18:16:24', '2026-02-21 18:16:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `LoaiKhoaHoc`
--

CREATE TABLE `LoaiKhoaHoc` (
  `loaiKhoaHocId` int(11) NOT NULL,
  `tenLoai` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `moTa` text DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `LoaiKhoaHoc`
--

INSERT INTO `LoaiKhoaHoc` (`loaiKhoaHocId`, `tenLoai`, `slug`, `moTa`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'IELTS', 'ielts', 'Luyện thi chứng chỉ IELTS 4 kỹ năng', 1, '2026-01-23 20:07:20', '2026-01-25 13:18:28'),
(2, 'Communication', 'communication', 'Tiếng Anh giao tiếp mọi tình huống', 1, '2026-01-23 20:07:20', '2026-01-25 13:18:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `LopHoc`
--

CREATE TABLE `LopHoc` (
  `lopHocId` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `tenLopHoc` varchar(255) DEFAULT NULL,
  `khoaHocId` int(11) DEFAULT NULL,
  `phongHocId` int(11) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL COMMENT 'Giáo viên phụ trách',
  `hocPhiId` bigint(20) DEFAULT NULL,
  `ngayBatDau` date DEFAULT NULL,
  `ngayKetThuc` date DEFAULT NULL,
  `soBuoiDuKien` int(11) DEFAULT NULL,
  `soHocVienToiDa` int(11) DEFAULT NULL,
  `donGiaDay` decimal(15,2) DEFAULT NULL,
  `lichHoc` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `coSoId` int(11) DEFAULT NULL,
  `caHocId` int(11) NOT NULL,
  `trangThai` enum('0','1','2','3') DEFAULT NULL COMMENT '0: sắp mở, 1: đang học, 2: kết thúc, 3: hủy',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `LopHoc`
--

INSERT INTO `LopHoc` (`lopHocId`, `slug`, `tenLopHoc`, `khoaHocId`, `phongHocId`, `taiKhoanId`, `hocPhiId`, `ngayBatDau`, `ngayKetThuc`, `soBuoiDuKien`, `soHocVienToiDa`, `donGiaDay`, `lichHoc`, `coSoId`, `caHocId`, `trangThai`, `created_at`, `updated_at`) VALUES
(6, 'lop-tieng-anh-giao-tiep-123', 'Lớp tiếng anh giao tiếp 1', 3, NULL, NULL, NULL, '2026-02-26', '2026-03-26', 13, 40, 50000.00, '3,5,7', 13, 1, '0', '2026-02-26 14:45:21', '2026-02-26 15:45:45'),
(7, 'lop-tieng-anh-giao-tiep-123-1', 'Lớp tiếng anh giao tiếp 2', 3, 3, 26, NULL, '2026-02-27', '2026-06-30', 36, 40, 150000.00, '3,6', 13, 2, '0', '2026-02-26 15:30:54', '2026-02-26 15:46:38'),
(8, 'lop-tieng-anh-giao-tiep-1234', 'Lớp tiếng anh giao tiếp 3', 3, 4, 5, NULL, '2026-03-06', '2026-05-21', 33, 40, 225000.00, '3,6,7', 1, 2, '0', '2026-02-26 15:45:32', '2026-02-26 15:46:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `Luong`
--

CREATE TABLE `Luong` (
  `luongId` int(11) NOT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `thangLuong` varchar(10) DEFAULT NULL,
  `tongLuongDay` decimal(15,2) DEFAULT NULL,
  `thuong` decimal(15,2) DEFAULT 0.00,
  `phat` decimal(15,2) DEFAULT 0.00,
  `phuCap` decimal(15,2) DEFAULT 0.00,
  `tongTienThucLanh` decimal(15,2) DEFAULT NULL,
  `tongBuoiDay` int(11) DEFAULT NULL,
  `ngayThanhToan` date DEFAULT NULL,
  `phuongThucThanhToan` tinyint(4) DEFAULT NULL,
  `ghiChu` text DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `LuongChiTiet`
--

CREATE TABLE `LuongChiTiet` (
  `luongChiTietId` int(11) NOT NULL,
  `luongId` int(11) DEFAULT NULL,
  `lopHocId` int(11) DEFAULT NULL,
  `soBuoiDay` int(11) DEFAULT NULL,
  `donGiaMotBuoi` decimal(15,2) DEFAULT NULL,
  `tongTien` decimal(15,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_21_224413_add_ma_api_to_tinhthanh_table', 2),
(5, '2026_02_21_224430_add_address_fields_to_cosodaotao_table', 3),
(6, '2026_02_26_152345_add_soft_delete_to_khoahoc_table', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `NhanSu`
--

CREATE TABLE `NhanSu` (
  `taiKhoanId` int(11) NOT NULL,
  `maNhanVien` varchar(20) DEFAULT NULL,
  `chucVu` varchar(50) DEFAULT NULL,
  `luongCoBan` decimal(15,2) DEFAULT NULL,
  `ngayVaoLam` date DEFAULT NULL,
  `chuyenMon` varchar(255) DEFAULT NULL,
  `bangCap` varchar(255) DEFAULT NULL,
  `hocVi` varchar(100) DEFAULT NULL,
  `coSoId` int(11) DEFAULT NULL,
  `loaiHopDong` varchar(255) DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL COMMENT '0: đang làm, 1: tạm nghỉ, 2: đã nghỉ việc',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `NhanSu`
--

INSERT INTO `NhanSu` (`taiKhoanId`, `maNhanVien`, `chucVu`, `luongCoBan`, `ngayVaoLam`, `chuyenMon`, `bangCap`, `hocVi`, `coSoId`, `loaiHopDong`, `trangThai`, `created_at`, `updated_at`) VALUES
(4, 'GV001', 'Giáo viên bản ngữ', 30000000.00, '2024-01-10', 'IELTS Speaking', 'CELTA', 'Đại học', 1, 'Chính thức', 0, '2026-01-23 20:07:12', '2026-01-26 19:52:29'),
(5, 'GV002', 'Giáo viên VN', 18000000.00, '2024-02-01', 'Grammar', 'IELTS 8.5', 'Thạc Sĩ', 1, 'Chính thức', 0, '2026-01-23 20:07:12', '2026-02-21 23:45:00'),
(26, NULL, 'Giáo viên', NULL, '2026-02-25', 'Tiếng Anh', 'Cử nhân', NULL, 13, 'Toàn thời gian', 1, '2026-02-25 18:37:41', '2026-02-25 18:37:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhomQuyen`
--

CREATE TABLE `nhomQuyen` (
  `nhomQuyenId` int(11) UNSIGNED NOT NULL,
  `tenNhom` varchar(100) NOT NULL COMMENT 'VD: Kế toán, Nhân sự, Tư vấn học vụ',
  `moTa` varchar(255) DEFAULT NULL COMMENT 'Mô tả nhóm quyền',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhomQuyen`
--

INSERT INTO `nhomQuyen` (`nhomQuyenId`, `tenNhom`, `moTa`, `created_at`, `updated_at`) VALUES
(5, 'Kế toán', NULL, '2026-02-20 09:54:28', '2026-02-20 09:54:28'),
(6, 'Giáo viên', NULL, '2026-02-22 11:29:32', '2026-02-22 11:29:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `NoiDungBaiHoc`
--

CREATE TABLE `NoiDungBaiHoc` (
  `noiDungId` bigint(20) NOT NULL,
  `buoiHocId` int(11) DEFAULT NULL,
  `tieuDe` varchar(255) DEFAULT NULL,
  `noiDung` text DEFAULT NULL,
  `taiLieuId` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('levanhaum@gmail.com', '$2y$12$bL5mTSObEDCXF0GkMa9zT.K/wO3js71B/P/4Wg4UbkT3e5AXedRj6', '2026-02-19 04:49:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `PhanHoi`
--

CREATE TABLE `PhanHoi` (
  `phanHoiId` int(11) NOT NULL,
  `tieuDe` varchar(255) DEFAULT NULL,
  `noiDung` text DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `danhGia` tinyint(4) DEFAULT NULL,
  `buoiHocId` int(11) DEFAULT NULL,
  `trangThai` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phanQuyen`
--

CREATE TABLE `phanQuyen` (
  `phanQuyenId` int(11) UNSIGNED NOT NULL,
  `nhomQuyenId` int(11) UNSIGNED NOT NULL,
  `tinhNang` varchar(50) NOT NULL COMMENT 'khoa_hoc | lop_hoc | hoc_vien | giao_vien | nhan_vien | tai_chinh | dang_ky | tai_khoan | cai_dat',
  `coXem` tinyint(1) NOT NULL DEFAULT 0,
  `coThem` tinyint(1) NOT NULL DEFAULT 0,
  `coSua` tinyint(1) NOT NULL DEFAULT 0,
  `coXoa` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phanQuyen`
--

INSERT INTO `phanQuyen` (`phanQuyenId`, `nhomQuyenId`, `tinhNang`, `coXem`, `coThem`, `coSua`, `coXoa`, `created_at`, `updated_at`) VALUES
(22, 5, 'khoa_hoc', 1, 1, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(23, 5, 'lop_hoc', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(24, 5, 'hoc_vien', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(25, 5, 'giao_vien', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(26, 5, 'nhan_vien', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(27, 5, 'tai_chinh', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(28, 5, 'dang_ky', 1, 1, 1, 1, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(29, 5, 'tai_khoan', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(30, 5, 'cai_dat', 0, 0, 0, 0, '2026-02-20 09:57:00', '2026-02-20 09:57:00'),
(40, 6, 'khoa_hoc', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(41, 6, 'lop_hoc', 1, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(42, 6, 'hoc_vien', 1, 1, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(43, 6, 'giao_vien', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(44, 6, 'nhan_vien', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(45, 6, 'tai_chinh', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(46, 6, 'dang_ky', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(47, 6, 'tai_khoan', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23'),
(48, 6, 'cai_dat', 0, 0, 0, 0, '2026-02-22 17:56:23', '2026-02-22 17:56:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `PhieuThu`
--

CREATE TABLE `PhieuThu` (
  `phieuThuId` bigint(20) NOT NULL,
  `hoaDonId` int(11) DEFAULT NULL,
  `soTien` decimal(15,2) DEFAULT NULL,
  `ngayThu` date DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL COMMENT 'người thu',
  `ghiChu` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `PhongHoc`
--

CREATE TABLE `PhongHoc` (
  `phongHocId` int(11) NOT NULL,
  `tenPhong` varchar(100) DEFAULT NULL,
  `sucChua` int(11) DEFAULT NULL,
  `trangThietBi` text DEFAULT NULL,
  `coSoId` int(11) DEFAULT NULL,
  `trangThai` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `PhongHoc`
--

INSERT INTO `PhongHoc` (`phongHocId`, `tenPhong`, `sucChua`, `trangThietBi`, `coSoId`, `trangThai`, `created_at`, `updated_at`) VALUES
(3, 'P101', 50, 'tivi, điều hòa, loa', 13, 1, '2026-02-26 15:29:05', '2026-02-26 15:29:05'),
(4, 'P201', 40, 'tivi, điều hòa, loa', 1, 1, '2026-02-26 15:44:08', '2026-02-26 15:44:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('fRAZvnrn6sqWINzIRib6eP0T7VAahUEtNuCqC7ji', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoick85TmZSa2RjSXNzVU9OclNzaU96aE14aWVYbUpBMDFFenVWeWFRYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzcyMDg5NTE5O319', 1772090545),
('jvpf5cPfsKuFHyE4C1mt94y9jgEBTtap8qo1Nasf', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoibHNudVZOekI0SU5RM1ZLWnV1d0tZd3VpeG9la3JkdUUyd3VyVGFJTiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4va2hvYS1ob2MvdGFvLW1vaSI7czo1OiJyb3V0ZSI7czoyMToiYWRtaW4ua2hvYS1ob2MuY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NzIwOTA2MjM7fX0=', 1772095947);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `display_name` varchar(191) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'text',
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `display_name`, `type`, `group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Five Genius Academy', 'Tên trung tâm', 'text', 'general', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(2, 'site_phone', '0777464347', 'Số điện thoại', 'text', 'general', '2026-01-28 11:30:17', '2026-01-28 11:38:29'),
(3, 'site_email', 'contact@fivegenius.edu.vn', 'Email liên hệ', 'text', 'general', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(4, 'site_address', '27G1 Nguyễn Oanh, Phường 7, TP. Vũng Tàu', 'Địa chỉ', 'textarea', 'general', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(5, 'site_logo', 'uploads/settings/logo.png', 'Logo Website', 'image', 'general', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(6, 'site_favicon', 'uploads/settings/favicon.ico', 'Favicon', 'image', 'general', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(7, 'facebook_url', 'https://facebook.com/fivegenius', 'Link Facebook', 'text', 'social', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(8, 'youtube_url', 'https://youtube.com/@fivegenius', 'Link Youtube', 'text', 'social', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(9, 'tiktok_url', 'https://tiktok.com/@fivegenius', 'Link Tiktok', 'text', 'social', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(10, 'meta_title', 'Five Genius Academy - Trung tâm Anh ngữ hàng đầu', 'Tiêu đề SEO', 'text', 'seo', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(11, 'meta_description', 'Đào tạo IELTS, SAT, tiếng Anh giao tiếp chuyên nghiệp với lộ trình cá nhân hóa.', 'Mô tả SEO', 'textarea', 'seo', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(12, 'google_analytics_id', 'UA-XXXXX-Y', 'Mã Google Analytics', 'text', 'seo', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(13, 'dark_mode', '0', 'Chế độ nền tối (1: Bật, 0: Tắt)', 'text', 'theme', '2026-01-28 11:30:17', '2026-01-28 11:30:17'),
(14, 'primary_color', '#e32c2d', 'Màu chủ đạo (Navy)', 'text', 'theme', '2026-01-28 11:30:17', '2026-01-28 11:42:43'),
(15, 'accent_color', '#E31E24', 'Màu nhấn (Đỏ)', 'text', 'theme', '2026-01-28 11:30:17', '2026-01-28 11:30:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `Tags`
--

CREATE TABLE `Tags` (
  `tagId` bigint(20) UNSIGNED NOT NULL,
  `tenTag` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `Tags`
--

INSERT INTO `Tags` (`tagId`, `tenTag`, `slug`, `created_at`) VALUES
(1, 'IELTS', 'ielts', '2026-01-27 06:11:36'),
(2, 'TOEIC', 'toeic', '2026-01-27 06:11:36'),
(3, 'Giao tiếp', 'giao-tiep', '2026-01-27 06:11:36'),
(4, 'Ngữ pháp', 'ngu-phap', '2026-01-27 06:11:36'),
(5, 'Từ vựng', 'tu-vung', '2026-01-27 06:11:36'),
(6, 'Listening', 'listening', '2026-01-27 06:11:36'),
(7, 'Speaking', 'speaking', '2026-01-27 06:11:36'),
(8, 'Reading', 'reading', '2026-01-27 06:11:36'),
(9, 'Writing', 'writing', '2026-01-27 06:11:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `taiKhoanId` int(11) NOT NULL,
  `taiKhoan` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `role` tinyint(4) NOT NULL COMMENT '0: học viên, 1: giáo viên, 2: nhân viên, 3: admin',
  `nhomQuyenId` int(11) UNSIGNED DEFAULT NULL COMMENT 'Chỉ dùng cho role 1 (GV) và 2 (NV)',
  `trangThai` tinyint(4) DEFAULT 1 COMMENT '0: Khóa, 1: Hoạt động, 2: Chờ kích hoạt',
  `remember_token` varchar(100) DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`taiKhoanId`, `taiKhoan`, `matKhau`, `email`, `role`, `nhomQuyenId`, `trangThai`, `remember_token`, `lastLogin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', '$2y$12$R12EhHjbvn9YPWfel4EVXO4Q4IF8u60X//oZvBmy.vSQ3AWjXB3AO', 'levanhaum@gmail.com', 3, NULL, 1, 'scUzQ3Xun0c6J39txhqo1vY9hQEVzi8ZgH45nwUHErbNLWraMgwOeozfxDkk', '2026-02-26 14:23:43', '2026-01-23 20:07:12', '2026-02-26 14:23:43', NULL),
(4, 'gv_john_smith', '$2y$10$abcdef', 'Levanhaum11@gmail.com', 1, 6, 0, NULL, NULL, '2026-01-23 20:07:12', '2026-02-25 18:18:17', NULL),
(5, 'gv_le_hoa', '$2y$12$X76PLtZH03IrMNVYcJMCGuhR7iwOGUI9onnYLmZCICcWDEic8Ux7S', 'Levanhaum10@gmail.com', 1, 6, 1, NULL, '2026-02-22 17:24:40', '2026-01-23 20:07:12', '2026-02-22 17:24:40', NULL),
(22, 'Levanhaum1@gmail.com', '$2y$12$1IY6Um0/pOUrYWknZESQmeP8aPcuEiIo.p3FBmrvlqsp7ck7bRYLy', 'Levanhaum1@gmail.com', 0, NULL, 1, NULL, '2026-02-21 11:37:31', '2026-02-21 11:30:47', '2026-02-21 11:54:20', '2026-02-21 04:54:20'),
(23, 'User_049204011849', '$2y$12$Jl5dI7CO8vRlQIaEI5Rlh.6sTKQXeFSbBZ.9w70Lqjzv9ef0k368u', 'levanhau2@gmail.com', 0, NULL, 1, NULL, '2026-02-21 11:47:40', '2026-02-21 11:45:30', '2026-02-21 11:54:14', NULL),
(26, 'User_049204011848', '$2y$12$LAlX9l/zGiYLnfbl6eQ9SeNjv5zpUWLyojiwHcc3VQSQydH/rWv9e', 'Levanhaugv@gmail.com', 1, 6, 1, NULL, '2026-02-25 18:38:32', '2026-02-25 18:37:41', '2026-02-25 18:38:32', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `TaiLieu`
--

CREATE TABLE `TaiLieu` (
  `taiLieuId` varchar(50) NOT NULL,
  `tenTaiLieu` varchar(255) DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `loaiTaiLieu` varchar(50) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `duongDan` varchar(255) DEFAULT NULL,
  `khoaHocId` int(11) DEFAULT NULL,
  `buoiHocId` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ThongBao`
--

CREATE TABLE `ThongBao` (
  `thongBaoId` bigint(20) NOT NULL,
  `tieuDe` varchar(255) DEFAULT NULL,
  `noiDung` text DEFAULT NULL,
  `nguoiGuiId` int(11) DEFAULT NULL,
  `loaiThongBao` tinyint(4) DEFAULT NULL,
  `doiTuongGui` tinyint(4) DEFAULT NULL COMMENT '0-Tất cả, 1-Theo lớp, 2-Theo khóa học, 3-Cá nhân',
  `doiTuongId` bigint(20) DEFAULT NULL,
  `ngayGui` datetime DEFAULT current_timestamp(),
  `trangThai` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ThongBao`
--

INSERT INTO `ThongBao` (`thongBaoId`, `tieuDe`, `noiDung`, `nguoiGuiId`, `loaiThongBao`, `doiTuongGui`, `doiTuongId`, `ngayGui`, `trangThai`, `created_at`, `updated_at`) VALUES
(1, 'Nghỉ Tết 2026', 'Trung tâm nghỉ tết từ ngày 25/01/2026', 1, 1, 0, NULL, '2026-01-23 20:07:39', NULL, '2026-01-23 20:07:39', '2026-01-23 20:07:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ThongBaoNguoiDung`
--

CREATE TABLE `ThongBaoNguoiDung` (
  `id` bigint(20) NOT NULL,
  `thongBaoId` bigint(20) DEFAULT NULL,
  `taiKhoanId` int(11) DEFAULT NULL,
  `daDoc` tinyint(4) DEFAULT 0,
  `ngayDoc` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `TinhThanh`
--

CREATE TABLE `TinhThanh` (
  `tinhThanhId` int(11) NOT NULL,
  `maAPI` int(10) UNSIGNED DEFAULT NULL,
  `tenTinhThanh` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `division_type` varchar(50) DEFAULT NULL,
  `codename` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `TinhThanh`
--

INSERT INTO `TinhThanh` (`tinhThanhId`, `maAPI`, `tenTinhThanh`, `slug`, `division_type`, `codename`) VALUES
(190, 1, 'Thành phố Hà Nội', 'ha_noi', 'thành phố trung ương', 'ha_noi'),
(191, 4, 'Tỉnh Cao Bằng', 'cao_bang', 'tỉnh', 'cao_bang'),
(192, 8, 'Tỉnh Tuyên Quang', 'tuyen_quang', 'tỉnh', 'tuyen_quang'),
(193, 11, 'Tỉnh Điện Biên', 'dien_bien', 'tỉnh', 'dien_bien'),
(194, 12, 'Tỉnh Lai Châu', 'lai_chau', 'tỉnh', 'lai_chau'),
(195, 14, 'Tỉnh Sơn La', 'son_la', 'tỉnh', 'son_la'),
(196, 15, 'Tỉnh Lào Cai', 'lao_cai', 'tỉnh', 'lao_cai'),
(197, 19, 'Tỉnh Thái Nguyên', 'thai_nguyen', 'tỉnh', 'thai_nguyen'),
(198, 20, 'Tỉnh Lạng Sơn', 'lang_son', 'tỉnh', 'lang_son'),
(199, 22, 'Tỉnh Quảng Ninh', 'quang_ninh', 'tỉnh', 'quang_ninh'),
(200, 24, 'Tỉnh Bắc Ninh', 'bac_ninh', 'tỉnh', 'bac_ninh'),
(201, 25, 'Tỉnh Phú Thọ', 'phu_tho', 'tỉnh', 'phu_tho'),
(202, 31, 'Thành phố Hải Phòng', 'hai_phong', 'thành phố trung ương', 'hai_phong'),
(203, 33, 'Tỉnh Hưng Yên', 'hung_yen', 'tỉnh', 'hung_yen'),
(204, 37, 'Tỉnh Ninh Bình', 'ninh_binh', 'tỉnh', 'ninh_binh'),
(205, 38, 'Tỉnh Thanh Hóa', 'thanh_hoa', 'tỉnh', 'thanh_hoa'),
(206, 40, 'Tỉnh Nghệ An', 'nghe_an', 'tỉnh', 'nghe_an'),
(207, 42, 'Tỉnh Hà Tĩnh', 'ha_tinh', 'tỉnh', 'ha_tinh'),
(208, 44, 'Tỉnh Quảng Trị', 'quang_tri', 'tỉnh', 'quang_tri'),
(209, 46, 'Thành phố Huế', 'hue', 'thành phố trung ương', 'hue'),
(210, 48, 'Thành phố Đà Nẵng', 'da_nang', 'thành phố trung ương', 'da_nang'),
(211, 51, 'Tỉnh Quảng Ngãi', 'quang_ngai', 'tỉnh', 'quang_ngai'),
(212, 52, 'Tỉnh Gia Lai', 'gia_lai', 'tỉnh', 'gia_lai'),
(213, 56, 'Tỉnh Khánh Hòa', 'khanh_hoa', 'tỉnh', 'khanh_hoa'),
(214, 66, 'Tỉnh Đắk Lắk', 'dak_lak', 'tỉnh', 'dak_lak'),
(215, 68, 'Tỉnh Lâm Đồng', 'lam_dong', 'tỉnh', 'lam_dong'),
(216, 75, 'Tỉnh Đồng Nai', 'dong_nai', 'tỉnh', 'dong_nai'),
(217, 79, 'Thành phố Hồ Chí Minh', 'ho_chi_minh', 'thành phố trung ương', 'ho_chi_minh'),
(218, 80, 'Tỉnh Tây Ninh', 'tay_ninh', 'tỉnh', 'tay_ninh'),
(219, 82, 'Tỉnh Đồng Tháp', 'dong_thap', 'tỉnh', 'dong_thap'),
(220, 86, 'Tỉnh Vĩnh Long', 'vinh_long', 'tỉnh', 'vinh_long'),
(221, 91, 'Tỉnh An Giang', 'an_giang', 'tỉnh', 'an_giang'),
(222, 92, 'Thành phố Cần Thơ', 'can_tho', 'thành phố trung ương', 'can_tho'),
(223, 96, 'Tỉnh Cà Mau', 'ca_mau', 'tỉnh', 'ca_mau');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `BaiThi`
--
ALTER TABLE `BaiThi`
  ADD PRIMARY KEY (`baiThiId`),
  ADD KEY `fk_baithi_khoa` (`khoaHocId`);

--
-- Chỉ mục cho bảng `BaiViet`
--
ALTER TABLE `BaiViet`
  ADD PRIMARY KEY (`baiVietId`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_baiviet_taikhoan` (`taiKhoanId`);

--
-- Chỉ mục cho bảng `BaiViet_DanhMuc`
--
ALTER TABLE `BaiViet_DanhMuc`
  ADD PRIMARY KEY (`baiVietId`,`danhMucId`),
  ADD KEY `fk_bvdm_danhmuc` (`danhMucId`);

--
-- Chỉ mục cho bảng `BaiViet_Tag`
--
ALTER TABLE `BaiViet_Tag`
  ADD PRIMARY KEY (`baiVietId`,`tagId`),
  ADD KEY `fk_bvt_tag` (`tagId`);

--
-- Chỉ mục cho bảng `BuoiHoc`
--
ALTER TABLE `BuoiHoc`
  ADD PRIMARY KEY (`buoiHocId`),
  ADD KEY `fk_buoi_gv` (`taiKhoanId`),
  ADD KEY `fk_buoi_phong` (`phongHocId`),
  ADD KEY `fk_buoi_ca` (`caHocId`),
  ADD KEY `fk_buoi_lich` (`lopHocId`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `CaHoc`
--
ALTER TABLE `CaHoc`
  ADD PRIMARY KEY (`caHocId`);

--
-- Chỉ mục cho bảng `CoSoDaoTao`
--
ALTER TABLE `CoSoDaoTao`
  ADD PRIMARY KEY (`coSoId`),
  ADD UNIQUE KEY `maCoSo` (`maCoSo`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_coso_tinhthanh` (`tinhThanhId`);

--
-- Chỉ mục cho bảng `DangKyLopHoc`
--
ALTER TABLE `DangKyLopHoc`
  ADD PRIMARY KEY (`dangKyLopHocId`),
  ADD KEY `fk_dk_hocvien` (`taiKhoanId`),
  ADD KEY `fk_dk_lich` (`lopHocId`);

--
-- Chỉ mục cho bảng `DanhGiaGiaoVien`
--
ALTER TABLE `DanhGiaGiaoVien`
  ADD PRIMARY KEY (`danhGiaId`),
  ADD KEY `fk_dg_gv` (`giaoVienId`),
  ADD KEY `fk_dg_hv` (`hocVienId`),
  ADD KEY `fk_dg_lich` (`lopHocId`);

--
-- Chỉ mục cho bảng `DanhMucBaiViet`
--
ALTER TABLE `DanhMucBaiViet`
  ADD PRIMARY KEY (`danhMucId`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `DiemBaiThi`
--
ALTER TABLE `DiemBaiThi`
  ADD PRIMARY KEY (`diemThiId`),
  ADD KEY `fk_diem_hocvien` (`taiKhoanId`),
  ADD KEY `fk_diem_baithi` (`baiThiId`);

--
-- Chỉ mục cho bảng `DiemDanh`
--
ALTER TABLE `DiemDanh`
  ADD PRIMARY KEY (`diemDanhId`),
  ADD KEY `fk_dd_hocvien` (`taiKhoanId`),
  ADD KEY `fk_dd_buoi` (`buoiHocId`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `HoaDon`
--
ALTER TABLE `HoaDon`
  ADD PRIMARY KEY (`hoaDonId`),
  ADD KEY `fk_hd_hocvien` (`taiKhoanId`),
  ADD KEY `fk_hd_dk` (`dangKyLopHocId`),
  ADD KEY `fk_hd_coso` (`coSoId`);

--
-- Chỉ mục cho bảng `HocPhi`
--
ALTER TABLE `HocPhi`
  ADD PRIMARY KEY (`hocPhiId`),
  ADD KEY `fk_hocphi_khoahoc` (`khoaHocId`);

--
-- Chỉ mục cho bảng `HoSoNguoiDung`
--
ALTER TABLE `HoSoNguoiDung`
  ADD PRIMARY KEY (`taiKhoanId`),
  ADD UNIQUE KEY `cccd` (`cccd`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `KhoaHoc`
--
ALTER TABLE `KhoaHoc`
  ADD PRIMARY KEY (`khoaHocId`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_khoahoc_loai` (`loaiKhoaHocId`);

--
-- Chỉ mục cho bảng `LienHe`
--
ALTER TABLE `LienHe`
  ADD PRIMARY KEY (`lienHeId`),
  ADD KEY `fk_lh_taikhoan` (`taiKhoanId`);

--
-- Chỉ mục cho bảng `LoaiKhoaHoc`
--
ALTER TABLE `LoaiKhoaHoc`
  ADD PRIMARY KEY (`loaiKhoaHocId`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `LopHoc`
--
ALTER TABLE `LopHoc`
  ADD PRIMARY KEY (`lopHocId`),
  ADD KEY `fk_lop_giaovien` (`taiKhoanId`),
  ADD KEY `fk_lop_hocphi` (`hocPhiId`),
  ADD KEY `fk_lop_khoahoc` (`khoaHocId`),
  ADD KEY `fk_lop_phong` (`phongHocId`),
  ADD KEY `fk_lop_coso` (`coSoId`),
  ADD KEY `fk_lop_cahoc` (`caHocId`);

--
-- Chỉ mục cho bảng `Luong`
--
ALTER TABLE `Luong`
  ADD PRIMARY KEY (`luongId`),
  ADD KEY `fk_luong_taikhoan` (`taiKhoanId`);

--
-- Chỉ mục cho bảng `LuongChiTiet`
--
ALTER TABLE `LuongChiTiet`
  ADD PRIMARY KEY (`luongChiTietId`),
  ADD KEY `fk_ctluong_luong` (`luongId`),
  ADD KEY `fk_ctluong_lich` (`lopHocId`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `NhanSu`
--
ALTER TABLE `NhanSu`
  ADD PRIMARY KEY (`taiKhoanId`),
  ADD UNIQUE KEY `maNhanVien` (`maNhanVien`),
  ADD KEY `fk_nhansu_coso` (`coSoId`);

--
-- Chỉ mục cho bảng `nhomQuyen`
--
ALTER TABLE `nhomQuyen`
  ADD PRIMARY KEY (`nhomQuyenId`);

--
-- Chỉ mục cho bảng `NoiDungBaiHoc`
--
ALTER TABLE `NoiDungBaiHoc`
  ADD PRIMARY KEY (`noiDungId`),
  ADD KEY `fk_nd_buoi` (`buoiHocId`),
  ADD KEY `fk_nd_tailieu` (`taiLieuId`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `PhanHoi`
--
ALTER TABLE `PhanHoi`
  ADD PRIMARY KEY (`phanHoiId`),
  ADD KEY `fk_ph_taikhoan` (`taiKhoanId`),
  ADD KEY `fk_ph_buoi` (`buoiHocId`);

--
-- Chỉ mục cho bảng `phanQuyen`
--
ALTER TABLE `phanQuyen`
  ADD PRIMARY KEY (`phanQuyenId`),
  ADD UNIQUE KEY `uk_nhom_tinhnang` (`nhomQuyenId`,`tinhNang`);

--
-- Chỉ mục cho bảng `PhieuThu`
--
ALTER TABLE `PhieuThu`
  ADD PRIMARY KEY (`phieuThuId`),
  ADD KEY `fk_pt_hoadon` (`hoaDonId`),
  ADD KEY `fk_pt_nhanvien` (`taiKhoanId`);

--
-- Chỉ mục cho bảng `PhongHoc`
--
ALTER TABLE `PhongHoc`
  ADD PRIMARY KEY (`phongHocId`),
  ADD KEY `fk_phong_coso` (`coSoId`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Chỉ mục cho bảng `Tags`
--
ALTER TABLE `Tags`
  ADD PRIMARY KEY (`tagId`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`taiKhoanId`),
  ADD UNIQUE KEY `taiKhoan` (`taiKhoan`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_tk_nhomquyen` (`nhomQuyenId`);

--
-- Chỉ mục cho bảng `TaiLieu`
--
ALTER TABLE `TaiLieu`
  ADD PRIMARY KEY (`taiLieuId`),
  ADD KEY `fk_tl_taikhoan` (`taiKhoanId`),
  ADD KEY `fk_tl_khoahoc` (`khoaHocId`);

--
-- Chỉ mục cho bảng `ThongBao`
--
ALTER TABLE `ThongBao`
  ADD PRIMARY KEY (`thongBaoId`),
  ADD KEY `fk_tb_nguoigui` (`nguoiGuiId`);

--
-- Chỉ mục cho bảng `ThongBaoNguoiDung`
--
ALTER TABLE `ThongBaoNguoiDung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbnd_thongbao` (`thongBaoId`),
  ADD KEY `fk_tbnd_taikhoan` (`taiKhoanId`);

--
-- Chỉ mục cho bảng `TinhThanh`
--
ALTER TABLE `TinhThanh`
  ADD PRIMARY KEY (`tinhThanhId`),
  ADD UNIQUE KEY `tinhthanh_maapi_unique` (`maAPI`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `BaiThi`
--
ALTER TABLE `BaiThi`
  MODIFY `baiThiId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `BaiViet`
--
ALTER TABLE `BaiViet`
  MODIFY `baiVietId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `BuoiHoc`
--
ALTER TABLE `BuoiHoc`
  MODIFY `buoiHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT cho bảng `CaHoc`
--
ALTER TABLE `CaHoc`
  MODIFY `caHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `CoSoDaoTao`
--
ALTER TABLE `CoSoDaoTao`
  MODIFY `coSoId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `DangKyLopHoc`
--
ALTER TABLE `DangKyLopHoc`
  MODIFY `dangKyLopHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT cho bảng `DanhGiaGiaoVien`
--
ALTER TABLE `DanhGiaGiaoVien`
  MODIFY `danhGiaId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `DanhMucBaiViet`
--
ALTER TABLE `DanhMucBaiViet`
  MODIFY `danhMucId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `DiemBaiThi`
--
ALTER TABLE `DiemBaiThi`
  MODIFY `diemThiId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `HoaDon`
--
ALTER TABLE `HoaDon`
  MODIFY `hoaDonId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `HocPhi`
--
ALTER TABLE `HocPhi`
  MODIFY `hocPhiId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `KhoaHoc`
--
ALTER TABLE `KhoaHoc`
  MODIFY `khoaHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `LienHe`
--
ALTER TABLE `LienHe`
  MODIFY `lienHeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `LoaiKhoaHoc`
--
ALTER TABLE `LoaiKhoaHoc`
  MODIFY `loaiKhoaHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `LopHoc`
--
ALTER TABLE `LopHoc`
  MODIFY `lopHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `Luong`
--
ALTER TABLE `Luong`
  MODIFY `luongId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `LuongChiTiet`
--
ALTER TABLE `LuongChiTiet`
  MODIFY `luongChiTietId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `nhomQuyen`
--
ALTER TABLE `nhomQuyen`
  MODIFY `nhomQuyenId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `NoiDungBaiHoc`
--
ALTER TABLE `NoiDungBaiHoc`
  MODIFY `noiDungId` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `PhanHoi`
--
ALTER TABLE `PhanHoi`
  MODIFY `phanHoiId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `phanQuyen`
--
ALTER TABLE `phanQuyen`
  MODIFY `phanQuyenId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT cho bảng `PhieuThu`
--
ALTER TABLE `PhieuThu`
  MODIFY `phieuThuId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `PhongHoc`
--
ALTER TABLE `PhongHoc`
  MODIFY `phongHocId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `Tags`
--
ALTER TABLE `Tags`
  MODIFY `tagId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  MODIFY `taiKhoanId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `ThongBao`
--
ALTER TABLE `ThongBao`
  MODIFY `thongBaoId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `ThongBaoNguoiDung`
--
ALTER TABLE `ThongBaoNguoiDung`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `TinhThanh`
--
ALTER TABLE `TinhThanh`
  MODIFY `tinhThanhId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `BaiThi`
--
ALTER TABLE `BaiThi`
  ADD CONSTRAINT `fk_baithi_khoa` FOREIGN KEY (`khoaHocId`) REFERENCES `KhoaHoc` (`khoaHocId`);

--
-- Các ràng buộc cho bảng `BaiViet`
--
ALTER TABLE `BaiViet`
  ADD CONSTRAINT `fk_baiviet_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `BaiViet_DanhMuc`
--
ALTER TABLE `BaiViet_DanhMuc`
  ADD CONSTRAINT `fk_bvdm_baiviet` FOREIGN KEY (`baiVietId`) REFERENCES `BaiViet` (`baiVietId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bvdm_danhmuc` FOREIGN KEY (`danhMucId`) REFERENCES `DanhMucBaiViet` (`danhMucId`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `BaiViet_Tag`
--
ALTER TABLE `BaiViet_Tag`
  ADD CONSTRAINT `fk_bvt_baiviet` FOREIGN KEY (`baiVietId`) REFERENCES `BaiViet` (`baiVietId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bvt_tag` FOREIGN KEY (`tagId`) REFERENCES `Tags` (`tagId`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `BuoiHoc`
--
ALTER TABLE `BuoiHoc`
  ADD CONSTRAINT `fk_buoi_ca` FOREIGN KEY (`caHocId`) REFERENCES `CaHoc` (`caHocId`),
  ADD CONSTRAINT `fk_buoi_gv` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_buoi_lich` FOREIGN KEY (`lopHocId`) REFERENCES `LopHoc` (`lopHocId`),
  ADD CONSTRAINT `fk_buoi_phong` FOREIGN KEY (`phongHocId`) REFERENCES `PhongHoc` (`phongHocId`);

--
-- Các ràng buộc cho bảng `CoSoDaoTao`
--
ALTER TABLE `CoSoDaoTao`
  ADD CONSTRAINT `fk_coso_tinhthanh` FOREIGN KEY (`tinhThanhId`) REFERENCES `TinhThanh` (`tinhThanhId`);

--
-- Các ràng buộc cho bảng `DangKyLopHoc`
--
ALTER TABLE `DangKyLopHoc`
  ADD CONSTRAINT `fk_dk_hocvien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_dk_lich` FOREIGN KEY (`lopHocId`) REFERENCES `LopHoc` (`lopHocId`);

--
-- Các ràng buộc cho bảng `DanhGiaGiaoVien`
--
ALTER TABLE `DanhGiaGiaoVien`
  ADD CONSTRAINT `fk_dg_gv` FOREIGN KEY (`giaoVienId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_dg_hv` FOREIGN KEY (`hocVienId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_dg_lich` FOREIGN KEY (`lopHocId`) REFERENCES `LopHoc` (`lopHocId`);

--
-- Các ràng buộc cho bảng `DiemBaiThi`
--
ALTER TABLE `DiemBaiThi`
  ADD CONSTRAINT `fk_diem_baithi` FOREIGN KEY (`baiThiId`) REFERENCES `BaiThi` (`baiThiId`),
  ADD CONSTRAINT `fk_diem_hocvien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `DiemDanh`
--
ALTER TABLE `DiemDanh`
  ADD CONSTRAINT `fk_dd_buoi` FOREIGN KEY (`buoiHocId`) REFERENCES `BuoiHoc` (`buoiHocId`),
  ADD CONSTRAINT `fk_dd_hocvien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `HoaDon`
--
ALTER TABLE `HoaDon`
  ADD CONSTRAINT `fk_hd_coso` FOREIGN KEY (`coSoId`) REFERENCES `CoSoDaoTao` (`coSoId`),
  ADD CONSTRAINT `fk_hd_dk` FOREIGN KEY (`dangKyLopHocId`) REFERENCES `DangKyLopHoc` (`dangKyLopHocId`),
  ADD CONSTRAINT `fk_hd_hocvien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `HocPhi`
--
ALTER TABLE `HocPhi`
  ADD CONSTRAINT `fk_hocphi_khoahoc` FOREIGN KEY (`khoaHocId`) REFERENCES `KhoaHoc` (`khoaHocId`);

--
-- Các ràng buộc cho bảng `HoSoNguoiDung`
--
ALTER TABLE `HoSoNguoiDung`
  ADD CONSTRAINT `fk_hoso_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `KhoaHoc`
--
ALTER TABLE `KhoaHoc`
  ADD CONSTRAINT `fk_khoahoc_loai` FOREIGN KEY (`loaiKhoaHocId`) REFERENCES `LoaiKhoaHoc` (`loaiKhoaHocId`);

--
-- Các ràng buộc cho bảng `LienHe`
--
ALTER TABLE `LienHe`
  ADD CONSTRAINT `fk_lh_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `LopHoc`
--
ALTER TABLE `LopHoc`
  ADD CONSTRAINT `fk_lop_cahoc` FOREIGN KEY (`caHocId`) REFERENCES `CaHoc` (`caHocId`),
  ADD CONSTRAINT `fk_lop_coso` FOREIGN KEY (`coSoId`) REFERENCES `CoSoDaoTao` (`coSoId`),
  ADD CONSTRAINT `fk_lop_giaovien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_lop_hocphi` FOREIGN KEY (`hocPhiId`) REFERENCES `HocPhi` (`hocPhiId`),
  ADD CONSTRAINT `fk_lop_khoahoc` FOREIGN KEY (`khoaHocId`) REFERENCES `KhoaHoc` (`khoaHocId`),
  ADD CONSTRAINT `fk_lop_phong` FOREIGN KEY (`phongHocId`) REFERENCES `PhongHoc` (`phongHocId`);

--
-- Các ràng buộc cho bảng `Luong`
--
ALTER TABLE `Luong`
  ADD CONSTRAINT `fk_luong_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `LuongChiTiet`
--
ALTER TABLE `LuongChiTiet`
  ADD CONSTRAINT `fk_ctluong_lich` FOREIGN KEY (`lopHocId`) REFERENCES `LopHoc` (`lopHocId`),
  ADD CONSTRAINT `fk_ctluong_luong` FOREIGN KEY (`luongId`) REFERENCES `Luong` (`luongId`);

--
-- Các ràng buộc cho bảng `NhanSu`
--
ALTER TABLE `NhanSu`
  ADD CONSTRAINT `fk_nhansu_coso` FOREIGN KEY (`coSoId`) REFERENCES `CoSoDaoTao` (`coSoId`),
  ADD CONSTRAINT `fk_nhansu_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `NoiDungBaiHoc`
--
ALTER TABLE `NoiDungBaiHoc`
  ADD CONSTRAINT `fk_nd_buoi` FOREIGN KEY (`buoiHocId`) REFERENCES `BuoiHoc` (`buoiHocId`),
  ADD CONSTRAINT `fk_nd_tailieu` FOREIGN KEY (`taiLieuId`) REFERENCES `TaiLieu` (`taiLieuId`);

--
-- Các ràng buộc cho bảng `PhanHoi`
--
ALTER TABLE `PhanHoi`
  ADD CONSTRAINT `fk_ph_buoi` FOREIGN KEY (`buoiHocId`) REFERENCES `BuoiHoc` (`buoiHocId`),
  ADD CONSTRAINT `fk_ph_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `phanQuyen`
--
ALTER TABLE `phanQuyen`
  ADD CONSTRAINT `fk_pq_nhomquyen` FOREIGN KEY (`nhomQuyenId`) REFERENCES `nhomQuyen` (`nhomQuyenId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `PhieuThu`
--
ALTER TABLE `PhieuThu`
  ADD CONSTRAINT `fk_pt_hoadon` FOREIGN KEY (`hoaDonId`) REFERENCES `HoaDon` (`hoaDonId`),
  ADD CONSTRAINT `fk_pt_nhanvien` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `PhongHoc`
--
ALTER TABLE `PhongHoc`
  ADD CONSTRAINT `fk_phong_coso` FOREIGN KEY (`coSoId`) REFERENCES `CoSoDaoTao` (`coSoId`);

--
-- Các ràng buộc cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `fk_tk_nhomquyen` FOREIGN KEY (`nhomQuyenId`) REFERENCES `nhomQuyen` (`nhomQuyenId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `TaiLieu`
--
ALTER TABLE `TaiLieu`
  ADD CONSTRAINT `fk_tl_khoahoc` FOREIGN KEY (`khoaHocId`) REFERENCES `KhoaHoc` (`khoaHocId`),
  ADD CONSTRAINT `fk_tl_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `ThongBao`
--
ALTER TABLE `ThongBao`
  ADD CONSTRAINT `fk_tb_nguoigui` FOREIGN KEY (`nguoiGuiId`) REFERENCES `TaiKhoan` (`taiKhoanId`);

--
-- Các ràng buộc cho bảng `ThongBaoNguoiDung`
--
ALTER TABLE `ThongBaoNguoiDung`
  ADD CONSTRAINT `fk_tbnd_taikhoan` FOREIGN KEY (`taiKhoanId`) REFERENCES `TaiKhoan` (`taiKhoanId`),
  ADD CONSTRAINT `fk_tbnd_thongbao` FOREIGN KEY (`thongBaoId`) REFERENCES `ThongBao` (`thongBaoId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
