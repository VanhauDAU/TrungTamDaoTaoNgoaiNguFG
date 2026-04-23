# 3.4. Mô tả các bảng dữ liệu

Hệ thống sử dụng cơ sở dữ liệu quan hệ MySQL, được tổ chức thành 8 nhóm module nghiệp vụ. Dưới đây là mô tả chi tiết toàn bộ các bảng dữ liệu trong dự án.

---

## Module 1: Xác thực & Phân quyền (Auth)

### 3.4.1. Bảng: taikhoan

*Lưu thông tin tài khoản đăng nhập hệ thống (học viên, giáo viên, nhân viên, admin).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| taiKhoanId | INT | PK, Auto Increment | Khóa chính |
| taiKhoan | VARCHAR(255) | Required, Unique | Tên đăng nhập |
| email | VARCHAR(255) | Required | Email liên hệ |
| matKhau | VARCHAR(255) | Required | Mật khẩu (đã hash bằng Bcrypt) |
| role | INT | Required, Default: 0 | 0=Học viên, 1=Giáo viên, 2=Nhân viên, 3=Admin |
| nhomQuyenId | INT | FK, Nullable | FK → nhomQuyen.nhomQuyenId |
| trangThai | INT | Required, Default: 1 | 0=Khóa, 1=Hoạt động |
| remember_token | VARCHAR(100) | Nullable | Token ghi nhớ đăng nhập |
| lastLogin | TIMESTAMP | Nullable | Lần đăng nhập cuối |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.2. Cấu trúc bảng taikhoan*

---

### 3.4.2. Bảng: hosonguoidung

*Lưu hồ sơ chi tiết của người dùng (thông tin cá nhân, liên hệ, người giám hộ).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| taiKhoanId | INT | PK, FK | FK → taikhoan.taiKhoanId |
| hoTen | VARCHAR(255) | Nullable | Họ tên đầy đủ |
| soDienThoai | VARCHAR(20) | Nullable | Số điện thoại |
| zalo | VARCHAR(20) | Nullable | Số Zalo |
| ngaySinh | DATE | Nullable | Ngày sinh |
| gioiTinh | TINYINT | Nullable | 0=Nữ, 1=Nam |
| diaChi | VARCHAR(500) | Nullable | Địa chỉ |
| cccd | VARCHAR(20) | Nullable | Căn cước công dân |
| anhDaiDien | VARCHAR(255) | Nullable | Đường dẫn ảnh đại diện |
| nguoiGiamHo | VARCHAR(255) | Nullable | Tên người giám hộ |
| sdtGuardian | VARCHAR(20) | Nullable | SĐT người giám hộ |
| moiQuanHe | VARCHAR(100) | Nullable | Mối quan hệ với người giám hộ |
| trinhDoHienTai | VARCHAR(100) | Nullable | Trình độ ngoại ngữ hiện tại |
| ngonNguMucTieu | VARCHAR(100) | Nullable | Ngôn ngữ mục tiêu |
| nguonBietDen | VARCHAR(255) | Nullable | Nguồn biết đến trung tâm |
| ghiChu | TEXT | Nullable | Ghi chú |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.3. Cấu trúc bảng hosonguoidung*

---

### 3.4.3. Bảng: nhansu

*Lưu thông tin nhân sự (giáo viên, nhân viên) bổ sung cho tài khoản.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| taiKhoanId | INT | PK, FK | FK → taikhoan.taiKhoanId |
| maNhanVien | VARCHAR(20) | Unique, Nullable | Mã nhân viên |
| chucVu | VARCHAR(100) | Nullable | Chức vụ |
| luongCoBan | DECIMAL(15,2) | Nullable | Lương cơ bản (VNĐ) |
| ngayVaoLam | DATE | Nullable | Ngày vào làm |
| chuyenMon | VARCHAR(255) | Nullable | Chuyên môn |
| bangCap | VARCHAR(255) | Nullable | Bằng cấp |
| hocVi | VARCHAR(100) | Nullable | Học vị |
| coSoId | INT | FK, Nullable | FK → cosodaotao.coSoId |
| loaiHopDong | TINYINT | Nullable | Loại hợp đồng |
| trangThai | TINYINT | Required, Default: 1 | 0=Nghỉ việc, 1=Đang làm |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.4. Cấu trúc bảng nhansu*

---

### 3.4.4. Bảng: nhomQuyen

*Nhóm quyền dùng để phân quyền cho tài khoản (RBAC).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhomQuyenId | INT | PK, Auto Increment | Khóa chính |
| tenNhom | VARCHAR(100) | Required | Tên nhóm quyền |
| moTa | TEXT | Nullable | Mô tả |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.5. Cấu trúc bảng nhomQuyen*

---

### 3.4.5. Bảng: phanQuyen

*Bảng chi tiết quyền CRUD cho từng tính năng theo nhóm quyền.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| phanQuyenId | INT | PK, Auto Increment | Khóa chính |
| nhomQuyenId | INT | FK, Required | FK → nhomQuyen.nhomQuyenId |
| tinhNang | VARCHAR(100) | Required | Tên tính năng (VD: khoa_hoc, tai_chinh) |
| coXem | BOOLEAN | Default: false | Quyền xem |
| coThem | BOOLEAN | Default: false | Quyền thêm |
| coSua | BOOLEAN | Default: false | Quyền sửa |
| coXoa | BOOLEAN | Default: false | Quyền xóa |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.6. Cấu trúc bảng phanQuyen*

---

### 3.4.6. Bảng: phien_dang_nhap

