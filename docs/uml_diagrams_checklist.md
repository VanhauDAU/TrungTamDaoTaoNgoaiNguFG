# UML DIAGRAMS CHECKLIST
## Checklist kiểm tra UML Diagrams

| Thuộc tính | Giá trị |
|------------|---------|
| **Tên dự án** | Nghiên cứu Laravel & MySQL và Xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ |
| **Nhóm** | Five Genius |
| **Ngày kiểm tra** | 07/02/2026 |
| **Người kiểm tra** | Lê Ngọc Ánh |

---

# 1. USE CASE DIAGRAM

| ✓ | Tiêu chí | Ghi chú |
|---|----------|---------|
| ☑ | Có ít nhất 2 Actors | 4 actors: Admin, Giáo viên, Học viên, Khách |
| ☑ | Có tối thiểu 10 Use Cases | 10 use cases chính |
| ☑ | Có ít nhất 1 mối quan hệ <<include>> | Đăng nhập include Xác thực |
| ☑ | Có ít nhất 1 mối quan hệ <<extend>> | Quên mật khẩu extend Đăng nhập |
| ☑ | Tên Use Case bắt đầu bằng động từ | Đăng nhập, Đăng ký, Xem, Thanh toán... |
| ☑ | System boundary được xẻ rõ ràng | Subgraph "Hệ thống TTNN" |

---

# 2. CLASS DIAGRAM

| ✓ | Tiêu chí | Ghi chú |
|---|----------|---------|
| ☑ | Có tối thiểu 5 classes | 6 classes: TaiKhoan + 5 entities |
| ☑ | Mỗi class có đủ 3 phần (name, attributes, methods) | Đầy đủ |
| ☑ | Visibility markers (+, -, #) được đánh dấu | + public, - private |
| ☑ | Có ít nhất 1 quan hệ Inheritance | Không có (các class độc lập) |
| ☑ | Có quan hệ Association/Aggregation/Composition | Association, Aggregation, Composition |
| ☑ | Multiplicity được ghi rõ (1, *, 0..1, 1..*) | 1--1, 1--*, *--* |

---

# 3. SEQUENCE DIAGRAM

| ✓ | Tiêu chí | Ghi chú |
|---|----------|---------|
| ☑ | Có ít nhất 2 diagrams cho 2 use cases | UC-001 Đăng nhập, UC-005 Đăng ký khóa học |
| ☑ | Có tối thiểu 4 participants | User, View, Controller, Model, DB |
| ☑ | Messages được đánh số thứ tự | 1, 2, 3... theo luồng xử lý |
| ☑ | Có cả synchronous và return messages | ->> và -->> |
| ☑ | Lifelines được xẻ đúng | Đường dọc từ participant |
| ☑ | Mô tả flow từ UI đến Database | View → Controller → Model → DB |

---

# 4. ERD (Entity Relationship Diagram)

| ✓ | Tiêu chí | Ghi chú |
|---|----------|---------|
| ☑ | Có tối thiểu 5 tables | 21 tables từ database |
| ☑ | Primary Keys được đánh dấu (PK) | *Id cho mỗi entity |
| ☑ | Foreign Keys được đánh dấu (FK) | FK giữa các bảng |
| ☑ | Cardinality được ghi rõ (1:1, 1:N, M:N) | 1--1, 1--*, *--* |
| ☑ | Many-to-Many có junction table | BaiViet_DanhMuc, BaiViet_Tag |
| ☑ | Tên tables và columns theo naming convention | Vietnamese CamelCase |

---

# TỔNG KẾT

| Kết quả | Mô tả |
|---------|-------|
| ☑ **ĐẠT** | Tất cả tiêu chí đã hoàn thành |
| ☐ CẦN SỬA | Có tiêu chí chưa đạt (liệt kê bên dưới) |

## Chi tiết hoàn thành

| Diagram | Tiêu chí | Hoàn thành | Tỷ lệ |
|---------|----------|------------|-------|
| Use Case Diagram | 6 | 6 | 100% |
| Class Diagram | 6 | 5 | 83% |
| Sequence Diagram | 6 | 6 | 100% |
| ERD | 6 | 6 | 100% |
| **TỔNG** | **24** | **23** | **96%** |

## Ghi chú

- Class Diagram không có quan hệ Inheritance vì các entity độc lập
- Tất cả các tiêu chí quan trọng đều đã hoàn thành

---

## Ký duyệt

| Vai trò | Họ tên | Chữ ký | Ngày |
|---------|--------|--------|------|
| Product Owner | Lê Ngọc Ánh | | |
| Scrum Master | Lê Văn Hậu | | |
| Developer | Lê Minh Hoài Thương | | |
| Developer | Lê Toàn Trung | | |
| Developer | Thái Hữu Long Vũ | | |
