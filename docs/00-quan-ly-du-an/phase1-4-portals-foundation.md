# Phase 1 - Chuẩn hóa 4 Portal

> Trạng thái: đang triển khai, đã tách xong route/controller ownership nền
> Nhánh làm việc: `codex/phase1-4-portals-foundation`
> Cập nhật gần nhất: 2026-04-21

## 1. Mục tiêu của phase này

Phase 1 chỉ dựng nền kiến trúc cho 4 cổng:

- `hoc-vien`
- `teacher`
- `staff`
- `admin`

Mục tiêu là tách rõ auth, route, layout, điều hướng và ownership module để các phase sau có thể phát triển riêng theo portal mà không tiếp tục dồn toàn bộ nghiệp vụ vào `/admin/*`.

Không nằm trong phase này:

- tài liệu lớp học
- nhận xét học viên
- điểm danh hoàn chỉnh
- nâng cấp workflow gộp lớp/chuyển lớp

## 2. Quyết định kiến trúc đã chốt

### 2.1 Portal và route đăng nhập

- Học viên: `/login`
- Giáo viên: `/teacher/login`
- Nhân viên: `/staff/login`
- Admin: `/admin/login`

### 2.2 Landing route sau đăng nhập

- Học viên: `home.student.index`
- Giáo viên: `teacher.dashboard`
- Nhân viên: `staff.dashboard`
- Admin: `admin.dashboard`

### 2.3 Portal gating

Portal gating mới dùng role cố định, không phụ thuộc `nhomquyen/phanquyen`:

- `portal:teacher` chỉ cho giáo viên
- `portal:staff` chỉ cho nhân viên
- `portal:admin` chỉ cho admin

`nhomquyen/phanquyen` hiện chỉ là phần legacy, bị đóng băng trong phase này và không còn là cơ chế chính để quyết định người dùng được vào cổng nào.

## 3. Ownership module theo portal

### 3.1 Học viên

Giữ các route hiện có ở khu học viên:

- hồ sơ
- lớp học của tôi
- lịch học
- hóa đơn
- chat
- thông báo

### 3.2 Teacher

Portal giáo viên hiện đã có nền route/layout cho:

- `teacher.dashboard`
- `teacher.profile`
- `teacher.classes.index`
- `teacher.schedule.index`
- `teacher.notifications.index`
- `teacher.materials.index` placeholder
- `teacher.evaluations.index` placeholder
- `teacher.attendance.index` placeholder

### 3.3 Staff

Các module vận hành đang được chuẩn hóa sang staff portal:

- học viên
- đăng ký học
- lớp học
- buổi học
- hóa đơn
- thông báo vận hành

### 3.4 Admin

Admin chỉ giữ scope quản trị hệ thống và dữ liệu nền:

- dashboard quản trị
- tài khoản
- giáo viên
- nhân viên
- cơ sở
- phòng học
- ca học
- khóa học
- danh mục
- bài viết
- cấu hình hệ thống

## 4. Những gì đã làm xong trong code

### 4.1 Auth và session

- `LoginService` đã nâng lên 4 context `student|teacher|staff|admin`
- `GET /admin/login` hoạt động như cổng riêng, không còn redirect sang `staff/login`
- logout redirect và landing route đi đúng theo từng role
- `GET /auth/session-status` hiểu đúng 4 portal
- `DeviceSessionService` không còn gộp admin vào staff

### 4.2 Middleware

- thêm `EnsurePortalAccess`
- thêm alias `portal` trong `bootstrap/app.php`
- `IsAdmin` được siết lại đúng nghĩa admin-only
- `CheckPermission` không còn là cổng chính để khóa teacher/staff portal

### 4.3 Route

- thêm route group `teacher.*`
- thêm route group `staff.*`
- giữ route group `admin.*` nhưng thu gọn scope
- thêm redirect mềm từ các URL cũ `/admin/*` của module vận hành sang `staff/*`
- staff routes nay trỏ về namespace riêng:
  - `App\Http\Controllers\Staff\HocVien\*`
  - `App\Http\Controllers\Staff\KhoaHoc\*`
  - `App\Http\Controllers\Staff\TaiChinh\*`
  - `App\Http\Controllers\Staff\ThongBao\*`
- teacher routes nay trỏ về controller tách theo từng màn thay vì dồn vào một `PortalController`

### 4.4 Layout và điều hướng