*Quản lý phiên đăng nhập (thiết bị, IP, trình duyệt) cho từng tài khoản.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| phienDangNhapId | BIGINT | PK, Auto Increment | Khóa chính |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId |
| sessionId | VARCHAR(255) | Unique | Mã phiên làm việc |
| portal | VARCHAR(20) | Nullable | Cổng đăng nhập (admin, student, teacher) |
| loginMethod | VARCHAR(20) | Default: 'password' | Phương thức đăng nhập (password, google) |
| remembered | BOOLEAN | Default: false | Có ghi nhớ phiên không |
| ipAddress | VARCHAR(45) | Nullable | Địa chỉ IP |
| userAgent | TEXT | Nullable | User Agent trình duyệt |
| deviceName | VARCHAR(150) | Nullable | Tên thiết bị |
| platform | VARCHAR(80) | Nullable | Hệ điều hành |
| browser | VARCHAR(80) | Nullable | Tên trình duyệt |
| lastSeenAt | TIMESTAMP | Nullable, Index | Hoạt động cuối |
| revokedAt | TIMESTAMP | Nullable, Index | Thời điểm thu hồi phiên |
| revokeReason | VARCHAR(100) | Nullable | Lý do thu hồi |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.7. Cấu trúc bảng phien_dang_nhap*

---

## Module 2: Cơ sở vật chất (Facility)

### 3.4.7. Bảng: tinhthanh

*Danh mục tỉnh/thành phố Việt Nam.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| tinhThanhId | INT | PK, Auto Increment | Khóa chính |
| maAPI | INT UNSIGNED | Unique, Nullable | Mã API provinces.open-api.vn |
| tenTinhThanh | VARCHAR(255) | Required | Tên tỉnh/thành phố |
| slug | VARCHAR(255) | Required | Slug URL-friendly |
| division_type | VARCHAR(50) | Nullable | Loại đơn vị hành chính |
| codename | VARCHAR(100) | Nullable | Tên mã code |

*Bảng 3.8. Cấu trúc bảng tinhthanh*

---

### 3.4.8. Bảng: cosodaotao

*Lưu thông tin các cơ sở đào tạo / chi nhánh của trung tâm.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| coSoId | INT | PK, Auto Increment | Khóa chính |
| maCoSo | VARCHAR(20) | Required, Unique | Mã cơ sở |
| slug | VARCHAR(255) | Required | Slug URL-friendly |
| tenCoSo | VARCHAR(255) | Required | Tên cơ sở |
| diaChi | VARCHAR(500) | Nullable | Địa chỉ |
| soDienThoai | VARCHAR(20) | Nullable | Số điện thoại |
| email | VARCHAR(255) | Nullable | Email liên hệ |
| tinhThanhId | INT | FK, Nullable | FK → tinhthanh.tinhThanhId |
| maPhuongXa | INT UNSIGNED | Nullable | Mã phường/xã từ API |
| tenPhuongXa | VARCHAR(150) | Nullable | Tên phường/xã |
| viDo | DECIMAL(10,7) | Nullable | Vĩ độ (bản đồ) |
| kinhDo | DECIMAL(10,7) | Nullable | Kinh độ (bản đồ) |
| banDoGoogle | TEXT | Nullable | Link Google Maps |
| ngayKhaiTruong | DATE | Nullable | Ngày khai trương |
| trangThai | TINYINT | Required, Default: 1 | 0=Đóng cửa, 1=Hoạt động |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.9. Cấu trúc bảng cosodaotao*

---

### 3.4.9. Bảng: phonghoc

*Phòng học tại mỗi cơ sở đào tạo.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| phongHocId | INT | PK, Auto Increment | Khóa chính |
| tenPhong | VARCHAR(100) | Required | Tên phòng học |
| sucChua | INT | Nullable | Sức chứa (người) |
| trangThietBi | TEXT | Nullable | Trang thiết bị có sẵn |
| coSoId | INT | FK, Required | FK → cosodaotao.coSoId |
| trangThai | TINYINT | Required, Default: 1 | 0=Bảo trì, 1=Hoạt động |

*Bảng 3.10. Cấu trúc bảng phonghoc*

---

## Module 3: Khóa học (Course)

### 3.4.10. Bảng: danhmuckhoahoc

*Danh mục phân loại khóa học (hỗ trợ cây phân cấp nhiều cấp qua parent_id).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| danhMucId | INT | PK, Auto Increment | Khóa chính |
| tenDanhMuc | VARCHAR(255) | Required | Tên danh mục |
| slug | VARCHAR(255) | Required, Unique | Slug URL-friendly |
| moTa | TEXT | Nullable | Mô tả |
| trangThai | TINYINT | Required, Default: 1 | 0=Ẩn, 1=Hiện |
| parent_id | INT | FK, Nullable, Index | FK → danhmuckhoahoc.danhMucId (tự tham chiếu) |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.11. Cấu trúc bảng danhmuckhoahoc*

---

### 3.4.11. Bảng: khoahoc

*Lưu thông tin khóa học do trung tâm cung cấp.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| khoaHocId | INT | PK, Auto Increment | Khóa chính |
| danhMucId | INT | FK, Required | FK → danhmuckhoahoc.danhMucId |
| tenKhoaHoc | VARCHAR(255) | Required | Tên khóa học |
| slug | VARCHAR(255) | Required, Unique | Slug URL-friendly |
| anhKhoaHoc | VARCHAR(255) | Nullable | Ảnh minh họa |
| moTa | TEXT | Nullable | Mô tả khóa học |
| doiTuong | TEXT | Nullable | Đối tượng học viên |
| yeuCauDauVao | TEXT | Nullable | Yêu cầu đầu vào |
| ketQuaDatDuoc | TEXT | Nullable | Kết quả đạt được |
| trangThai | TINYINT | Required, Default: 1 | 0=Ẩn, 1=Hiện |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.12. Cấu trúc bảng khoahoc*

---

## Module 4: Giáo dục & Lớp học (Education)

### 3.4.12. Bảng: cahoc

