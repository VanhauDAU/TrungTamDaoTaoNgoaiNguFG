# KẾ HOẠCH CHẠY NƯỚC RÚT 2
*(Sprint 2 Planning)*

| Tên dự án | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ | **Nhóm** | FiveGenius | **Thời gian** | 19/03 đến 02/04 |
| :--- | :--- | :--- | :--- | :--- | :--- |

---

## 1. KẾT QUẢ SPRINT 1

| Story Points hoàn thành | SP đã đăng ký | Tỷ lệ hoàn thành |
| :---: | :---: | :---: |
| 34 SP | 34 SP | 100% |

> **Tóm tắt Sprint 1:** Hoàn thành toàn bộ module **Xác thực & Phân quyền** (Đăng nhập/Đăng xuất, tách cổng đăng nhập theo role, Google Login, reCAPTCHA, Joi validation, quản lý phiên đăng nhập, audit log), **Đăng ký học viên** tự đăng ký + xác thực email, **Quên mật khẩu** và **Giao diện trang chủ Client** cơ bản.

---

## 2. MỤC TIÊU SPRINT 2 (Sprint Goal)

Hoàn thiện hệ thống **Quản lý người dùng** (CRUD tài khoản, hồ sơ người dùng, khóa/kích hoạt tài khoản) và xây dựng module **Khóa học** hoàn chỉnh (quản lý khóa học, phân loại, hiển thị danh sách & chi tiết khóa học phía Client với SEO slug).

---

## 3. DANH SÁCH CÔNG VIỆC SPRINT 2

| STT | User Story / Công việc | SP | Ưu tiên | Người làm | Ghi chú |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **📋** | **Công việc tồn đọng từ Sprint 1** | | | | |
| 1 | *(Không có — Sprint 1 hoàn thành 100%)* | 0 | — | — | — |
| 2 | — | — | — | — | — |
| **🆕** | **Công việc mới Sprint 2** | | | | |
| | **I. Quản lý người dùng** | | | | |
| 3 | Quản lý tài khoản (CRUD) — Tạo, xem, sửa, xóa tài khoản cho Admin, Giáo viên, Nhân viên, Học viên | 5 | Must have | | Sử dụng Service Layer + Soft Delete |
| 4 | Quản lý hồ sơ người dùng — Xem & cập nhật thông tin cá nhân, ảnh đại diện, đổi mật khẩu | 5 | Must have | | Áp dụng Joi validation frontend + Laravel validation backend |
| 5 | Khóa / kích hoạt tài khoản — Toggle trạng thái tài khoản, rotate remember_token khi khóa | 3 | Should have | | AJAX toggle, ghi audit log |
| | **II. Khóa học** | | | | |
| 6 | Quản lý khóa học — CRUD khóa học (tên, mô tả, ảnh, trạng thái, soft delete) | 5 | Must have | | Admin panel, upload ảnh, TinyMCE mô tả |
| 7 | Phân loại khóa học — Quản lý danh mục khóa học dạng cây đệ quy vô hạn cấp | 5 | Must have | | Nested category, drag & drop sắp xếp |
| 8 | Hiển thị danh sách khóa học (Client) — Trang `/khoa-hoc` với sidebar cây danh mục, search, sort, phân trang | 5 | Must have | | Responsive, cache Redis, SEO meta tags |
| 9 | Chi tiết khóa học (SEO slug) — Trang `/khoa-hoc/{slug}` hiển thị thông tin chi tiết, danh sách lớp đang mở, nút đăng ký | 5 | Must have | | SEO friendly URL, Schema markup |

---

| **Tổng SP Sprint 2** | **33 SP** | **So với Sprint 1** | ☐ Bằng &nbsp;&nbsp; ☐ Tăng &nbsp;&nbsp; ☑ Giảm |
| :--- | :---: | :--- | :--- |
