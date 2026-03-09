# DEFINITION OF DONE CHECKLIST

> Checklist xác nhận hoàn thành cho mỗi User Story / Task
> Đồ án Chuyên ngành CNPM — Trường ĐH Kiến trúc Đà Nẵng — Nhóm **Five Genius**

| Thông tin | Chi tiết |
|-----------|----------|
| **Dự án** | Hệ thống Quản lý Trung tâm Ngoại ngữ |
| **Tech Stack** | Laravel 12 · PHP 8.2+ · MySQL 8 · Blade · Vite |
| **Ngày cập nhật** | 05/03/2026 |

---

## SPRINT 1 — AUTH

---

### US-001: Đăng ký tài khoản học viên

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 1 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Route `/register`, Controller, Blade view |
| ☐ | Validation đầy đủ (email, password, hoTen) | Bắt buộc | FormRequest hoặc `$request->validate()` |
| ☐ | Mật khẩu được hash trước khi lưu DB | Bắt buộc | Dùng `Hash::make()` hoặc bcrypt |
| ☐ | Không hardcode (dùng `.env` cho mail config) | Bắt buộc | |
| ☐ | Không còn `dd()`, `dump()`, `console.log()` | Bắt buộc | |
| ☐ | Tuân thủ coding standard (Laravel Pint) | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Đăng ký thành công với thông tin hợp lệ | Bắt buộc | Tạo bản ghi `taikhoan` + `hosonguoidung` |
| ☐ | AC2: Báo lỗi khi email đã tồn tại | Bắt buộc | Hiển thị message lỗi trùng email |
| ☐ | Kiểm tra validation: email sai format, password quá ngắn | Bắt buộc | |
| ☐ | Kiểm tra giao diện form đăng ký hiển thị đúng | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Pull Request có mô tả: "US-001 Đăng ký tài khoản" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Tất cả review comments đã xử lý | Bắt buộc |
| ☐ | Commit message: `feat: đăng ký tài khoản học viên` | Bắt buộc |
| ☐ | Không merge conflict, đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema nếu thêm/sửa cột | Khuyến khích |
| ☐ | README cập nhật nếu thêm route/config mới | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-002: Đăng nhập

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 1 |
| **Assignee** | __________________ |
| **Story Points** | 2 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Route `/login`, LoginController |
| ☐ | Sử dụng `Auth::attempt()` để xác thực | Bắt buộc | Xác thực qua bảng `taikhoan` |
| ☐ | Redirect đúng theo role (Admin→admin, HV→client) | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |
| ☐ | Tuân thủ coding standard | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Đăng nhập thành công → chuyển hướng Dashboard | Bắt buộc | Kiểm tra cả role Admin và Học viên |
| ☐ | AC2: Sai thông tin → hiển thị lỗi rõ ràng | Bắt buộc | Message "Tài khoản hoặc mật khẩu không chính xác" |
| ☐ | Kiểm tra tài khoản bị khóa (trangThai=0) không đăng nhập được | Bắt buộc | |
| ☐ | Remember me hoạt động đúng | Khuyến khích | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-002 Đăng nhập" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: chức năng đăng nhập` | Bắt buộc |
| ☐ | Không merge conflict, đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật README nếu thay đổi flow auth | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-003: Quên mật khẩu

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 1 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Route forgot-password & reset-password |
| ☐ | Sử dụng `Password::sendResetLink()` của Laravel | Bắt buộc | Dùng bảng `password_reset_tokens` |
| ☐ | Mail config qua `.env` (không hardcode) | Bắt buộc | |
| ☐ | Link reset có thời hạn hết hạn | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Nhập email hợp lệ → gửi link reset thành công | Bắt buộc | Kiểm tra trong mail log |
| ☐ | AC2: Click link → đặt mật khẩu mới → đăng nhập lại | Bắt buộc | |
| ☐ | Email không tồn tại → hiển thị thông báo lỗi | Bắt buộc | |
| ☐ | Link hết hạn → hiển thị thông báo phù hợp | Khuyến khích | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-003 Quên mật khẩu" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: chức năng quên mật khẩu` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật cấu hình MAIL trong ENV guide | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-004: Phân quyền (Học viên / Giáo viên / Admin)

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 1 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Middleware, `canDo()`, `isStaff()` |
| ☐ | Sử dụng bảng `nhomQuyen` + `phanQuyen` đúng cách | Bắt buộc | CRUD quyền theo tính năng |
| ☐ | Middleware kiểm tra role trước khi vào route admin | Bắt buộc | |
| ☐ | Menu sidebar ẩn/hiện theo quyền | Bắt buộc | Blade `@if` kiểm tra `canDo()` |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Admin gán vai trò "Giáo viên" → chỉ thấy menu điểm danh | Bắt buộc | |
| ☐ | AC2: Học viên đăng nhập → chỉ thấy lịch học và đăng ký | Bắt buộc | |
| ☐ | Truy cập trực tiếp URL admin khi không có quyền → bị chặn (403) | Bắt buộc | |
| ☐ | Admin (role=3) luôn có toàn quyền | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-004 Phân quyền" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: phân quyền theo nhóm quyền` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng nhomQuyen, phanQuyen) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## SPRINT 2 — USER & COURSE MANAGEMENT

