# 03 — Tài liệu Database

## 1. Sơ đồ quan hệ (ERD tóm tắt)

```
tinhthanh ──< cosodaotao ──< phonghoc
                   │
                   └──< lophoc >── giaoVienHoSo (NhanSu)
                          │   └── cahoc
                          │   └── phonghoc
                          │
                         BuoiHoc >── DiemDanh >── TaiKhoan(HocVien)
                          │
                   DangKyLopHoc >── HoaDon >── PhieuThu
                      (HocVien)

TaiKhoan ──< HoSoNguoiDung
         ──< NhanSu
         ──< NhomQuyen >── PhanQuyen

DanhMucKhoaHoc (self-join parent_id, đệ quy)
         └──< KhoaHoc ──< LopHoc
                    └──< NoiDungBaiHoc
                    └──< TaiLieu
LopHoc ──||── LopHocChinhSachGia ──< LopHocDotThu
   │
   └──< DangKyLopHoc >── HoaDon >── PhieuThu
            │
            └── snapshot học phí tại thời điểm đăng ký

ThongBao ──< ThongBaoNguoiDung (polymorphic nhận)
         └──< ThongBaoTepDinh

LienHe ──< LienHeLichSu
       ──< LienHePhanHoi

BaiViet >──< Tag (through BaiVietTag)
        > DanhMucBaiViet

ChatRoom >──< ChatRoomMember >── TaiKhoan
         └──< ChatMessage >── ChatMessageReaction
                        └──< ChatMessageAttachment
                        └──< ChatMessageDelete
         └──< ChatAuditLog
```

---

## 2. Mô tả bảng

### Auth & User

| Bảng            | Mô tả                           | Cột chính                                                |
| --------------- | ------------------------------- | -------------------------------------------------------- |
| `users`         | Tài khoản đăng nhập             | id, name, email, password, role (1=hv,2=gv,3=admin,4=nv) |
| `hosonguoidung` | Thông tin chi tiết cá nhân      | taiKhoanId, hoTen, soDienThoai, ngaySinh, anhDaiDien     |
| `nhansu`        | Nhân sự (giáo viên / nhân viên) | taiKhoanId, coSoId, chuyenMon, moTa                      |
| `nhomquyen`     | Nhóm quyền (role group)         | nhomQuyenId, tenNhom                                     |
| `phanquyen`     | Ánh xạ tài khoản – nhóm quyền   | taiKhoanId, nhomQuyenId                                  |

### Cơ sở vật chất

| Bảng         | Mô tả          | Cột chính                                        |
| ------------ | -------------- | ------------------------------------------------ |
| `tinhthanh`  | Tỉnh/Thành phố | tinhThanhId, tenTinhThanh, maApi                 |
| `cosodaotao` | Cơ sở đào tạo  | coSoId, tenCoSo, diaChi, tinhThanhId, lat, lng   |
| `phonghoc`   | Phòng học      | phongHocId, coSoId, tenPhong, sucChua, trangThai |

### Khóa học

| Bảng             | Mô tả               | Cột chính                                                            |
| ---------------- | ------------------- | -------------------------------------------------------------------- |
| `danhmuckhoahoc` | Danh mục cây đệ quy | danhMucId, tenDanhMuc, slug, parent_id, trangThai                    |
| `khoahoc`        | Khóa học            | khoaHocId, tenKhoaHoc, danhMucId, moTa, slug, anhKhoaHoc, deleted_at |
| `noidungbaihoc`  | Nội dung bài học    | noiDungId, khoaHocId, tieuDe, noiDung, thuTu                         |
| `tailieu`        | Tài liệu khóa học   | taiLieuId, khoaHocId, tenFile, duongDan                              |

### Giảng dạy

| Bảng           | Mô tả                        | Cột chính                                                                                                  |
| -------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------- |
| `cahoc`        | Ca học (giờ, thứ)            | caHocId, tenCa, gioKetThuc, gioBatDau, thu, trangThai                                                      |
| `lophoc` | Lớp học cụ thể | lopHocId, khoaHocId, coSoId, phongHocId, taiKhoanId, caHocId, slug, ngayBatDau, ngayKetThuc, soBuoiDuKien, soHocVienToiDa, donGiaDay, lichHoc, trangThai |
| `lophoc_chinhsachgia` | Chính sách giá của lớp | lopHocChinhSachGiaId, lopHocId, loaiThu, hocPhiNiemYet, soBuoiCamKet, hieuLucTu, hieuLucDen, trangThai |
| `lophoc_dotthu` | Kế hoạch thu theo đợt của lớp | lopHocDotThuId, lopHocChinhSachGiaId, tenDotThu, thuTu, soTien, hanThanhToan, batBuoc, trangThai |
| `buoihoc` | Buổi học | buoiHocId, lopHocId, ngayHoc, ghiChu, trangThai |
| `diemdanh` | Điểm danh học viên theo buổi | diemDanhId, buoiHocId, taiKhoanId, trangThai, ghiChu |
| `dangkylophoc` | Học viên đăng ký lớp + snapshot giá | dangKyLopHocId, lopHocId, taiKhoanId, lopHocChinhSachGiaId, hocPhiNiemYetSnapshot, hocPhiPhaiThuSnapshot, ngayDangKy, trangThai |

