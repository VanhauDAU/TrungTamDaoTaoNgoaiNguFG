# Auth Module Guide

> Cập nhật: 2026-03-12

Tài liệu này là điểm vào chính cho module Auth sau đợt nâng cấp.

## 1. Phạm vi

Module Auth hiện bao gồm:
- đăng nhập học viên
- đăng nhập nhân sự
- đăng ký học viên
- xác thực email
- quên mật khẩu
- đăng nhập Google cho học viên
- Google reCAPTCHA cho form public
- quy ước username hệ thống
- hiển thị avatar và hình thức đăng nhập cho tài khoản học viên

## 2. Route chính

### Public / Student

- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `GET /email/verify`
- `GET /email/verify/{id}/{hash}`
- `POST /email/resend`
- `POST /password/email`
- `GET /auth/google/redirect`
- `GET /auth/google/callback`

### Staff

- `GET /admin/login`
- `POST /admin/login`

### Protected student area

Các route học viên yêu cầu:
- đã đăng nhập
- là học viên
- đã xác thực email

## 3. Quy ước username

`username` hiện là mã hệ thống, không dùng email, không dùng CCCD.

Quy ước:
- `HV######`
- `GV######`
- `NV######`
- `AD######`

Ví dụ:
- `HV000123`
- `GV000015`

## 4. Tài liệu liên quan

- Phân tích quyết định Auth:
  - `docs/01-phan-tich/auth-kien-truc-va-quyet-dinh.md`
- Cấu hình môi trường và triển khai:
  - `docs/05-huong-dan/auth-cau-hinh-va-trien-khai.md`
- Vận hành và kiểm thử:
  - `docs/05-huong-dan/auth-van-hanh-va-kiem-thu.md`
- Changelog:
  - `CHANGELOG.md`

## 5. Entry points trong code

### Controller

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `app/Http/Controllers/Auth/VerificationController.php`
- `app/Http/Controllers/Auth/GoogleLoginController.php`

### Middleware

- `app/Http/Middleware/EnsureStudentEmailIsVerified.php`
- `app/Http/Middleware/ForceChangePassword.php`
- `app/Http/Middleware/IsAdmin.php`

### Model

- `app/Models/Auth/TaiKhoan.php`

### Config

- `config/auth.php`
- `config/services.php`
- `.env.example`

### View

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/verify.blade.php`
- `resources/views/auth/passwords/email.blade.php`
- `resources/views/auth/partials/recaptcha-script.blade.php`

### Migration

- `database/migrations/2026_03_12_120000_add_auth_columns_to_taikhoan_table.php`

## 6. Hành vi quan trọng

### Đăng nhập học viên

- Cho phép dùng email hoặc username.
- Có reCAPTCHA nếu đã bật cấu hình.
- Nếu chưa verify email thì sẽ bị chuyển sang trang verify.
- Nút Google chỉ hiện khi cả `GOOGLE_CLIENT_ID` và `GOOGLE_CLIENT_SECRET` đều đã được cấu hình.

### Đăng nhập staff

- Chỉ dùng ở `/admin/login`.
- Chỉ chấp nhận giáo viên, nhân viên, admin.
- Không dùng Google login ở đây.

### Đăng ký học viên

- Tạo `TaiKhoan` role học viên.
- Hệ thống tự cấp username.
- Gửi email verification.
- Nếu Google OAuth đã được cấu hình, trang đăng ký sẽ hiện nút đăng ký / đăng nhập bằng Google.

### Avatar và provider

- Tài khoản local hiển thị avatar từ `hoSoNguoiDung.anhDaiDien` nếu có.
- Tài khoản Google ưu tiên hiển thị `google_avatar`.
- Nếu không có avatar hợp lệ, hệ thống fallback về `assets/images/user-default.png`.
- Trang hồ sơ học viên hiển thị nhãn hình thức đăng nhập:
  - `Google`
  - `Email và mật khẩu`

### Tạo tài khoản bởi admin

- Học viên, giáo viên, nhân viên đều được cấp username hệ thống.
- Tài khoản nội bộ được đánh dấu verified để không chặn vận hành.
- Nếu `phaiDoiMatKhau = 1` thì bị ép đổi mật khẩu khi đăng nhập lần đầu.

## 7. Checklist đọc nhanh

Khi debug Auth, kiểm tra theo thứ tự:

1. Route đúng chưa.
2. Role đúng chưa.
3. `email_verified_at` có giá trị chưa.
4. `phaiDoiMatKhau` có đang bật không.
5. `GOOGLE_*` hoặc `RECAPTCHA_*` có cấu hình chưa.
6. `APP_URL` có khớp với redirect URI của Google không.
7. SMTP có gửi mail thật không.
