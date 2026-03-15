# 05C - Hồ sơ nhân sự, bàn giao tài khoản và gói lương

## 1. Mục tiêu

Tài liệu này chốt luồng vận hành mới cho giáo viên và nhân viên sau các thay đổi ngày `2026-03-15`.

Phạm vi gồm:

- tạo tài khoản nhân sự
- hiển thị thông tin bàn giao thật thay vì placeholder `###`
- hồ sơ nhân sự chi tiết
- xuất PDF bàn giao tài khoản và PDF hồ sơ
- quản lý gói lương hiện hành
- lưu CV / tài liệu nhân sự theo cơ chế private + versioning
- hoàn thiện màn sửa giáo viên và nhân viên

## 2. Kết quả đã triển khai

### 2.1 Tài khoản nhân sự

- Khi tạo giáo viên hoặc nhân viên, hệ thống sinh `username` hệ thống theo role.
- Mật khẩu khởi tạo là mật khẩu tạm ngẫu nhiên, không dùng CCCD.
- Sau khi lưu thành công, hệ thống điều hướng sang trang hồ sơ nhân sự thay vì chỉ quay lại danh sách.
- Trang hồ sơ hiển thị thẻ bàn giao tài khoản có:
  - username thật
  - mật khẩu tạm
  - thời gian hiệu lực
  - nút copy
  - nút tải PDF bàn giao
- Mật khẩu tạm chỉ hiển thị một lần qua one-time token ngắn hạn.

### 2.2 Hồ sơ nhân sự

- Cả giáo viên và nhân viên đều có màn:
  - `create`
  - `edit`
  - `show`
- Form dùng partial chung để tránh lệch field giữa create và edit.
- `username` chỉ đọc, không cho chỉnh sửa.
- Màn sửa đã hoàn thiện đầy đủ cho:
  - email
  - trạng thái hoạt động / khóa
  - mật khẩu mới tùy chọn
  - thông tin cá nhân
  - thông tin nhân sự
  - cơ sở làm việc
  - ghi chú nội bộ

### 2.3 PDF

- Hệ thống xuất được 2 loại PDF:
  - `Phiếu bàn giao tài khoản`
  - `Hồ sơ nhân sự`
- `Phiếu bàn giao tài khoản` có thông tin tài khoản tạm để in và ký nhận nội bộ.
- `Hồ sơ nhân sự` không chứa mật khẩu, chỉ chứa dữ liệu hồ sơ, gói lương hiện hành và snapshot quy định.

### 2.4 Gói lương

- Mỗi nhân sự có thể có nhiều gói lương theo lịch sử hiệu lực.
- Chỉ có 1 gói lương active tại một thời điểm.
- Hệ thống hỗ trợ:
  - `MONTHLY`
  - `HOURLY`
  - `PER_SESSION`
  - `FIXED_ALLOWANCE`
- Chi tiết gói lương tách thành các dòng:
  - phụ cấp
  - khấu trừ tham chiếu
  - thưởng cố định
  - ghi chú khác

### 2.5 Tài liệu nhân sự

- CV và hồ sơ đính kèm được lưu private, không public URL trực tiếp.
- Tài liệu được versioned, thay file mới không ghi đè file cũ.
- Hỗ trợ phân loại:
  - `CV`
  - `IDENTITY`
  - `DEGREE`
  - `CERTIFICATE`
  - `CONTRACT`
  - `DECISION`
  - `OTHER`

## 3. Luồng vận hành chuẩn

### 3.1 Tạo giáo viên / nhân viên

1. Admin vào màn tạo giáo viên hoặc nhân viên.
2. Nhập thông tin cá nhân và nhân sự.
3. Chọn mẫu quy định áp dụng.
4. Khởi tạo gói lương đầu tiên.
5. Upload CV nếu có.
6. Lưu hồ sơ.
7. Hệ thống sinh tài khoản, mật khẩu tạm và điều hướng sang hồ sơ chi tiết.
8. Admin tải hoặc in phiếu bàn giao tài khoản nếu cần.

### 3.2 Bàn giao tài khoản

1. Ở lần mở đầu tiên sau khi tạo, thẻ bàn giao hiển thị username và mật khẩu tạm.
2. Admin có thể:
  - copy nhanh
  - in PDF
  - giao trực tiếp cho giáo viên / nhân viên
