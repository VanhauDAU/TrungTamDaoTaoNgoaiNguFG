# 05 — Hướng dẫn Cài đặt & Sử dụng

## Phần 1: Cài đặt môi trường phát triển

### Yêu cầu

- PHP >= 8.3
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

### Khi pull code mới về máy

Sau mỗi lần `git pull`, không nên chạy app ngay nếu chưa kiểm tra thay đổi môi trường và dependency.

Checklist tối thiểu:

```bash
# 1. Kéo code mới
git pull

# 2. Đồng bộ dependency PHP khi composer.* thay đổi
composer install

# 3. Đồng bộ dependency frontend khi package.* thay đổi
npm install

# 4. Clear cache cấu hình/router/view sau khi đổi code hoặc .env
php artisan optimize:clear

# 5. Chạy migration nếu branch mới có migration
php artisan migrate

# 6. Nếu có thay đổi asset frontend
npm run dev
```

Nếu team bật Redis cho local:

```bash
redis-cli PING
```

Nếu không thấy `PONG`, khởi động lại Redis:

```bash
redis-server /opt/homebrew/etc/redis.conf
```

Nếu máy đó cần chạy mail queue, gửi thông báo hàng loạt hoặc export nền bằng Redis:

```bash
composer queue:redis
```

Lưu ý:
- Nếu pull về có thay đổi `.env.example`, cần so sánh và cập nhật `.env` thủ công.
- Project hiện yêu cầu PHP runtime `>= 8.3`; nếu máy còn chạy bằng XAMPP PHP 8.2 thì `artisan` có thể fail ngay từ Composer platform check.
- Nếu vừa thêm package mới như `predis/predis` mà chưa chạy `composer install`, các tính năng Redis sẽ không hoạt động đúng.
- Nếu pull về có thêm cache public Redis, nhớ bổ sung `PUBLIC_LIST_CACHE_STORE` và `PUBLIC_LIST_CACHE_TTL` trong `.env` nếu máy đang dùng Redis local.

### Quy trình chuẩn để pull và chạy full Redis trên macOS và Windows

Để giảm lệch môi trường giữa macOS và Windows, team nên thống nhất:
- PHP `>= 8.3`
- Composer `2.x`
- Node.js `>= 20`
- MySQL local
- Redis chạy bằng Docker

Khởi động Redis giống nhau trên cả hai hệ điều hành:

```bash
docker run --name fivegenius-redis -p 6379:6379 -d redis:7-alpine
docker exec -it fivegenius-redis redis-cli PING
```

Nếu Redis trả `PONG`, dùng quy trình sau sau mỗi lần `git pull`:

```bash
git pull
composer install
npm install
php artisan optimize:clear
php artisan migrate
```

Nếu là máy mới hoặc vừa clone lần đầu:

```bash
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Khi muốn chạy project đầy đủ với Redis, cập nhật `.env` local theo mẫu ở Phần 2 bên dưới, rồi chạy:

```bash
php artisan serve
php artisan queue:work redis --queue=exports,notifications,maintenance,default --tries=3 --timeout=300
npm run dev
```

Hoặc chạy gọn:

```bash
composer dev
```

Checklist lỗi nhanh:
- `Class "Redis" not found`: đang dùng sai client hoặc đang phụ thuộc PHP extension `redis`; với project này nên giữ `REDIS_CLIENT=predis`.
- `Call to a member function connect() on null`: `.env` đang để sai `REDIS_CLIENT=redis`; giá trị hợp lệ là `predis` hoặc `phpredis`.
- `Connection refused 127.0.0.1:6379`: Redis chưa chạy hoặc Docker container chưa start.
- `Composer detected issues in your platform`: PHP local đang thấp hơn version package hiện yêu cầu.

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

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1

CACHE_STORE=database
SESSION_DRIVER=database

REGISTER_EMAIL_CHECK_CACHE_STORE=redis
REGISTER_EMAIL_CHECK_CACHE_TTL=60
RATE_LIMITER_STORE=redis
PUBLIC_LIST_CACHE_STORE=redis
PUBLIC_LIST_CACHE_TTL=300
QUEUED_EXPORT_STORE=redis
QUEUED_EXPORT_TTL=30
QUEUED_EXPORT_DISK=local

QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
```

Redis trong project hiện dùng cho:
- cache kiểm tra email realtime ở form đăng ký
- rate limit auth cho `login`, `register`, `check-email`
- cache danh sách public ở `/khoa-hoc`, `/blog`, trang chủ, footer và `register-advice`
- queue cho:
  - mail auth
  - gửi thông báo hàng loạt
  - export Excel/PDF
  - batch `invoice:check-overdue`
  - batch `registration:expire-holds`

Kiểm nhanh Redis sau khi pull:

```bash
redis-cli PING
redis-cli --scan --pattern '*public-content*'
```

Kiểm worker Redis:

