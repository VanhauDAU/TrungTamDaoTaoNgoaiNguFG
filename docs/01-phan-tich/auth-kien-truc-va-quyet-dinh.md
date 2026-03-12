# Phân Tích Kiến Trúc Auth

> Cập nhật: 2026-03-12

## 1. Mục tiêu của đợt nâng cấp

Đợt nâng cấp Auth này xử lý 5 nhu cầu chính:

1. Học viên tự đăng ký phải xác thực email.
2. Nhân sự có cổng đăng nhập riêng.
3. Học viên có thể đăng nhập bằng Google.
4. Form public cần có chống bot bằng Google reCAPTCHA.
5. `username` phải phù hợp với cả luồng tự đăng ký và luồng nhân viên tạo hộ tài khoản.

## 2. Quyết định kiến trúc đã chốt

### 2.1 Tách hai cổng đăng nhập

- Học viên dùng `/login`
- Giáo viên, nhân viên, admin dùng `/admin/login`

Lý do:
- UX rõ ràng hơn.
- Dễ áp chính sách bảo mật riêng cho staff sau này.
- Không phải tiếp tục trộn thông điệp học viên và staff trên cùng một màn hình.

### 2.2 Email verification chỉ bắt buộc cho học viên

- Học viên tự đăng ký bắt buộc xác thực email.
- Staff do nội bộ tạo không bị chặn bởi email verification.
- Học viên cũ được đánh dấu verified trong migration để tránh khóa nhầm dữ liệu đang vận hành.

Lý do:
- Email verification là nhu cầu của self-service registration.
- Staff đang đi theo luồng vận hành nội bộ và đã có `force-change-password`.

### 2.3 Google login chỉ áp dụng cho học viên

- Chỉ cho phép `role = 0` dùng Google login.
- Nếu email Google trùng với staff thì từ chối đăng nhập Google.
- Nếu email Google trùng với học viên hiện có thì liên kết vào tài khoản cũ.

Lý do:
- Social login phù hợp với học viên hơn staff.
- Staff cần đường vào ổn định, audit rõ, ít phụ thuộc nhà cung cấp ngoài.

### 2.4 reCAPTCHA chỉ áp dụng cho form public

Áp dụng cho:
- `/login`
- `/register`
- `/password/email`

Không áp dụng ở giai đoạn này cho:
- `/admin/login`

Lý do:
- Giảm bot/spam ở khu vực public.
- Tránh làm nặng UX cho nhân sự nội bộ.
- Staff nên được tăng cường bằng route riêng, lockout, và có thể thêm 2FA sau.

### 2.5 `username` là mã hệ thống, không phải email, không phải CCCD

Quy ước:
- Học viên: `HV######`
- Giáo viên: `GV######`
- Nhân viên: `NV######`
- Admin: `AD######`

Lý do:
- Không lộ dữ liệu nhạy cảm như CCCD.
- Không phụ thuộc email, tránh phải đổi username khi email đổi.
- Đồng nhất giữa tài khoản tự đăng ký và tài khoản do nhân viên tạo hộ.
- Dễ hỗ trợ vận hành, dễ in ấn, dễ tra cứu.

## 3. Tác động nghiệp vụ

### Học viên tự đăng ký

Luồng mới:
- đăng ký
- nhận email xác thực
- xác thực email
- đăng nhập hoặc dùng Google login
- vào khu vực học viên

### Học viên do nhân viên tạo hộ

Luồng mới:
- hệ thống tự cấp `username` dạng `HV######`
- mật khẩu tạm vẫn có thể sinh từ CCCD hoặc fallback
- tài khoản hiện được đánh dấu verified để không cản vận hành bàn giao
- học viên đăng nhập lần đầu và có thể đổi mật khẩu sau đó

### Giáo viên / Nhân viên / Admin

Luồng mới:
- vào `/admin/login`
- đăng nhập bằng email hoặc mã hệ thống
- nếu là tài khoản mới tạo thì bị chuyển sang đổi mật khẩu bắt buộc

## 4. Phạm vi kỹ thuật đã thay đổi

### Backend

- `App\Models\Auth\TaiKhoan`
- `Auth\LoginController`
- `Auth\RegisterController`
- `Auth\ForgotPasswordController`
- `Auth\VerificationController`
- `Auth\GoogleLoginController`
- `EnsureStudentEmailIsVerified`

### Routing

- Bật `Auth::routes(['verify' => true])`
- Thêm `/admin/login`
- Thêm `/auth/google/redirect`
- Thêm `/auth/google/callback`
- Áp middleware `verified.student` cho khu vực học viên

### Database

Thêm cột vào `taikhoan`:
- `email_verified_at`
- `auth_provider`
- `google_id`
- `google_avatar`

## 5. Những gì chưa làm trong đợt này

- 2FA cho staff
- Quy trình đổi email có xác thực lại
- Audit log chuyên biệt cho Google link/unlink
- Tách guard riêng cho staff và student
- Social login cho Facebook
- reCAPTCHA cho `/admin/login`

## 6. Rủi ro và lưu ý

- Nếu chưa cấu hình SMTP, email verification sẽ không gửi được.
- Nếu chưa cấu hình Google OAuth, nút Google login sẽ không hiện.
- Nếu bật reCAPTCHA nhưng thiếu `site_key` hoặc `secret_key`, form public sẽ lỗi xác minh.
- Repo hiện vẫn có rủi ro độc lập về migration nền chưa đủ cho một số bảng domain; không nên nhầm với lỗi Auth.
