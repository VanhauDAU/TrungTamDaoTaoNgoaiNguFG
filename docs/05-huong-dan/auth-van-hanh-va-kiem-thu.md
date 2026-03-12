# Auth - Vận Hành Và Kiểm Thử

> Cập nhật: 2026-03-12

## 1. Luồng nghiệp vụ chuẩn

### 1.1 Học viên tự đăng ký

1. Truy cập `/register`
2. Nhập họ tên, email, mật khẩu
3. Submit form public
4. Nếu reCAPTCHA bật thì phải verify thành công
5. Hệ thống tạo tài khoản học viên
6. Hệ thống gửi email xác thực
7. Học viên bấm link email
8. Học viên đăng nhập và vào `/hoc-vien`

### 1.2 Học viên do admin tạo hộ

1. Admin vào tạo học viên
2. Hệ thống sinh `username` dạng `HV######`
3. Admin bàn giao username + mật khẩu tạm
4. Học viên đăng nhập
5. Nếu tài khoản đang bật `phaiDoiMatKhau` thì đổi mật khẩu trước

### 1.3 Giáo viên / Nhân viên / Admin

1. Truy cập `/admin/login`
2. Nhập email hoặc mã tài khoản
3. Đăng nhập thành công
4. Nếu là tài khoản mới tạo thì bị ép đổi mật khẩu
5. Vào `/admin/dashboard`

### 1.4 Google login cho học viên

1. Học viên bấm nút Google ở `/login` hoặc `/register`
2. Redirect tới Google
3. Callback về `/auth/google/callback`
4. Nếu email là học viên hợp lệ thì đăng nhập
5. Nếu email là staff thì bị từ chối
6. Sau khi đăng nhập, hồ sơ hiển thị đúng hình thức đăng nhập và avatar

### 1.5 Thiết lập mật khẩu cho tài khoản Google

1. Học viên đăng nhập bằng Google
2. Vào hồ sơ cá nhân hoặc trang đổi mật khẩu
3. Bấm `Thiết lập mật khẩu`
4. Hệ thống gửi email reset password tới email của học viên
5. Học viên mở email và đặt mật khẩu mới
6. Từ thời điểm đó có thể đăng nhập bằng cả Google lẫn email/username + mật khẩu

## 2. Checklist kiểm thử thủ công

### 2.1 Student login

- [ ] Đăng nhập bằng email thành công
- [ ] Đăng nhập bằng username thành công
- [ ] Submit trống thì `Joi` chặn ngay ở phía trình duyệt
- [ ] Sai mật khẩu thì hiện lỗi
- [ ] Sai nhiều lần thì lockout
- [ ] Tài khoản chưa verify bị chuyển tới `/email/verify`

### 2.2 Admin login

- [ ] Giáo viên vào được `/admin/login`
- [ ] Nhân viên vào được `/admin/login`
- [ ] Admin vào được `/admin/login`
- [ ] Học viên không vào được `/admin/login`

### 2.3 Registration

- [ ] Đăng ký học viên mới thành công
- [ ] Email sai định dạng bị `Joi` chặn trước khi submit
- [ ] Tạo ra username hệ thống, không dùng email
- [ ] Gửi email verification thành công
- [ ] Chưa verify thì không vào được `/hoc-vien`

### 2.4 Google login

- [ ] Học viên mới có thể tạo tài khoản bằng Google
- [ ] Học viên cũ trùng email được link đúng tài khoản
- [ ] Staff trùng email bị chặn
- [ ] Header và sidebar hiển thị đúng avatar Google
- [ ] Trang hồ sơ hiển thị `Google` ở trường hình thức đăng nhập
- [ ] Tài khoản Google có nút `Thiết lập mật khẩu`
- [ ] Bấm nút sẽ gửi email reset password thành công
- [ ] Sau khi đặt mật khẩu, đăng nhập bằng email hoặc username hoạt động

### 2.5 reCAPTCHA

- [ ] `/login` có token reCAPTCHA khi bật config
- [ ] `/register` có token reCAPTCHA khi bật config
- [ ] `/password/email` có token reCAPTCHA khi bật config
- [ ] Form invalid theo `Joi` thì không gọi tiếp flow reCAPTCHA submit
- [ ] Tắt `RECAPTCHA_ENABLED` thì form vẫn submit bình thường
- [ ] Khi submit, request thực sự có trường `recaptcha_token`

## 3. Dữ liệu cần quan sát khi debug

Trong bảng `taikhoan`, kiểm tra:
- `taiKhoan`
- `email`
- `role`
- `trangThai`
- `phaiDoiMatKhau`
- `email_verified_at`
- `auth_provider`
- `google_id`
- `google_avatar`
- `lastLogin`

Trong bảng `nhatky_dangnhap`, kiểm tra:
- `taiKhoan`
- `ip`
- `thanhCong`
- `thoiGian`

## 4. Tình huống support thường gặp

### User báo không vào được khu học viên

Kiểm tra:
1. đã đăng nhập đúng cổng chưa
2. role có phải học viên không
3. `email_verified_at` có null không
4. `phaiDoiMatKhau` có đang bật không

### Staff báo không vào được admin

Kiểm tra:
1. có đang login ở `/admin/login` không
2. role có phải `1`, `2`, `3` không
3. tài khoản có bị khóa hoặc bị tắt không

### User báo không nhận được mail verify

Kiểm tra:
1. SMTP
2. spam folder
3. log mailer
4. email trong DB có đúng không

### User báo Google login lỗi

Kiểm tra:
1. `GOOGLE_CLIENT_ID`
2. `GOOGLE_CLIENT_SECRET`
3. redirect URI
4. email tài khoản thuộc role nào
5. `APP_URL` có khớp với callback đã khai báo trong Google Console không

### User đăng nhập Google nhưng không biết mật khẩu local

Kiểm tra:
1. tài khoản có email hợp lệ không
2. SMTP có gửi mail thật không
3. học viên đã bấm nút `Thiết lập mật khẩu` ở hồ sơ hoặc trang đổi mật khẩu chưa
4. mail reset password có vào spam folder không

### User báo avatar bị hỏng sau Google login

Kiểm tra:
1. `google_avatar` có dữ liệu không
2. UI có đang dùng helper lấy avatar chuẩn không
3. `hoSoNguoiDung.anhDaiDien` có bị lưu nhầm URL ngoài không

### User báo reCAPTCHA lỗi `browser-error`

Kiểm tra:
1. trình duyệt có chặn script Google không
2. domain local có nằm trong reCAPTCHA console không
3. có đang dùng đúng key v3 không
4. thử browser khác hoặc tab ẩn danh

## 5. Ghi chú kỹ thuật

- `verified.student` chỉ chặn học viên, không chặn staff.
- Dữ liệu cũ được đánh dấu verified trong migration để tránh khóa nhầm toàn bộ user đang hoạt động.
- Google login đang được triển khai theo flow OAuth trực tiếp bằng HTTP client, không dùng Socialite.
- reCAPTCHA đang triển khai theo v3, action-based verification.
- `Joi` đang là lớp validate client-side dùng chung cho các form Auth quan trọng.
- Token reCAPTCHA được gắn vào form bằng JavaScript trước khi submit; nếu form bị chỉnh sửa layout, cần đảm bảo token vẫn được append vào đúng form.

## 6. Khuyến nghị backlog tiếp theo

- thêm 2FA cho `/admin/login`
- thêm trang profile staff để tự đổi mật khẩu
- thêm audit log cho link/unlink Google
- thêm test feature riêng cho Auth khi repo có migration nền đầy đủ
