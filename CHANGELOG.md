# Changelog

Tất cả thay đổi đáng chú ý của dự án sẽ được ghi tại đây.

## [2026-04-15] - Cải thiện module quản lý lớp học: xung đột lịch, ngày kết thúc, bỏ số buổi khỏi form

### Changed

- Bỏ kiểm tra xung đột lịch dạy giáo viên khi thêm/sửa lớp học; chỉ còn kiểm tra xung đột phòng học.
- Bỏ realtime preview xung đột giáo viên trên form thêm/sửa lớp; realtime preview phòng học vẫn hoạt động.
- Trường `ngayKetThuc` giờ là bắt buộc và được nhập trực tiếp trên form thay vì tự đồng bộ từ buổi học cuối.
- Thêm validation server: `ngayKetThuc` không được nhỏ hơn `ngayBatDau` (`after_or_equal`).
- Thêm validation client: JS tự set `min` cho ô ngày kết thúc và auto-correct khi ngày bắt đầu thay đổi.
- Xóa trường `soBuoiDuKien` và `soBuoiCamKet` khỏi form thêm/sửa lớp (dữ liệu cũ trong DB vẫn giữ nguyên).
- Card "Thời gian & Số buổi" đổi thành "Thời gian".
- Kiểm tra xung đột phòng giờ dùng khoảng `ngayBatDau – ngayKetThuc` thay vì `ngayBatDau + soBuoiDuKien`.
- Preview chính sách giá không còn hiển thị "N buổi cam kết" hay "Theo số buổi dự kiến".

### Affected Files

- `app/Services/Admin/KhoaHoc/LopHocService.php`
- `resources/views/admin/lop-hoc/create.blade.php`
- `resources/views/admin/lop-hoc/edit.blade.php`

## [2026-04-12] - Hoàn thiện xuất PDF và gửi email cho hóa đơn, phiếu thu ở admin

### Added

- Thêm service dùng chung `FinanceDocumentService` để render PDF và gửi email cho:
  - hóa đơn
  - phiếu thu
- Thêm mailer `FinanceDocumentMail` và mẫu email đính kèm tài liệu tài chính.
- Thêm 2 mẫu PDF mới:
  - `resources/views/admin/hoa-don/pdf/invoice.blade.php`
  - `resources/views/admin/hoa-don/pdf/receipt.blade.php`
- Thêm route in/email cho admin:
  - `GET /admin/hoa-don/{id}/in`
  - `POST /admin/hoa-don/{id}/gui-email`
  - `GET /admin/hoa-don/phieu-thu/{id}/in`
  - `POST /admin/hoa-don/phieu-thu/{id}/gui-email`
- Thêm test `FinanceDocumentWorkflowTest` cho:
  - admin lập phiếu thu và mở in ngay
  - admin gửi email hóa đơn
  - cổng học viên không expose route in/gửi email tài liệu

### Changed

- Trang chi tiết hóa đơn admin có thêm cụm thao tác `In hóa đơn` và `Gửi email`.
- Form lập phiếu thu ở admin có thêm tùy chọn `Lưu và in phiếu thu`.
- Lịch sử phiếu thu trong chi tiết hóa đơn admin có thêm thao tác in/email trên từng phiếu hợp lệ.
- Trang danh sách hóa đơn admin có thêm nút in nhanh tại từng dòng.
- Cổng học viên giữ phạm vi chỉ-tra-cứu; không hiển thị nút in hoặc gửi email.

### Fixed

- Bịt thiếu hụt nghiệp vụ trước đây khi đã ghi nhận thu tiền nhưng chưa thể xuất ngay phiếu thu cho người nộp.
- Chuẩn hóa luồng phát hành tài liệu tài chính tại khu vực admin thay vì rải logic ở nhiều màn hình.

## [2026-04-02] - Chuẩn hóa upload ảnh dùng chung và áp dụng cho form admin

### Added

- Thêm cấu hình preset upload ảnh dùng chung tại `config/uploads.php`.
- Thêm service `ImageUploadService` để gom validation, lưu file, transform ảnh và trả metadata theo một format thống nhất.
- Thêm API upload ảnh dùng chung `POST /api/uploads/images` qua `ImageUploadController`.
- Thêm Blade component `x-upload.image` và module JS/CSS dùng chung cho toàn dự án.
- Thêm 2 chế độ sử dụng cho component:
  - `instant`: upload AJAX ngay, có progress bar
  - `deferred`: chỉ preview/drag-drop, file được submit cùng form cha
- Thêm test `ReusableImageUploadTest` cho API upload ảnh dùng chung và flow cập nhật avatar học viên.

### Changed

- Luồng upload avatar học viên không còn tự giữ logic validate/lưu ảnh riêng trong `StudentService`; đã chuyển sang dùng `ImageUploadService` với preset `avatar`.
- Upload ảnh inline của bài viết trong TinyMCE được chuyển sang dùng chung backend upload ảnh.
- Form ảnh đại diện bài viết và ảnh khóa học ở admin đã đổi sang `x-upload.image` ở chế độ `deferred`, thay thế preview script gắn cứng tại từng view.
- Tài liệu `README`, `README_vi`, `docs/progress.md` và checklist bảo mật upload được cập nhật để phản ánh kiến trúc upload mới.

