# 05 — Hướng dẫn Cài đặt & Sử dụng

## Phần 1: Cài đặt môi trường phát triển

### Yêu cầu

- PHP >= 8.2
- MySQL 8.0
- Composer 2.x
- Node.js >= 18 (cho vite/npm)
- XAMPP (hoặc Laragon, Herd)

### Các bước cài đặt

```bash
# 1. Clone project
git clone https://github.com/VanhauDAU/TrungTamDaoTaoNgoaiNguFG.git
cd TrungTamDaoTaoNgoaiNguFG

# 2. Cài dependencies PHP
composer install

# 3. Cài dependencies Node
npm install

# 4. Tạo file .env
cp .env.example .env
php artisan key:generate

# 5. Cấu hình .env
# DB_DATABASE=trungTamNN
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Chạy migration
php artisan migrate

# 7. Tạo symbolic link storage
php artisan storage:link

# 8. Chạy seeder (dữ liệu mẫu)
php artisan db:seed

# 9. Build assets (nếu cần)
npm run dev

# 10. Khởi động server
php artisan serve
```

Truy cập: `http://127.0.0.1:8000`

---

## Phần 2: Cấu hình .env quan trọng

```env
APP_NAME="Five Genius Academy"
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trungTamNN
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=app_password
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="Five Genius"
```

---

## Phần 3: Hướng dẫn sử dụng Admin Panel

### 3.1 Đăng nhập

Vào `http://localhost/admin/dashboard` → Redirect về trang login nếu chưa đăng nhập.

Tài khoản mặc định (sau seeder):

- **Admin:** `admin` / `12345678`

### 3.2 Quy trình thiết lập ban đầu

1. **Tạo Tỉnh/Thành** → (seeder có sẵn từ API)
2. **Tạo Cơ sở đào tạo** → `/admin/co-so/tao-moi`
3. **Tạo Phòng học** → Trong trang chi tiết cơ sở
4. **Tạo Ca học** → `/admin/ca-hoc`
5. **Tạo Danh mục Khóa học** → `/admin/danh-muc-khoa-hoc/tao-moi` (hỗ trợ cây nhiều cấp)
6. **Tạo Khóa học** → `/admin/khoa-hoc/tao-moi`
7. **Tạo Gói học phí** → Trong trang chi tiết khóa học
8. **Tạo Lớp học** → `/admin/lop-hoc/tao-moi`
9. **Tạo Giáo viên** → `/admin/giao-vien/tao-moi`
10. **Tự động tạo buổi học** → Trong trang chi tiết lớp học

### 3.3 Quản lý Danh mục Khóa học (Cây đa cấp)

- Tạo danh mục gốc: không chọn danh mục cha
- Tạo danh mục con: chọn danh mục cha từ dropdown (hiển thị cây với thụt lề)
- Không giới hạn độ sâu của cây
- Không được chọn chính mình hoặc cấp con/cháu làm cha (tránh vòng lặp)

### 3.4 Gửi thông báo

1. Vào `/admin/thong-bao/tao-moi`
2. Điền tiêu đề, nội dung
3. Chọn nhóm nhận (Tất cả / Học viên / Giáo viên / Nhân viên / Cá nhân)
4. Đính kèm file (tùy chọn, nhiều file)
5. Nhấn Gửi

---

## Phần 4: Hướng dẫn Website Client (Học viên)

### 4.1 Đăng ký tài khoản

- Vào `/register` → điền thông tin → đăng nhập

### 4.2 Đăng ký lớp học

1. Vào `/khoa-hoc` → chọn khóa học
2. Xem danh sách lớp → chọn lớp phù hợp
3. Click **Đăng ký** → Xác nhận thông tin
4. Submit → Hóa đơn được tạo tự động
5. Thanh toán tại cơ sở hoặc chuyển khoản

### 4.3 Xem lịch học

- Đăng nhập → `/hoc-vien/lich-hoc`

### 4.4 Xem hóa đơn

- `/hoc-vien/hoa-don` → danh sách hóa đơn → click xem chi tiết