```bash
composer queue:redis
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

### 3.0.1 Tài liệu nhân sự và lương mới

Xem thêm bộ tài liệu mới cho nhân sự:

- `docs/05-huong-dan/nhan-su-ho-so-va-ban-giao-tai-khoan.md`
- `docs/05-huong-dan/luong-nhan-su-va-payroll.md`
- `docs/05-huong-dan/figma-luong-handoff.md`
- `docs/05-huong-dan/figma-luong-wireframe.html`

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
10. **Tạo Nhân viên** → `/admin/nhan-vien/tao-moi`
11. **Hoàn thiện hồ sơ nhân sự** → Tải CV, chọn mẫu quy định, chốt gói lương đầu tiên
12. **Tự động tạo buổi học** → Trong trang chi tiết lớp học

### 3.2.1 Quy trình tạo và bàn giao tài khoản nhân sự

Luồng chuẩn mới cho giáo viên và nhân viên:

1. Tạo hồ sơ nhân sự từ màn tạo tương ứng.
2. Hệ thống sinh `username` thật và mật khẩu tạm ngẫu nhiên.
3. Sau khi lưu, hệ thống chuyển sang trang hồ sơ chi tiết.
4. Nếu vừa tạo xong, trang hồ sơ hiển thị thẻ bàn giao tài khoản để copy hoặc in PDF.
5. Mật khẩu tạm chỉ hiển thị một lần; các lần mở lại sau đó chỉ còn thao tác reset mật khẩu.
6. Màn sửa giáo viên và nhân viên đã hoàn thiện đầy đủ để cập nhật hồ sơ sau này.

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
6. `Theo tháng` không còn được hỗ trợ trong runtime hiện tại.
7. Đăng ký `Chờ thanh toán` có `ngày hết hạn giữ chỗ`; job hệ thống sẽ tự hủy giữ chỗ quá hạn nếu chưa thu được tiền.
8. Hóa đơn quá hạn khi lớp đang học sẽ được job hệ thống xử lý để chuyển đăng ký sang `Tạm dừng do nợ học phí`.

Tài liệu chi tiết xem thêm:

- `docs/05-huong-dan/hoc-phi-lop-hoc.md`
- `docs/05-huong-dan/dang-ky-thanh-toan-va-phieu-thu.md`

### 3.4 Quản lý đăng ký học ở admin

Module `/admin/dang-ky` hiện hỗ trợ:

- tạo đăng ký tại quầy
- xác nhận đăng ký
- hủy đăng ký
- bảo lưu
- khôi phục
- chuyển lớp

Quy tắc vận hành:

- không hủy hoặc chuyển lớp nếu đã phát sinh thu tiền
- khôi phục phải kiểm tra lại sĩ số và trùng lịch
- điều chuyển sẽ hủy đăng ký cũ và tạo đăng ký mới ở lớp đích
- mọi thay đổi hóa đơn từ admin đều phải tự recalculate hóa đơn và trạng thái đăng ký liên quan

### 3.4.1 Quản lý hồ sơ nhân sự và gói lương

Module hồ sơ nhân sự hiện cho phép:

- xem hồ sơ chi tiết giáo viên / nhân viên
- sửa hồ sơ đầy đủ
- tải lên CV và tài liệu nhân sự theo version
- tải PDF hồ sơ nhân sự
- tải phiếu bàn giao tài khoản khi còn token hợp lệ
- cấu hình gói lương hiện hành và lịch sử gói lương

Nguyên tắc vận hành:

- không hiển thị lại mật khẩu cũ sau khi token bàn giao hết hạn
- không cho đổi `username`
- chỉ 1 gói lương được active tại một thời điểm
- sửa mẫu quy định không tự đổi snapshot quy định ở hồ sơ cũ

### 3.5 Quản lý Danh mục Khóa học (Cây đa cấp)

- Tạo danh mục gốc: không chọn danh mục cha
- Tạo danh mục con: chọn danh mục cha từ dropdown (hiển thị cây với thụt lề)
- Không giới hạn độ sâu của cây
- Không được chọn chính mình hoặc cấp con/cháu làm cha (tránh vòng lặp)

### 3.6 Gửi thông báo

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
6. Hệ thống đặt `ngày hết hạn giữ chỗ` cho đăng ký chờ thanh toán
7. Thanh toán tại cơ sở hoặc chuyển khoản

Ghi chú hiện tại:

- hệ thống chỉ hỗ trợ `một lần` hoặc `theo đợt`
- không còn hỗ trợ `theo tháng`
- đăng ký quá hạn giữ chỗ có thể bị hệ thống tự hủy nếu chưa phát sinh thu tiền
- khi trung tâm ghi nhận thanh toán, `phiếu thu` sẽ cập nhật ngược trở lại `hóa đơn` và `trạng thái đăng ký`

### 4.3 Xem lịch học

- Đăng nhập → `/hoc-vien/lich-hoc`

### 4.4 Xem hóa đơn

- `/hoc-vien/hoa-don` → danh sách hóa đơn → click xem chi tiết
- Tại trang chi tiết hóa đơn và danh sách phiếu thu, học viên chỉ xem thông tin để đối soát
- Nếu cần bản in hoặc file gửi mail, nhân sự thực hiện tại khu vực admin sau khi ghi nhận thu tiền

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

---

## Phần 5: Ghi chú import SQL và phục hồi dữ liệu

- Không nên import dump SQL bằng cách tắt khóa ngoại rồi bỏ qua kiểm tra dữ liệu cha-con.
- Với các bảng domain có quan hệ chặt như `lophoc`, `lophoc_chinhsachgia`, `buoihoc`, `chat_rooms`, cần bảo đảm:
  - bản ghi cha tồn tại trước
  - không có orphan record ở bảng con
- Nếu import dump thủ công từ phpMyAdmin và gặp lỗi `#1452`, cần kiểm tra lại tính nhất quán của dữ liệu trong file dump thay vì chỉ bỏ khóa ngoại.
- Với dữ liệu lớp học nhập ngoài hệ thống, sau khi import nên chạy:

```bash
php artisan chat:init-class-rooms --dry-run
php artisan chat:init-class-rooms
```