### Fixed

- Loại bỏ tình trạng logic upload ảnh bị lặp ở nhiều màn hình Blade.
- Đồng bộ trải nghiệm chọn ảnh, drag-drop, preview và validation frontend giữa profile học viên, bài viết và khóa học.

## [2026-04-02] — Tối ưu giao diện upload avatar & Kiểm định bảo mật upload

### Changed

- **Giao diện profile học viên — khu vực avatar** được thiết kế lại hoàn toàn:
  - Layout 2 cột: cột trái (avatar + nút + progress), cột phải (info + chọn ảnh + guideline + feedback).
  - Ảnh xem trước **thay thế ngay vào chỗ avatar hiện tại** (cùng một `<img>`) thay vì hiển thị ở ô riêng — loại bỏ hiện tượng nhiều avatar xuất hiện cùng lúc.
  - Nút **Xác nhận / Hủy** nằm ngay dưới avatar, ẩn khi chưa chọn ảnh, hiện khi đã chọn.
  - Click vào avatar mở file picker trực tiếp qua `<label for="avatarInput">` bên trong overlay.
  - Class `is-preview` thêm vào circle khi đang xem trước (đổi outline thành dashed teal); Hủy → khôi phục `src` về ảnh gốc.
  - **Thanh tiến trình upload** chuyển sang cột trái (ngay dưới nút Xác nhận/Hủy) để người dùng thấy tiến độ cạnh ảnh.

- **CSS avatar** (`account.css`) được tái cấu trúc:
  - Thêm `@keyframes` cho pulse, shimmer progress, slide-up, spin.
  - `.avatar-progress-wrap` có `width: 100%` để fill toàn cột trái.
  - `.avatar-card-actions` — flex column, `gap: 6px`, animation `fadeSlideUp`.
  - Feedback message có style riêng theo loại (error/success/info) thay vì chỉ thay đổi class màu chữ.
  - Xóa styles cũ của `avatar-review-box`, `avatar-thumb-wrap`, `avatar-review-badge`, `avatar-review-content`.

- **JavaScript** avatar upload được tái cấu trúc:
  - Bỏ `pendingPreview` riêng biệt, thay bằng swap `avatarImg.src = previewUrl` trực tiếp.
  - `resetSelection()` khôi phục `src` và xóa class `is-preview` khi người dùng hủy.
  - Helper `setFeedback()` render HTML với icon Font Awesome theo kiểu (error/success/info).
  - Sau khi upload thành công hiển thị progress 100% trong 600ms rồi mới reset giao diện.

### Added

- Tài liệu bảo mật upload: `docs/05-huong-dan/upload-security-checklist.md`
  - Kiểm định đầy đủ 5 tiêu chí bảo mật upload cho tính năng avatar học viên (kết quả: **5/5 ĐẠT**).
  - Liệt kê các cải tiến tiếp theo cho môi trường production.

### Security (Audit — Không thay đổi code)

Kết quả kiểm tra **upload ảnh đại diện học viên** theo checklist bảo mật:

| # | Tiêu chí | Kết quả |
|---|----------|---------|
| 1 | Kiểm tra MIME type thực | ✅ Đạt — rule `image` + `mimes` dùng `finfo` |
| 2 | Tên file ngẫu nhiên | ✅ Đạt — `store()` sinh UUID v4 tự động |
| 3 | Lưu ngoài webroot | ✅ Đạt — `storage/app/public/`, symlink ra `public/storage` |
| 4 | Giới hạn kích thước & số lượng | ✅ Đạt — 2MB backend + frontend, 1 file/lần |
| 5 | Yêu cầu xác thực | ✅ Đạt — `auth` + `verified.student` + type-check |

**Cải tiến đề xuất (chưa triển khai):**
- Rate limit 5 req/phút cho route upload avatar.
- Rule `dimensions` giới hạn kích thước pixel.
- Resize ảnh về 400×400px dùng `intervention/image`.
- Strip EXIF metadata (GPS, camera info).

## [2026-03-22] - Ổn định phiên đăng nhập khi chuyển portal trong cùng trình duyệt

### Added

- Thêm endpoint `GET /auth/session-status` để giao diện admin/client kiểm tra trạng thái phiên hiện tại theo context `staff|student`.
- Thêm `portal session guard` dùng chung cho layout admin và client để:
  - đồng bộ lại CSRF token khi tab quay lại foreground
  - phát hiện phiên bị thay thế bởi portal khác trong cùng trình duyệt
  - chặn submit logout từ tab stale trước khi văng `419`
- Thêm test `SessionPortalGuardTest` cho luồng mismatch session và redirect mềm.

### Changed

- Middleware `isAdmin` và `verified.student` không còn trả `403` HTML thô khi session đã bị thay thế bởi portal khác; thay vào đó sẽ redirect mềm về portal hiện còn hiệu lực kèm cảnh báo.
- Nút đăng xuất ở layout admin/client giờ xác minh lại session trước khi submit form để tránh dùng CSRF token cũ.

### Fixed

- Sửa lỗi local khi đăng nhập admin ở một tab rồi đăng nhập client ở tab khác khiến trang admin báo không truy cập được nhưng bấm đăng xuất lại bị `419`.

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