*Định nghĩa các ca học trong ngày (khung giờ, thứ).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| caHocId | INT | PK, Auto Increment | Khóa chính |
| tenCa | VARCHAR(100) | Required | Tên ca học (VD: Ca sáng) |
| gioBatDau | TIME | Required | Giờ bắt đầu |
| gioKetThuc | TIME | Required | Giờ kết thúc |
| moTa | TEXT | Nullable | Mô tả |
| trangThai | TINYINT | Required, Default: 1 | 0=Ngừng, 1=Hoạt động |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.13. Cấu trúc bảng cahoc*

---

### 3.4.13. Bảng: lophoc

*Lưu thông tin lớp học cụ thể (gắn với khóa học, giáo viên, phòng học, ca học).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| lopHocId | INT | PK, Auto Increment | Khóa chính |
| slug | VARCHAR(255) | Required, Unique | Slug URL-friendly |
| khoaHocId | INT | FK, Required | FK → khoahoc.khoaHocId |
| tenLopHoc | VARCHAR(255) | Required | Tên lớp học |
| phongHocId | INT | FK, Nullable | FK → phonghoc.phongHocId |
| taiKhoanId | INT | FK, Nullable | FK → taikhoan.taiKhoanId (Giáo viên phụ trách) |
| coSoId | INT | FK, Nullable | FK → cosodaotao.coSoId |
| caHocId | INT | FK, Nullable | FK → cahoc.caHocId |
| ngayBatDau | DATE | Nullable | Ngày bắt đầu |
| ngayKetThuc | DATE | Nullable | Ngày kết thúc |
| soBuoiDuKien | INT | Nullable | Số buổi dự kiến |
| soHocVienToiDa | INT | Nullable | Sĩ số tối đa |
| donGiaDay | DECIMAL(15,2) | Nullable | Đơn giá dạy/buổi (trả GV) |
| lichHoc | VARCHAR(255) | Nullable | Lịch học dạng "2,4,6" |
| trangThai | TINYINT | Required, Default: 0 | 0=Sắp mở, 1=Đang tuyển, 2=Đóng ĐK, 3=Hủy, 4=Đang học |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.14. Cấu trúc bảng lophoc*

---

### 3.4.14. Bảng: lophoc_chinhsachgia

*Chính sách giá (học phí) gắn liền với từng lớp học. Mỗi lớp có một chính sách giá.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| lopHocChinhSachGiaId | INT | PK, Auto Increment | Khóa chính |
| lopHocId | INT | FK, Unique | FK → lophoc.lopHocId (1 lớp – 1 chính sách) |
| loaiThu | TINYINT | Default: 0 | 0=Trọn gói, 1=Theo tháng, 2=Theo đợt |
| hocPhiNiemYet | DECIMAL(15,2) | Default: 0 | Học phí niêm yết (VNĐ) |
| soBuoiCamKet | INT UNSIGNED | Nullable | Số buổi cam kết trong gói |
| ghiChuChinhSach | TEXT | Nullable | Ghi chú chính sách giá |
| hieuLucTu | DATETIME | Nullable | Ngày bắt đầu hiệu lực |
| hieuLucDen | DATETIME | Nullable | Ngày hết hiệu lực |
| trangThai | TINYINT | Default: 1 | 0=Ngừng, 1=Hoạt động |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.15. Cấu trúc bảng lophoc_chinhsachgia*

---

### 3.4.15. Bảng: lophoc_dotthu

*Kế hoạch thu theo đợt (cho chính sách giá loại "Theo đợt"). Mỗi đợt sinh một hóa đơn riêng.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| lopHocDotThuId | INT | PK, Auto Increment | Khóa chính |
| lopHocChinhSachGiaId | INT UNSIGNED | FK, Required | FK → lophoc_chinhsachgia.lopHocChinhSachGiaId |
| tenDotThu | VARCHAR(255) | Required | Tên đợt thu (VD: Đợt 1, Đợt 2) |
| thuTu | INT UNSIGNED | Default: 1 | Thứ tự đợt thu |
| soTien | DECIMAL(15,2) | Default: 0 | Số tiền cho đợt thu này |
| hanThanhToan | DATE | Nullable | Hạn thanh toán |
| batBuoc | TINYINT | Default: 1 | 1=Bắt buộc, 0=Tùy chọn |
| trangThai | TINYINT | Default: 1 | 0=Ngừng, 1=Hoạt động |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.16. Cấu trúc bảng lophoc_dotthu*

---

### 3.4.16. Bảng: dangkylophoc

*Lưu đăng ký lớp học của học viên, kèm snapshot giá tại thời điểm đăng ký.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| dangKyLopHocId | INT | PK, Auto Increment | Khóa chính |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId (Học viên) |
| lopHocId | INT | FK, Required | FK → lophoc.lopHocId |
| lopHocChinhSachGiaId | INT UNSIGNED | FK, Nullable | FK → lophoc_chinhsachgia |
| loaiThuSnapshot | TINYINT | Nullable | Snapshot loại thu tại thời điểm ĐK |
| hocPhiNiemYetSnapshot | DECIMAL(15,2) | Nullable | Snapshot học phí niêm yết |
| giamGiaSnapshot | DECIMAL(15,2) | Default: 0 | Snapshot giảm giá |
| hocPhiPhaiThuSnapshot | DECIMAL(15,2) | Nullable | Snapshot học phí phải thu |
| soBuoiCamKetSnapshot | INT UNSIGNED | Nullable | Snapshot số buổi cam kết |
| ghiChuGiaSnapshot | TEXT | Nullable | Snapshot ghi chú giá |
| ngayDangKy | DATE | Nullable | Ngày đăng ký |
| trangThai | TINYINT | Required, Default: 0 | 0=Chờ duyệt, 1=Đang học, 2=Tạm dừng, 3=Đã hủy |

