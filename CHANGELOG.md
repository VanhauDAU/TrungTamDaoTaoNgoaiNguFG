# Changelog

Tất cả thay đổi đáng chú ý của dự án sẽ được ghi tại đây.

## [2026-03-12] - Nâng cấp Auth toàn hệ thống

### Added
- Tách cổng đăng nhập học viên `/login` và cổng đăng nhập nhân sự `/admin/login`.
- Bật xác thực email cho học viên tự đăng ký.
- Thêm đăng nhập Google cho học viên.
- Thêm Google reCAPTCHA v3 cho các form public:
  - đăng nhập học viên
  - đăng ký học viên
  - quên mật khẩu
- Thêm middleware `verified.student` để chặn học viên chưa xác thực email khỏi khu vực học viên và các API client cần xác thực.
- Thêm các cột auth mới cho bảng `taikhoan`:
  - `email_verified_at`
  - `auth_provider`
  - `google_id`
  - `google_avatar`
- Thêm bộ tài liệu Auth:
  - quyết định kiến trúc
  - cấu hình môi trường
  - vận hành và kiểm thử

### Changed
- Chuẩn hóa `username` thành mã hệ thống theo role:
  - `HV######` cho học viên
  - `GV######` cho giáo viên
  - `NV######` cho nhân viên
  - `AD######` cho admin
- Luồng tự đăng ký học viên không còn dùng `taiKhoan = email`.
- Luồng admin tạo tài khoản học viên/giáo viên/nhân viên không còn sinh username theo CCCD.
- Cập nhật giao diện login/register/verify để phản ánh luồng xác thực mới.
- Cập nhật link đăng nhập trong layout chung để phân biệt học viên và nhân sự.

### Security
- Khóa social login chỉ cho `role = học viên`.
- Không cho staff dùng Google login để vào khu vực nhân sự.
- Học viên chưa xác thực email không được truy cập khu vực `/hoc-vien` và các API liên quan.
- reCAPTCHA chỉ áp dụng cho luồng public để giảm bot/spam mà không làm nặng luồng staff nội bộ.

### Migration / Deployment Notes
- Cần chạy `php artisan migrate` để bổ sung cột auth mới.
- Cần cấu hình `MAIL_*` để email verification hoạt động.
- Cần cấu hình `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` nếu bật Google login.
- Cần cấu hình `RECAPTCHA_*` nếu bật reCAPTCHA.

### Known Notes
- Migration mới đang đánh dấu `email_verified_at` cho dữ liệu cũ để tránh khóa nhầm tài khoản hiện có.
- Bộ test hiện tại của repo vẫn còn rủi ro do thiếu migration nền cho một số bảng domain như `khoahoc`; phần này độc lập với thay đổi Auth.
