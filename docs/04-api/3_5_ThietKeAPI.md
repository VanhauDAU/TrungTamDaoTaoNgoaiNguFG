# 3.5. Thiết kế API / Endpoints

Do dự án ứng dụng mô hình kết hợp (**Server-Side Rendering** bằng Blade và **Client-Side Requests** qua AJAX/Fetch), hệ thống giao tiếp thông qua hệ thống định tuyến (Route System) thay cho mô hình API thuần túy. 

Dưới đây là danh sách trích xuất các Endpoints (API và Web Routes) trọng yếu nhất dùng để xử lý dữ liệu hệ thống:

### 3.5.1. Nhóm Xác nhận & Định danh (Auth)
*Thực hiện các phiên đăng nhập, tạo tài khoản và xác thực cho từng đối tượng.*

| Method | Endpoint | Mô tả | Auth (Middlewares) |
| :--- | :--- | :--- | :--- |
| **POST** | `/register` | Đăng ký tài khoản học viên | Public / Guest |
| **POST** | `/login` | Đăng nhập dành cho Học viên | Public / Guest |
| **POST** | `/admin/login` | Cổng đăng nhập cho Quản trị viên | Public / Guest |
| **POST** | `/teacher/login` | Cổng đăng nhập cho Giảng viên | Public / Guest |
| **GET** | `/auth/google/redirect` | Yêu cầu URL xác thực SSO qua Google | Public |

<br/>

### 3.5.2. Nhóm Giao tiếp API Dữ liệu chung (Public & AJAX API)
*Xử lý các request truy xuất thông tin không mật, cần độ trễ thấp để render giao diện động.*

| Method | Endpoint | Mô tả | Auth (Middlewares) |
| :--- | :--- | :--- | :--- |
| **GET** | `/api/co-so` | Lấy danh sách toàn bộ các cơ sở đào tạo | Public |
| **GET** | `/api/phuong-xa/{maTinh}` | Lấy danh sách phường xã theo tỉnh thành | Public |
| **GET** | `/api/phong-hoc/{coSoId}` | Truy vấn phòng học của một cơ sở (đổ form) | Auth (Admin) |
| **GET** | `/api/giao-vien/{coSoId}` | Truy vấn danh sách giảng viên trực thuộc cơ sở | Auth (Admin) |
| **POST** | `/api/uploads/images` | Upload ảnh nhúng vào bài viết/bình luận | Auth (User) |

<br/>

### 3.5.3. Nhóm Endpoint Học viên (Student Zone)
*Vùng xử lý nghiệp vụ dành riêng cho học viên đã chứng thực tham gia.*

| Method | Endpoint | Mô tả | Auth (Middlewares) |
| :--- | :--- | :--- | :--- |
| **POST** | `/hoc-vien` | Cập nhật hồ sơ cá nhân | Auth (Sinh viên) |
| **POST** | `/hoc-vien/anh-dai-dien` | Upload ảnh thẻ (Rate limit: 5 req/phút) | Auth (Sinh viên) |
| **GET** | `/api/chat/poll` | Polling lấy tin nhắn chat realtime | Auth (Sinh viên) |
| **POST** | `/api/chat/messages` | Gửi gói tin nhắn vào phòng học (Room) | Auth (Sinh viên) |
| **GET** | `/api/thong-bao/chua-doc` | Đếm thông báo mới từ hệ thống chuông | Auth (Sinh viên) |
| **POST** | `/lop-hoc/{slug}/{id}/xac-nhan-dang-ky` | Tiến hành ghi danh cứng vào lớp | Auth (Sinh viên) |

<br/>

### 3.5.4. Nhóm Endpoint Quản trị Hệ thống (Admin & Teacher)
*Xử lý tác vụ quản lý CRUD (Lưu ý: Hầu hết tương tác bằng Form Request Data).*

| Method | Endpoint | Mô tả | Auth (Middlewares) |
| :--- | :--- | :--- | :--- |
| **POST** | `/admin/khoa-hoc` | Lưu mới thông tin và chương trình học | Auth (Admin) |
| **PUT** | `/admin/lop-hoc/{slug}` | Cập nhật chính sách học phí, thời biểu lớp | Auth (Admin) |
| **POST** | `/admin/buoi-hoc/tu-dong-tao/{id}` | Sinh tự động chuỗi buổi học dựa trên lịch | Auth (Admin) |
| **POST** | `/admin/hoc-vien/tra-cuu-cccd` | Proxy truy xuất căn cước công dân | Auth (Admin) |
| **POST** | `/admin/hoa-don/{id}/phieu-thu` | Trút phiếu thu (xác nhận ghi nhận tiền mặt) | Auth (Admin) |
| **DELETE** | `/admin/lien-he/bulk/xoa` | Xóa hàng loạt dữ liệu liên hệ (Bulk Action) | Auth (Admin) |
| **GET** | `/admin/api/tags` | Auto-complete fetch từ khóa Bài viết (Tags) | Auth (Admin) |
| **GET** | `/admin/api/thong-bao/nguoi-nhan` | Render dropdown danh sách gửi thông báo | Auth (Admin) |
