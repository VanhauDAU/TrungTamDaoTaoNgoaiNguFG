# ENVIRONMENT SETUP GUIDE

> Hướng dẫn thiết lập môi trường phát triển cho team members

| Thông tin | Chi tiết |
|-----------|----------|
| **Tên dự án** | Hệ thống Quản lý Trung tâm Ngoại ngữ (DACNCNPM) |
| **Tech Stack** | Laravel 12 · PHP 8.2+ · MySQL 8.x · Vite 7 · Bootstrap 5 · Blade |
| **Node Version** | >= 18.x LTS |
| **Database** | MySQL 8.x |
| **Ngày cập nhật** | 05/03/2026 |

---

## MỤC LỤC

1. [Prerequisites](#1-prerequisites)
2. [Setup Steps](#2-setup-steps)
3. [Cấu hình Environment Variables](#3-cấu-hình-environment-variables)
4. [Chạy dự án](#4-chạy-dự-án)
5. [Verification Checklist](#5-verification-checklist)
6. [Lệnh hữu ích](#6-lệnh-hữu-ích)
7. [Cấu trúc dự án](#7-cấu-trúc-dự-án)
8. [Quy trình phát triển (Git Workflow)](#8-quy-trình-phát-triển-git-workflow)
9. [Xử lý lỗi thường gặp](#9-xử-lý-lỗi-thường-gặp)

---

## 1. PREREQUISITES

Đảm bảo đã cài đặt các công cụ sau trước khi bắt đầu:

| ✓ | Công cụ | Phiên bản | Link download | Ghi chú |
|---|---------|-----------|---------------|---------|
| ☐ | **Git** | >= 2.30 | [git-scm.com](https://git-scm.com/) | Quản lý source code |
| ☐ | **PHP** | >= 8.2 | [php.net](https://www.php.net/) | Hoặc cài qua XAMPP |
| ☐ | **Composer** | >= 2.x | [getcomposer.org](https://getcomposer.org/) | PHP dependency manager |
| ☐ | **Node.js** | >= 18.x LTS | [nodejs.org](https://nodejs.org/) | Kèm npm |
| ☐ | **MySQL** | >= 8.0 | [mysql.com](https://www.mysql.com/) | Hoặc dùng qua XAMPP |
| ☐ | **XAMPP** | Latest (PHP 8.2+) | [apachefriends.org](https://www.apachefriends.org/) | Bao gồm PHP + MySQL + Apache |
| ☐ | **VS Code** | Latest | [code.visualstudio.com](https://code.visualstudio.com/) | Khuyến nghị dùng IDE này |
| ☐ | **DBeaver / phpMyAdmin** | Latest | [dbeaver.io](https://dbeaver.io/) | Quản lý database |

### VS Code Extensions khuyến nghị

| Extension | Mô tả |
|-----------|--------|
| PHP Intelephense | IntelliSense cho PHP |
| Laravel Blade Snippets | Hỗ trợ Blade template |
| Laravel Extra Intellisense | Autocomplete route, config, view |
| Tailwind CSS IntelliSense | Hỗ trợ TailwindCSS |
| MySQL (cweijan) | Kết nối & query MySQL |
| GitLens | Xem lịch sử Git chi tiết |

---

## 2. SETUP STEPS

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd DoAnChuyenNganhCNPM
```

> **Lưu ý (XAMPP):** Clone project vào thư mục `C:\xampp\htdocs\` để Apache có thể serve.

---

### Step 2: Cài đặt PHP Dependencies

```bash
composer install
```

> Nếu gặp lỗi thiếu extension PHP, kiểm tra `php.ini` và bật các extension:
> `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`

---

### Step 3: Cài đặt Node.js Dependencies

```bash
npm install
```

---

### Step 4: Setup Environment Variables

```bash
# Tạo file .env từ mẫu
cp .env.example .env

# Tạo APP_KEY
php artisan key:generate
```

Sau đó mở file `.env` và chỉnh sửa theo hướng dẫn ở [Mục 3](#3-cấu-hình-environment-variables).

---

### Step 5: Tạo Database

Mở **phpMyAdmin** hoặc **DBeaver** và tạo database mới:

```sql
CREATE DATABASE doanchuyennganhcnpm_trungtamnn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

---

### Step 6: Chạy Migrations

```bash
php artisan migrate
```

> ⚠️ **Lưu ý quan trọng:** Dự án có nhiều bảng domain custom (`taikhoan`, `lophoc`, `hoadon`, ...) được tạo bằng SQL dump ban đầu. Các migration trong repo chủ yếu là migration bổ sung/cập nhật. Nếu setup trên máy sạch, cần **import schema đầy đủ từ team** (file `.sql`) trước khi chạy migration.

---

### Step 7: Tạo Storage Symlink

```bash
php artisan storage:link
```

> Tạo symbolic link từ `public/storage` → `storage/app/public` để serve file upload.

---

### Step 8: Setup nhanh (tùy chọn)

Nếu muốn chạy tất cả bước 2-7 tự động:

```bash
composer setup
```

Script `composer setup` sẽ tự động:
1. `composer install`
2. Copy `.env.example` → `.env` (nếu chưa có)
3. `php artisan key:generate`
4. `php artisan migrate`
5. `npm install`
6. `npm run build`

---

## 3. CẤU HÌNH ENVIRONMENT VARIABLES

Mở file `.env` và cập nhật các giá trị sau:

### Cấu hình cơ bản

```env
APP_NAME="DACNCNPM TrungTamNN"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

### Cấu hình Database (MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doanchuyennganhcnpm_trungtamnn
DB_USERNAME=root
DB_PASSWORD=
```

> **Nếu dùng XAMPP:** MySQL port mặc định là `3306`. Nếu bị conflict (đã cài MySQL riêng), có thể đổi port trong XAMPP → `3307` và cập nhật `DB_PORT=3307`.

### Cấu hình Session & Queue

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database
CACHE_STORE=database
```

### Cấu hình Mail (Development)

```env
# Dùng log driver để xem mail trong file log (không gửi thật)
MAIL_MAILER=log
```

> **Production:** Đổi sang SMTP / Mailgun / SES tùy yêu cầu.

---

## 4. CHẠY DỰ ÁN

### 🚀 Cách 1: Chạy đồng thời (KHUYẾN NGHỊ)

```bash
composer dev
```

Lệnh này sẽ chạy đồng thời 4 tiến trình:
| Tiến trình | Mô tả | Mặc định |
|------------|--------|----------|
| **server** | `php artisan serve` | `http://127.0.0.1:8000` |
| **queue** | `php artisan queue:listen` | Xử lý job queue |
| **logs** | `php artisan pail` | Xem log realtime |
| **vite** | `npm run dev` | `http://localhost:5173` (HMR) |

---

### Cách 2: Chạy từng tiến trình riêng

Mở **3 terminal** riêng biệt:

**Terminal 1 — Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 — Vite (Frontend):**
```bash
npm run dev
```

**Terminal 3 — Queue Worker (nếu cần):**
```bash
php artisan queue:listen --tries=1 --timeout=0
```

---

### Cách 3: Dùng XAMPP + Apache

1. Đặt project trong `C:\xampp\htdocs\DoAnChuyenNganhCNPM`
2. Cấu hình Virtual Host trong `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName dacncnpm.local
    DocumentRoot "C:/xampp/htdocs/DoAnChuyenNganhCNPM/public"
    <Directory "C:/xampp/htdocs/DoAnChuyenNganhCNPM/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Thêm vào `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1    dacncnpm.local
```

4. Chạy Vite riêng: `npm run dev`
5. Truy cập: `http://dacncnpm.local`

---

### Build Production

```bash
npm run build
```

> Biên dịch CSS/JS thành file static trong `public/build/`. Dùng cho deploy lên production.

---

## 5. VERIFICATION CHECKLIST

Sau khi setup xong, kiểm tra các mục sau:

| ✓ | Kiểm tra | Lệnh / Cách kiểm tra | Kết quả mong đợi |
|---|----------|----------------------|-------------------|
| ☐ | PHP version đúng | `php -v` | `PHP 8.2.x` trở lên |
| ☐ | Composer hoạt động | `composer -V` | `Composer version 2.x` |
| ☐ | Node.js version đúng | `node -v` | `v18.x` trở lên |
| ☐ | npm hoạt động | `npm -v` | `9.x` trở lên |
| ☐ | MySQL đang chạy | `mysql -u root -p -e "SELECT 1"` | Trả về `1` |
| ☐ | Database tồn tại | Truy cập phpMyAdmin | Thấy DB `doanchuyennganhcnpm_trungtamnn` |
| ☐ | `.env` đã cấu hình | Kiểm tra `DB_*` trong `.env` | Đúng thông tin DB local |
| ☐ | APP_KEY đã tạo | Kiểm tra `.env` | `APP_KEY=base64:...` (không rỗng) |
| ☐ | Migration OK | `php artisan migrate:status` | Tất cả migration `Ran` |
| ☐ | Storage link OK | `php artisan storage:link` | Không báo lỗi |
| ☐ | Laravel server chạy | `php artisan serve` | `Server running on [http://127.0.0.1:8000]` |
| ☐ | Vite dev server chạy | `npm run dev` | `VITE ready in xxx ms` |
| ☐ | Trang web load được | Mở `http://127.0.0.1:8000` | Hiển thị giao diện client |
| ☐ | Admin login được | Mở `http://127.0.0.1:8000/admin` | Hiển thị trang đăng nhập |
| ☐ | CSS/JS load đúng | Inspect → Console | Không có lỗi 404 asset |

---

## 6. LỆNH HỮU ÍCH

### Artisan Commands

```bash
# ── Chạy & Debug ─────────────────────────────
php artisan serve                        # Khởi động server
php artisan tinker                       # REPL tương tác với app

# ── Database ──────────────────────────────────
php artisan migrate                      # Chạy migration mới
php artisan migrate:rollback             # Rollback migration gần nhất
php artisan migrate:status               # Kiểm tra trạng thái migration
php artisan db:seed                      # Chạy seeder

# ── Cache & Optimize ─────────────────────────
php artisan optimize:clear               # Xóa toàn bộ cache
php artisan config:clear                 # Xóa config cache
php artisan route:clear                  # Xóa route cache
php artisan view:clear                   # Xóa compiled views

# ── Storage ───────────────────────────────────
php artisan storage:link                 # Tạo symlink public/storage

# ── Test ──────────────────────────────────────
php artisan test                         # Chạy test suite
composer test                            # Chạy test (qua composer)

# ── Code Style ────────────────────────────────
./vendor/bin/pint                        # Format code theo Laravel standard

# ── Nghiệp vụ ─────────────────────────────────
php artisan invoice:check-overdue        # Kiểm tra hóa đơn quá hạn
php artisan invoice:check-overdue --dry-run  # Chạy thử (không thay đổi DB)
```

### NPM Scripts

```bash
npm run dev                              # Khởi động Vite dev server (HMR)
npm run build                            # Build production assets
```

### Composer Scripts

```bash
composer install                         # Cài PHP dependencies
composer setup                           # Setup toàn bộ dự án (một lần)
composer dev                             # Chạy đồng thời server + queue + vite
```

---

## 7. CẤU TRÚC DỰ ÁN

```
DoAnChuyenNganhCNPM/
├── app/
│   ├── Http/Controllers/
│   │   ├── Client/              # Controller giao diện người học
│   │   └── Admin/               # Controller khu vực quản trị
│   │       ├── KhoaHoc/         #   Khóa học, lớp học, buổi học
│   │       ├── TaiChinh/        #   Hóa đơn, phiếu thu
│   │       ├── NhanSu/          #   Giáo viên, nhân viên
│   │       └── ...
│   ├── Models/                  # Eloquent Models theo module
│   │   ├── Auth/                #   TaiKhoan, NhanSu, NhomQuyen, PhanQuyen
│   │   ├── Content/             #   BaiViet, DanhMucBaiViet, Tag
│   │   ├── Course/              #   KhoaHoc, DanhMucKhoaHoc, HocPhi
│   │   ├── Education/           #   LopHoc, BuoiHoc, DangKyLopHoc, DiemDanh
│   │   ├── Facility/            #   CoSoDaoTao, PhongHoc, TinhThanh
│   │   ├── Finance/             #   HoaDon, PhieuThu
│   │   └── Interaction/         #   ThongBao, LienHe
│   └── Services/                # Business logic services
├── resources/
│   ├── views/
│   │   ├── clients/             # Blade views cho client
│   │   ├── admin/               # Blade views cho admin
│   │   └── components/          # Reusable Blade components
│   ├── sass/                    # SCSS stylesheets
│   └── js/                      # JavaScript files
├── routes/
│   └── web.php                  # Toàn bộ routes (client + admin)
├── database/
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── public/
│   └── assets/                  # Static assets (images, css, js)
├── docs/                        # Tài liệu dự án
├── .env.example                 # Mẫu biến môi trường
├── composer.json                # PHP dependencies & scripts
├── package.json                 # Node.js dependencies
└── vite.config.js               # Vite build config
```

---

## 8. QUY TRÌNH PHÁT TRIỂN (GIT WORKFLOW)

### Branching Strategy

```
main (production)
 └── develop (integration)
      ├── feat/ten-tinh-nang
      ├── fix/ten-loi
      └── docs/ten-tai-lieu
```

### Quy tắc

1. **KHÔNG push trực tiếp vào `main`**
2. Tạo branch theo tính năng → mở **Pull Request** → review → merge
3. Viết commit message rõ ràng theo convention:

| Prefix | Mục đích | Ví dụ |
|--------|----------|-------|
| `feat:` | Thêm tính năng mới | `feat: thêm chức năng điểm danh` |
| `fix:` | Sửa lỗi | `fix: sửa lỗi đăng ký lớp trùng` |
| `refactor:` | Tái cấu trúc code | `refactor: tách service điểm danh` |
| `docs:` | Cập nhật tài liệu | `docs: thêm database schema` |
| `chore:` | Việc hệ thống/cấu hình | `chore: update composer dependencies` |
| `style:` | Format code, không đổi logic | `style: format blade theo pint` |

### Workflow mẫu

```bash
# 1. Cập nhật develop mới nhất
git checkout develop
git pull origin develop

# 2. Tạo branch mới
git checkout -b feat/diem-danh-online

# 3. Code & commit
git add .
git commit -m "feat: thêm điểm danh online cho giáo viên"

# 4. Push & tạo PR
git push origin feat/diem-danh-online
# → Mở PR trên GitHub/GitLab để review
```

---

## 9. XỬ LÝ LỖI THƯỜNG GẶP

### ❌ Lỗi: `SQLSTATE[HY000] [2002] Connection refused`

**Nguyên nhân:** MySQL chưa chạy hoặc sai port.

**Cách fix:**
1. Kiểm tra MySQL đã start trong XAMPP Control Panel
2. Kiểm tra `DB_PORT` trong `.env` có khớp port MySQL thật
3. Thử: `mysql -u root -h 127.0.0.1 -P 3306`

---

### ❌ Lỗi: `Vite manifest not found`

**Nguyên nhân:** Chưa build assets hoặc Vite dev server chưa chạy.

**Cách fix:**
```bash
# Development: chạy Vite dev server
npm run dev

# Production: build assets
npm run build
```

---

### ❌ Lỗi: `The stream or file ... could not be opened ... Permission denied`

**Nguyên nhân:** Thiếu quyền ghi thư mục `storage/` hoặc `bootstrap/cache/`.

**Cách fix (Windows):**
```bash
# Đảm bảo thư mục tồn tại
mkdir storage\logs
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir storage\framework\cache
```

---

### ❌ Lỗi: `Class not found` sau khi pull code mới

**Cách fix:**
```bash
composer dump-autoload
php artisan optimize:clear
```

---

### ❌ Lỗi: `The symlink storage already exists` 

**Cách fix:**
```bash
# Xóa symlink cũ (Windows)
rmdir public\storage
php artisan storage:link
```

---

### ❌ Lỗi: `npm install` thất bại

**Cách fix:**
```bash
# Xóa cache & node_modules, cài lại
rmdir /s /q node_modules
del package-lock.json
npm cache clean --force
npm install
```

---

> **📮 Hỗ trợ:** Nếu gặp lỗi khác khi setup, tạo Issue trong repository kèm theo:
> - Log lỗi đầy đủ
> - Các bước tái hiện
> - Thông tin môi trường: OS, PHP version, Node version, MySQL version
