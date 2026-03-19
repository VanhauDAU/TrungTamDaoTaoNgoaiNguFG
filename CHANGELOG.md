# Changelog

Tất cả thay đổi đáng chú ý của dự án sẽ được ghi tại đây.

## [2026-03-19] - Sửa lỗi giao diện Auth và Validate

### Fixed

- **Validate Học viên**: Thêm logic JavaScript chặn nhập ký tự chữ cái trực tiếp vào trường "Số điện thoại" trên form Đăng ký (`register.blade.php`).
- **reCAPTCHA Bug**: Sửa lỗi Backend (trong `RegisterService.php` và `LoginService.php`) khiến bảng reCAPTCHA vẫn hiển thị trên form Đăng nhập và Đăng ký dù hệ thống đã tắt trong tệp cấu hình `.env` (`RECAPTCHA_ENABLED=false`).
- **Clean code**: Dọn dẹp một số dòng code thừa và bổ sung từ khóa ở file ngôn ngữ `vi.json` cũng như trong các middleware `IsAdmin`, `TrackAuthenticatedDeviceSession`.

## [2026-03-16] - Bổ sung Redis cho Auth realtime và rate limit

### Added

- Thêm endpoint `GET /register/check-email` để kiểm tra email đã được sử dụng hay chưa ngay trên form đăng ký.
- Thêm debounce và thông báo trạng thái inline trên ô email của trang `/register`.
- Thêm test `RegisterEmailCheckTest` cho endpoint kiểm tra email.
- Thêm script `composer queue:redis` để chạy worker Redis chuẩn cho `exports,notifications,default`.
- Thêm queued job `maintenance` cho:
  - `invoice:check-overdue`
  - `registration:expire-holds`
- Thêm test `MaintenanceBatchQueueTest` cho luồng dispatch batch maintenance.
- Thêm cache Redis cho các danh sách public:
  - trang danh sách khóa học `/khoa-hoc`
  - danh sách lớp public trong chi tiết khóa học `/khoa-hoc/{slug}`
  - trang blog `/blog`
  - block public ở trang chủ
  - danh sách khóa học ở footer và `register-advice`
- Thêm rate limit dùng Redis cho:
  - `POST /login`
  - `POST /teacher/login`
  - `POST /staff/login`
  - `POST /register`
  - `GET /register/check-email`
- Thêm test `AuthRedisRateLimitTest` cho các limiter của auth.
- Thêm test `PublicContentCacheServiceTest` cho cache public và observer invalidation.
- Bổ sung biến môi trường:
  - `RATE_LIMITER_STORE`
  - `AUTH_LOGIN_RATE_LIMIT_PER_MINUTE`
  - `AUTH_LOGIN_RATE_LIMIT_IP_PER_MINUTE`
  - `AUTH_REGISTER_RATE_LIMIT_PER_MINUTE`
  - `AUTH_REGISTER_RATE_LIMIT_IP_PER_MINUTE`
  - `AUTH_EMAIL_CHECK_RATE_LIMIT_PER_MINUTE`
  - `AUTH_EMAIL_CHECK_RATE_LIMIT_IP_PER_MINUTE`
  - `REGISTER_EMAIL_CHECK_CACHE_STORE`
  - `REGISTER_EMAIL_CHECK_CACHE_TTL`
  - `PUBLIC_LIST_CACHE_STORE`
  - `PUBLIC_LIST_CACHE_TTL`
- Bổ sung dependency `predis/predis` để hỗ trợ Redis client không phụ thuộc `ext-redis`.

### Changed

- Luồng đăng ký học viên vẫn giữ validation cuối cùng bằng MySQL `unique:taikhoan,email`, nhưng kết quả kiểm tra realtime được cache trong Redis với TTL ngắn để giảm query lặp lại.
- Auth giờ chặn request spam sớm ở tầng middleware throttle trước khi đi sâu vào login/register service.
- `composer dev` giờ chạy worker đúng queue `exports,notifications,maintenance,default` thay vì chỉ lắng nghe queue mặc định, để thông báo hàng loạt, export nền và batch maintenance không bị kẹt ở trạng thái chờ xử lý.
- Các danh sách public có versioned cache key riêng; khi `KhoaHoc`, `LopHoc`, `BaiViet` hoặc danh mục liên quan thay đổi, namespace cache public sẽ tự đổi để tránh dữ liệu cũ bám lâu.
- Observer invalidation của cache public bỏ qua update chỉ tăng `luotXem` bài viết để trang blog detail không làm bust Redis liên tục.
- Tài liệu setup được cập nhật để phản ánh yêu cầu runtime PHP `>= 8.3` của dependency hiện tại và lưu ý môi trường XAMPP PHP 8.2 không còn phù hợp để chạy `artisan`/web app.
- Hướng dẫn local được cập nhật theo hướng ưu tiên Redis server + `predis` trước, sau đó mới cân nhắc `phpredis`.