---

### US-005: Quản lý tài khoản (CRUD)

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 2 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | CRUD Controller cho taikhoan |
| ☐ | Validation đầy đủ khi tạo/sửa tài khoản | Bắt buộc | Email unique, role hợp lệ |
| ☐ | Soft delete khi xóa tài khoản | Bắt buộc | SoftDeletes trên model TaiKhoan |
| ☐ | Phân trang danh sách | Bắt buộc | `paginate()` |
| ☐ | Không còn debug code, tuân thủ coding standard | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Tạo tài khoản mới → hiển thị trong danh sách | Bắt buộc | |
| ☐ | AC2: Cập nhật thông tin → lưu thành công + thông báo | Bắt buộc | |
| ☐ | Xóa tài khoản → không còn trong danh sách | Bắt buộc | Soft delete |
| ☐ | Tìm kiếm/lọc tài khoản theo role hoạt động đúng | Khuyến khích | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-005 CRUD tài khoản" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: quản lý tài khoản CRUD` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema nếu thêm/sửa cột | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-006: Quản lý hồ sơ người dùng

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 2 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Trang profile, upload ảnh |
| ☐ | Upload ảnh đại diện lưu vào `storage/` đúng cách | Bắt buộc | `Storage::putFile()` |
| ☐ | Validation: SĐT, ngày sinh, kích thước ảnh | Bắt buộc | |
| ☐ | Không còn debug code, tuân thủ coding standard | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Thay đổi SĐT/địa chỉ → cập nhật + hiển thị ngay | Bắt buộc | Bảng `hosonguoidung` |
| ☐ | AC2: Upload ảnh đại diện → ảnh thay đổi trên navbar | Bắt buộc | |
| ☐ | Upload file không phải ảnh → báo lỗi | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-006 Quản lý hồ sơ" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: quản lý hồ sơ người dùng` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Đảm bảo `php artisan storage:link` trong setup guide | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-007: Quản lý khóa học

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 2 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | CRUD Controller khóa học |
| ☐ | Slug tự động generate từ tên khóa học | Bắt buộc | `Str::slug()` |
| ☐ | Upload ảnh khóa học vào storage | Bắt buộc | |
| ☐ | Soft delete khi xóa khóa học | Bắt buộc | SoftDeletes |
| ☐ | Không còn debug code, tuân thủ coding standard | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Tạo khóa học (tên, slug, mô tả, ảnh) → lưu DB | Bắt buộc | Bảng `khoahoc` |
| ☐ | AC2: Đổi trạng thái "Ẩn" → không hiển thị ở Client | Bắt buộc | `trangThai=0` |
| ☐ | Liên kết đúng danh mục (`danhMucId`) | Bắt buộc | |
| ☐ | Xóa khóa học → soft delete | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-007 Quản lý khóa học" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: quản lý khóa học CRUD` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng khoahoc, danhmuckhoahoc) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-008: Hiển thị danh sách khóa học (Client)

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 2 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Client Controller + Blade view |
| ☐ | Chỉ hiển thị khóa học `trangThai=1` | Bắt buộc | Scope `active` |
| ☐ | Giao diện dạng Card (Ảnh, Tên, Giá, Loại) | Bắt buộc | Responsive Bootstrap |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Truy cập trang → hiển thị danh sách Card | Bắt buộc | |
| ☐ | AC2: Lọc theo "Loại khóa học" → chỉ hiển thị đúng nhóm | Bắt buộc | Filter theo `danhMucId` |
| ☐ | Khóa học ẩn (`trangThai=0`) không hiển thị | Bắt buộc | |
| ☐ | Giao diện responsive trên mobile | Khuyến khích | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-008 Danh sách khóa học client" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: hiển thị danh sách khóa học client` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Không yêu cầu cập nhật tài liệu | — |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## SPRINT 3 — CLASS MANAGEMENT & ENROLLMENT