*Bảng 3.17. Cấu trúc bảng dangkylophoc*

---

### 3.4.17. Bảng: buoihoc

*Lưu thông tin từng buổi học cụ thể của lớp.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| buoiHocId | INT | PK, Auto Increment | Khóa chính |
| lopHocId | INT | FK, Required | FK → lophoc.lopHocId |
| tenBuoiHoc | VARCHAR(255) | Nullable | Tên buổi học |
| ngayHoc | DATE | Required | Ngày học |
| caHocId | INT | FK, Nullable | FK → cahoc.caHocId |
| phongHocId | INT | FK, Nullable | FK → phonghoc.phongHocId |
| taiKhoanId | INT | FK, Nullable | FK → taikhoan.taiKhoanId (GV dạy) |
| ghiChu | TEXT | Nullable | Ghi chú |
| daDiemDanh | BOOLEAN | Default: false | Đã điểm danh chưa |
| daHoanThanh | BOOLEAN | Default: false | Đã hoàn thành chưa |
| trangThai | TINYINT | Required, Default: 1 | Trạng thái buổi học |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.18. Cấu trúc bảng buoihoc*

---

### 3.4.18. Bảng: diemdanh

*Ghi nhận điểm danh từng học viên cho mỗi buổi học.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| diemDanhId | BIGINT | PK, Auto Increment | Khóa chính |
| buoiHocId | INT | FK, Required | FK → buoihoc.buoiHocId (CASCADE) |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId (Học viên, CASCADE) |
| dangKyLopHocId | INT | FK, Nullable | FK → dangKyLopHoc.dangKyLopHocId (SET NULL) |
| trangThai | TINYINT | Required, Default: 1 | 0=Vắng KP, 1=Có mặt, 2=Đi trễ, 3=Có phép, 4=Bị khóa |
| coMat | TINYINT | Default: 0 | 1=Có mặt/trễ (thống kê nhanh) |
| phutDiTre | SMALLINT | Nullable | Phút đi trễ (khi trangThai=2) |
| lyDo | VARCHAR(500) | Nullable | Lý do vắng/trễ/có phép |
| hinhThuc | TINYINT | Default: 0 | 0=Trực tiếp, 1=Online |
| nguoiDiemDanhId | INT | FK, Nullable | FK → taikhoan.taiKhoanId (GV/Admin) |
| thoiGianDiemDanh | DATETIME | Nullable | Thời điểm ghi nhận |
| ghiChu | TEXT | Nullable | Ghi chú |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.19. Cấu trúc bảng diemdanh*

---

## Module 5: Tài chính (Finance)

### 3.4.19. Bảng: hoadon

*Hóa đơn thanh toán học phí của học viên.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| hoaDonId | INT | PK, Auto Increment | Khóa chính |
| maHoaDon | VARCHAR(20) | Unique, Nullable | Mã hóa đơn (HD-YYYYMM-XXXXXX) |
| ngayLap | DATE | Required | Ngày lập hóa đơn |
| ngayHetHan | DATE | Nullable | Ngày hết hạn thanh toán |
| tongTien | DECIMAL(15,2) | Default: 0 | Tổng tiền gốc |
| giamGia | DECIMAL(15,2) | Default: 0 | Số tiền giảm giá |
| thue | DECIMAL(5,2) | Default: 0 | % Thuế |
| tongTienSauThue | DECIMAL(15,2) | Default: 0 | Tổng tiền sau thuế |
| daTra | DECIMAL(15,2) | Default: 0 | Số tiền đã trả |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId (Học viên) |
| nguoiLapId | BIGINT UNSIGNED | FK, Nullable | FK → taikhoan (Người lập hóa đơn) |
| dangKyLopHocId | INT | FK, Nullable | FK → dangKyLopHoc.dangKyLopHocId |
| lopHocDotThuId | INT UNSIGNED | FK, Nullable | FK → lophoc_dotthu.lopHocDotThuId |
| phuongThucThanhToan | TINYINT | Nullable | 1=Tiền mặt, 2=Chuyển khoản, 3=VNPay |
| loaiHoaDon | TINYINT | Default: 0 | 0=Đăng ký mới, 1=Gia hạn, 2=Khác |
| coSoId | INT | FK, Nullable | FK → cosodaotao.coSoId |
| trangThai | TINYINT | Default: 0 | 0=Chưa TT, 1=Một phần, 2=Đã TT đủ |
| ghiChu | TEXT | Nullable | Ghi chú |

*Bảng 3.20. Cấu trúc bảng hoadon*

---

### 3.4.20. Bảng: phieuthu

*Phiếu thu ghi nhận từng lần thu tiền cho hóa đơn.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| phieuThuId | INT | PK, Auto Increment | Khóa chính |
| maPhieuThu | VARCHAR(20) | Unique, Nullable | Mã phiếu thu (PT-YYYYMM-XXXXXX) |
| hoaDonId | INT | FK, Required | FK → hoadon.hoaDonId |
| soTien | DECIMAL(15,2) | Required | Số tiền thu |
| ngayThu | DATE | Required | Ngày thu |
| phuongThucThanhToan | TINYINT | Default: 1 | 1=Tiền mặt, 2=Chuyển khoản, 3=VNPay |
| taiKhoanId | INT | FK, Nullable | FK → taikhoan.taiKhoanId (Người nộp) |
| nguoiDuyetId | BIGINT UNSIGNED | FK, Nullable | FK → taikhoan (Người duyệt) |
| ghiChu | TEXT | Nullable | Ghi chú |
| trangThai | TINYINT | Default: 1 | 0=Hủy, 1=Hợp lệ |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.21. Cấu trúc bảng phieuthu*

---

## Module 6: Nhân sự mở rộng (HR)

### 3.4.21. Bảng: nhansu_mau_quydinh