### Tài chính

| Bảng           | Mô tả                      | Cột chính                                                  |
| -------------- | -------------------------- | ---------------------------------------------------------- |
| `hoadon` | Hóa đơn | hoaDonId, dangKyLopHocId, lopHocDotThuId, tongTien, daTra, trangThai, ghiChu |
| `phieuthu`     | Phiếu thu (lần thu tiền)   | phieuThuId, hoaDonId, soTien, ngayThu, hinhThucTT, ghiChu  |
| `luong`        | Bảng lương nhân sự         | luongId, nhanSuId, thangNam, luongCoBan, tongLuong         |
| `luongchitiet` | Chi tiết phụ cấp, khấu trừ | luongChiTietId, luongId, loai, soTien, moTa                |

### Nội dung

| Bảng             | Mô tả                | Cột chính                                                                      |
| ---------------- | -------------------- | ------------------------------------------------------------------------------ |
| `baiviet`        | Bài viết/Blog        | baiVietId, tieuDe, slug, noiDung, anhDaiDien, danhMucId, trangThai, deleted_at |
| `danhmucbaiviet` | Danh mục bài viết    | danhMucId, tenDanhMuc, slug                                                    |
| `tag`            | Tags                 | tagId, tenTag, slug                                                            |
| `baiviet_tag`    | Pivot bài viết – tag | baiVietId, tagId                                                               |

### Tương tác

| Bảng                 | Mô tả                   | Cột chính                                                             |
| -------------------- | ----------------------- | --------------------------------------------------------------------- |
| `thongbao`           | Thông báo               | thongBaoId, tieuDe, noiDung, loai, nguoiGuiId, ghimLenDau, trangThai  |
| `thongbao_nguoidung` | Ai nhận thông báo       | id, thongBaoId, nguoiNhanId, daDoc                                    |
| `thongbao_tepdinh`   | File đính kèm thông báo | id, thongBaoId, tenFile, duongDan, kichCo, loaiFile                   |
| `lienhe`             | Liên hệ tư vấn          | lienHeId, hoTen, soDienThoai, email, khoaHocId, trangThai, deleted_at |
| `lienhe_lichsu`      | Lịch sử xử lý liên hệ   | id, lienHeId, nguoiThucHienId, hanhDong, ghiChu                       |
| `lienhe_phanhoi`     | Phản hồi liên hệ        | id, lienHeId, nguoiGuiId, noiDung                                     |
| `phanhoi`            | Đánh giá giáo viên      | phanHoiId, taiKhoanId, nhanSuId, noiDung, diemSo                      |

### Chat

| Bảng | Mô tả | Cột chính |
| --- | --- | --- |
| `chat_rooms` | Phòng chat lớp hoặc direct chat | chatRoomId, loai, tenPhong, lopHocId, taoBoiId, lastMessageId, trangThai |
| `chat_room_members` | Thành viên room chat | chatRoomMemberId, chatRoomId, taiKhoanId, vaiTro, joinedAt, lastReadMessageId, lastSeenAt, roiAt |
| `chat_messages` | Tin nhắn chat | chatMessageId, chatRoomId, nguoiGuiId, replyToMessageId, loai, noiDung, guiLuc, deadlineThuHoi, thuHoiLuc |
| `chat_message_attachments` | File/ảnh đính kèm của tin nhắn | chatAttachmentId, chatMessageId, disk, path, tenGoc, mime, size |
| `chat_message_reactions` | Reaction theo emoji | chatReactionId, chatMessageId, taiKhoanId, emoji |
| `chat_message_deletes` | Bản ghi xóa phía mình | chatMessageDeleteId, chatMessageId, taiKhoanId, deletedAt |
| `chat_audit_logs` | Audit thao tác chat | chatAuditLogId, chatRoomId, chatMessageId, taiKhoanId, hanhDong |

---

## 3. Migration Timeline