---

### US-009: Tạo lớp học cho khóa học

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 3 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | LopHocController CRUD |
| ☐ | Liên kết đúng khoaHocId, hocPhiId, coSoId, caHocId | Bắt buộc | FK đúng |
| ☐ | Validation: tên lớp, ngày bắt đầu, sĩ số tối đa | Bắt buộc | |
| ☐ | Slug tự động generate | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Tạo lớp liên kết đúng khóa học | Bắt buộc | Bảng `lophoc` |
| ☐ | AC2: Học phí hiển thị chính xác khi HV xem chi tiết | Bắt buộc | Join `hocphi` |
| ☐ | Trạng thái lớp đúng (0=Sắp mở, 1=Đang mở ĐK...) | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-009 Tạo lớp học" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: tạo lớp học cho khóa học` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng lophoc, hocphi) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-010: Phân công giáo viên

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 3 |
| **Assignee** | __________________ |
| **Story Points** | 2 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Gán `taiKhoanId` (GV) vào `lophoc` |
| ☐ | Dropdown chỉ hiển thị tài khoản role=1 (Giáo viên) | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Gán GV → GV thấy lớp trong lịch dạy | Bắt buộc | |
| ☐ | AC2: Thay đổi GV → lịch GV cũ & mới cập nhật đúng | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-010 Phân công giáo viên" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: phân công giáo viên vào lớp` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Không yêu cầu cập nhật tài liệu | — |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-011: Đăng ký lớp học

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 3 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Tạo bản ghi `dangKyLopHoc` |
| ☐ | Kiểm tra sĩ số trước khi cho đăng ký | Bắt buộc | Đếm dangKyLopHoc vs soHocVienToiDa |
| ☐ | Kiểm tra trùng lặp (HV đã đăng ký lớp này) | Bắt buộc | |
| ☐ | Trạng thái đăng ký mặc định = 0 (Chờ duyệt) | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Lớp còn chỗ → đăng ký thành công + thông báo | Bắt buộc | |
| ☐ | AC2: Lớp đủ sĩ số → nút đăng ký bị vô hiệu hóa | Bắt buộc | |
| ☐ | HV đã đăng ký → hiển thị "ĐÃ ĐĂNG KÝ" | Bắt buộc | |
| ☐ | Lớp chưa mở ĐK (trangThai≠1) → không cho đăng ký | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-011 Đăng ký lớp học" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: đăng ký lớp học cho học viên` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng dangKyLopHoc) | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-012: Điểm danh học viên

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 3 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | DiemDanhController |
| ☐ | Sử dụng đúng trạng thái: 0=Vắng, 1=Có mặt, 2=Trễ, 3=Có phép, 4=Bị khóa | Bắt buộc | Constants trong Model |
| ☐ | Unique constraint (buoiHocId + taiKhoanId) | Bắt buộc | Không điểm danh trùng |
| ☐ | FK đúng: buoiHocId, taiKhoanId, dangKyLopHocId | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: GV tích Có mặt/Vắng → lưu trạng thái đúng | Bắt buộc | Bảng `diemDanh` |
| ☐ | AC2: Nhấn "Hoàn thành" → cập nhật `daDiemDanh` trong buổi học | Bắt buộc | |
| ☐ | HV bị nợ HP → tự động đánh dấu trạng thái=4 | Bắt buộc | |
| ☐ | Giao diện danh sách điểm danh hiển thị đúng | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-012 Điểm danh học viên" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: điểm danh học viên theo buổi` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng diemDanh) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## SPRINT 4 — LEARNING CONTENT & GRADING

---