*Mẫu quy định nhân sự (nội quy, hợp đồng mẫu) do Admin tạo ra.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhanSuMauQuyDinhId | BIGINT | PK, Auto Increment | Khóa chính |
| maMau | VARCHAR(50) | Required, Unique | Mã mẫu quy định |
| tieuDe | VARCHAR(255) | Required | Tiêu đề mẫu |
| phamViApDung | VARCHAR(20) | Default: 'both' | Phạm vi: teacher / staff / both |
| loaiHopDongApDung | VARCHAR(30) | Nullable | Loại hợp đồng áp dụng |
| noiDung | LONGTEXT | Required | Nội dung quy định (HTML) |
| phienBan | INT UNSIGNED | Default: 1 | Phiên bản mẫu |
| trangThai | TINYINT | Default: 1 | 0=Ngừng, 1=Hoạt động |
| createdById | INT | FK, Nullable | FK → taikhoan (Người tạo) |
| updatedById | INT | FK, Nullable | FK → taikhoan (Người cập nhật cuối) |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.22. Cấu trúc bảng nhansu_mau_quydinh*

---

### 3.4.22. Bảng: nhansu_hoso

*Hồ sơ nhân sự mở rộng, gắn với mẫu quy định.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhanSuHoSoId | BIGINT | PK, Auto Increment | Khóa chính |
| taiKhoanId | INT | FK, Unique | FK → taikhoan.taiKhoanId |
| maHoSo | VARCHAR(50) | Required, Unique | Mã hồ sơ nhân sự |
| nhanSuMauQuyDinhId | BIGINT UNSIGNED | FK, Nullable | FK → nhansu_mau_quydinh |
| tieuDeMauSnapshot | VARCHAR(255) | Nullable | Snapshot tiêu đề mẫu tại thời điểm gắn |
| noiDungQuyDinhSnapshot | LONGTEXT | Nullable | Snapshot nội dung quy định |
| trangThaiHoSo | VARCHAR(20) | Default: 'draft' | draft / active / archived |
| ghiChuHoSo | TEXT | Nullable | Ghi chú |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.23. Cấu trúc bảng nhansu_hoso*

---

### 3.4.23. Bảng: nhansu_goi_luong

*Gói lương hiện hành / lịch sử lương cho nhân sự.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhanSuGoiLuongId | BIGINT | PK, Auto Increment | Khóa chính |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId |
| loaiLuong | VARCHAR(30) | Required | Loại lương (cố định, theo giờ, ...) |
| luongChinh | DECIMAL(15,2) | Nullable | Mức lương chính |
| hieuLucTu | DATE | Required | Ngày bắt đầu hiệu lực |
| hieuLucDen | DATE | Nullable | Ngày kết thúc hiệu lực |
| ghiChu | TEXT | Nullable | Ghi chú |
| trangThai | TINYINT | Default: 1 | 0=Ngừng, 1=Hoạt động |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.24. Cấu trúc bảng nhansu_goi_luong*

---

### 3.4.24. Bảng: nhansu_goi_luong_chi_tiet

*Các dòng cấu phần lương (phụ cấp, khấu trừ) của từng gói lương.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhanSuGoiLuongChiTietId | BIGINT | PK, Auto Increment | Khóa chính |
| nhanSuGoiLuongId | BIGINT UNSIGNED | FK, Required | FK → nhansu_goi_luong (CASCADE) |
| loai | VARCHAR(30) | Required | Loại cấu phần (phu_cap / khau_tru) |
| tenKhoan | VARCHAR(150) | Required | Tên khoản (VD: Phụ cấp đi lại) |
| soTien | DECIMAL(15,2) | Required | Số tiền |
| ghiChu | TEXT | Nullable | Ghi chú |
| sortOrder | INT UNSIGNED | Default: 0 | Thứ tự sắp xếp |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.25. Cấu trúc bảng nhansu_goi_luong_chi_tiet*

---

### 3.4.25. Bảng: nhansu_tai_lieu

*Tài liệu hồ sơ nhân sự (hợp đồng, bằng cấp, CCCD scan...).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| nhanSuTaiLieuId | BIGINT | PK, Auto Increment | Khóa chính |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId |
| loaiTaiLieu | VARCHAR(30) | Required | Loại (hop_dong, bang_cap, cccd, khac) |
| tenHienThi | VARCHAR(255) | Required | Tên hiển thị |
| tenGoc | VARCHAR(255) | Required | Tên file gốc khi upload |
| duongDan | VARCHAR(500) | Required | Đường dẫn lưu trữ |
| disk | VARCHAR(30) | Default: 'local' | Disk lưu trữ |
| mime | VARCHAR(100) | Nullable | MIME type |
| kichThuoc | BIGINT UNSIGNED | Default: 0 | Kích thước file (byte) |
| checksum | VARCHAR(64) | Nullable | Hash kiểm tra toàn vẹn |
| phienBan | INT UNSIGNED | Default: 1 | Phiên bản tài liệu |
| duocTaiLenBoiId | INT | FK, Nullable | FK → taikhoan (Người tải lên) |
| trangThai | VARCHAR(20) | Default: 'active' | active / archived |
| ghiChu | TEXT | Nullable | Ghi chú |
| archivedAt | TIMESTAMP | Nullable | Thời điểm lưu trữ |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.26. Cấu trúc bảng nhansu_tai_lieu*

---

## Module 7: Nội dung & Bài viết (Content)

### 3.4.26. Bảng: baiviet

