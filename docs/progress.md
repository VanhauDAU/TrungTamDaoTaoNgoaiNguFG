# TIEN DO DU AN - Trung Tam Dao Tao Ngoai Ngu Five Genius

> Cap nhat lan cuoi: 2026-03-15 22:10

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
- [x] Loai bo `thu theo thang` khoi runtime billing moi
- [x] Snapshot hoc phi khi hoc vien dang ky lop
- [x] Chong race condition dang ky lop bang unique index + transaction lock
- [x] Them `ngayHetHanGiuCho` cho dang ky cho thanh toan
- [x] Job tu dong huy giu cho qua han
- [x] Quan ly Buoi hoc (tu dong sinh theo ca hoc)
- [x] Dong bo ngay ket thuc lop theo buoi hoc cuoi cung
- [x] Chuyen trang thai lop hoc nhanh bang AJAX tai trang index
- [x] Ma tran chuyen trang thai lop hoc + validation server
- [x] Ca hoc (CRUD, toggle active)
- [x] Diem danh hoc vien tung buoi hoc
- [x] Module admin quan ly dang ky hoc: tao tai quay, xac nhan, huy, bao luu, khoi phuc, chuyen lop

### Người dùng

- [x] Quản lý Học viên (CRUD, thùng rác, khôi phục)
- [x] Quản lý Giáo viên (create, edit, show, profile, cơ sở)
- [x] Quản lý Nhân viên (create, edit, show)
- [x] Hồ sơ nhân sự chi tiết sau khi tạo tài khoản
- [x] Phiếu bàn giao tài khoản với username thật + mật khẩu tạm một lần
- [x] Xuất PDF hồ sơ nhân sự và PDF bàn giao tài khoản
- [x] Mẫu quy định nhân sự + snapshot quy định theo hồ sơ
- [x] Quản lý CV / tài liệu nhân sự private + versioning
- [x] Phân quyền mở rộng cho nhóm `nhan_su`

### Lương & Hồ sơ nhân sự

- [x] Gói lương hiện hành cho giáo viên / nhân viên
- [x] Chi tiết gói lương: phụ cấp, khấu trừ tham chiếu, thưởng cố định
- [x] Lịch sử hiệu lực gói lương
- [x] Đồng bộ `luongCoBan` cũ sang mô hình gói lương bằng migration backfill
- [x] Tài liệu đặc tả vận hành lương và payroll
- [x] Handoff Figma cho luồng lương, payroll, phiếu lương

### Tai chinh

- [x] Hoa don (tu tao khi dang ky lop)
- [x] Phieu thu (ghi nhan thanh toan)
- [x] Thong ke trang thai thanh toan
- [x] Ho tro cau hinh dot thu o cap schema va giao dien lop hoc
- [x] Validation nghiep vu cho thu theo dot o form lop hoc
- [x] Scheduler chay `invoice:check-overdue` hang ngay
- [x] Ghi log ket qua xu ly hoa don qua han
- [x] Chuan hoa ownership cua `phieuthu`: hoc vien nop tien va nhan su ghi nhan tach rieng
- [x] Moi thay doi hoa don tu admin deu recalculate hoa don va dang ky lien quan

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

### Docs & Quality

- [x] Changelog được cập nhật theo mốc auth, lớp học, tài chính, hồ sơ nhân sự
- [x] Hướng dẫn vận hành tổng hợp cho auth, học phí lớp, đăng ký và hồ sơ nhân sự
- [x] Tài liệu database được cập nhật thêm mô hình nhân sự mở rộng
- [x] Feature test `NhanSuWorkflowTest` cho luồng hồ sơ nhân sự

---

## Dang phat trien

- [ ] Bang diem hoc vien
- [ ] Danh gia giao vien sau khoa hoc
- [ ] Bao cao doanh thu chi tiet (admin)
- [ ] Dashboard thong ke nang cao
- [ ] Quy trinh hoan tien / dieu chinh cong no / credit note
- [ ] Bang luong ky / payroll thuc te theo ky
- [ ] Phieu luong va xac nhan chi tra

---

## Ke hoach

- [ ] Gui email tu dong (dang ky lop, nhac hoc phi)
- [ ] Export bao cao Excel/PDF
- [ ] App mobile (React Native hoac Flutter)
- [ ] Tich hop cong thanh toan (MoMo, VNPay)
- [ ] He thong hoc truc tuyen (video, quiz)