### US-013: Quản lý tài liệu học tập

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 4 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Upload/download file |
| ☐ | Hỗ trợ định dạng PDF, Video | Bắt buộc | Validate MIME type |
| ☐ | File lưu vào `storage/` an toàn | Bắt buộc | Không lưu trong `public/` trực tiếp |
| ☐ | Giới hạn kích thước file upload | Bắt buộc | Cấu hình `php.ini` + validation |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: GV đính kèm file → HV trong lớp truy cập được | Bắt buộc | |
| ☐ | AC2: GV xóa tài liệu → HV không thấy nữa | Bắt buộc | |
| ☐ | HV không thuộc lớp → không truy cập được tài liệu | Khuyến khích | |
| ☐ | Upload file quá lớn → báo lỗi | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-013 Quản lý tài liệu" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: quản lý tài liệu học tập` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema nếu tạo bảng tài liệu mới | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-014: Nhập điểm bài thi

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 4 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Should |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Nhập/sửa điểm cho HV |
| ☐ | Validation điểm: 0 ≤ điểm ≤ 10 | Bắt buộc | `numeric|min:0|max:10` |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Nhập điểm (0-10) → lưu + hiển thị trong bảng điểm HV | Bắt buộc | |
| ☐ | AC2: Nhập sai định dạng → báo lỗi nhập lại | Bắt buộc | VD: chữ, số âm, >10 |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-014 Nhập điểm bài thi" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: nhập điểm bài thi` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng diembaithi) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## SPRINT 5 — PAYMENT & BLOG

---

### US-015: Tạo hóa đơn học phí

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 5 |
| **Assignee** | __________________ |
| **Story Points** | 6 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | HoaDonController |
| ☐ | Mã hóa đơn tự động (HD-YYYYMM-XXXXXX) | Bắt buộc | `HoaDon::generateMaHoaDon()` |
| ☐ | Tính tổng tiền đúng (soBuoi × donGia) | Bắt buộc | Lấy từ `hocphi` |
| ☐ | Liên kết đúng `dangKyLopHocId`, `taiKhoanId`, `coSoId` | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: HV đăng ký lớp → hóa đơn tạo trạng thái "Chờ TT" | Bắt buộc | `trangThai=0` |
| ☐ | AC2: Admin in hóa đơn → đủ mã HD, tên HV, số tiền | Bắt buộc | Trang in/PDF |
| ☐ | Tính giảm giá, thuế, tổng sau thuế đúng | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-015 Tạo hóa đơn học phí" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: tạo hóa đơn học phí` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng hoadon) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-016: Theo dõi trạng thái thanh toán

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 5 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Must |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Phiếu thu + recalculate |
| ☐ | `HoaDon::recalculate()` tính đúng daTra, trangThai | Bắt buộc | Từ tổng phiếu thu hợp lệ |
| ☐ | Mã phiếu thu tự động (PT-YYYYMM-XXXXXX) | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Admin xác nhận nhận tiền → trạng thái "Đã thanh toán" | Bắt buộc | Tạo phiếu thu |
| ☐ | AC2: Đã TT đủ → HV được kích hoạt đăng ký (trangThai=1) | Bắt buộc | Phục hồi dangKyLopHoc |
| ☐ | Thanh toán một phần → trạng thái "Thanh toán một phần" | Bắt buộc | |
| ☐ | Hủy phiếu thu → tính lại trạng thái hóa đơn | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-016 Theo dõi thanh toán" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: theo dõi trạng thái thanh toán` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng phieuthu) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-017: Quản lý bài viết

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 5 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Should |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | CRUD bài viết + rich editor |
| ☐ | Slug tự động generate | Bắt buộc | |
| ☐ | Liên kết N-N đúng với danh mục và tag | Bắt buộc | Bảng pivot |
| ☐ | Soft delete khi xóa | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Tạo bài viết → hiển thị ngoài trang chủ | Bắt buộc | `trangThai=1` |
| ☐ | AC2: Xóa bài viết → biến mất khỏi danh sách | Bắt buộc | Soft delete |
| ☐ | Gán danh mục + tag hoạt động đúng | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-017 Quản lý bài viết" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: quản lý bài viết CRUD` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng baiviet, pivot) | Khuyến khích |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-018: Hiển thị tin tức (Client)

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 5 |
| **Assignee** | __________________ |
| **Story Points** | 2 |
| **Priority** | Should |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | Client blog controller + views |
| ☐ | Hiển thị danh sách bài viết `trangThai=1` | Bắt buộc | Scope `active` |
| ☐ | Sắp xếp bài mới nhất lên đầu | Bắt buộc | `orderBy('created_at', 'desc')` |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Click bài viết → hiển thị nội dung, ngày đăng, tác giả | Bắt buộc | |
| ☐ | AC2: Danh sách bài viết → bài mới nhất nằm trên cùng | Bắt buộc | |
| ☐ | Bài viết ẩn (`trangThai=0`) không hiển thị | Bắt buộc | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-018 Hiển thị tin tức client" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: hiển thị tin tức client` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Không yêu cầu cập nhật tài liệu | — |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## SPRINT 6 — REPORTS & NOTIFICATION