*Lưu bài viết / blog trên hệ thống.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| baiVietId | INT | PK, Auto Increment | Khóa chính |
| tieuDe | VARCHAR(255) | Required | Tiêu đề bài viết |
| slug | VARCHAR(255) | Required, Unique | Slug URL-friendly |
| tomTat | TEXT | Nullable | Tóm tắt nội dung |
| noiDung | LONGTEXT | Nullable | Nội dung chi tiết (HTML) |
| anhDaiDien | VARCHAR(255) | Nullable | Đường dẫn ảnh đại diện |
| taiKhoanId | INT | FK, Nullable | FK → taikhoan.taiKhoanId (Người viết) |
| luotXem | INT | Default: 0 | Số lượt xem |
| trangThai | TINYINT | Default: 0 | 0=Nháp, 1=Đã xuất bản |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.27. Cấu trúc bảng baiviet*

---

### 3.4.27. Bảng: danhmucbaiviet

*Danh mục phân loại bài viết.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| danhMucId | INT | PK, Auto Increment | Khóa chính |
| tenDanhMuc | VARCHAR(255) | Required | Tên danh mục |
| slug | VARCHAR(255) | Required, Unique | Slug URL-friendly |
| moTa | TEXT | Nullable | Mô tả danh mục |
| trangThai | TINYINT | Default: 1 | 0=Ẩn, 1=Hiện |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.28. Cấu trúc bảng danhmucbaiviet*

---

### 3.4.28. Bảng: tags

*Thẻ tag gắn cho bài viết.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| tagId | INT | PK, Auto Increment | Khóa chính |
| tenTag | VARCHAR(100) | Required | Tên tag |
| slug | VARCHAR(100) | Required, Unique | Slug URL-friendly |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.29. Cấu trúc bảng tags*

---

### 3.4.29. Bảng: baiviet_tag

*Bảng pivot liên kết N-N giữa bài viết và tag.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| baiVietId | INT | FK, Required | FK → baiviet.baiVietId |
| tagId | INT | FK, Required | FK → tags.tagId |

*Bảng 3.30. Cấu trúc bảng baiviet_tag*

---

## Module 8: Tương tác & Thông báo (Interaction)

### 3.4.30. Bảng: thongbao

*Thông báo gửi đến người dùng (hệ thống, học tập, tài chính, sự kiện, khẩn cấp).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| thongBaoId | INT | PK, Auto Increment | Khóa chính |
| tieuDe | VARCHAR(255) | Required | Tiêu đề thông báo |
| noiDung | LONGTEXT | Nullable | Nội dung (HTML) |
| nguoiGuiId | INT | FK, Nullable | FK → taikhoan.taiKhoanId |
| loaiThongBao | TINYINT | Nullable | Loại thông báo |
| doiTuongGui | TINYINT | Default: 0 | 0=Tất cả, 1=Theo lớp, 2=Theo khóa, 3=Cá nhân, 4=Theo role |
| doiTuongId | INT | Nullable | ID đối tượng (lopHocId/khoaHocId/taiKhoanId) |
| ngayGui | DATETIME | Nullable | Ngày giờ gửi |
| loaiGui | TINYINT | Default: 0 | 0=Hệ thống, 1=Học tập, 2=Tài chính, 3=Sự kiện, 4=Khẩn cấp |
| uuTien | TINYINT | Default: 0 | 0=Bình thường, 1=Quan trọng, 2=Khẩn cấp |
| ghim | BOOLEAN | Default: false | Ghim lên đầu |
| hinhAnh | VARCHAR(255) | Nullable | Ảnh đính kèm |
| trangThai | TINYINT | Default: 1 | Trạng thái |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.31. Cấu trúc bảng thongbao*

---

### 3.4.31. Bảng: thongbaonguoidung

*Bảng pivot lưu trạng thái đọc thông báo của từng người dùng.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| thongBaoNguoiDungId | INT | PK, Auto Increment | Khóa chính |
| thongBaoId | INT | FK, Required | FK → thongbao.thongBaoId |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId |
| daDoc | BOOLEAN | Default: false | Đã đọc chưa |
| ngayDoc | DATETIME | Nullable | Thời gian đọc |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.32. Cấu trúc bảng thongbaonguoidung*

---

### 3.4.32. Bảng: thongbao_tepdinh

*Tệp đính kèm cho thông báo.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| tepDinhId | BIGINT | PK, Auto Increment | Khóa chính |
| thongBaoId | INT | FK, Required, Index | FK → thongbao.thongBaoId |
| tenFile | VARCHAR(255) | Required | Tên file gốc |
| tenFileLuu | VARCHAR(255) | Required | Tên file lưu server (uuid+ext) |
| duongDan | VARCHAR(500) | Required | Đường dẫn trong storage |
| loaiFile | VARCHAR(100) | Nullable | MIME type |
| kichThuoc | BIGINT UNSIGNED | Default: 0 | Kích thước (byte) |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.33. Cấu trúc bảng thongbao_tepdinh*

---

### 3.4.33. Bảng: lienhe

*Lưu liên hệ / yêu cầu tư vấn từ khách hàng (CRM).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| lienHeId | INT | PK, Auto Increment | Khóa chính |
| hoTen | VARCHAR(255) | Required | Họ tên người liên hệ |
| email | VARCHAR(255) | Nullable | Email |
| soDienThoai | VARCHAR(20) | Nullable | Số điện thoại |
| tieuDe | VARCHAR(255) | Nullable | Tiêu đề |
| noiDung | TEXT | Nullable | Nội dung liên hệ |
| loaiLienHe | ENUM | Default: 'tu_van' | tu_van, ho_tro, khieu_nai, khac |
| trangThai | TINYINT | Default: 0 | 0=Chưa xử lý, 1=Đang XL, 2=Đã XL, 3=Từ chối |
| taiKhoanId | INT | FK, Nullable | FK → taikhoan.taiKhoanId |
| ghiChuNoiBo | TEXT | Nullable | Ghi chú nội bộ (admin/NV) |
| nguoiPhuTrachId | BIGINT UNSIGNED | FK, Nullable | FK → taikhoan (Người phụ trách) |
| thoiGianXuLy | TIMESTAMP | Nullable | Thời gian xử lý xong |
| deleted_at | TIMESTAMP | Nullable | Soft delete |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.34. Cấu trúc bảng lienhe*