### Fixed

- Thêm fallback an toàn: nếu Redis chưa sẵn sàng hoặc cấu hình sai, backend vẫn query trực tiếp MySQL thay vì làm gãy form đăng ký.

## [2026-03-15] - Hoàn thiện hồ sơ nhân sự, bàn giao tài khoản và tài liệu vận hành

### Added

- Thêm hồ sơ nhân sự chi tiết cho giáo viên và nhân viên sau khi tạo tài khoản.
- Thêm thẻ bàn giao tài khoản hiển thị `username` thật và mật khẩu tạm bằng one-time token.
- Thêm xuất `Phiếu bàn giao tài khoản` PDF và `Hồ sơ nhân sự` PDF bằng DOMPDF.
- Thêm module mẫu quy định nhân sự với snapshot quy định gắn vào hồ sơ.
- Thêm các bảng nhân sự mở rộng:
  - `nhansu_hoso`
  - `nhansu_mau_quydinh`
  - `nhansu_goi_luong`
  - `nhansu_goi_luong_chi_tiet`
  - `nhansu_tai_lieu`
- Thêm cơ chế lưu CV / tài liệu nhân sự private-only, có version và metadata file.
- Thêm tài liệu vận hành mới:
  - hồ sơ nhân sự và bàn giao tài khoản
  - vận hành lương / payroll
  - Figma handoff cho luồng lương

### Changed

- Luồng tạo giáo viên và nhân viên không còn dừng ở danh sách; sau khi tạo sẽ đi vào hồ sơ chi tiết.
- Mật khẩu khởi tạo của nhân sự đổi sang random tạm thời, không dùng giá trị suy đoán được như CCCD.
- Create/Edit giáo viên và nhân viên được chuẩn hóa bằng partial dùng chung để giảm lệch field.
- Phần lương được chốt lại theo mô hình `gói lương` tách biệt với `bảng lương kỳ`.
- README, progress, hướng dẫn vận hành và database docs đã được đồng bộ lại theo các thay đổi mới.

### Fixed

- Hoàn thiện đầy đủ màn sửa giáo viên và màn sửa nhân viên vốn đã có controller nhưng thiếu view.
- Sửa validation cập nhật email / CCCD theo cơ chế ignore bản ghi hiện tại.
- Sửa hành vi cập nhật tài khoản nhân sự để rotate `remember_token` khi đổi mật khẩu hoặc khóa tài khoản.
- Ghi rõ lưu ý import SQL khi dump có orphan record để tránh lỗi foreign key lúc phục hồi dữ liệu.

## [2026-03-15] - Củng cố đăng ký học, giữ chỗ, hóa đơn và phiếu thu

### Added

- Thêm unique index `uq_dangkylophoc_student_class` để chặn trùng đăng ký theo cặp `taiKhoanId + lopHocId`.
- Thêm cột `ngayHetHanGiuCho` cho `dangkylophoc`.
- Thêm command `registration:expire-holds` để tự động hủy giữ chỗ quá hạn chưa phát sinh thu tiền.
- Thêm scheduler:
  - `invoice:check-overdue` chạy hằng ngày
  - `registration:expire-holds` chạy mỗi giờ
- Thêm module admin `/admin/dang-ky` để quản lý đăng ký học:
  - tạo tại quầy
  - xác nhận
  - hủy
  - bảo lưu
  - khôi phục
  - chuyển lớp
- Thêm tài liệu vận hành mới cho luồng đăng ký, thanh toán, hóa đơn và phiếu thu.

### Changed

- Luồng đăng ký lớp của học viên được đưa vào transaction có `lockForUpdate()` để giảm race condition.
- Hệ thống tự đặt `ngayHetHanGiuCho` theo hạn thanh toán học phí gần nhất khi tạo đăng ký.
- `Theo tháng` bị loại khỏi cấu hình runtime mới; hệ thống chỉ còn hỗ trợ `Một lần` và `Theo đợt`.
- Admin sửa hóa đơn giờ luôn `recalculate()` lại hóa đơn và đồng bộ trạng thái đăng ký liên quan.
- `phieuthu.taiKhoanId` được chuẩn hóa theo nghĩa `học viên / người nộp tiền`; `nguoiDuyetId` là nhân sự ghi nhận thu tiền.
- Màn `Phiếu thu tổng hợp` của học viên đổi sang đọc đúng ownership của phiếu thu và hiển thị người ghi nhận.