3. Sau khi token hết hạn, hệ thống không hiển thị lại mật khẩu cũ.
4. Nếu cần cấp lại, dùng luồng reset mật khẩu.

### 3.3 Cập nhật hồ sơ

1. Vào màn `Sửa`.
2. Cập nhật các trường được phép.
3. Nếu bỏ trống mật khẩu thì không đổi mật khẩu.
4. Nếu đổi mật khẩu hoặc khóa tài khoản, hệ thống rotate `remember_token`.
5. Sau khi lưu, quay lại danh sách theo flow hiện tại của controller.

### 3.4 Quản lý tài liệu

1. Vào hồ sơ nhân sự.
2. Tải lên CV hoặc giấy tờ mới.
3. Hệ thống ghi metadata file, checksum, người tải lên, phiên bản.
4. Nếu tải lại cùng loại tài liệu, hệ thống archive bản active cũ và tạo version mới.
5. Download chỉ đi qua route có phân quyền.

### 3.5 Quản lý gói lương

1. Vào hồ sơ nhân sự.
2. Xem gói lương active hiện hành.
3. Tạo gói mới khi có quyết định điều chỉnh lương.
4. Hệ thống đóng gói cũ bằng `hieuLucDen` và kích hoạt gói mới.
5. Hồ sơ PDF luôn đọc gói lương active mới nhất.

## 4. Dữ liệu và bảng liên quan

Các bảng mới / trọng yếu cho module này:

- `nhansu_hoso`
- `nhansu_mau_quydinh`
- `nhansu_goi_luong`
- `nhansu_goi_luong_chi_tiet`
- `nhansu_tai_lieu`

Backfill đã được bổ sung để:

- tạo hồ sơ mặc định cho dữ liệu nhân sự cũ
- tạo gói lương đầu tiên từ `nhansu.luongCoBan` nếu dữ liệu cũ có lương
- snapshot quy định mặc định ở mức an toàn

## 5. Quy tắc nghiệp vụ cần nhớ

- Không hiển thị mật khẩu cũ sau khi hết one-time token.
- Không cho sửa `username`.
- Không cho nhiều hơn 1 gói lương active tại cùng một thời điểm.
- Chỉnh sửa mẫu quy định không tự làm thay đổi snapshot quy định của hồ sơ cũ.
- Tài liệu nhân sự phải lưu private.
- Mọi thao tác tải file phải qua permission phù hợp.

## 6. Quyền và phân quyền

Module này hiện dùng 3 nhóm quyền:

- `giao_vien`
- `nhan_vien`
- `nhan_su`

Quy tắc áp dụng:

- `giao_vien`: quản lý CRUD giáo viên
- `nhan_vien`: quản lý CRUD nhân viên
- `nhan_su`: quản lý mẫu quy định, xuất hồ sơ PDF, tải tài liệu nhân sự, gói lương

## 7. Tài liệu đi kèm

- Vận hành lương và payroll: `docs/05-huong-dan/luong-nhan-su-va-payroll.md`
- Handoff Figma luồng lương: `docs/05-huong-dan/figma-luong-handoff.md`
- Wireframe tham chiếu cho designer: `docs/05-huong-dan/figma-luong-wireframe.html`

## 8. Kiểm thử đã bổ sung

Đã thêm feature test cho các tình huống chính:

- mở được màn sửa giáo viên và nhân viên
- tạo giáo viên redirect sang hồ sơ chi tiết
- hiển thị thông tin bàn giao bằng token một lần
- tải PDF bàn giao
- cập nhật không đổi mật khẩu nếu để trống
- cập nhật có đổi mật khẩu và khóa tài khoản thì xoay `remember_token`
- validate email / CCCD trùng
- upload tài liệu và tăng version đúng cách

File test chính:

- `tests/Feature/NhanSuWorkflowTest.php`

## 9. Ghi chú vận hành

- Nếu môi trường chưa cài PDF engine, cần giữ `barryvdh/laravel-dompdf`.
- Nếu import dữ liệu cũ, cần kiểm tra quan hệ `taikhoan -> nhansu -> nhansu_hoso / nhansu_goi_luong`.
- Không nên import dump SQL có dữ liệu orphan rồi mới bật khóa ngoại; cần làm sạch quan hệ cha-con trước khi import.