---

### 3.4.34. Bảng: lienhe_lichsu

*Lịch sử thao tác trên liên hệ (audit log).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| lichSuId | BIGINT | PK, Auto Increment | Khóa chính |
| lienHeId | BIGINT UNSIGNED | FK, Required, Index | FK → lienhe.lienHeId |
| hanhDong | VARCHAR(100) | Required | Hành động (tiep_nhan, cap_nhat_trang_thai...) |
| noiDung | TEXT | Nullable | Nội dung mô tả |
| giaTriCu | VARCHAR(200) | Nullable | Giá trị cũ |
| giaTriMoi | VARCHAR(200) | Nullable | Giá trị mới |
| nguoiThucHienId | BIGINT UNSIGNED | Nullable | ID người thực hiện |
| tenNguoiThucHien | VARCHAR(200) | Nullable | Tên người thực hiện |
| created_at | TIMESTAMP | Required, Default: now | Thời gian tạo |

*Bảng 3.35. Cấu trúc bảng lienhe_lichsu*

---

### 3.4.35. Bảng: lienhe_phanhoi

*Phản hồi cho liên hệ (nội bộ hoặc email cho khách).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| phanHoiId | BIGINT | PK, Auto Increment | Khóa chính |
| lienHeId | BIGINT UNSIGNED | FK, Required, Index | FK → lienhe.lienHeId |
| noiDung | TEXT | Required | Nội dung phản hồi |
| loai | ENUM | Default: 'noi_bo' | noi_bo / email |
| nguoiGuiId | BIGINT UNSIGNED | Nullable | ID người gửi |
| tenNguoiGui | VARCHAR(200) | Nullable | Tên người gửi |
| daGuiEmail | BOOLEAN | Default: false | Đã gửi email cho khách chưa |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.36. Cấu trúc bảng lienhe_phanhoi*

---

## Module 9: Chat Realtime

### 3.4.36. Bảng: chat_rooms

*Phòng chat theo lớp học hoặc trò chuyện riêng (direct).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatRoomId | BIGINT | PK, Auto Increment | Khóa chính |
| loai | VARCHAR(20) | Required | class_group / direct |
| tenPhong | VARCHAR(150) | Nullable | Tên phòng chat |
| lopHocId | INT | Unique, Nullable | FK → lophoc.lopHocId |
| matKhauHash | VARCHAR(255) | Nullable | Mật khẩu phòng (nếu có) |
| taoBoiId | INT | Nullable, Index | FK → taikhoan.taiKhoanId |
| lastMessageId | BIGINT UNSIGNED | Nullable | ID tin nhắn cuối cùng |
| trangThai | TINYINT | Default: 1 | 0=Inactive, 1=Active, 2=Archived |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.37. Cấu trúc bảng chat_rooms*

---

### 3.4.37. Bảng: chat_room_members

*Thành viên của từng phòng chat.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatRoomMemberId | BIGINT | PK, Auto Increment | Khóa chính |
| chatRoomId | BIGINT UNSIGNED | FK, Required | FK → chat_rooms.chatRoomId |
| taiKhoanId | INT | FK, Required | FK → taikhoan.taiKhoanId |
| vaiTro | VARCHAR(20) | Default: 'member' | member / teacher / owner |
| joinedAt | TIMESTAMP | Nullable | Thời điểm tham gia |
| joinedByPasswordAt | TIMESTAMP | Nullable | Tham gia bằng mật khẩu |
| lastReadMessageId | BIGINT UNSIGNED | Nullable | Tin nhắn đã đọc cuối |
| lastSeenAt | TIMESTAMP | Nullable | Lần truy cập cuối |
| isMuted | BOOLEAN | Default: false | Tắt thông báo |
| roiAt | TIMESTAMP | Nullable | Thời điểm rời phòng |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.38. Cấu trúc bảng chat_room_members*

---

### 3.4.38. Bảng: chat_messages

*Tin nhắn chat.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatMessageId | BIGINT | PK, Auto Increment | Khóa chính |
| chatRoomId | BIGINT UNSIGNED | FK, Required, Index | FK → chat_rooms.chatRoomId |
| nguoiGuiId | INT | FK, Required, Index | FK → taikhoan.taiKhoanId |
| replyToMessageId | BIGINT UNSIGNED | Nullable, Index | FK → chat_messages (Trả lời tin nhắn) |
| loai | VARCHAR(20) | Default: 'text' | text / image / file / location / system |
| noiDung | LONGTEXT | Nullable | Nội dung tin nhắn |
| metaJson | JSON | Nullable | Metadata bổ sung |
| guiLuc | TIMESTAMP | Default: now, Index | Thời điểm gửi |
| deadlineThuHoi | TIMESTAMP | Nullable | Hạn thu hồi |
| thuHoiLuc | TIMESTAMP | Nullable | Thời điểm thu hồi |
| xoaLuc | TIMESTAMP | Nullable | Thời điểm xóa |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.39. Cấu trúc bảng chat_messages*

---

### 3.4.39. Bảng: chat_message_attachments

*File / ảnh đính kèm của tin nhắn.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatAttachmentId | BIGINT | PK, Auto Increment | Khóa chính |
| chatMessageId | BIGINT UNSIGNED | FK, Required, Index | FK → chat_messages.chatMessageId |
| disk | VARCHAR(50) | Default: 'public' | Disk lưu trữ |
| path | VARCHAR(500) | Required | Đường dẫn file |
| thumbnailPath | VARCHAR(500) | Nullable | Đường dẫn ảnh thu nhỏ |
| tenGoc | VARCHAR(255) | Required | Tên file gốc |
| mime | VARCHAR(100) | Nullable | MIME type |
| size | BIGINT UNSIGNED | Default: 0 | Kích thước (byte) |
| width | INT UNSIGNED | Nullable | Chiều rộng (ảnh) |
| height | INT UNSIGNED | Nullable | Chiều cao (ảnh) |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.40. Cấu trúc bảng chat_message_attachments*

