# THIẾT KẾ LUỒNG XÁC THỰC NGƯỜI DÙNG

| Thông tin | Chi tiết |
|---|---|
| **Tên dự án** | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ |
| **Nhóm** | FiveGenius |

> **Ghi chú:** Dự án sử dụng **Laravel Session-based Authentication** (server-rendered Blade) thay vì SPA + JWT. Các mục bên dưới được thiết kế bám sát kiến trúc thực tế của dự án.

---

## 1. THÔNG TIN API XÁC THỰC

| Endpoint | Method | Mô tả | Request Body | Response |
|----------|--------|-------|--------------|----------|
| `/register` | POST | Đăng ký tài khoản học viên mới | `name` (string, no digits), `email` (string, unique), `phone` (string, 10 digits), `password` (string, min:8), `password_confirmation` (string), `recaptcha_token` (string), `_token` (CSRF) | **Thành công**: Redirect `/email/verify` + session flash success. **Lỗi**: Redirect back + `$errors` (422) |
| `/login` | POST | Đăng nhập học viên | `taiKhoan` (string — email hoặc mã tài khoản), `password` (string, min:8), `remember` (boolean), `recaptcha_token` (string), `_token` (CSRF) | **Thành công**: Redirect `/hoc-vien` + set session cookie. **Lỗi**: Redirect back + `$errors` (message + số lần thử còn lại). **Lockout**: Redirect back + countdown timer |
| `/teacher/login` | POST | Đăng nhập giảng viên | `taiKhoan`, `password`, `remember`, `_token` | **Thành công**: Redirect `/admin/dashboard`. **Lỗi**: tương tự `/login` |
| `/staff/login` | POST | Đăng nhập nhân viên/admin | `taiKhoan`, `password`, `remember`, `_token` | **Thành công**: Redirect `/admin/dashboard`. **Lỗi**: tương tự `/login` |
| `/logout` | POST | Đăng xuất | `_token` (CSRF) | Revoke device session → invalidate session → regenerate CSRF → redirect login page theo role |
| `/hoc-vien` | GET | Lấy hồ sơ học viên | — | Render view `profile/index` (middleware: `auth`, `verified.student`) |
| `/hoc-vien/doi-mat-khau` | POST | Đổi mật khẩu học viên | `current_password`, `new_password`, `new_password_confirmation`, `_token` | Redirect back + flash success/errors |
| `/doi-mat-khau-bat-buoc` | POST | Đổi mật khẩu bắt buộc (lần đầu) | `new_password`, `new_password_confirmation`, `_token` | Redirect dashboard theo role |
| `/password/email` | POST | Gửi link reset mật khẩu | `email`, `_token` | Redirect back + flash status |
| `/password/reset` | POST | Đặt lại mật khẩu | `email`, `password`, `password_confirmation`, `token`, `_token` | Redirect login + flash success |

---

## 2. CẤU TRÚC TOKEN / SESSION

Dự án sử dụng **Laravel Session Authentication** (không phải JWT):

| Mục | Chi tiết |
|-----|---------|
| **Loại xác thực** | Session-based (cookie `laravel_session`) |
| **Session driver** | `database` (bảng `sessions` trong MySQL) |
| **Session chứa** | ☑ `taiKhoanId` (user ID) · ☑ `auth_portal` (student/teacher/staff) · ☑ `auth_login_method` (password/google) · ☑ `auth_remembered` (boolean) |
| **Thời hạn session** | ☑ **120 phút** (2 giờ) — `SESSION_LIFETIME=120` trong `.env` |
| **Lưu trữ phía client** | ☑ **Cookie** — `laravel_session` (encrypted, HttpOnly, SameSite=Lax) |
| **CSRF Protection** | `_token` hidden field trong form + `X-CSRF-TOKEN` header cho AJAX |
| **Remember Token** | 60 ký tự random, được rotate khi: đổi mật khẩu, reset mật khẩu, admin reset, khóa tài khoản, đăng xuất thiết bị |
| **Mã hóa** | AES-256-CBC (`APP_KEY` base64, 32 bytes) |
| **Bcrypt Rounds** | **12** rounds (`BCRYPT_ROUNDS=12`) |

---

## 3. PHÂN QUYỀN NGƯỜI DÙNG

| Vai trò | Role ID | Quyền hạn | Trang được truy cập |
|---------|---------|-----------|---------------------|
| **Admin** | `3` | Toàn quyền hệ thống, bypass hoàn toàn kiểm tra phân quyền. Quản lý tài khoản, nhóm quyền, tất cả module | `/admin/*` (dashboard, học viên, giáo viên, nhân viên, khóa học, lớp học, cơ sở, tài chính, bài viết, thông báo, phân quyền, tài khoản) |
| **Nhân viên** | `2` | Quyền theo nhóm quyền được gán (RBAC): xem/thêm/sửa/xóa theo từng tính năng (khóa học, lớp học, học viên, tài chính…). Kiểm tra qua `CheckPermission` middleware | `/admin/*` (các trang được phép theo nhóm quyền), không truy cập phân quyền |
| **Giáo viên** | `1` | Quyền theo nhóm quyền được gán. Truy cập khu vực admin hạn chế | `/admin/*` (các trang được phép theo nhóm quyền) |
| **Học viên** | `0` | Xem/sửa hồ sơ cá nhân, đăng ký lớp, xem lịch học, thanh toán học phí, chat, thông báo. Yêu cầu xác thực email | `/hoc-vien/*`, `/khoa-hoc/*`, `/lop-hoc/*`, `/thong-bao/*`, `/api/chat/*` |
| **Khách** | — | Chỉ xem trang công khai, đăng ký, đăng nhập, quên mật khẩu, gửi form tư vấn | `/`, `/khoa-hoc`, `/blog`, `/lien-he`, `/ve-chung-toi`, `/login`, `/register`, `/password/*` |

