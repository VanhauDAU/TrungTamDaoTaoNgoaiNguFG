# UML DIAGRAMS CHECKLIST

## Thông tin dự án

| Thông tin          | Chi tiết                                                                            |
| ------------------ | ----------------------------------------------------------------------------------- |
| **Tên dự án**      | Nghiên cứu Laravel & MySQL và Xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ |
| **Nhóm**           | Five Genius                                                                         |
| **Ngày kiểm tra**  | 07/02/2026                                                                          |
| **Người kiểm tra** | Lê Ngọc Ánh                                                                         |

---

## 1. USE CASE DIAGRAM

| ✓   | Tiêu chí                                 | Ghi chú                                                                                                                        |
| --- | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| ☑   | Có ít nhất **2 Actors**                  | ✅ Đã có: Học viên, Giảng viên, Quản trị viên, Nhân viên                                                                       |
| ☑   | Có tối thiểu **10 Use Cases**            | ✅ Đầy đủ các UC chính: Đăng ký, Đăng nhập, Quản lý khóa học, Quản lý lớp học, Thanh toán, Xem hóa đơn, Quản lý tài liệu, v.v. |
| ☑   | Có ít nhất **1 mối quan hệ <<include>>** | ✅ Ví dụ: "Đăng ký lớp học" <<include>> "Xác thực người dùng"                                                                  |
| ☑   | Có ít nhất **1 mối quan hệ <<extend>>**  | ✅ Ví dụ: "Thanh toán VNPay" <<extend>> "Thanh toán học phí"                                                                   |
| ☑   | **Tên Use Case bắt đầu bằng động từ**    | ✅ Đăng ký, Đăng nhập, Quản lý, Xem, Tạo, Sửa, Xóa                                                                             |
| ☑   | **System boundary** được vẽ rõ ràng      | ✅ Hệ thống "Trung tâm Đào tạo Ngoại ngữ" được khoanh vùng                                                                     |

### Gợi ý cải thiện:

- Nhóm các use case theo chức năng (Quản lý học viên, Quản lý học vụ, Quản lý tài chính)
- Sử dụng màu sắc hoặc package để phân biệt module

---

## 2. CLASS DIAGRAM

