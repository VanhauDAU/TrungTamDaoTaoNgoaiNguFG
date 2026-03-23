# Auth - Vận Hành Và Kiểm Thử

> Cập nhật: 2026-03-23

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

1. Giảng viên truy cập `/teacher/login`
2. Nhân viên hoặc admin truy cập `/staff/login`
3. Nhập email hoặc mã tài khoản
4. Đăng nhập thành công
5. Nếu là tài khoản mới tạo thì bị ép đổi mật khẩu
6. Hiện tại đều đi vào khu nội bộ `/admin/dashboard`

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
- [ ] Không tick `Ghi nhớ đăng nhập` thì hết session phải đăng nhập lại
- [ ] Tick `Ghi nhớ đăng nhập` thì có thể đăng nhập lại tự động sau khi session thường hết hạn
- [ ] Sai mật khẩu thì hiện lỗi
- [ ] Sai lần thứ 5 liên tiếp thì lockout 1 phút
- [ ] Sai tiếp sau khi hết khóa thì thời gian lockout tăng 5 phút mỗi bậc
- [ ] Tài khoản chưa verify bị chuyển tới `/email/verify`
- [ ] Trang `/hoc-vien/thiet-bi-dang-nhap` hiển thị đúng thiết bị hiện tại

### 2.2 Admin login

- [ ] Giáo viên vào được `/teacher/login`
- [ ] Nhân viên vào được `/staff/login`
- [ ] Admin vào được `/staff/login`
- [ ] Học viên không vào được các cổng nội bộ
- [ ] Tick `Ghi nhớ đăng nhập` ở `/teacher/login` hoạt động đúng
- [ ] Tick `Ghi nhớ đăng nhập` ở `/staff/login` hoạt động đúng

### 2.3 Registration

- [ ] Đăng ký học viên mới thành công
- [ ] Email sai định dạng bị `Joi` chặn trước khi submit
- [ ] Tạo ra username hệ thống, không dùng email
- [ ] Gửi email verification thành công
- [ ] Chưa verify thì không vào được `/hoc-vien`
- [ ] Học viên đã đăng nhập mở lại `/register` sẽ về `/hoc-vien`, không rơi vào `/home`
- [ ] Nhân sự đã đăng nhập mở lại `/register` sẽ về dashboard nội bộ, không rơi vào `/home`

### 2.4 Google login

- [ ] Học viên mới có thể tạo tài khoản bằng Google
- [ ] Học viên cũ trùng email được link đúng tài khoản
- [ ] Staff trùng email bị chặn
- [ ] Header và sidebar hiển thị đúng avatar Google
- [ ] Trang hồ sơ hiển thị `Google` ở trường hình thức đăng nhập
- [ ] Tài khoản Google có nút `Thiết lập mật khẩu`
- [ ] Bấm nút sẽ gửi email reset password thành công
- [ ] Sau khi đặt mật khẩu, đăng nhập bằng email hoặc username hoạt động
- [ ] Google login vẫn hoạt động ở chế độ remembered

### 2.5 reCAPTCHA

- [ ] `/login` có token reCAPTCHA khi bật config
- [ ] `/register` có token reCAPTCHA khi bật config
- [ ] `/password/email` có token reCAPTCHA khi bật config
- [ ] Form invalid theo `Joi` thì không gọi tiếp flow reCAPTCHA submit
- [ ] Tắt `RECAPTCHA_ENABLED` thì form vẫn submit bình thường
- [ ] Khi submit, request thực sự có trường `recaptcha_token`

### 2.6 Remembered session invalidation

- [ ] Học viên tự đổi mật khẩu thì remembered session cũ hết hiệu lực
- [ ] Đổi mật khẩu bắt buộc thì remembered session cũ hết hiệu lực
- [ ] Reset mật khẩu qua email thì remembered session cũ hết hiệu lực
- [ ] Admin reset mật khẩu cho user thì remembered session cũ hết hiệu lực

### 2.7 Device session management

