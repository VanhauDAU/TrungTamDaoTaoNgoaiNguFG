# SYSTEM ARCHITECTURE DOCUMENT
## Tài liệu Kiến trúc Hệ thống

| Thuộc tính | Giá trị |
|------------|---------|
| **Tên dự án** | Nghiên cứu Laravel & MySQL và Xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ |
| **Phiên bản** | 1.0 |
| **Ngày cập nhật** | 07/02/2026 |
| **Người viết** | Nhóm Five Genius |

---

# 1. TỔNG QUAN HỆ THỐNG

## 1.1. Mô tả ngắn

Hệ thống Quản lý Trung tâm Ngoại ngữ là một ứng dụng web được phát triển nhằm số hóa và tự động hóa các quy trình quản lý tại trung tâm đào tạo ngoại ngữ, bao gồm:

- **Quản lý học viên:** Đăng ký, hồ sơ, theo dõi quá trình học
- **Quản lý khóa học:** Tạo, cập nhật, phân loại khóa học
- **Quản lý lớp học:** Lịch học, giáo viên, phòng học
- **Điểm danh & Điểm số:** Theo dõi chuyên cần, nhập điểm
- **Quản lý tài chính:** Học phí, hóa đơn, thanh toán
- **Nội dung website:** Bài viết, thông báo, liên hệ

**Đối tượng sử dụng:** Admin, Giáo viên, Học viên, Khách

---

# 2. KIẾN TRÚC ÁP DỤNG

**Chọn kiến trúc:** ☐ Monolithic ☑ **Layered** ☐ Microservices ☐ Khác: ____

**Lý do chọn:**

Layered Architecture (Kiến trúc phân lớp) được chọn vì:

1. **Phù hợp quy mô dự án:** Dự án vừa và nhỏ, không cần microservices
2. **Dễ phân chia công việc:** Mỗi thành viên có thể phụ trách một layer
3. **Cấu trúc rõ ràng:** Phân tách Presentation, Business Logic, Data Access
4. **Tương thích Laravel:** Framework Laravel hỗ trợ tốt MVC pattern
5. **Dễ bảo trì:** Thay đổi một layer không ảnh hưởng layer khác

```
┌─────────────────────────────────────┐
│     PRESENTATION LAYER              │
│  (Blade Views, Controllers)         │
├─────────────────────────────────────┤
│     BUSINESS LOGIC LAYER            │
│  (Services, Form Requests)          │
├─────────────────────────────────────┤
│     DATA ACCESS LAYER               │
│  (Eloquent Models, Query Builder)   │
├─────────────────────────────────────┤
│     DATABASE LAYER                  │
│  (MySQL 8.0)                        │
└─────────────────────────────────────┘
```

---

# 3. CÔNG NGHỆ SỬ DỤNG (Technology Stack)

| Layer/Component | Technology | Version | Lý do chọn |
|-----------------|------------|---------|------------|
| **Frontend** | Blade, Bootstrap, JavaScript | Bootstrap 5 | Template engine tích hợp Laravel, responsive design |
| **Backend** | Laravel (PHP) | 10.x / PHP 8.1+ | MVC rõ ràng, Eloquent ORM mạnh, cộng đồng lớn |
| **Database** | MySQL | 8.0 | Quan hệ phức tạp, transaction, tích hợp tốt Laravel |
| **Cache** | File Cache | Laravel default | Đơn giản, phù hợp quy mô dự án |
| **Authentication** | Laravel Auth | Session-based | Có sẵn trong Laravel, bảo mật cao |
| **Hosting** | XAMPP / Apache | Apache 2.4 | Môi trường phát triển quen thuộc |

---

# 4. CÁC THÀNH PHẦN CHÍNH

*(Liệt kê các modules/services chính của hệ thống)*

| Component | Trách nhiệm | Phụ thuộc |
|-----------|-------------|-----------|
| **Auth Module** | Đăng nhập, đăng ký, phân quyền, quên mật khẩu | TaiKhoan, HoSoNguoiDung |
| **Course Module** | Quản lý khóa học, loại khóa học, nội dung bài học | KhoaHoc, LoaiKhoaHoc, TaiLieu |
| **Education Module** | Quản lý lớp học, buổi học, điểm danh, đăng ký lớp | LopHoc, BuoiHoc, DiemDanh, DangKyLopHoc |
| **Exam Module** | Quản lý bài thi, nhập điểm, xem điểm | BaiThi, DiemBaiThi |
| **Finance Module** | Quản lý học phí, hóa đơn, phiếu thu, lương | HoaDon, PhieuThu, HocPhi, Luong |
| **Content Module** | Quản lý bài viết, danh mục, tags | BaiViet, DanhMucBaiViet, Tag |
| **Facility Module** | Quản lý cơ sở, phòng học | CoSoDaoTao, PhongHoc |
| **Interaction Module** | Liên hệ, thông báo, phản hồi | LienHe, ThongBao, PhanHoi |

---

# 5. THAM CHIẾU DIAGRAMS