| ✓   | Tiêu chí                                               | Ghi chú                                                                                                       |
| --- | ------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------- |
| ☑   | Có tối thiểu **5 classes**                             | ✅ TaiKhoan, HoSoNguoiDung, KhoaHoc, LopHoc, DangKyLopHoc, HoaDon, PhieuThu, BuoiHoc, CaHoc, CoSoDaoTao, v.v. |
| ☑   | Mỗi class có **đủ 3 phần** (name, attributes, methods) | ✅ Tất cả class đều có: tên, thuộc tính, phương thức                                                          |
| ☑   | **Visibility markers** (+, -, #) được đánh dấu         | ✅ Public (+), Private (-), Protected (#)                                                                     |
| ☑   | Có ít nhất **1 quan hệ Inheritance**                   | ✅ Ví dụ: HocVien, GiangVien, QuanTriVien kế thừa từ TaiKhoan                                                 |
| ☑   | Có quan hệ **Association/Aggregation/Composition**     | ✅ Composition: LopHoc ◆→ BuoiHoc, Association: GiangVien → LopHoc                                            |
| ☑   | **Multiplicity** được ghi rõ (1, _, 0..1, 1.._)        | ✅ KhoaHoc 1 → _ LopHoc, LopHoc _ → \* HocVien                                                                |

### Ví dụ các quan hệ:

```
TaiKhoan (1) ──── (0..*) DangKyLopHoc
LopHoc (1) ──── (0..*) DangKyLopHoc
LopHoc (1) ◆──── (*) BuoiHoc (Composition)
HoaDon (1) ──── (0..*) PhieuThu
```

---

## 3. SEQUENCE DIAGRAM

| ✓   | Tiêu chí                                  | Ghi chú                                          |
| --- | ----------------------------------------- | ------------------------------------------------ |
| ☑   | Có ít nhất **2 diagrams** cho 2 use cases | ✅ SD1: Đăng ký lớp học, SD2: Thanh toán học phí |
| ☑   | Có tối thiểu **4 participants**           | ✅ HocVien, UI (Controller), Service, Database   |
| ☑   | **Messages được đánh số thứ tự**          | ✅ 1.0, 1.1, 1.2, 2.0, v.v.                      |
| ☑   | Có cả **synchronous và return messages**  | ✅ Solid arrow (→) và dotted arrow (⇢)           |
| ☑   | **Lifelines** được vẽ đúng                | ✅ Activation boxes trên lifeline                |
| ☑   | Mô tả **flow từ UI đến Database**         | ✅ UI → Controller → Service → Model → Database  |

### Sequence Diagram 1: Đăng ký lớp học

```
Participants: HocVien, CourseController, DangKyLopHoc, LopHoc, HoaDon, Database
Flow:
1. HocVien chọn lớp học
2. Kiểm tra điều kiện đăng ký (sĩ số, trùng lịch)
3. Tạo DangKyLopHoc
4. Tạo HoaDon
5. Return success/error
```

### Sequence Diagram 2: Thanh toán học phí

```
Participants: HocVien, StaffController, HoaDon, PhieuThu, Database
Flow:
1. Nhân viên nhập thông tin thanh toán
2. Validation
3. Tạo PhieuThu
4. Update HoaDon.daTra
5. Update trạng thái
6. Return receipt
```

---

## 4. ERD (Entity Relationship Diagram)

| ✓   | Tiêu chí                                         | Ghi chú                                                                                                                      |
| --- | ------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| ☑   | Có tối thiểu **5 tables**                        | ✅ 15+ tables: taikhoan, hosонguoidung, khoahoc, lophoc, dangkylophoc, hoadon, phieuthu, buoihoc, cahoc, cơ_sơ_dao_tao, v.v. |
| ☑   | **Primary Keys** được đánh dấu (PK)              | ✅ Tất cả table đều có PK (taiKhoanId, khoaHocId, lopHocId, v.v.)                                                            |
| ☑   | **Foreign Keys** được đánh dấu (FK)              | ✅ FK rõ ràng: dangKyLopHoc.taiKhoanId → taikhoan.taiKhoanId                                                                 |
| ☑   | **Cardinality** được ghi rõ (1:1, 1:N, M:N)      | ✅ 1:N (khoahoc → lophoc), M:N (lophoc ←→ taikhoan qua dangkylophoc)                                                         |
| ☑   | **Many-to-Many có junction table**               | ✅ dangkylophoc là junction table giữa lophoc và taikhoan                                                                    |
| ☑   | **Tên tables và columns** theo naming convention | ✅ camelCase cho columns, lowercase cho tables                                                                               |

### Các quan hệ chính:

#### 1:N Relationships

- `khoahoc` (1) → (\*) `lophoc`
- `lophoc` (1) → (\*) `buoihoc`
- `lophoc` (1) → (\*) `dangkylophoc`
- `taikhoan` (1) → (\*) `dangkylophoc`
- `hoadon` (1) → (\*) `phieuthu`
- `cosodaotao` (1) → (\*) `lophoc`

#### M:N Relationships (qua junction table)

- `lophoc` (_) ←→ (_) `taikhoan` (qua `dangkylophoc`)

#### 1:1 Relationships

- `taikhoan` (1) ← (1) `hosơnguoidung`

### ERD Schema Example:

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│  khoahoc    │ 1     * │   lophoc     │ 1     * │  buoihoc    │
├─────────────┤─────────├──────────────┤─────────├─────────────┤
│ PK khoaHocId│         │ PK lopHocId  │         │ PK buoiHocId│
│    tenKhoaHoc│         │ FK khoaHocId │         │ FK lopHocId │
│    moTa     │         │    tenLopHoc │         │    ngayHoc  │
│    hocPhi   │         │    ngayBatDau│         │ FK caHocId  │
└─────────────┘         │    trangThai │         └─────────────┘
                        └──────────────┘
                               │ *
                               │
                               │ *
                        ┌──────────────┐
                        │dangkylophoc  │
                        ├──────────────┤
                        │ PK dangKyId  │
                        │ FK lopHocId  │
                        │ FK taiKhoanId│
                        │    ngayDangKy│
                        │    trangThai │
                        └──────────────┘
                               │ *
                               │
                               │ 1
                        ┌──────────────┐
                        │  taikhoan    │
                        ├──────────────┤
                        │ PK taiKhoanId│
                        │    tenDangNhap│
                        │    matKhau   │
                        │    vaiTro    │
                        └──────────────┘
```

---

## 5. ACTIVITY DIAGRAM (Bổ sung - Khuyến nghị)

| ✓   | Tiêu chí                                   | Ghi chú                                                        |
| --- | ------------------------------------------ | -------------------------------------------------------------- |
| ☐   | Có ít nhất **1 diagram** cho flow phức tạp | Nên có: Activity Diagram cho "Quy trình đăng ký và thanh toán" |
| ☐   | Có **decision nodes** (diamond)            | Kiểm tra điều kiện: sĩ số, trùng lịch, phương thức thanh toán  |
| ☐   | Có **swim lanes** phân vai trò             | Học viên, Hệ thống, Nhân viên                                  |
| ☐   | Có **parallel activities** (fork/join)     | Ví dụ: Tạo đồng thời DangKyLopHoc và HoaDon                    |

---

## 6. STATE DIAGRAM (Bổ sung - Khuyến nghị)

| ✓   | Tiêu chí                             | Ghi chú                                                     |
| --- | ------------------------------------ | ----------------------------------------------------------- |
| ☐   | Mô tả **lifecycle của HoaDon**       | Chưa TT → TT 1 phần → Đã đủ                                 |
| ☐   | Mô tả **lifecycle của LopHoc**       | Sắp mở → Đang mở → Đang học → Đã kết thúc                   |
| ☐   | Có **states và transitions** rõ ràng | States: hình chữ nhật bo góc, Transitions: mũi tên có label |

---

## TỔNG KẾT

### ✅ Đánh giá chung

| Kết luận  | Chi tiết                               |
| --------- | -------------------------------------- |
| ☑ **ĐẠT** | Tất cả tiêu chí bắt buộc đã hoàn thành |
| ☐ CẦN SỬA | Không có tiêu chí nào chưa đạt         |

### 📊 Thống kê

| Loại biểu đồ     | Số lượng | Trạng thái         |
| ---------------- | -------- | ------------------ |
| Use Case Diagram | 1        | ✅ Hoàn thành      |
| Class Diagram    | 1        | ✅ Hoàn thành      |
| Sequence Diagram | 2+       | ✅ Hoàn thành      |
| ERD              | 1        | ✅ Hoàn thành      |
| **Tổng**         | **5+**   | **✅ Đạt yêu cầu** |

### 🎯 Điểm mạnh

1. ✅ **Use Case Diagram**: Đầy đủ actors, use cases, và relationships
2. ✅ **Class Diagram**: Cấu trúc rõ ràng với nhiều quan hệ phức tạp
3. ✅ **Sequence Diagram**: Mô tả chi tiết flow từ UI đến Database
4. ✅ **ERD**: Database schema đầy đủ với 15+ tables, FK/PK rõ ràng

### 💡 Đề xuất cải thiện (Không bắt buộc)

1. **Activity Diagram**: Thêm để mô tả quy trình nghiệp vụ phức tạp
2. **State Diagram**: Mô tả lifecycle của HoaDon và LopHoc
3. **Component Diagram**: Mô tả kiến trúc hệ thống (MVC, Layers)
4. **Deployment Diagram**: Mô tả môi trường triển khai (Server, Database, Client)

---

## PHỤ LỤC: Danh sách các biểu đồ UML

### Biểu đồ bắt buộc ✅

1. ✅ Use Case Diagram - `usecase_diagram.png`
2. ✅ Class Diagram - `class_diagram.png`
3. ✅ Sequence Diagram 1 (Đăng ký lớp học) - `sequence_dangky.png`
4. ✅ Sequence Diagram 2 (Thanh toán) - `sequence_thanhtoan.png`
5. ✅ ERD (Entity Relationship Diagram) - `erd_diagram.png`

### Biểu đồ bổ sung (Khuyến nghị) 💡

6. ⭐ Activity Diagram (Quy trình đăng ký) - `activity_dangky.png`
7. ⭐ State Diagram (Lifecycle HoaDon) - `state_hoadon.png`
8. ⭐ State Diagram (Lifecycle LopHoc) - `state_lophoc.png`

---

## Chú thích

- ☑ : Đã hoàn thành
- ☐ : Chưa hoàn thành
- ✅ : Đạt yêu cầu
- ⭐ : Khuyến nghị bổ sung
- 💡 : Gợi ý cải thiện

---

**Người kiểm tra**: Lê Ngọc Ánh  
**Ngày kiểm tra**: 07/02/2026  
**Kết luận**: Dự án đã đạt đầy đủ các tiêu chí về UML Diagrams và sẵn sàng cho báo cáo cuối kỳ.

---

_Tài liệu này được tạo tự động từ hệ thống kiểm tra UML Diagrams - Five Genius Team_
