# DACNCNPM - Hệ Thống Quản Lý Trung Tâm Ngoại Ngữ

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-blue)](https://www.php.net/)
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
- [8. Mo hinh hoc phi hien tai](#8-mo-hinh-hoc-phi-hien-tai)
- [9. Tai lieu Auth va Nhan su](#9-tai-lieu-auth-va-nhan-su)
- [10. Lenh huu ich](#10-lenh-huu-ich)
- [11. Test va chat luong ma nguon](#11-test-va-chat-luong-ma-nguon)
- [12. Luu y du lieu va migration](#12-luu-y-du-lieu-va-migration)
- [13. Quy trinh phat trien](#13-quy-trinh-phat-trien)
- [14. Ho tro](#14-ho-tro)

## 1. Tong quan
- Nganh: He thong thong tin quan ly trung tam ngoai ngu.
- Kien truc: Laravel + Blade + MySQL.
- Doi tuong su dung:
  - Khach/nguoi hoc: xem khoa hoc, dang ky tu van, theo doi thong tin ca nhan.
  - Nhan su/Admin: van hanh dao tao, tai chinh, noi dung, thong bao, lien he.
- Route chinh:
  - `web client`: `/`
  - `dang nhap hoc vien`: `/login`
  - `dang nhap giang vien`: `/teacher/login`
  - `dang nhap nhan vien/admin`: `/staff/login`
  - `khu noi bo`: `/admin` (yeu cau dang nhap + middleware staff)

## 2. Tinh nang chinh
### Client
- Trang chu, gioi thieu, blog, danh sach/chi tiet khoa hoc.
- Dang ky lop hoc va checkout.
- Trang lien he, form dang ky tu van.
- Khu vuc hoc vien: profile, doi mat khau, lich hoc, lop hoc, hoa don.
- Auth hoc vien: dang ky, xac thuc email, quen/dat lai mat khau, dang nhap Google, reCAPTCHA, ghi nho dang nhap.
- Thong bao realtime cho hoc vien (dropdown + stream API).

### Admin
- Dashboard thong ke tong quan.
- Quan ly hoc vien, giao vien, nhan vien.
- Ho so nhan su chi tiet, ban giao tai khoan, tai lieu nhan su private.
- Quan ly dao tao: danh muc khoa hoc, khoa hoc, lop hoc, buoi hoc, ca hoc, chinh sach gia lop.
- Quan ly tai chinh: hoa don, phieu thu, cap nhat trang thai.
- Goi luong nhan su va tai lieu handoff payroll/Figma.
- Quan ly noi dung: bai viet, danh muc bai viet, tag.
- Quan ly thong bao noi bo.
- Quan ly lien he/lead (co ho tro thung rac va thao tac loat).
- Cau hinh co so dao tao, phong hoc, dia chi theo tinh/phuong.
- Cong noi bo tach theo portal:
  - `/teacher/login` cho giang vien
  - `/staff/login` cho nhan vien va admin

## 3. Cong nghe su dung
- Backend: Laravel 12, PHP 8.3+
- Frontend: Blade, Bootstrap 5, JS, Vite
- Database: MySQL 8.x
- Cache/Redis client: Predis 3.x
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
- PHP 8.3+
- Composer 2+
- Node.js 18+ va npm
- MySQL 8.x
- Redis 7+ neu muon bat cache Redis cho cac chuc nang realtime

### 5.2 Clone va cai dat
```bash
git clone <repo-url>
cd DACNCNPM_TrungTamNN
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Luu y moi truong:
- Dependency hien tai yeu cau runtime PHP `>= 8.3`.
- Neu may dang co ca XAMPP PHP 8.2 va Homebrew PHP 8.5, hay dung `php`, `composer`, `artisan` theo PHP Homebrew de chay local.
- XAMPP van co the dung cho MySQL/Apache neu PHP di kem da duoc nang cap; neu khong, nen chay app bang `php artisan serve`.

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

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
RATE_LIMITER_STORE=redis
PUBLIC_LIST_CACHE_STORE=redis
PUBLIC_LIST_CACHE_TTL=300
AUTH_LOGIN_RATE_LIMIT_PER_MINUTE=12
AUTH_LOGIN_RATE_LIMIT_IP_PER_MINUTE=30
AUTH_REGISTER_RATE_LIMIT_PER_MINUTE=6
AUTH_REGISTER_RATE_LIMIT_IP_PER_MINUTE=12
AUTH_EMAIL_CHECK_RATE_LIMIT_PER_MINUTE=30
AUTH_EMAIL_CHECK_RATE_LIMIT_IP_PER_MINUTE=120
REGISTER_EMAIL_CHECK_CACHE_STORE=redis
REGISTER_EMAIL_CHECK_CACHE_TTL=60
```

Neu bat Redis local:
```bash
brew tap redis/redis
brew install --cask redis
redis-server /opt/homebrew/etc/redis.conf
redis-cli PING
```

Neu Redis tra `PONG`, cai client PHP cho Laravel:
```bash
composer require predis/predis
php artisan optimize:clear
```

Neu may local can chay mail queue, gui thong bao hang loat hoac export nen bang Redis:
```env
QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
```

## 6. Chay du an
### Cach 1: Chay dong thoi server + queue + vite (khuyen nghi)
```bash
composer dev
```

### Cach 2: Tu chay tung tien trinh
```bash
php artisan serve
composer queue:redis
npm run dev
```

### Cach 3: Build static assets cho production
```bash
npm run build
```

Neu dung XAMPP/Apache:
- Dat project trong `htdocs`.
- Truy cap qua virtual host hoac `/public` theo cau hinh Apache.
- Chi dung XAMPP de chay web khi PHP cua XAMPP dat `>= 8.3`; neu XAMPP con PHP 8.2 thi `artisan` va ung dung se bi chan boi Composer platform check.

## 7. Bien moi truong quan trong
- `APP_URL`: URL goc ung dung.
- `DB_*`: ket noi CSDL.
- `QUEUE_CONNECTION`: nen de `redis` neu may local can chay mail queue, gui thong bao hang loat hoac export nen.
- `REDIS_*`: cau hinh ket noi Redis.
- `RATE_LIMITER_STORE`: cache store dung cho Laravel rate limiter, mac dinh `redis`.
- `PUBLIC_LIST_CACHE_STORE`: store dung cho cache danh sach public nhu khoa hoc/blog/footer.
- `PUBLIC_LIST_CACHE_TTL`: thoi gian cache danh sach public, mac dinh 300 giay.
- `AUTH_LOGIN_RATE_LIMIT_*`: nguong chan spam cho login theo tai khoan/IP.
- `AUTH_REGISTER_RATE_LIMIT_*`: nguong chan spam cho submit dang ky.
- `AUTH_EMAIL_CHECK_RATE_LIMIT_*`: nguong chan spam cho kiem tra email realtime.
- `REGISTER_EMAIL_CHECK_CACHE_STORE`: store dung cho cache kiem tra email realtime.
- `REGISTER_EMAIL_CHECK_CACHE_TTL`: thoi gian cache ket qua check email, mac dinh 60 giay.
- `MAIL_*`: cau hinh gui mail.
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`: dang nhap Google cho hoc vien.
- `RECAPTCHA_*`: reCAPTCHA v3 cho login/register/quen mat khau public.
- `GEMINI_API_KEY`, `GEMINI_MODEL`: khoa/mode AI (neu kich hoat tinh nang lien quan).

## 8. Mo hinh hoc phi hien tai
- Hoc phi duoc quan ly o cap `lop hoc`, khong con o cap `khoa hoc`.
- `khoahoc` chi mo ta san pham dao tao; gia ban va cach thu tien nam o `lophoc_chinhsachgia`.
- `lophoc` co the tao truoc khi nhap hoc phi, nhung phai co chinh sach gia hop le truoc khi chuyen sang trang thai tuyen sinh/van hanh.
- `ngayKetThuc` cua lop khong nhap tay trong flow moi; he thong dong bo theo buoi hoc cuoi cung.
- Khi hoc vien dang ky, he thong chup `snapshot` hoc phi vao `dangkylophoc` de khoa gia tai thoi diem dang ky.
- `hieuLucTu` / `hieuLucDen` la khoang thoi gian chinh sach gia duoc phep ap dung cho dang ky moi, khong phai ngay hoc cua lop.
- Bang `lophoc_dotthu` duoc dung de luu ke hoach thu theo dot; runtime hien tai van tao 1 hoa don tong cho moi dang ky va de san nen cho billing tach dot ve sau.
- Huong dan van hanh tong hop: `docs/05-huong-dan/huong-dan.md`.

## 9. Tai lieu Auth va Nhan su
- Portal dang nhap hien tai:
  - Hoc vien: `/login`
  - Giang vien: `/teacher/login`
  - Nhan vien/Admin: `/staff/login`
- Giao dien login su dung dock chuyen portal co dinh o day man hinh de doi nhanh giua cac cong dang nhap.
- Tong quan module Auth: `docs/05-huong-dan/auth.md`
- Kien truc va quyet dinh: `docs/01-phan-tich/auth-kien-truc-va-quyet-dinh.md`
- Cau hinh va trien khai: `docs/05-huong-dan/auth-cau-hinh-va-trien-khai.md`
- Van hanh va kiem thu: `docs/05-huong-dan/auth-van-hanh-va-kiem-thu.md`
- Joi validation phia client: `docs/05-huong-dan/auth-joi-validation.md`
- Ho so nhan su, ban giao tai khoan, CV, PDF: `docs/05-huong-dan/nhan-su-ho-so-va-ban-giao-tai-khoan.md`
- Luong nhan su va payroll: `docs/05-huong-dan/luong-nhan-su-va-payroll.md`
- Figma handoff payroll: `docs/05-huong-dan/figma-luong-handoff.md`
- Thay doi theo moc: `CHANGELOG.md`

## 10. Lenh huu ich
```bash
# Chay test
php artisan test

# Clear cache
php artisan optimize:clear

# Kiem tra Redis
redis-cli PING
php artisan tinker

# Chay worker Redis cho mail/thong bao/export
composer queue:redis

# Tao symlink storage
php artisan storage:link

# Kiem tra hoa don qua han (thu cong)
php artisan invoice:check-overdue
php artisan invoice:check-overdue --dry-run
```

Trong `tinker` co the kiem tra nhanh:
```php
Cache::store('redis')->put('redis_test', 'ok', 60);
Cache::store('redis')->get('redis_test');
```

## Redis dang lam gi trong project
- Cache ket qua `GET /register/check-email` de giam query MySQL lap lai khi nguoi dung go email dang ky.
- Lam store cho Laravel rate limiter cua `login`, `register` va `check-email`.
- Cache cac danh sach public doc nhieu:
  - `/khoa-hoc`
  - danh sach lop public trong `/khoa-hoc/{slug}`
  - `/blog`
  - block public o trang chu
  - footer va `register-advice`
- Chat typing/presence hien dung Laravel `Cache`; phan nay chi chay tren Redis neu ban cau hinh store cache phu hop cho chat.

Muon quan sat cache public bang Redis:
```bash
redis-cli MONITOR
```

Sau do mo mot trong cac trang:
- `/khoa-hoc`
- `/blog`
- `/`

Hoac scan key:
```bash
redis-cli --scan --pattern '*public-content*'
```

Muon quan sat Redis dang duoc hit khi nhap email o form dang ky:
```bash
redis-cli MONITOR
```

Sau do mo `/register` va nhap email. Ban se thay key dang `auth:register:email-check:<sha1>` duoc `GET`/`SETEX`.

Muon quan sat rate limit Redis cua Auth:
- nhap login qua nhanh hoac goi lap `/register/check-email`
- tren `redis-cli MONITOR` ban se thay cac key limiter duoc doc/ghi truoc khi request vao sau controller
- cac route nay hien co limiter rieng:
  - `auth-login`
  - `auth-register`
  - `auth-email-check`

Sau khi `git pull` code moi tu team, nen chay lai toi thieu:
```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
```

Neu pull co thay doi `.env.example`, hay cap nhat `.env` thu cong truoc khi chay app.

Neu may do dung Redis local, chay them:
```bash
redis-cli PING
composer queue:redis
```

## 11. Test va chat luong ma nguon
```bash
# Test full
composer test

# Format code (Laravel Pint)
./vendor/bin/pint
```

Thu muc test hien co:
- `tests/Feature`
- `tests/Unit`

## 12. Luu y du lieu va migration
- Du an hien co nhieu bang domain custom (`taikhoan`, `lienhe`, `hoadon`, ...).
- Thu muc migration trong repo chu yeu la migration bo sung/cap nhat.
- Neu khoi tao moi tren may sach, can dam bao da co schema nen tu team (hoac bo migration day du) truoc khi chay du an toan phan.
- Migration `2026_03_14_150000_refactor_class_pricing_to_lophoc_chinhsachgia.php` chuyen hoc phi tu mo hinh cu (`hocphi`, `lophoc.hocPhiId`) sang mo hinh moi theo lop hoc.
- Migration `2026_03_15_200000` -> `2026_03_15_200400` bo sung ho so nhan su, goi luong, tai lieu nhan su va backfill du lieu cu.

Luu y import dump SQL:
- Khong nen phuc hoi du lieu bang cach tat `FOREIGN_KEY_CHECKS` roi bo qua orphan record.
- Cac bang nhu `lophoc`, `lophoc_chinhsachgia`, `buoihoc`, `chat_rooms` phai dong bo khoa ngoai truoc khi them constraint.
- Neu gap loi `#1452`, can soat lai dump de dam bao ban ghi cha ton tai truoc ban ghi con.

Lenh migrate co ban:
```bash
php artisan migrate
```

## 13. Quy trinh phat trien
- Khong push truc tiep vao `main`.
- Tao branch theo chuc nang, mo Pull Request de review.
- Viet commit message ro rang theo muc dich:
  - `feat:` them tinh nang
  - `fix:` sua loi
  - `refactor:` tai cau truc
  - `docs:` cap nhat tai lieu
  - `chore:` viec he thong/cau hinh

## 14. Ho tro
- Neu gap loi khi setup, tao issue trong repository va kem:
  - log loi
  - buoc tai hien
  - moi truong (OS, PHP, Node, MySQL)

---

Cap nhat README gan day: dong bo mo hinh hoc phi theo lop hoc, bo sung link van hanh hoc phi va luu y migration refactor pricing.
