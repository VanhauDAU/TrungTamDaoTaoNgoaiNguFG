# DACNCNPM - Hệ Thống Quản Lý Trung Tâm Ngoại Ngữ

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-yellow)](https://opensource.org/licenses/MIT)

Monolith Laravel phục vụ quản lý vận hành trung tâm ngoại ngữ: khóa học, lớp học, học viên, giáo viên, tài chính, bài viết, thông báo và liên hệ khách hàng.

Tai lieu:
- Vietnamese (co dau): [README_vi.md](README_vi.md)

## Muc luc
- [1. Tong quan](#1-tong-quan)
- [2. Tinh nang chinh](#2-tinh-nang-chinh)
- [3. Cong nghe su dung](#3-cong-nghe-su-dung)
- [4. Cau truc du an](#4-cau-truc-du-an)
- [5. Cai dat moi truong local](#5-cai-dat-moi-truong-local)
- [6. Chay du an](#6-chay-du-an)
- [7. Bien moi truong quan trong](#7-bien-moi-truong-quan-trong)
- [8. Lenh huu ich](#8-lenh-huu-ich)
- [9. Test va chat luong ma nguon](#9-test-va-chat-luong-ma-nguon)
- [10. Luu y du lieu va migration](#10-luu-y-du-lieu-va-migration)
- [11. Quy trinh phat trien](#11-quy-trinh-phat-trien)
- [12. Ho tro](#12-ho-tro)

## 1. Tong quan
- Nganh: He thong thong tin quan ly trung tam ngoai ngu.
- Kien truc: Laravel + Blade + MySQL.
- Doi tuong su dung:
  - Khach/nguoi hoc: xem khoa hoc, dang ky tu van, theo doi thong tin ca nhan.
  - Nhan su/Admin: van hanh dao tao, tai chinh, noi dung, thong bao, lien he.
- Route chinh:
  - `web client`: `/`
  - `admin`: `/admin` (yeu cau dang nhap + middleware staff)

## 2. Tinh nang chinh
### Client
- Trang chu, gioi thieu, blog, danh sach/chi tiet khoa hoc.
- Dang ky lop hoc va checkout.
- Trang lien he, form dang ky tu van.
- Khu vuc hoc vien: profile, doi mat khau, lich hoc, lop hoc, hoa don.
- Thong bao realtime cho hoc vien (dropdown + stream API).

### Admin
- Dashboard thong ke tong quan.
- Quan ly hoc vien, giao vien, nhan vien.
- Quan ly dao tao: danh muc khoa hoc, khoa hoc, lop hoc, buoi hoc, ca hoc, hoc phi.
- Quan ly tai chinh: hoa don, phieu thu, cap nhat trang thai.
- Quan ly noi dung: bai viet, danh muc bai viet, tag.
- Quan ly thong bao noi bo.
- Quan ly lien he/lead (co ho tro thung rac va thao tac loat).
- Cau hinh co so dao tao, phong hoc, dia chi theo tinh/phuong.

## 3. Cong nghe su dung
- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade, Bootstrap 5, JS, Vite
- Database: MySQL 8.x
- Build tool: Vite
- Test: PHPUnit (Laravel test runner)

## 4. Cau truc du an
```text
app/
  Http/Controllers/
    Client/        # Controller cho giao dien nguoi hoc
    Admin/         # Controller khu vuc quan tri
  Models/          # Domain model theo nhom: Auth, Course, Education, Finance...
  Services/        # DashboardService, ThongBaoService...
resources/views/
  clients/         # View client
  admin/           # View admin
  components/      # Blade components
routes/
  web.php          # Toan bo route web + admin
database/
  migrations/      # Migration
  seeders/         # Seeder
public/assets/     # Static assets css/js/image
```

## 5. Cai dat moi truong local
### 5.1 Yeu cau
- PHP 8.2+
- Composer 2+
- Node.js 18+ va npm
- MySQL 8.x

### 5.2 Clone va cai dat
```bash
git clone <repo-url>
cd DACNCNPM_TrungTamNN
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 5.3 Cau hinh `.env` (MySQL)
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

## 6. Chay du an
### Cach 1: Chay dong thoi server + queue + vite (khuyen nghi)
```bash
composer dev
```

### Cach 2: Tu chay tung tien trinh
```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

### Cach 3: Build static assets cho production
```bash
npm run build
```

Neu dung XAMPP/Apache:
- Dat project trong `htdocs`.
- Truy cap qua virtual host hoac `/public` theo cau hinh Apache.

## 7. Bien moi truong quan trong
- `APP_URL`: URL goc ung dung.
- `DB_*`: ket noi CSDL.
- `QUEUE_CONNECTION`: mac dinh `database`.
- `MAIL_*`: cau hinh gui mail.
- `GEMINI_API_KEY`, `GEMINI_MODEL`: khoa/mode AI (neu kich hoat tinh nang lien quan).

## 8. Lenh huu ich
```bash
# Chay test
php artisan test

# Clear cache
php artisan optimize:clear

# Tao symlink storage
php artisan storage:link

# Kiem tra hoa don qua han (thu cong)
php artisan invoice:check-overdue
php artisan invoice:check-overdue --dry-run
```

## 9. Test va chat luong ma nguon
```bash
# Test full
composer test

# Format code (Laravel Pint)
./vendor/bin/pint
```

Thu muc test hien co:
- `tests/Feature`
- `tests/Unit`

## 10. Luu y du lieu va migration
- Du an hien co nhieu bang domain custom (`taikhoan`, `lienhe`, `hoadon`, ...).
- Thu muc migration trong repo chu yeu la migration bo sung/cap nhat.
- Neu khoi tao moi tren may sach, can dam bao da co schema nen tu team (hoac bo migration day du) truoc khi chay du an toan phan.

Lenh migrate co ban:
```bash
php artisan migrate
```

## 11. Quy trinh phat trien
- Khong push truc tiep vao `main`.
- Tao branch theo chuc nang, mo Pull Request de review.
- Viet commit message ro rang theo muc dich:
  - `feat:` them tinh nang
  - `fix:` sua loi
  - `refactor:` tai cau truc
  - `docs:` cap nhat tai lieu
  - `chore:` viec he thong/cau hinh

## 12. Ho tro
- Neu gap loi khi setup, tao issue trong repository va kem:
  - log loi
  - buoc tai hien
  - moi truong (OS, PHP, Node, MySQL)

---

Cap nhat README gan day: chuan hoa tai lieu, dong bo voi Laravel 12/PHP 8.2+, bo sung huong dan setup/chay/test va luu y migration.
