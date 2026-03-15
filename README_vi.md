# DACNCNPM - Hệ Thống Quản Lý Trung Tâm Ngoại Ngữ (Bản Tiếng Việt)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-yellow)](https://opensource.org/licenses/MIT)

Monolith Laravel phục vụ quản lý vận hành trung tâm ngoại ngữ: khóa học, lớp học, học viên, giáo viên, tài chính, bài viết, thông báo và liên hệ khách hàng.

## Mục lục
- [1. Tổng quan](#1-tổng-quan)
- [2. Sơ đồ luồng hệ thống](#2-sơ-đồ-luồng-hệ-thống)
- [3. Tính năng chính](#3-tính-năng-chính)
- [4. Công nghệ sử dụng](#4-công-nghệ-sử-dụng)
- [5. Cấu trúc dự án](#5-cấu-trúc-dự-án)
- [6. Cài đặt môi trường local](#6-cài-đặt-môi-trường-local)
- [7. Chạy dự án](#7-chạy-dự-án)
- [8. Biến môi trường quan trọng](#8-biến-môi-trường-quan-trọng)
- [9. Mô hình học phí hiện tại](#9-mô-hình-học-phí-hiện-tại)
- [10. Lệnh hữu ích](#10-lệnh-hữu-ích)
- [11. Test và chất lượng mã nguồn](#11-test-và-chất-lượng-mã-nguồn)
- [12. Lưu ý dữ liệu và migration](#12-lưu-ý-dữ-liệu-và-migration)
- [13. Tài liệu Auth](#13-tài-liệu-auth)
- [14. Quy trình phát triển](#14-quy-trình-phát-triển)
- [15. Hỗ trợ](#15-hỗ-trợ)

## 1. Tổng quan
- Ngành: Hệ thống thông tin quản lý trung tâm ngoại ngữ.
- Kiến trúc: Laravel + Blade + MySQL.
- Đối tượng sử dụng:
  - Khách/người học: xem khóa học, đăng ký tư vấn, theo dõi thông tin cá nhân.
  - Nhân sự/Admin: vận hành đào tạo, tài chính, nội dung, thông báo, liên hệ.
- Route chính:
  - `web client`: `/`
  - `đăng nhập học viên`: `/login`
  - `đăng nhập giảng viên`: `/teacher/login`
  - `đăng nhập nhân viên/admin`: `/staff/login`
  - `khu nội bộ`: `/admin` (yêu cầu đăng nhập + middleware staff)

## 2. Sơ đồ luồng hệ thống
```mermaid
flowchart LR
    A["Người dùng Client"] --> B["Routes Client (/ , /khoa-hoc, /lien-he, /hoc-vien)"]
    C["Nhân sự/Admin"] --> D["Routes Admin (/admin/...)"]
    B --> E["Client Controllers"]
    D --> F["Admin Controllers"]
    E --> G["Services"]
    F --> G
    E --> H["Models (Domain)"]
    F --> H
    G --> H
    H --> I["MySQL Database"]
    E --> J["Blade Views Client"]
    F --> K["Blade Views Admin"]
    J --> L["Browser UI"]
    K --> L
```

## 3. Tính năng chính
### Client
- Trang chủ, giới thiệu, blog, danh sách/chi tiết khóa học.
- Đăng ký lớp học và checkout.
- Trang liên hệ, form đăng ký tư vấn.
- Khu vực học viên: profile, đổi mật khẩu, lịch học, lớp học, hóa đơn.
- Auth học viên: đăng ký, xác thực email, quên/đặt lại mật khẩu, đăng nhập Google, reCAPTCHA, ghi nhớ đăng nhập.
- Thông báo realtime cho học viên (dropdown + stream API).

### Admin
- Dashboard thống kê tổng quan.
- Quản lý học viên, giáo viên, nhân viên.
- Quản lý đào tạo: danh mục khóa học, khóa học, lớp học, buổi học, ca học, chính sách giá lớp.
- Quản lý tài chính: hóa đơn, phiếu thu, cập nhật trạng thái.
- Quản lý nội dung: bài viết, danh mục bài viết, tag.
- Quản lý thông báo nội bộ.
- Quản lý liên hệ/lead (có hỗ trợ thùng rác và thao tác loạt).
- Cấu hình cơ sở đào tạo, phòng học, địa chỉ theo tỉnh/phường.
- Cổng nội bộ tách theo portal:
  - `/teacher/login` cho giảng viên
  - `/staff/login` cho nhân viên và admin

## 4. Công nghệ sử dụng
- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade, Bootstrap 5, JavaScript, Vite
- Database: MySQL 8.x
- Build tool: Vite
- Test: PHPUnit (Laravel test runner)

## 5. Cấu trúc dự án
```text
app/
  Http/Controllers/
    Client/        # Controller cho giao diện người học
    Admin/         # Controller khu vực quản trị
  Models/          # Domain model theo nhóm: Auth, Course, Education, Finance...
  Services/        # DashboardService, ThongBaoService...
resources/views/
  clients/         # View client
  admin/           # View admin
  components/      # Blade components
routes/
  web.php          # Toàn bộ route web + admin
database/
  migrations/      # Migration
  seeders/         # Seeder
public/assets/     # Static assets css/js/image
```

## 6. Cài đặt môi trường local
### 6.1 Yêu cầu
- PHP 8.2+
- Composer 2+
- Node.js 18+ và npm
- MySQL 8.x

### 6.2 Clone và cài đặt
```bash
git clone <repo-url>
cd DACNCNPM_TrungTamNN
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 6.3 Cấu hình `.env` (MySQL)
```env
APP_NAME="DACNCNPM TrungTamNN"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dacncnpm_trungtamnn
DB_USERNAME=root
DB_PASSWORD=
```

## 7. Chạy dự án
### Cách 1: Chạy đồng thời server + queue + vite (khuyến nghị)
```bash
composer dev
```

### Cách 2: Tự chạy từng tiến trình
```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

### Cách 3: Build static assets cho production
```bash
npm run build
```

Nếu dùng XAMPP/Apache:
- Đặt project trong `htdocs`.
- Truy cập qua virtual host hoặc `/public` theo cấu hình Apache.

## 8. Biến môi trường quan trọng
- `APP_URL`: URL gốc ứng dụng.
- `DB_*`: kết nối CSDL.
- `QUEUE_CONNECTION`: mặc định `database`.
- `MAIL_*`: cấu hình gửi mail.
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`: đăng nhập Google cho học viên.
- `RECAPTCHA_*`: reCAPTCHA v3 cho login/register/quên mật khẩu public.
- `GEMINI_API_KEY`, `GEMINI_MODEL`: khóa/mode AI (nếu kích hoạt tính năng liên quan).

## 9. Mô hình học phí hiện tại
- Học phí được quản lý ở cấp `lớp học`, không còn ở cấp `khóa học`.
- `Khóa học` chỉ mô tả chương trình đào tạo; giá bán và cách thu tiền nằm ở `lophoc_chinhsachgia`.
- `Lớp học` có thể được tạo trước khi nhập học phí, nhưng phải có chính sách giá hợp lệ trước khi chuyển sang trạng thái tuyển sinh hoặc đang học.
- `ngày kết thúc` của lớp không nhập tay trong flow mới; hệ thống đồng bộ theo buổi học cuối cùng.
- Khi học viên đăng ký, hệ thống chụp `snapshot` học phí vào `dangkylophoc` để không bị ảnh hưởng khi lớp thay đổi giá sau này.
- Hệ thống hiện chỉ hỗ trợ `một lần` hoặc `theo đợt`; `theo tháng` đã bị loại khỏi runtime mới.
- `lophoc_dotthu` dùng để lưu kế hoạch thu theo đợt; runtime hiện tại đã sinh nhiều hóa đơn học phí khi lớp cấu hình thu theo đợt.
- Đăng ký `Chờ thanh toán` có `ngày hết hạn giữ chỗ`; job hệ thống sẽ tự hủy giữ chỗ quá hạn nếu chưa phát sinh thu tiền.
- Hóa đơn quá hạn khi lớp đang học sẽ được job hệ thống xử lý để chuyển đăng ký sang trạng thái nợ học phí.
- Hướng dẫn vận hành tổng hợp: `docs/05-huong-dan/huong-dan.md`.
- Hướng dẫn chi tiết:
  - `docs/05-huong-dan/hoc-phi-lop-hoc.md`
  - `docs/05-huong-dan/dang-ky-thanh-toan-va-phieu-thu.md`

## 10. Lệnh hữu ích
```bash
# Chạy test
php artisan test

# Clear cache
php artisan optimize:clear

# Tạo symlink storage
php artisan storage:link

# Kiểm tra hóa đơn quá hạn (thủ công)
php artisan invoice:check-overdue
php artisan invoice:check-overdue --dry-run

# Kiểm tra và hủy giữ chỗ quá hạn (thủ công)
php artisan registration:expire-holds
php artisan registration:expire-holds --dry-run
```

## 11. Test và chất lượng mã nguồn
```bash
# Test full
composer test

# Format code (Laravel Pint)
./vendor/bin/pint
```

Thư mục test hiện có:
- `tests/Feature`
- `tests/Unit`

## 12. Lưu ý dữ liệu và migration
- Dự án hiện có nhiều bảng domain custom (`taikhoan`, `lienhe`, `hoadon`, ...).
- Thư mục migration trong repo chủ yếu là migration bổ sung/cập nhật.
- Nếu khởi tạo mới trên máy sạch, cần đảm bảo đã có schema nền từ team (hoặc bộ migration đầy đủ) trước khi chạy dự án toàn phần.
- Migration `2026_03_14_150000_refactor_class_pricing_to_lophoc_chinhsachgia.php` chuyển học phí từ mô hình cũ (`hocphi`, `lophoc.hocPhiId`) sang mô hình mới theo lớp học.

Lệnh migrate cơ bản:
```bash
php artisan migrate
```

## 13. Tài liệu Auth
- Portal đăng nhập hiện tại:
  - Học viên: `/login`
  - Giảng viên: `/teacher/login`
  - Nhân viên/Admin: `/staff/login`
- Giao diện login sử dụng dock chuyển portal cố định ở đáy màn hình để đổi nhanh giữa các cổng đăng nhập.
- Tổng quan module Auth: `docs/05-huong-dan/auth.md`
- Kiến trúc và quyết định: `docs/01-phan-tich/auth-kien-truc-va-quyet-dinh.md`
- Cấu hình và triển khai: `docs/05-huong-dan/auth-cau-hinh-va-trien-khai.md`
- Vận hành và kiểm thử: `docs/05-huong-dan/auth-van-hanh-va-kiem-thu.md`
- Joi validation phía client: `docs/05-huong-dan/auth-joi-validation.md`
- Thay đổi theo mốc: `CHANGELOG.md`

## 14. Quy trình phát triển
- Không push trực tiếp vào `main`.
- Tạo branch theo chức năng, mở Pull Request để review.
- Viết commit message rõ ràng theo mục đích:
  - `feat:` thêm tính năng
  - `fix:` sửa lỗi
  - `refactor:` tái cấu trúc
  - `docs:` cập nhật tài liệu
  - `chore:` việc hệ thống/cấu hình

## 15. Hỗ trợ
- Nếu gặp lỗi khi setup, tạo issue trong repository và kèm:
  - log lỗi
  - bước tái hiện
  - môi trường (OS, PHP, Node, MySQL)

---

Tài liệu này ưu tiên cho onboarding thành viên mới và demo nghiệp vụ với stakeholder.
