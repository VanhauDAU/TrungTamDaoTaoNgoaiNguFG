# Auth - Cấu Hình Và Triển Khai

> Cập nhật: 2026-03-12

## 1. Điều kiện tiên quyết

Trước khi bật đầy đủ Auth mới, cần có:
- database đã migrate cột auth mới
- mail server chạy được
- Google OAuth App nếu muốn bật Google login
- Google reCAPTCHA v3 keys nếu muốn bật reCAPTCHA
- `node_modules` đã cài để bundle được `Joi` và asset frontend Auth

## 2. Migration bắt buộc

```bash
php artisan migrate
```

Migration mới:
- `2026_03_12_120000_add_auth_columns_to_taikhoan_table.php`
- `2026_03_12_130000_add_remember_token_to_taikhoan_table.php`
- `2026_03_12_140000_create_phien_dang_nhap_table.php`
- `2026_03_12_140100_create_nhatky_bao_mat_table.php`

Migration này thêm:
- `email_verified_at`
- `auth_provider`
- `google_id`
- `google_avatar`
- `remember_token` nếu bảng `taikhoan` chưa có cột này
- `phien_dang_nhap` để theo dõi phiên và thiết bị hoạt động
- `nhatky_bao_mat` để audit các sự kiện bảo mật nền

Ngoài ra migration sẽ đánh dấu `email_verified_at` cho tài khoản cũ để tránh khóa nhầm user hiện có.

## 3. Cấu hình `.env`

### 3.1 Mail

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_16_char_app_password
MAIL_EHLO_DOMAIN=localhost
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Nếu mail không chạy:
- email verification sẽ không gửi được
- reset password mail cũng không gửi được

Lưu ý:
- với project hiện tại, không dùng `MAIL_ENCRYPTION=tls`
- nên dùng `MAIL_SCHEME=smtp` cho port `587`
- hoặc `MAIL_SCHEME=smtps` cho port `465`

Ví dụ Mailpit local:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_EHLO_DOMAIN=127.0.0.1
MAIL_FROM_ADDRESS="noreply@fivegenius.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3.2 Google OAuth

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

Redirect URI cần khai báo ở Google Console:

```text
http://your-domain/auth/google/callback
```

Ví dụ local:

```text
http://127.0.0.1:8000/auth/google/callback
```

Hoặc nếu bạn chạy local bằng `localhost`:

```text
http://localhost:8000/auth/google/callback
```

Ví dụ XAMPP:

```text
http://localhost/DACNCNPM_TrungTamNN/public/auth/google/callback
```

Lưu ý:
- nếu dùng virtual host thì phải dùng đúng domain đó
- nếu `GOOGLE_*` trống, nút Google login sẽ không hiển thị
- `APP_URL` và `Authorized redirect URI` phải khớp tuyệt đối:
  - `127.0.0.1` khác `localhost`
  - khác port cũng bị fail
  - khác `http`/`https` cũng bị fail
- lỗi thường gặp nhất là `redirect_uri_mismatch`

### 3.3 Google reCAPTCHA v3

```env
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
RECAPTCHA_MIN_SCORE=0.5
```

Actions đang dùng:
- `student_login`
- `student_register`
- `forgot_password`

Nếu chưa muốn bật thật:

```env
RECAPTCHA_ENABLED=false
```

Lưu ý quan trọng:
- code hiện dùng reCAPTCHA v3, không phải checkbox v2
- vì là v3 nên không có widget hiện ra trên giao diện
- local domain nên add cả:
  - `127.0.0.1`
  - `localhost`

## 4. Cấu hình production

Checklist tối thiểu:
- `APP_URL` đúng domain thật
- `MAIL_FROM_*` đúng brand
- `SESSION_DOMAIN` đúng nếu dùng subdomain
- `GOOGLE_CLIENT_*` đúng project production
- `RECAPTCHA_*` đúng site production
- chạy `php artisan config:cache`

## 5. Trình tự deploy khuyến nghị

```bash
git pull
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ghi chú:
- từ khi thêm `Joi`, nếu thiếu `npm install` hoặc chưa build lại asset, form Auth sẽ không có lớp validate phía trình duyệt mới
- nếu chỉ sửa PHP/.env thì không cần build lại frontend

## 6. Kiểm tra sau deploy

### Student login

- mở `/login`
- thử đăng nhập học viên
- thử tài khoản chưa verify
- thử login có và không tick `Ghi nhớ đăng nhập`
- vào `/hoc-vien/thiet-bi-dang-nhap` và xác nhận thấy thiết bị hiện tại

### Staff login

- mở `/teacher/login` và thử tài khoản giáo viên
- mở `/staff/login` và thử tài khoản nhân viên/admin
- thử dùng tài khoản học viên ở các cổng nội bộ và xác nhận bị từ chối
- thử remembered login ở cả `/teacher/login` và `/staff/login`

### Registration

- tạo học viên mới qua `/register`
- xác nhận có email verification
- xác nhận chưa verify thì không vào được `/hoc-vien`

### Google login

- bấm Google ở `/login` hoặc `/register`
- xác nhận callback hoạt động
- xác nhận staff email không dùng được Google login
- xác nhận avatar Google hiển thị đúng trên header/sidebar/profile
- xác nhận profile hiển thị đúng hình thức đăng nhập
- xác nhận phiên Google login được ghi vào trang thiết bị đã đăng nhập

### reCAPTCHA

- bật `RECAPTCHA_ENABLED=true`
- submit form public
- xác nhận token được tạo và backend verify thành công
- xác nhận hidden input `recaptcha_token` nằm trong form khi submit

## 7. Lỗi thường gặp

### Lỗi: không nhận email xác thực

Nguyên nhân thường gặp:
- SMTP sai
- port/mail encryption sai
- `MAIL_FROM_ADDRESS` không hợp lệ

### Lỗi: Google callback 400

Nguyên nhân thường gặp:
- redirect URI sai
- domain local không khớp cấu hình Google Console
- client secret sai
- `APP_URL` đang là `127.0.0.1` nhưng Google Console lại khai báo `localhost`
- thay đổi trên Google Console chưa kịp áp dụng

### Lỗi: reCAPTCHA luôn fail

Nguyên nhân thường gặp:
- site key / secret key không cùng site
- domain chưa add vào reCAPTCHA console
- action trả về khác với action backend chờ
- điểm score thấp hơn `RECAPTCHA_MIN_SCORE`
- bị extension / browser privacy chặn script Google
- local báo `browser-error` khi trình duyệt không tạo được token