| Diagram | Link/File |
|---------|-----------|
| **Use Case Diagram** | `docs/mermaid_usecase_code.txt` |
| **Class Diagram** | `docs/class_specification.md` |
| **Sequence Diagrams** | `docs/system_architecture.md` (phần 6) |
| **ERD** | `docs/system_architecture.md` (phần 7) |
| **System Context Diagram** | `docs/system_architecture.md` (phần 8) |

---

# 6. SEQUENCE DIAGRAMS

## 6.1. Sequence Diagram - Đăng nhập

```mermaid
sequenceDiagram
    autonumber
    actor User as 👤 Người dùng
    participant View as 🖥️ LoginView
    participant Controller as ⚙️ LoginController
    participant Auth as 🔐 Auth Service
    participant Model as 📦 TaiKhoan Model
    participant DB as 🗄️ MySQL
    
    User->>View: Truy cập trang đăng nhập
    View-->>User: Hiển thị form đăng nhập
    User->>View: Nhập email + mật khẩu
    View->>Controller: POST /login
    Controller->>Auth: attempt(credentials)
    Auth->>Model: findByEmail(email)
    Model->>DB: SELECT * FROM TaiKhoan
    DB-->>Model: User data
    Model-->>Auth: TaiKhoan object
    Auth->>Auth: Hash::check(password)
    alt Mật khẩu đúng
        Auth-->>Controller: return true
        Controller-->>View: redirect('/home')
        View-->>User: Trang chủ
    else Mật khẩu sai
        Auth-->>Controller: return false
        Controller-->>View: Error message
        View-->>User: "Sai mật khẩu"
    end
```

## 6.2. Sequence Diagram - Đăng ký khóa học

```mermaid
sequenceDiagram
    autonumber
    actor Student as 🎓 Học viên
    participant View as 🖥️ CourseView
    participant Controller as ⚙️ DangKyController
    participant LopHoc as 📦 LopHoc Model
    participant DangKy as 📦 DangKyLopHoc Model
    participant HoaDon as 📦 HoaDon Model
    participant DB as 🗄️ MySQL
    
    Student->>View: Xem chi tiết khóa học
    View->>Controller: GET /khoa-hoc/{slug}
    Controller->>LopHoc: getLopHocByKhoaHoc()
    LopHoc->>DB: SELECT * FROM LopHoc
    DB-->>LopHoc: Danh sách lớp
    Controller-->>View: return view
    View-->>Student: Hiển thị các lớp
    Student->>View: Chọn lớp + Đăng ký
    View->>Controller: POST /dang-ky-lop
    Controller->>LopHoc: checkSlot()
    alt Còn slot
        Controller->>DangKy: create()
        DangKy->>DB: INSERT DangKyLopHoc
        Controller->>HoaDon: create()
        HoaDon->>DB: INSERT HoaDon
        Controller-->>View: success
        View-->>Student: "Đăng ký thành công"
    else Hết slot
        Controller-->>View: error
        View-->>Student: "Lớp đã đầy"
    end
```

---

# 7. ERD (Entity Relationship Diagram)

```mermaid
erDiagram
    TaiKhoan {
        int taiKhoanId PK
        string taiKhoan
        string email
        string matKhau
        enum role
        boolean trangThai
    }
    
    HoSoNguoiDung {
        int taiKhoanId PK,FK
        string hoTen
        string soDienThoai
        date ngaySinh
        string diaChi
    }
    
    KhoaHoc {
        int khoaHocId PK
        int loaiKhoaHocId FK
        string tenKhoaHoc
        decimal hocPhi
    }
    
    LopHoc {
        int lopHocId PK
        int khoaHocId FK
        int giaoVienId FK
        string tenLop
        int soHocVienToiDa
    }
    
    DangKyLopHoc {
        int dangKyId PK
        int taiKhoanId FK
        int lopHocId FK
        enum trangThai
    }
    
    DiemDanh {
        int diemDanhId PK
        int taiKhoanId FK
        int buoiHocId FK
        enum trangThai
    }

    TaiKhoan ||--o| HoSoNguoiDung : "has"
    TaiKhoan ||--o{ DangKyLopHoc : "registers"
    TaiKhoan ||--o{ DiemDanh : "attends"
    KhoaHoc ||--o{ LopHoc : "has"
    LopHoc ||--o{ DangKyLopHoc : "enrolls"
```

---

# 8. SYSTEM CONTEXT DIAGRAM

```mermaid
graph TB
    subgraph "External"
        Admin["👨‍💼 Admin"]
        GV["👨‍🏫 Giáo viên"]
        HV["🎓 Học viên"]
        Khach["👤 Khách"]
    end
    
    subgraph "System"
        WebApp["🌐 Hệ thống TTNN<br/>Laravel Application"]
    end
    
    subgraph "Infrastructure"
        MySQL[("🗄️ MySQL<br/>Database")]
        FileStorage["📁 File Storage"]
    end
    
    Admin --> WebApp
    GV --> WebApp
    HV --> WebApp
    Khach --> WebApp
    WebApp --> MySQL
    WebApp --> FileStorage
```