---

### US-019: Thống kê học viên

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 6 |
| **Assignee** | __________________ |
| **Story Points** | 5 |
| **Priority** | Nice to have |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | DashboardService / ReportController |
| ☐ | Query thống kê tối ưu (tránh N+1) | Bắt buộc | Dùng `groupBy`, `selectRaw` |
| ☐ | Biểu đồ render đúng (Chart.js hoặc tương tự) | Bắt buộc | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Chọn khoảng thời gian → biểu đồ/bảng số lượng HV mới | Bắt buộc | |
| ☐ | AC2: Chọn cơ sở cụ thể → chỉ thống kê HV thuộc cơ sở đó | Bắt buộc | Filter theo `coSoId` |
| ☐ | Không có dữ liệu → hiển thị thông báo "Không có dữ liệu" | Khuyến khích | |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-019 Thống kê học viên" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: thống kê học viên theo tháng` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Không yêu cầu cập nhật tài liệu | — |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

### US-020: Gửi thông báo hệ thống

| Mục | Chi tiết |
|-----|----------|
| **Sprint** | Sprint 6 |
| **Assignee** | __________________ |
| **Story Points** | 3 |
| **Priority** | Nice to have |
| **Review Date** | ___/___/2026 |

#### 1. CODE QUALITY

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | Code chạy không lỗi | Bắt buộc | ThongBaoController |
| ☐ | Tạo bản ghi `thongbao` + gửi vào `thongbaonguoidung` | Bắt buộc | Nhiều người nhận |
| ☐ | Hỗ trợ đối tượng gửi: Tất cả, theo lớp, theo role, cá nhân | Bắt buộc | `doiTuongGui` 0-4 |
| ☐ | Hỗ trợ ưu tiên và ghim | Khuyến khích | |
| ☐ | Không còn debug code | Bắt buộc | |

#### 2. TESTING

| ✓ | Tiêu chí | Yêu cầu | Ghi chú |
|---|----------|---------|---------|
| ☐ | AC1: Gửi "Tất cả" → HV đăng nhập thấy icon thông báo | Bắt buộc | |
| ☐ | AC2: HV click xem → trạng thái "Chưa xem" → "Đã xem" | Bắt buộc | `daDoc` = true |
| ☐ | Tệp đính kèm upload/download hoạt động | Khuyến khích | Bảng `thongbao_tepdinh` |

#### 3. CODE REVIEW & GIT

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | PR có mô tả: "US-020 Gửi thông báo hệ thống" | Bắt buộc |
| ☐ | Code reviewed bởi ít nhất 1 thành viên | Bắt buộc |
| ☐ | Commit message: `feat: gửi thông báo hệ thống` | Bắt buộc |
| ☐ | Đã merge vào `develop` | Bắt buộc |

#### 4. DOCUMENTATION

| ✓ | Tiêu chí | Yêu cầu |
|---|----------|---------|
| ☐ | Cập nhật DB Schema (bảng thongbao, thongbaonguoidung) | Bắt buộc |

**Quyết định:** ☐ DONE ☐ NOT DONE

---

## TỔNG HỢP

| Sprint | User Stories | Tổng SP | Trạng thái |
|--------|-------------|---------|------------|
| Sprint 1 | US-001, US-002, US-003, US-004 | 13 | ☐ Done |
| Sprint 2 | US-005, US-006, US-007, US-008 | 16 | ☐ Done |
| Sprint 3 | US-009, US-010, US-011, US-012 | 15 | ☐ Done |
| Sprint 4 | US-013, US-014 | 6 | ☐ Done |
| Sprint 5 | US-015, US-016, US-017, US-018 | 14 | ☐ Done |
| Sprint 6 | US-019, US-020 | 8 | ☐ Done |
| **Tổng** | **20 User Stories** | **72** | |

---

## CHỮ KÝ XÁC NHẬN

| Vai trò | Họ tên | Chữ ký | Ngày |
|---------|--------|--------|------|
| **Developer** | __________________ | __________________ | ___/___/2026 |
| **Reviewer** | __________________ | __________________ | ___/___/2026 |
| **Product Owner** | __________________ | __________________ | ___/___/2026 |