- [ ] Thu hồi một thiết bị khác sẽ làm session của thiết bị đó hết hiệu lực
- [ ] Thu hồi thiết bị đang dùng sẽ đăng xuất phiên hiện tại
- [ ] `Đăng xuất khỏi tất cả thiết bị` sẽ logout luôn thiết bị hiện tại
- [ ] Sau khi thu hồi thiết bị, remembered cookie cũ không tự khôi phục lại phiên đã bị cắt
- [ ] `nhatky_bao_mat` có log cho `session_registered`, `session_revoked`, `logout_all_devices`, `remember_token_rotated`

### 2.8 Portal switching trong cùng trình duyệt

- [ ] Đăng nhập `/staff/login` ở tab A, sau đó đăng nhập `/login` ở tab B cùng trình duyệt thì tab A không còn thao tác được như tab nội bộ hợp lệ
- [ ] Khi quay lại tab A, hệ thống hiện cảnh báo phiên nội bộ đã bị thay thế và chuyển hướng mềm thay vì render `403` thô
- [ ] Từ tab admin stale, bấm `Đăng xuất` không còn bị `419`
- [ ] Đăng nhập học viên trước rồi đăng nhập staff ở tab khác thì tab học viên cũ cũng bị phát hiện tương tự
- [ ] Nếu cần dùng đồng thời admin và học viên, test bằng trình duyệt khác hoặc cửa sổ ẩn danh cho kết quả ổn định

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
1. có đang login đúng cổng `/teacher/login` hoặc `/staff/login` không
2. role có phải `1`, `2`, `3` không
3. tài khoản có bị khóa hoặc bị tắt không
4. có vừa đăng nhập cổng học viên ở tab khác cùng trình duyệt không

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

### User báo vẫn còn đăng nhập trên thiết bị cũ sau khi đổi mật khẩu

Kiểm tra:
1. request đổi/reset mật khẩu có chạy thành công không
2. `remember_token` trong bảng `taikhoan` có đổi sau thao tác đó không
3. user đang dùng remembered login hay chỉ là session hiện tại chưa logout

### User báo tab admin hoặc tab học viên bị đá ra sau khi đăng nhập tab khác

Kiểm tra:
1. có đang dùng cùng một trình duyệt cho hai portal không
2. request `GET /auth/session-status` ở tab cũ đang trả `reason = portal_mismatch` hay không
3. trang cũ có giữ CSRF token cũ trước khi submit logout hay không
4. hướng dẫn user dùng trình duyệt khác hoặc cửa sổ ẩn danh nếu cần song song hai vai trò

### User báo bị khóa đăng nhập quá lâu

Kiểm tra:
1. số lần sai liên tiếp gần đây trong `nhatky_dangnhap`
2. lần đăng nhập thành công gần nhất đã xảy ra chưa
3. `lockout_until` trong session còn bao lâu
4. người dùng có đang tiếp tục nhập sai sau mỗi lần hết khóa hay không

### User báo đang đăng nhập rồi nhưng mở `/register` lại bị đá sang trang không mong muốn

Kiểm tra:
1. user đang mang role nào trong bảng `taikhoan`
2. `email_verified_at` của học viên có null không
3. `phaiDoiMatKhau` có đang bật không
4. app đã nạp cấu hình redirect guest mới trong `AppServiceProvider` hay chưa
5. trình duyệt có đang giữ route `/home` cũ từ cache/tab cũ không

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
- Lockout hiện dùng chuỗi thất bại liên tiếp trong vòng 24 giờ gần nhất; đăng nhập thành công sẽ reset chuỗi này.
- Token reCAPTCHA được gắn vào form bằng JavaScript trước khi submit; nếu form bị chỉnh sửa layout, cần đảm bảo token vẫn được append vào đúng form.
- Auth hiện có thêm lớp `portal session guard` ở frontend để đồng bộ CSRF token mới và tránh `419` khi tab cũ bị stale do đổi portal trong cùng browser.

## 6. Khuyến nghị backlog tiếp theo

- thêm 2FA cho `/teacher/login` và `/staff/login`
- thêm trang profile staff để tự đổi mật khẩu
- thêm audit log cho link/unlink Google
- thêm test feature riêng cho Auth khi repo có migration nền đầy đủ
