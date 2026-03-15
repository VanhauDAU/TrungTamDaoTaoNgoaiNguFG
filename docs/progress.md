# TIEN DO DU AN - Trung Tam Dao Tao Ngoai Ngu Five Genius

> Cap nhat lan cuoi: 2026-03-15

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

### Khoa hoc

- [x] Danh muc khoa hoc dang cay de quy vo han cap
- [x] Quan ly Khoa hoc (CRUD, anh, soft delete)
- [x] Quan ly Lop hoc (gan giao vien, phong, ca, co so)
- [x] Chinh sach gia theo lop hoc, khong con goi hoc phi theo khoa hoc
- [x] Snapshot hoc phi khi hoc vien dang ky lop
- [x] Quan ly Buoi hoc (tu dong sinh theo ca hoc)
- [x] Dong bo ngay ket thuc lop theo buoi hoc cuoi cung
- [x] Chuyen trang thai lop hoc nhanh bang AJAX tai trang index
- [x] Ma tran chuyen trang thai lop hoc + validation server
- [x] Ca hoc (CRUD, toggle active)
- [x] Diem danh hoc vien tung buoi hoc

### Người dùng

- [x] Quản lý Học viên (CRUD, thùng rác, khôi phục)
- [x] Quản lý Giáo viên (profile, cơ sở)
- [x] Quản lý Nhân viên

### Tai chinh

- [x] Hoa don (tu tao khi dang ky lop)
- [x] Phieu thu (ghi nhan thanh toan)
- [x] Thong ke trang thai thanh toan
- [x] Ho tro cau hinh dot thu o cap schema va giao dien lop hoc
- [x] Validation nghiep vu cho thu theo dot o form lop hoc

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

## Dang phat trien

- [ ] Bang diem hoc vien
- [ ] Danh gia giao vien sau khoa hoc
- [ ] Bao cao doanh thu chi tiet (admin)
- [ ] Dashboard thong ke nang cao
- [ ] Tach nhieu hoa don theo tung dot thu trong runtime billing

---

## Ke hoach

- [ ] Gui email tu dong (dang ky lop, nhac hoc phi)
- [ ] Export bao cao Excel/PDF
- [ ] App mobile (React Native hoac Flutter)
- [ ] Tich hop cong thanh toan (MoMo, VNPay)
- [ ] He thong hoc truc tuyen (video, quiz)