| Migration                              | Mô tả                                 |
| -------------------------------------- | ------------------------------------- |
| `0001_01_01_000000_create_users_table` | Bảng users, sessions, password_resets |
| `0001_01_01_000001_create_cache_table` | Cache table                           |
| `0001_01_01_000002_create_jobs_table`  | Queue jobs                            |
| `2026_02_04_...`                       | Thêm `lichHoc` vào `lophoc`           |
| `2026_02_21_...`                       | Thêm `maApi` vào `tinhthanh`          |
| `2026_02_21_...`                       | Thêm địa chỉ vào `cosodaotao`         |
| `2026_02_26_...`                       | Soft delete `khoahoc`                 |
| `2026_02_28_...`                       | Soft delete `baiviet`                 |
| `2026_02_28_...`                       | Thêm trường mới `thongbao`            |
| `2026_03_01_...`                       | Thêm field `hoadon`, `phieuthu`       |
| `2026_03_01_...`                       | Redesign bảng `diemdanh`              |
| `2026_03_04_...`                       | Soft delete `lienhe`                  |
| `2026_03_05_013400`                    | Thêm CRM fields vào `lienhe`          |
| `2026_03_05_013401`                    | Tạo bảng `lienhe_lichsu`              |
| `2026_03_05_013402`                    | Tạo bảng `lienhe_phanhoi`             |
| `2026_03_05_130245`                    | Tạo bảng `thongbao_tepdinh`           |
| `2026_03_05_134900`                    | Thêm `parent_id` vào `danhmuckhoahoc` |
| `2026_03_07_120000`                    | Tạo toàn bộ bảng chat client          |
| `2026_03_14_150000`                    | Refactor học phí sang mô hình theo lớp |

---

## 4. Luồng tài chính lớp học

### 4.1 Mô hình hiện tại

- `Khóa học` không còn sở hữu bảng giá.
- `Lớp học` có thể được tạo trước, sau đó mới gắn `lophoc_chinhsachgia`.
- `Buổi học` và `số buổi thực tế` chỉ là dữ liệu vận hành; không tự động đổi số tiền phải thu.
- Khi học viên đăng ký, hệ thống chụp `snapshot` học phí vào `dangkylophoc`.
- `HoaDon` mới phải đọc từ snapshot này, không đọc lại từ giá hiện tại của lớp.

### 4.2 Quy tắc vận hành

- Lớp ở trạng thái `Sắp mở` có thể chưa có học phí.
- Trước khi chuyển sang `Đang tuyển sinh`, `Chốt danh sách`, `Đang học`, hoặc `Đã kết thúc`, lớp phải có chính sách giá hợp lệ.
- Thay đổi học phí lớp chỉ áp dụng cho đăng ký mới.
- Nếu cần kế hoạch thu theo đợt, dùng `lophoc_dotthu` để lưu cấu hình từng đợt.

---

## 5. Index & Optimization gợi ý

```sql
-- Truy vấn thường xuyên theo slug
CREATE INDEX idx_khoahoc_slug ON khoahoc(slug);
CREATE INDEX idx_baiviet_slug ON baiviet(slug);
CREATE INDEX idx_danhmuc_slug ON danhmuckhoahoc(slug);

-- Lọc theo parent (cây danh mục)
CREATE INDEX idx_danhmuc_parent ON danhmuckhoahoc(parent_id);

-- Thông báo chưa đọc
CREATE INDEX idx_notify_unread ON thongbao_nguoidung(nguoiNhanId, daDoc);

-- Điểm danh theo buổi
CREATE INDEX idx_diemdanh_buoi ON diemdanh(buoiHocId);

-- Chính sách giá theo lớp
CREATE UNIQUE INDEX uq_lophoc_chinhsachgia_lop ON lophoc_chinhsachgia(lopHocId);
CREATE INDEX idx_lophoc_dotthu_policy_order ON lophoc_dotthu(lopHocChinhSachGiaId, thuTu);

-- Snapshot đăng ký theo chính sách giá
CREATE INDEX idx_dangkylophoc_pricing_policy ON dangkylophoc(lopHocChinhSachGiaId);

-- Chat rooms theo loại và trạng thái
CREATE INDEX idx_chat_rooms_loai_trang_thai ON chat_rooms(loai, trangThai);

-- Thành viên đang hoạt động trong room chat
CREATE INDEX idx_chat_room_members_room_roi ON chat_room_members(chatRoomId, roiAt);

-- Tin nhắn mới nhất trong room chat
CREATE INDEX idx_chat_messages_room_message ON chat_messages(chatRoomId, chatMessageId);

-- Reaction theo người dùng
CREATE INDEX idx_chat_message_reactions_user ON chat_message_reactions(taiKhoanId);
```
