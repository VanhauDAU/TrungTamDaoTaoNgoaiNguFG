# TIẾN ĐỘ DỰ ÁN — Trung Tâm Đào Tạo Ngoại Ngữ Five Genius

> Cập nhật lần cuối: 2026-03-12

## ✅ Đã hoàn thành

### Auth & User Management

- [x] Đăng nhập / Đăng xuất (Laravel Auth)
- [x] Tách cổng đăng nhập học viên `/login`, giảng viên `/teacher/login`, nhân viên-admin `/staff/login`
- [x] Phân quyền: Admin, Giáo viên, Nhân viên, Học viên
- [x] Quản lý tài khoản hệ thống (bật/tắt, đặt lại mật khẩu)
- [x] Hồ sơ người dùng (ảnh đại diện, thông tin cá nhân)
- [x] Xác thực email cho học viên tự đăng ký
- [x] Google login cho học viên
- [x] Google reCAPTCHA cho form auth public
- [x] Chuẩn hóa username hệ thống theo role (`HV/GV/NV/AD`)
- [x] Ghi nhớ đăng nhập + rotate remember token khi đổi/reset mật khẩu
- [x] Học viên tự xem và thu hồi thiết bị đã đăng nhập
- [x] Audit log nền cho phiên đăng nhập và token rotation

### Cơ sở vật chất

- [x] Quản lý Cơ sở đào tạo (CRUD, bản đồ, API địa chỉ)
- [x] Quản lý Phòng học theo cơ sở (AJAX)
- [x] Tích hợp API Tỉnh/Phường/Xã

### Khóa học

- [x] Danh mục khóa học dạng cây đệ quy vô hạn cấp
- [x] Quản lý Khóa học (CRUD, ảnh, soft delete)
- [x] Gói học phí (nhiều gói cho 1 khóa, AJAX)
- [x] Quản lý Lớp học (gắn giáo viên, phòng, ca, cơ sở)
- [x] Quản lý Buổi học (tự động sinh theo ca học)
- [x] Ca học (CRUD, toggle active)
- [x] Điểm danh học viên từng buổi học

### Người dùng

- [x] Quản lý Học viên (CRUD, thùng rác, khôi phục)
- [x] Quản lý Giáo viên (profile, cơ sở)
- [x] Quản lý Nhân viên

### Tài chính

- [x] Hóa đơn (tự tạo khi đăng ký lớp)
- [x] Phiếu thu (ghi nhận thanh toán)
- [x] Thống kê trạng thái thanh toán

### CRM — Liên hệ

- [x] Form tư vấn client
- [x] Danh sách liên hệ (filter, bulk action, soft delete)
- [x] Lịch sử xử lý (giao việc, phản hồi)

### Nội dung

- [x] Bài viết / Blog (CRUD, slug, soft delete, tinymce)
- [x] Danh mục bài viết, Tags
- [x] Upload ảnh inline

### Thông báo

- [x] Gửi thông báo đến nhóm người dùng
- [x] File đính kèm thông báo (nhiều file)
- [x] Real-time unread counter (polling)
- [x] Dropdown thông báo (admin + client)

### Client Website

- [x] Trang chủ, Giới thiệu, Blog, Liên hệ
- [x] Danh sách khóa học (sidebar cây danh mục, search, sort)
- [x] Chi tiết khóa học + lớp học + đăng ký học
- [x] Trang học viên (hồ sơ, lịch học, hóa đơn)
- [x] Thông báo client (danh sách, đánh dấu đã đọc)
- [x] Module chat client cho học viên và giáo viên
- [x] Short-poll realtime, direct chat, reaction, reply, recall

---

## 🔄 Đang phát triển

- [ ] Bảng điểm học viên
- [ ] Đánh giá giáo viên sau khoá học
- [ ] Báo cáo doanh thu chi tiết (admin)
- [ ] Dashboard thống kê nâng cao

---

## 📋 Kế hoạch

- [ ] Gửi email tự động (đăng ký lớp, nhắc học phí)
- [ ] Export báo cáo Excel/PDF
- [ ] App mobile (React Native hoặc Flutter)
- [ ] Tích hợp cổng thanh toán (MoMo, VNPay)
- [ ] Hệ thống học trực tuyến (video, quiz)
