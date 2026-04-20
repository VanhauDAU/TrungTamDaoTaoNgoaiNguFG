# BÁO CÁO ĐÁNH GIÁ SPRINT 2
*(Sprint Review Report)*

| Tên dự án | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ | Sprint số | 2 |
| :--- | :--- | :--- | :--- |
| Nhóm | FiveGenius | Ngày báo cáo | 09/04/2026 |

---

## 1. MỤC TIÊU SPRINT 2

Hoàn thiện hệ thống **Quản lý người dùng** (CRUD tài khoản, hồ sơ người dùng, khóa/kích hoạt tài khoản) và xây dựng module **Khóa học** hoàn chỉnh (quản lý khóa học, phân loại danh mục, hiển thị danh sách & chi tiết khóa học phía Client với SEO slug).

---

## 2. KẾT QUẢ THỰC HIỆN CÔNG VIỆC (ĐỐI CHIẾU THEO KẾ HOẠCH)

| User Story / Công việc | Trạng thái | Demo? | Ghi chú / Chi tiết đã đạt được |
| :--- | :---: | :---: | :--- |
| **I. Quản lý người dùng** | | | |
| 1. Quản lý tài khoản (CRUD) cho Admin, Giáo viên, Nhân viên, Học viên | Hoàn thành | ☑ | Đã hoàn thiện tính năng quản lý tài khoản. Hệ thống quản lý Nhân viên & Giáo viên chuyên sâu đã được tích hợp (bao gồm cả tính năng export tài khoản). |
| 2. Quản lý hồ sơ người dùng (xem/cập nhật thông tin, ảnh đại diện, mật khẩu) | Hoàn thành | ☑ | Áp dụng đúng Joi Validation. Hoàn thành chức năng cập nhật Avatar cho Role Học viên/Giáo viên thông qua một **Upload Component dùng chung**. |
| 3. Khóa / kích hoạt tài khoản | Hoàn thành | ☑ | Đã xử lý khóa/kích hoạt và rotate trạng thái phiên đăng nhập portal an toàn. |
| **II. Khóa học** | | | |
| 4. Quản lý khóa học (Admin CRUD, soft delete) | Hoàn thành | ☑ | Đã thực hiện CRUD với giao diện đầy đủ. Xử lý triệt để các bug sau khi tích test. |
| 5. Phân loại danh mục khóa học (đệ quy vô hạn cấp) | Hoàn thành | ☑ | Xử lý tốt đệ quy tuyến tính, cấu hình được hệ thống cây thư mục khoa học. |
| 6. Hiển thị danh sách khóa học (Client) qua URL `/khoa-hoc` | Hoàn thành | ☑ | Giao diện đã có, phân trang và truy vấn dữ liệu hoạt động ổn định. Gắn link liên kết từ trang chủ chính xác. |
| 7. Chi tiết khóa học (Client) qua URL `/khoa-hoc/{slug}` | Hoàn thành | ☑ | Trang hiển thị đúng khóa học dựa trên SEO Slug. |

*(*) Ngoài kế hoạch (Vượt scope Sprint 2):* Nhóm đã làm thêm và merge thành công các luồng:
- Mở rộng ứng dụng thư viện bắt lỗi **Joi** (Đưa vào bắt lỗi Login/Register, Thêm phòng học,...).
- Tối ưu mạnh về code tái sử dụng: Hệ thống **Upload Component dùng chung** cho toàn bộ back-office.
- Xây dựng sớm tính năng **Blog / Bài viết** (Admin & Client) mặc dù chưa có trong kế hoạch ban đầu.

---

## 3. BÀI HỌC KINH NGHIỆM

### 😊 Điều làm tốt
- Hoàn thành đủ **33 Story Points** theo dự tính của Sprint 2 Planning cho phần Khóa học và Quản lý người dùng (Tài khoản/Avatar/Hồ sơ).
- Thay vì code nhiều lần các form xử lý ảnh, nhóm đã tư duy tái sử dụng bằng cách tạo ra **Upload Component** (giúp đồng bộ file sau này).
- Tích cực rà soát lỗi liên tục và hoàn thiện UI ngay khi build các tính năng ở Client-side.

### 😞 Điều cần cải thiện
- Quá trình test các luồng Joi validation và fix chức năng Upload Ảnh ở các phân quyền khác nhau (Học viên/Teacher) mất khá nhiều công fix bug sát ngày review.
- Việc merge thêm các tính năng ngoài kế hoạch (như Blog) vào nhánh Dev chứng tỏ tốc độ tốt nhưng cần phải quản lý sát phạm vi để tránh chồng chéo khi tích hợp mã nguồn chính. Ở Sprint sau cần ghi nhận các feature ngoài luồng vào Backlog cụ thể.

---

## 4. KẾ HOẠCH CHO SPRINT TIẾP THEO

1. Bổ sung các chuẩn SEO kỹ thuật nâng cao (Schema Markup `application/ld+json`, Meta Tags đầy đủ) cho trang Khóa học và Blog.
2. Vận hành chính thức module Blog/Bài Viết (Viết feature test và kiểm thử diện rộng).
3. Phát triển tiếp các tính năng nghiệp vụ Lớp học, Lịch học, Buổi học, và luồng thanh toán / hóa đơn sinh viên.

---

## 5. ĐÁNH GIÁ TỔNG THỂ

**Mục tiêu Sprint:** ☑ Đạt hoàn toàn (100% Mục tiêu) &nbsp;&nbsp; ☐ Đạt một phần &nbsp;&nbsp; ☐ Không đạt

**Nhận định:** Nhóm đã đáp ứng được sự mong đợi từ Kế hoạch Sprint 2 bằng việc deliver đầy đủ CRUD Khóa Học và Quản lý Hệ thống Nhân sự - Người dùng. Không chỉ tuân thủ Checklist, các feature được push lên còn mang tính tối ưu hệ thống tốt (Upload Component, Joi Validation). Sprint khép lại với kết quả tích cực, các core feature sẵn sàng làm nền tảng cho Sprint sau.