### Fixed

- Sửa lỗ hổng có thể tạo trùng đăng ký hoặc vượt sĩ số khi nhiều request đăng ký chạy đồng thời.
- Sửa dữ liệu phiếu thu cũ bị gắn sai chủ sở hữu bằng migration backfill từ `hoadon.taiKhoanId`.
- Loại các hóa đơn của đăng ký đã bị hủy giữ chỗ ra khỏi màn công nợ học viên khi chưa phát sinh thu tiền.
- Ghi log rõ hơn cho batch xử lý hóa đơn quá hạn để dễ vận hành.

## [2026-03-15] - Củng cố lớp học, học phí theo lớp và tài liệu vận hành

### Added

- Thêm đổi nhanh trạng thái lớp học bằng AJAX ngay trên trang danh sách lớp.
- Bổ sung ma trận chuyển trạng thái lớp học ở tầng domain để chặn các bước lùi/nhảy trạng thái sai luồng.
- Thêm đồng bộ `ngayKetThuc` của lớp theo buổi học cuối cùng còn hiệu lực.

### Changed

- Form cấu hình chính sách giá lớp được siết lại theo đúng nghiệp vụ:
  - chỉ `Theo đợt` mới được cấu hình `đợt thu`
  - tổng tiền các đợt phải khớp `hocPhiNiemYet`
  - hạn thanh toán phải tăng dần và nằm trong khoảng hiệu lực nếu có
- Trường `ngayKetThuc` của lớp không còn nhập tay trong form tạo/sửa; ngày này được cập nhật từ dữ liệu `buoihoc`.
- Luồng tự sinh buổi học được đổi sang chạy theo `ngayBatDau + lichHoc + soBuoiDuKien`, không phụ thuộc `ngayKetThuc` nhập tay.
- Rà soát và cập nhật tài liệu vận hành để phản ánh đúng:
  - học phí theo lớp
  - snapshot giá khi đăng ký
  - ý nghĩa `hieuLucTu` / `hieuLucDen`
  - giới hạn runtime hiện tại của `lophoc_dotthu`

### Fixed

- Sửa migration `2026_03_14_150000_refactor_class_pricing_to_lophoc_chinhsachgia.php` để kiểu khóa ngoại `lopHocId` khớp schema thực tế của bảng `lophoc`.
- Sửa sinh `maLopHoc` để không đụng unique key khi trong hệ thống đã có lớp bị soft delete.
- Sửa lỗi validation phía client của input hạn thanh toán trong block `đợt thu` không tự clear sau khi người dùng chọn ngày.
- Sửa layout/CSS của khu vực `Kế hoạch thu theo đợt` để khoảng cách, nhóm trường và trạng thái lỗi nhất quán hơn.

## [2026-03-13] - Sửa lỗi sau khi tách service

### Fixed

- Cập nhật namespace service sau khi chuyển sang thư mục `Admin` (ThongBao/Dashboard) để tránh lỗi class không tìm thấy.
- Bổ sung guard cho tài khoản đăng nhập ở `StudentController` và `CourseService` để tránh lỗi null và cảnh báo IDE.
- Siết kiểu đầu vào cho kiểm tra đăng ký lớp học để ổn định static analysis.

## [2026-03-12] - Nâng cấp Auth toàn hệ thống

### Added

- Tách cổng đăng nhập học viên `/login` và cổng đăng nhập nhân sự `/admin/login`.
- Bật xác thực email cho học viên tự đăng ký.
- Thêm đăng nhập Google cho học viên.
- Thêm `Joi` cho lớp xác thực đầu vào phía trình duyệt của các form Auth.
- Bổ sung migration đảm bảo bảng `taikhoan` có cột `remember_token` cho tính năng ghi nhớ đăng nhập.
- Thêm bảng `phien_dang_nhap` để quản lý thiết bị và phiên đăng nhập đang hoạt động.
- Thêm bảng `nhatky_bao_mat` cho audit log nền của đăng ký phiên, thu hồi phiên, logout all devices và remember token rotation.
- Thêm Google reCAPTCHA v3 cho các form public:
    - đăng nhập học viên
    - đăng ký học viên
    - quên mật khẩu
