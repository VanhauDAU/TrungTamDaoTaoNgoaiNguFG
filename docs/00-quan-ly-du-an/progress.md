# TIEN DO DU AN - Trung Tam Dao Tao Ngoai Ngu Five Genius

> Cập nhật lần cuối: 2026-04-12 10:10

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
- [x] **Upload avatar học viên** — validate MIME thực (không chỉ extension), tên file UUID ngẫu nhiên, lưu ngoài webroot, giới hạn 2MB, yêu cầu xác thực `auth + verified.student`
- [x] **Giao diện upload avatar** — preview thay vào avatar hiện tại, nút Xác nhận/Hủy dưới avatar, thanh tiến trình cột trái, feedback có icon, AJAX không reload trang
- [x] **Upload ảnh dùng chung** — service backend `ImageUploadService`, preset cấu hình `config/uploads.php`, API `POST /api/uploads/images`
- [x] **Component upload ảnh tái sử dụng** — `x-upload.image` hỗ trợ preview, drag-drop, progress, feedback, đồng bộ ảnh theo 2 mode `instant/deferred`

### Cơ sở vật chất

- [x] Quản lý Cơ sở đào tạo (CRUD, bản đồ, API địa chỉ)
- [x] Quản lý Phòng học theo cơ sở (AJAX)
- [x] Tích hợp API Tỉnh/Phường/Xã

### Khoa hoc

- [x] Danh muc khoa hoc dang cay de quy vo han cap
- [x] Quan ly Khoa hoc (CRUD, anh, soft delete)
- [x] Ap dung component upload anh dung chung cho form tao/sua khoa hoc
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
- [x] Xuat PDF hoa don va phiếu thu o ca admin va cong hoc vien
- [x] Ho tro `Luu va in phiếu thu` ngay sau khi ghi nhan thu tien
- [x] Gui email hoa don / phiếu thu kem file PDF

### CRM — Liên hệ

- [x] Form tư vấn client
- [x] Danh sách liên hệ (filter, bulk action, soft delete)
- [x] Lịch sử xử lý (giao việc, phản hồi)

### Nội dung

- [x] Bài viết / Blog (CRUD, slug, soft delete, tinymce)
- [x] Danh mục bài viết, Tags
- [x] Upload ảnh inline
- [x] Ap dung component upload anh dung chung cho form tao/sua bai viet

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
- [x] Feature test `FinanceDocumentWorkflowTest` cho in PDF / gui email hoa don, phiếu thu
- [x] README / README_vi / upload security checklist được đồng bộ theo kiến trúc upload ảnh dùng chung

---

## Dang phat trien

- [ ] Bảng điểm học viên
- [ ] Đánh giá giáo viên sau khóa học
- [ ] Báo cáo doanh thu chi tiết (admin)
- [ ] Dashboard thống kê nâng cao
- [ ] Quy trình hoàn tiền / điều chỉnh công nợ / credit note
- [ ] Bảng lương kỳ / payroll thực tế theo kỳ
- [ ] Phiếu lương và xác nhận chi trả

### 🔐 Upload bảo mật — cải tiến tiếp theo

- [ ] Cân nhắc disk private + signed URL cho file nhạy cảm hơn (production)
- [ ] Mở rộng preset và policy riêng cho upload file không phải ảnh (CV, tài liệu, đính kèm thông báo)
- [ ] Bổ sung thêm feature test cho mode `deferred` ở form admin nếu cần khóa chặt giao diện theo regression test

---

## Ke hoach

- [ ] Gui email tu dong (dang ky lop, nhac hoc phi)
- [ ] Export bao cao Excel/PDF doanh thu tong hop
- [ ] App mobile (React Native hoac Flutter)
- [ ] Tich hop cong thanh toan (MoMo, VNPay)
- [ ] He thong hoc truc tuyen (video, quiz)