- layout nội bộ dùng chung được tách menu theo portal
- thêm alias layout `resources/views/layouts/internal.blade.php` cho `teacher/staff` thay vì bám tên `layouts.admin`
- thêm sidebar riêng cho `teacher`, `staff`, `admin`
- admin dashboard được thu gọn về scope quản trị

### 4.5 Tổ chức lại `resources/views`

- admin dashboard đã chuyển sang:
  - `resources/views/admin/dashboard/index.blade.php`
- `resources/views/admin/*` hiện chỉ giữ view thuộc scope admin thật:
  - tài khoản
  - cấu hình
  - cơ sở
  - ca học
  - khóa học
  - nhân sự
  - bài viết
  - thông báo
- staff có folder ownership riêng cho các màn vận hành:
  - `resources/views/staff/dashboard/index.blade.php`
  - `resources/views/staff/hoc-vien/*`
  - `resources/views/staff/dang-ky/*`
  - `resources/views/staff/lop-hoc/*`
  - `resources/views/staff/hoa-don/*`
- teacher có folder riêng theo màn:
  - `resources/views/teacher/dashboard/index.blade.php`
  - `resources/views/teacher/profile/show.blade.php`
  - `resources/views/teacher/lop-hoc/index.blade.php`
  - `resources/views/teacher/lich-day/index.blade.php`
- `resources/views/internal/*` giữ vai trò shared-only cho:
  - notification list
  - placeholder screens
- các view vận hành admin cũ được chuyển về khu tương thích:
  - `resources/views/legacy/admin-operational/*`
- `resources/views/components/internal/sidebar-*.blade.php` là sidebar dùng thật; component cũ `components/admin/sidebar.blade.php` đã loại bỏ.

## 5. File entry point quan trọng

Nếu cần tiếp tục phase này, nên đọc theo thứ tự:

1. `routes/web.php`
2. `app/Services/Auth/LoginService.php`
3. `app/Http/Middleware/EnsurePortalAccess.php`
4. `app/Services/Auth/DeviceSessionService.php`
5. `resources/views/layouts/internal.blade.php`
6. `resources/views/layouts/admin.blade.php`
7. `resources/views/components/internal/sidebar-*.blade.php`
8. `app/Http/Controllers/Staff/**`
9. `app/Http/Controllers/Teacher/**`
10. `resources/views/staff/**`, `resources/views/teacher/**`, `resources/views/admin/dashboard/**`

## 6. Tình trạng kiểm thử hiện tại

Đã chạy và đang pass:

```bash
php artisan test \
  tests/Unit/LoginServiceTest.php \
  tests/Feature/SessionPortalGuardTest.php \
  tests/Feature/StudentPortalAccessTest.php \
  tests/Feature/InternalPortalAccessTest.php
```

Kết quả gần nhất:

- 18 tests passed
- 57 assertions

## 7. Các điểm chưa hoàn tất

- teacher portal mới là nền route/view, chưa có nghiệp vụ thật cho tài liệu, nhận xét, điểm danh
- staff portal đã có namespace controller riêng và ownership view riêng; phần admin cũ còn lại chỉ là lớp tương thích trong `legacy/admin-operational`
- admin hiện chỉ nên xem dữ liệu nền; các CTA điều hướng sang nghiệp vụ vận hành đã được đổi sang trạng thái read-only để tránh link sai portal
- một số tài liệu cũ trước 2026-04-20 có thể còn mô tả mô hình `staff/admin` chung cổng; khi gặp, lấy file này và `docs/05-huong-dan/auth.md` làm nguồn sự thật mới

## 8. Cách hỏi tiếp với Chat Deex

Khi mở phiên sau, nên nói trực tiếp một trong các câu sau:

- `Tiếp tục từ branch codex/phase1-4-portals-foundation. Đọc docs/00-quan-ly-du-an/phase1-4-portals-foundation.md và hoàn thiện teacher portal.`
- `Đọc phase1-4-portals-foundation.md, kiểm tra ownership staff/admin hiện tại và dọn nốt redirect legacy.`
- `Dựa trên phase1-4-portals-foundation.md, bắt đầu phase 2 cho tài liệu lớp học ở teacher/student portal.`

## 9. Gợi ý phase tiếp theo

### Phase 2

- tài liệu lớp học cho teacher upload, student truy cập

### Phase 3

- nhận xét học viên
- điểm danh
- thông báo trạng thái đi học

### Phase 4

- workflow gộp lớp/chuyển lớp ở staff portal
- chuẩn hóa invoice impact và audit log
