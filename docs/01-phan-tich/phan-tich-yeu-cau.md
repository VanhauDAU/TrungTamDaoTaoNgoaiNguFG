# 01 — Phân tích Yêu cầu Hệ thống

## 1. Tổng quan bài toán

**Trung Tâm Đào Tạo Ngoại Ngữ Five Genius** cần một hệ thống quản lý toàn diện để:

- Quản lý nhiều cơ sở đào tạo tại nhiều tỉnh/thành
- Quản lý khóa học, lớp học, buổi học, ca học
- Theo dõi học viên, giáo viên, nhân viên
- Xử lý học phí, hóa đơn, phiếu thu
- Tương tác với học viên qua website (đăng ký, thông báo, blog)

---

## 2. Các tác nhân (Actors)

| Tác nhân      | Mô tả                                                      |
| ------------- | ---------------------------------------------------------- |
| **Admin**     | Quản trị toàn hệ thống, có quyền cao nhất                  |
| **Nhân viên** | Quản lý lớp học, tiếp nhận liên hệ, gửi thông báo          |
| **Giáo viên** | Xem lịch dạy, điểm danh, xem danh sách lớp                 |
| **Học viên**  | Đăng ký lớp, xem lịch học, tra cứu hóa đơn, nhận thông báo |
| **Khách**     | Xem khóa học, gửi form tư vấn                              |

---

## 3. Yêu cầu chức năng

### 3.1 Quản lý người dùng

- **UC-01:** Đăng nhập / Đăng xuất
- **UC-02:** Phân nhóm quyền (Admin, Nhân viên, Giáo viên, Học viên)
- **UC-03:** CRUD hồ sơ: học viên, giáo viên, nhân viên
- **UC-04:** Admin cấp/thu hồi quyền, bật/tắt tài khoản

### 3.2 Cơ sở vật chất

- **UC-05:** CRUD cơ sở đào tạo (địa chỉ, Google Maps)
- **UC-06:** CRUD phòng học theo từng cơ sở
- **UC-07:** API lấy danh sách tỉnh/quận/phường

### 3.3 Đào tạo — Học thuật

- **UC-08:** CRUD danh mục khóa học (cây đa cấp)
- **UC-09:** CRUD khóa học (ảnh, mô tả, học phí)
- **UC-10:** CRUD lớp học (gắn khóa, giáo viên, phòng, ca học, cơ sở)
- **UC-11:** Tự động sinh buổi học từ ca học của lớp
- **UC-12:** Điểm danh học viên theo buổi học
- **UC-13:** Đăng ký lớp học (học viên tự đăng ký)
- **UC-14:** Quản lý ca học (giờ bắt đầu/kết thúc, thứ trong tuần)

### 3.4 Tài chính

- **UC-15:** Tự động tạo hóa đơn khi học viên đăng ký lớp
- **UC-16:** Ghi nhận phiếu thu (lần thanh toán)
- **UC-17:** Thống kê công nợ, tình trạng thanh toán

### 3.5 CRM — Tiếp thị

- **UC-18:** Học viên tiềm năng gửi form tư vấn (client)
- **UC-19:** Nhân viên xử lý, phân công, phản hồi liên hệ
- **UC-20:** Lịch sử tương tác với lead (CRM pipeline)

### 3.6 Nội dung

- **UC-21:** CRUD bài viết/blog (rich text, ảnh, phân loại, tag)
- **UC-22:** Phát hành / gỡ xuống bài viết

### 3.7 Thông báo

- **UC-23:** Admin gửi thông báo cho học viên/giáo viên theo nhóm
- **UC-24:** Đính kèm file vào thông báo (nhiều file)
- **UC-25:** Học viên nhận, đọc thông báo realtime

### 3.8 Website Client

- **UC-26:** Hiển thị danh sách khóa học theo danh mục cây
- **UC-27:** Tìm kiếm, lọc, sắp xếp khóa học
- **UC-28:** Học viên xem lịch học, hóa đơn, hồ sơ cá nhân

---

## 4. Yêu cầu phi chức năng

| Yêu cầu         | Chi tiết                                               |
| --------------- | ------------------------------------------------------ |
| **Hiệu năng**   | Trang client tải < 2s; admin dashboard < 1s            |
| **Bảo mật**     | Hash password (bcrypt), CSRF, session cookie           |
| **Phân quyền**  | Middleware `isAdmin`, `auth`; Gate/Policy theo role    |
| **Responsive**  | Bootstrap 5, hỗ trợ mobile trên website client         |
| **SEO**         | Slug, meta title/description, heading hierarchy        |
| **Soft Delete** | Khóa học, bài viết, học viên, liên hệ hỗ trợ thùng rác |

---

## 5. Ràng buộc nghiệp vụ

1. Một lớp học phải thuộc đúng một khóa học và một cơ sở
2. Giáo viên chỉ dạy tại cơ sở mình được phân công
3. Hóa đơn được tạo tự động khi xác nhận đăng ký lớp
4. Danh mục khóa học có cấu trúc cây đệ quy (không giới hạn cấp), không được tạo vòng lặp cha-con
5. Thông báo hỗ trợ gửi theo nhóm: tất cả, học viên, giáo viên, nhân viên, hoặc chọn cá nhân
