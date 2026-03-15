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

# 6.1. Nếu đã có dữ liệu lớp học và muốn đồng bộ trước room chat
php artisan chat:init-class-rooms --dry-run
php artisan chat:init-class-rooms

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

### 3.0 Tài liệu Auth mới

Xem thêm bộ tài liệu Auth chuyên biệt:

- `docs/05-huong-dan/auth.md`
- `docs/05-huong-dan/auth-cau-hinh-va-trien-khai.md`
- `docs/05-huong-dan/auth-van-hanh-va-kiem-thu.md`
- `docs/05-huong-dan/auth-joi-validation.md`
- `docs/01-phan-tich/auth-kien-truc-va-quyet-dinh.md`

### 3.1 Đăng nhập

Vào một trong các cổng nội bộ:

- `http://localhost/teacher/login` cho giảng viên
- `http://localhost/staff/login` cho nhân viên và admin

Học viên dùng:

- `http://localhost/login`

Nếu truy cập `http://localhost/admin/dashboard` khi chưa đăng nhập, hệ thống sẽ điều hướng về cổng nội bộ `/staff/login`.

Tài khoản mặc định (sau seeder):

- **Admin:** `admin` / `12345678`

### 3.2 Quy trình thiết lập ban đầu

1. **Tạo Tỉnh/Thành** → (seeder có sẵn từ API)
2. **Tạo Cơ sở đào tạo** → `/admin/co-so/tao-moi`
3. **Tạo Phòng học** → Trong trang chi tiết cơ sở
4. **Tạo Ca học** → `/admin/ca-hoc`
5. **Tạo Danh mục Khóa học** → `/admin/danh-muc-khoa-hoc/tao-moi` (hỗ trợ cây nhiều cấp)
6. **Tạo Khóa học** → `/admin/khoa-hoc/tao-moi`
7. **Tạo Lớp học** → `/admin/lop-hoc/tao-moi`
8. **Cấu hình Chính sách giá lớp** → Trong form tạo/sửa lớp học
9. **Tạo Giáo viên** → `/admin/giao-vien/tao-moi`
10. **Tự động tạo buổi học** → Trong trang chi tiết lớp học

### 3.3 Quy trình vận hành học phí lớp

Mô hình hiện tại không còn dùng `gói học phí` ở cấp khóa học.

Luồng chuẩn:

1. Tạo `khóa học` để mô tả chương trình đào tạo.
2. Tạo `lớp học` ngay cả khi chưa nhập giá.
3. Vào form lớp học để cấu hình:
   - `học phí niêm yết`
   - `số buổi cam kết` nếu cần
   - `loại thu`
   - `ghi chú chính sách`
   - danh sách đợt thu nếu thu theo đợt
   - `ngày kết thúc` không nhập tay; hệ thống cập nhật theo buổi học cuối cùng
4. Chỉ chuyển lớp sang `Đang tuyển sinh` khi đã có chính sách giá hợp lệ.
5. Khi học viên đăng ký, hệ thống tự chụp snapshot giá vào `dangkylophoc` và tạo hóa đơn từ snapshot đó.

Tài liệu chi tiết xem thêm:

- `docs/05-huong-dan/hoc-phi-lop-hoc.md`

### 3.4 Quản lý Danh mục Khóa học (Cây đa cấp)

- Tạo danh mục gốc: không chọn danh mục cha
- Tạo danh mục con: chọn danh mục cha từ dropdown (hiển thị cây với thụt lề)
- Không giới hạn độ sâu của cây
- Không được chọn chính mình hoặc cấp con/cháu làm cha (tránh vòng lặp)

### 3.5 Gửi thông báo

1. Vào `/admin/thong-bao/tao-moi`
2. Điền tiêu đề, nội dung
3. Chọn nhóm nhận (Tất cả / Học viên / Giáo viên / Nhân viên / Cá nhân)
4. Đính kèm file (tùy chọn, nhiều file)
5. Nhấn Gửi

---

## Phần 4: Hướng dẫn Website Client (Học viên)

### 4.1 Đăng ký tài khoản

- Vào `/register` → điền thông tin → xác thực email → đăng nhập

### 4.2 Đăng ký lớp học

1. Vào `/khoa-hoc` → chọn khóa học
2. Xem danh sách lớp → chọn lớp phù hợp
3. Click **Đăng ký** → Xác nhận thông tin
4. Submit → Hệ thống chụp snapshot giá của lớp tại thời điểm đăng ký
5. Hóa đơn được tạo tự động từ snapshot đó
6. Thanh toán tại cơ sở hoặc chuyển khoản

Ghi chú hiện tại:

- `Hiệu lực từ` và `Hiệu lực đến` nằm ở `chính sách giá lớp`, không phải ở bản thân lớp học.
- Hai trường này thể hiện khoảng thời gian mức giá đó được phép áp dụng cho đăng ký mới.
- Runtime hiện tại vẫn tạo 1 hóa đơn tổng cho mỗi đăng ký; cấu hình `đợt thu` đang được dùng cho validation, hiển thị và mở rộng billing về sau.

### 4.3 Xem lịch học

- Đăng nhập → `/hoc-vien/lich-hoc`

### 4.4 Xem hóa đơn

- `/hoc-vien/hoa-don` → danh sách hóa đơn → click xem chi tiết

### 4.5 Sử dụng chat lớp học

- Vào `/hoc-vien/chat`
- Sidebar trái hiển thị:
  - nhóm chat lớp học đủ điều kiện truy cập
  - các đoạn chat riêng đã tạo
- Nếu là học viên đã được xác nhận vào lớp nhưng lớp chưa vào học, bạn có thể vào room nhưng chưa chắc gửi được tin.
- Nếu lớp đang học và đăng ký ở trạng thái `Đang học`, bạn có thể gửi tin nhắn, trả lời, thả cảm xúc và thu hồi tin của chính mình trong 24 giờ.
- Tại panel thông tin bên phải, có thể xem thành viên room và mở direct chat với thành viên có quan hệ lớp hợp lệ.

### 4.6 Ghi chú vận hành chat

- Chat client dùng short-poll, không dùng WebSocket trong bản hiện tại.
- Nếu thêm dữ liệu lớp học trực tiếp vào database hoặc import từ nguồn khác, nên chạy lại `php artisan chat:init-class-rooms` để đồng bộ room nhóm.
- Tài liệu kỹ thuật chi tiết xem `docs/04-api/chat.md`.