- Thêm middleware `verified.student` để chặn học viên chưa xác thực email khỏi khu vực học viên và các API client cần xác thực.
- Thêm các cột auth mới cho bảng `taikhoan`:
    - `email_verified_at`
    - `auth_provider`
    - `google_id`
    - `google_avatar`
- Thêm bộ tài liệu Auth:
    - quyết định kiến trúc
    - cấu hình môi trường
    - vận hành và kiểm thử

### Changed

- Chuẩn hóa `username` thành mã hệ thống theo role:
    - `HV######` cho học viên
    - `GV######` cho giáo viên
    - `NV######` cho nhân viên
    - `AD######` cho admin
- Luồng tự đăng ký học viên không còn dùng `taiKhoan = email`.
- Luồng admin tạo tài khoản học viên/giáo viên/nhân viên không còn sinh username theo CCCD.
- Cập nhật giao diện login/register/verify để phản ánh luồng xác thực mới.
- Cập nhật link đăng nhập trong layout chung để phân biệt học viên và nhân sự.
- Cập nhật profile/sidebar/header học viên để hiển thị đúng avatar của tài khoản Google và nhãn hình thức đăng nhập.
- Với tài khoản đăng nhập bằng Google, khu vực học viên có thêm nút gửi email `Thiết lập mật khẩu` để người học tự đặt mật khẩu local và dùng song song với Google login.
- Các form Auth và đổi mật khẩu học viên giờ dùng schema `Joi` ở frontend trước khi submit; backend Laravel vẫn giữ validation để bảo vệ phía server.
- Chuẩn hóa phase 1 của tính năng `Ghi nhớ đăng nhập`: login thường giữ checkbox hiện tại, Google login tiếp tục đăng nhập ở chế độ remembered.
- Học viên có trang `Thiết bị đã đăng nhập` để tự xem phiên hiện tại, thu hồi từng thiết bị và đăng xuất khỏi tất cả thiết bị.
- Chính sách khóa đăng nhập được đổi từ cố định 15 phút sang backoff tăng dần theo số lần sai liên tiếp:
    - lần khóa đầu ở lần sai thứ 5: 1 phút
    - lần sai tiếp theo: 5 phút
    - sau đó tăng thêm 5 phút mỗi lần sai tiếp theo

### Fixed

- Sửa lỗi `register.js` 404 trên layout auth do include asset không tồn tại.
- Sửa lỗi reCAPTCHA không gửi `recaptcha_token` vì hidden input nằm ngoài thẻ form.
- Cải thiện chẩn đoán reCAPTCHA ở môi trường local: log `error-codes`, `action`, `score`.
- Sửa hiển thị avatar cho tài khoản đăng nhập Google:
    - không còn render sai kiểu `storage/https://...`
    - fallback đúng về ảnh mặc định khi không có avatar hợp lệ
- Sửa tài liệu/cấu hình local cho `APP_URL`, Gmail SMTP và Mailpit.
- Ghi rõ điều kiện hiển thị nút Google login và nguyên nhân lỗi `redirect_uri_mismatch`.
- Rotate `remember_token` ở mọi luồng đổi/reset mật khẩu để vô hiệu các remembered session cũ sau khi mật khẩu thay đổi.
- Khi thu hồi một thiết bị, hệ thống cũng xoay `remember_token` để cookie ghi nhớ đăng nhập cũ không thể tự phục hồi lại phiên đã bị thu hồi.

### Security

- Khóa social login chỉ cho `role = học viên`.
- Không cho staff dùng Google login để vào khu vực nhân sự.
- Học viên chưa xác thực email không được truy cập khu vực `/hoc-vien` và các API liên quan.
- reCAPTCHA chỉ áp dụng cho luồng public để giảm bot/spam mà không làm nặng luồng staff nội bộ.

### Migration / Deployment Notes

- Cần chạy `php artisan migrate` để bổ sung cột auth mới.
- Cần cấu hình `MAIL_*` để email verification hoạt động.
- Cần cấu hình `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` nếu bật Google login.
- Cần cấu hình `RECAPTCHA_*` nếu bật reCAPTCHA.

### Known Notes

- Migration mới đang đánh dấu `email_verified_at` cho dữ liệu cũ để tránh khóa nhầm tài khoản hiện có.
- Bộ test hiện tại của repo vẫn còn rủi ro do thiếu migration nền cho một số bảng domain như `khoahoc`; phần này độc lập với thay đổi Auth.
