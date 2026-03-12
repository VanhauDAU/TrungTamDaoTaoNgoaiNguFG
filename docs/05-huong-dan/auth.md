# Auth Module Guide

> Cập nhật: 2026-03-12

Tài liệu này là điểm vào chính cho module Auth sau đợt nâng cấp.

## 1. Phạm vi

Module Auth hiện bao gồm:
- đăng nhập học viên
- đăng nhập nhân sự
- ghi nhớ đăng nhập
- quản lý thiết bị đã đăng nhập
- logout khỏi tất cả thiết bị
- audit log bảo mật nền
- đăng ký học viên
- xác thực email
- quên mật khẩu
- đăng nhập Google cho học viên
- thiết lập mật khẩu local cho tài khoản Google qua email reset password
- xác thực client-side bằng `Joi` cho các form Auth
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

- `GET /teacher/login`
- `POST /teacher/login`
- `GET /staff/login`
- `POST /staff/login`
- `GET /admin/login` là đường dẫn cũ, hiện redirect sang `/staff/login`

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
- Luồng hoạt động Joi:
  - `docs/05-huong-dan/auth-joi-validation.md`
- Changelog:
  - `CHANGELOG.md`

## 5. Entry points trong code

### Controller

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `app/Http/Controllers/Auth/VerificationController.php`
- `app/Http/Controllers/Auth/GoogleLoginController.php`
- `app/Http/Controllers/Client/StudentController.php`

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
- `package.json`

### View

- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/verify.blade.php`
- `resources/views/auth/passwords/email.blade.php`
- `resources/views/auth/partials/recaptcha-script.blade.php`
- `resources/views/clients/hoc-vien/devices/index.blade.php`

### Frontend validation

- `resources/js/validation/forms.js`
- `resources/js/app.js`

### Migration

- `database/migrations/2026_03_12_120000_add_auth_columns_to_taikhoan_table.php`
- `database/migrations/2026_03_12_130000_add_remember_token_to_taikhoan_table.php`
- `database/migrations/2026_03_12_140000_create_phien_dang_nhap_table.php`
- `database/migrations/2026_03_12_140100_create_nhatky_bao_mat_table.php`

## 6. Hành vi quan trọng

### Đăng nhập học viên

- Cho phép dùng email hoặc username.
- Validate đầu vào bằng `Joi` trước khi submit.
- Có reCAPTCHA nếu đã bật cấu hình.
- Nếu chưa verify email thì sẽ bị chuyển sang trang verify.
- Nút Google chỉ hiện khi cả `GOOGLE_CLIENT_ID` và `GOOGLE_CLIENT_SECRET` đều đã được cấu hình.
- Checkbox `Ghi nhớ đăng nhập` dùng cơ chế remember me chuẩn của Laravel.
- Nếu nhập sai liên tiếp, hệ thống khóa đăng nhập theo backoff tăng dần thay vì khóa cố định:
  - lần sai thứ 5: khóa 1 phút
  - lần sai thứ 6: khóa 5 phút
  - lần sai thứ 7: khóa 10 phút
  - các lần sau tăng thêm 5 phút mỗi lần

### Đăng nhập staff

- Giảng viên dùng `/teacher/login`.
- Nhân viên và admin dùng `/staff/login`.
- Không dùng Google login ở các cổng nội bộ.
- Vẫn hỗ trợ checkbox `Ghi nhớ đăng nhập`.
- Hiện tại sau đăng nhập vẫn vào khu nội bộ `/admin/*`; sau này có thể tách `teacher.dashboard` và `staff.dashboard` mà không cần đổi core auth.

### Đăng ký học viên

- Tạo `TaiKhoan` role học viên.
- Hệ thống tự cấp username.
- Gửi email verification.
- Validate client-side bằng `Joi` cho họ tên, email, mật khẩu, xác nhận mật khẩu.
- Nếu Google OAuth đã được cấu hình, trang đăng ký sẽ hiện nút đăng ký / đăng nhập bằng Google.

### Avatar và provider

- Tài khoản local hiển thị avatar từ `hoSoNguoiDung.anhDaiDien` nếu có.
- Tài khoản Google ưu tiên hiển thị `google_avatar`.
- Nếu không có avatar hợp lệ, hệ thống fallback về `assets/images/user-default.png`.
- Trang hồ sơ học viên hiển thị nhãn hình thức đăng nhập:
  - `Google`
  - `Email và mật khẩu`

### Thiết lập mật khẩu cho tài khoản Google

- Tài khoản học viên đăng nhập bằng Google vẫn có thể dùng thêm đăng nhập bằng email hoặc username.
- Ở trang hồ sơ và trang đổi mật khẩu có nút `Thiết lập mật khẩu`.
- Khi bấm nút, hệ thống gửi email reset password tới email của tài khoản hiện tại.
- Sau khi đặt mật khẩu xong, học viên đăng nhập được cả:
  - Google login
  - email hoặc username + mật khẩu

### Tạo tài khoản bởi admin

- Học viên, giáo viên, nhân viên đều được cấp username hệ thống.
- Tài khoản nội bộ được đánh dấu verified để không chặn vận hành.
- Nếu `phaiDoiMatKhau = 1` thì bị ép đổi mật khẩu khi đăng nhập lần đầu.

### Joi ở frontend

- `Joi` hiện được áp dụng cho:
  - đăng nhập học viên
  - đăng nhập nhân sự
  - đăng ký học viên
  - quên mật khẩu
  - đặt lại mật khẩu
  - đổi mật khẩu bắt buộc
  - đổi mật khẩu ở khu học viên
- `Joi` không thay thế validation của Laravel. Backend vẫn là lớp kiểm tra bắt buộc để bảo mật.

### Ghi nhớ đăng nhập

- Login bằng form thường chỉ remembered khi người dùng tick checkbox.
- Google login hiện được giữ ở chế độ remembered để đồng nhất với trải nghiệm social login hiện tại.
- Khi mật khẩu bị đổi hoặc reset, hệ thống rotate `remember_token` để buộc các phiên remembered cũ hết hiệu lực.

### Thiết bị đã đăng nhập

- Hệ thống theo dõi phiên đăng nhập đang hoạt động trong bảng `phien_dang_nhap`.
- Trang học viên có mục `Thiết bị đã đăng nhập` để:
  - xem thiết bị hiện tại
  - xem các thiết bị khác còn đang hoạt động
  - thu hồi từng thiết bị
  - đăng xuất khỏi tất cả thiết bị
- Audit log nền được ghi vào `nhatky_bao_mat`.

## 7. Checklist đọc nhanh

Khi debug Auth, kiểm tra theo thứ tự:

1. Route đúng chưa.
2. Role đúng chưa.
3. `email_verified_at` có giá trị chưa.
4. `phaiDoiMatKhau` có đang bật không.
5. `GOOGLE_*` hoặc `RECAPTCHA_*` có cấu hình chưa.
6. `APP_URL` có khớp với redirect URI của Google không.
7. SMTP có gửi mail thật không.