---

## 4. SƠ ĐỒ LUỒNG XÁC THỰC (Mermaid Code)

Sơ đồ thể hiện toàn bộ luồng: **Đăng nhập → Lưu Session → Gọi API → Xác minh → Response**

```
flowchart TD
    %% ========== GIAI ĐOẠN 1: ĐĂNG NHẬP ==========
    subgraph DANG_NHAP["1 - ĐĂNG NHẬP"]
        A["Người dùng truy cập trang Đăng nhập"] --> B{"Chọn portal"}
        B -->|"Học viên"| C1["POST /login"]
        B -->|"Giảng viên"| C2["POST /teacher/login"]
        B -->|"Nhân viên/Admin"| C3["POST /staff/login"]
        C1 --> D["Gửi: taiKhoan + password + CSRF token"]
        C2 --> D
        C3 --> D
        D --> E{"Validate input + reCAPTCHA"}
        E -->|"Fail"| ERR1["Trả lỗi validation"]
        ERR1 --> A
        E -->|"Pass"| F{"Kiểm tra lockout - 5 lần sai bị khóa"}
        F -->|"Đang bị khóa"| ERR2["Hiển thị countdown timer chờ hết lockout"]
        ERR2 --> A
        F -->|"Không bị khóa"| G{"Auth::attempt - So khớp bcrypt hash"}
        G -->|"Sai mật khẩu"| ERR3["Ghi nhật ký thất bại + Trả lỗi kèm số lần thử còn lại"]
        ERR3 --> A
        G -->|"Đúng mật khẩu"| H{"Tài khoản bị khóa?"}
        H -->|"Bị khóa"| ERR4["Thông báo: Liên hệ trung tâm"]
        H -->|"Hoạt động"| I{"Portal khớp role?"}
        I -->|"Không khớp"| ERR5["Logout + Trả lỗi sai portal"]
        ERR5 --> A
        I -->|"Khớp"| J["XÁC THỰC THÀNH CÔNG"]
    end

    %% ========== GIAI ĐOẠN 2: LƯU SESSION ==========
    subgraph LUU_SESSION["2 - LƯU SESSION / TOKEN"]
        J --> K["Server tạo session trong DB - bảng sessions"]
        K --> L["Lưu vào session: taiKhoanId, auth_portal, auth_login_method"]
        L --> M["Ghi nhật ký đăng nhập - NhatKyDangNhap"]
        M --> N["Set cookie laravel_session - encrypted, HttpOnly"]
        N --> O["Cập nhật lastLogin + Track device session"]
    end

    %% ========== REDIRECT SAU ĐĂNG NHẬP ==========
    O --> P{"phaiDoiMatKhau == 1?"}
    P -->|"Có"| P1["Redirect /doi-mat-khau-bat-buoc"]
    P -->|"Không"| Q{"Học viên chưa verify email?"}
    Q -->|"Chưa"| Q1["Redirect /email/verify"]
    Q -->|"Rồi hoặc Staff"| R["Redirect trang chính theo role"]

    %% ========== GIAI ĐOẠN 3: GỌI API ==========
    subgraph GOI_API["3 - GỌI API - REQUEST"]
        R --> S["Browser gửi HTTP Request"]
        S --> T["Cookie laravel_session tự động đính kèm"]
        T --> U["CSRF token gửi qua _token hoặc X-CSRF-TOKEN header"]
    end

    %% ========== GIAI ĐOẠN 4: XÁC MINH ==========
    subgraph XAC_MINH["4 - XÁC MINH - MIDDLEWARE PIPELINE"]
        U --> V{"MW 1: VerifyCsrfToken"}
        V -->|"CSRF không hợp lệ"| VE["419 Token Mismatch"]
        V -->|"OK"| W{"MW 2: auth - Kiểm tra session"}
        W -->|"Chưa đăng nhập"| WE["302 Redirect Login"]
        W -->|"Đã đăng nhập"| X{"MW 3: EnsureActiveAccount"}
        X -->|"Tài khoản bị khóa"| XE["Logout + Redirect Login"]
        X -->|"OK"| Y{"MW 4: ForceChangePassword"}
        Y -->|"Phải đổi MK"| YE["Redirect /doi-mat-khau-bat-buoc"]
        Y -->|"OK"| Z{"MW 5: TrackDeviceSession"}
        Z --> AA{"MW 6: isAdmin - nếu route admin"}
        AA -->|"Không phải staff"| AAE["403 Forbidden"]
        AA -->|"Là staff"| BB{"MW 7: CheckPermission - nếu có"}
        BB -->|"Không có quyền"| BBE["403 Forbidden"]
        BB -->|"Có quyền"| CC["PASS - Chuyển đến Controller"]
        Z --> DD{"MW 6: verified.student - nếu route học viên"}
        DD -->|"Email chưa verify"| DDE["Redirect /email/verify"]
        DD -->|"Đã verify"| CC
    end

    %% ========== GIAI ĐOẠN 5: RESPONSE ==========
    subgraph RESPONSE["5 - RESPONSE"]
        CC --> EE["Controller xử lý business logic"]
        EE --> FF{"Loại response?"}
        FF -->|"Trang HTML"| GG["Render Blade view + data"]
        FF -->|"AJAX/JSON"| HH["Trả JSON response"]
        FF -->|"Redirect"| II["302 Redirect + flash message"]
        GG --> JJ["Browser nhận response + hiển thị"]
        HH --> JJ
        II --> JJ
    end

    JJ -->|"Gọi tiếp request khác"| S
```