---

### 3.4.40. Bảng: chat_message_reactions

*Reaction (emoji) trên tin nhắn.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatReactionId | BIGINT | PK, Auto Increment | Khóa chính |
| chatMessageId | BIGINT UNSIGNED | FK, Required | FK → chat_messages.chatMessageId |
| taiKhoanId | INT | FK, Required, Index | FK → taikhoan.taiKhoanId |
| emoji | VARCHAR(50) | Required | Ký tự emoji |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.41. Cấu trúc bảng chat_message_reactions*

---

### 3.4.41. Bảng: chat_message_deletes

*Bản ghi xóa phía người dùng (xóa cho mình, không ảnh hưởng người khác).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatMessageDeleteId | BIGINT | PK, Auto Increment | Khóa chính |
| chatMessageId | BIGINT UNSIGNED | FK, Required | FK → chat_messages.chatMessageId |
| taiKhoanId | INT | FK, Required, Index | FK → taikhoan.taiKhoanId |
| deletedAt | TIMESTAMP | Default: now | Thời điểm xóa |
| created_at | TIMESTAMP | Default: now | Ngày tạo |

*Bảng 3.42. Cấu trúc bảng chat_message_deletes*

---

### 3.4.42. Bảng: chat_audit_logs

*Audit log ghi nhận toàn bộ thao tác trong hệ thống chat.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| chatAuditLogId | BIGINT | PK, Auto Increment | Khóa chính |
| chatRoomId | BIGINT UNSIGNED | Nullable, Index | FK → chat_rooms.chatRoomId |
| chatMessageId | BIGINT UNSIGNED | Nullable, Index | FK → chat_messages.chatMessageId |
| taiKhoanId | INT | Nullable, Index | FK → taikhoan.taiKhoanId |
| hanhDong | VARCHAR(80) | Required, Index | Hành động (send, recall, delete, join, leave...) |
| duLieuCu | JSON | Nullable | Dữ liệu trước khi thay đổi |
| duLieuMoi | JSON | Nullable | Dữ liệu sau khi thay đổi |
| created_at | TIMESTAMP | Default: now | Thời gian tạo |

*Bảng 3.43. Cấu trúc bảng chat_audit_logs*

---

## Module 10: Cấu hình hệ thống

### 3.4.43. Bảng: cau_hinh_he_thong

*Cấu hình hệ thống động, quản lý từ giao diện Admin.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| id | BIGINT | PK, Auto Increment | Khóa chính |
| nhom | VARCHAR(80) | Required, Index | Nhóm cấu hình (he_thong, giao_duc, bao_mat...) |
| khoa | VARCHAR(120) | Required, Unique | Khóa cấu hình (snake_case) |
| ten_hien_thi | VARCHAR(200) | Required | Tên hiển thị tiếng Việt |
| gia_tri | TEXT | Nullable | Giá trị lưu trữ |
| kieu_du_lieu | VARCHAR(30) | Default: 'text' | text / number / boolean / select / textarea / color / json |
| mo_ta | TEXT | Nullable | Mô tả chi tiết cấu hình |
| gia_tri_mac_dinh | TEXT | Nullable | Giá trị mặc định |
| tuy_chon | JSON | Nullable | Tùy chọn cho kiểu select: [{label, value}] |
| yeu_cau | BOOLEAN | Default: false | Bắt buộc hay không |
| thu_tu | INT | Default: 0 | Thứ tự hiển thị trong nhóm |
| an_trong_ui | BOOLEAN | Default: false | Ẩn khỏi giao diện (chỉ dùng nội bộ) |
| created_at | TIMESTAMP | Nullable | Ngày tạo |
| updated_at | TIMESTAMP | Nullable | Ngày cập nhật |

*Bảng 3.44. Cấu trúc bảng cau_hinh_he_thong*

---

## Module 11: Bảng hệ thống Laravel

### 3.4.44. Bảng: sessions

*Quản lý session người dùng (Session Driver = database).*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| id | VARCHAR(255) | PK | Session ID |
| user_id | BIGINT | Nullable, Index | FK → users.id |
| ip_address | VARCHAR(45) | Nullable | Địa chỉ IP |
| user_agent | TEXT | Nullable | User Agent |
| payload | LONGTEXT | Required | Session data (encrypted) |
| last_activity | INT | Required, Index | Timestamp hoạt động cuối |

*Bảng 3.45. Cấu trúc bảng sessions*

---

### 3.4.45. Bảng: password_reset_tokens

*Token đặt lại mật khẩu.*

| Trường | Kiểu dữ liệu | Ràng buộc | Mô tả |
| :--- | :--- | :--- | :--- |
| email | VARCHAR(255) | PK | Email đăng ký |
| token | VARCHAR(255) | Required | Token reset |
| created_at | TIMESTAMP | Nullable | Ngày tạo |

*Bảng 3.46. Cấu trúc bảng password_reset_tokens*

---

> **Tổng kết:** Hệ thống gồm **45 bảng dữ liệu** được phân bổ trên 11 module nghiệp vụ, sử dụng cơ sở dữ liệu MySQL với các quy ước: Primary Key dạng INT AUTO_INCREMENT, Naming Convention camelCase cho tên cột, lowercase cho tên bảng, và áp dụng Soft Delete cho các bảng: taikhoan, khoahoc, baiviet, lienhe, lophoc, thongbao.
