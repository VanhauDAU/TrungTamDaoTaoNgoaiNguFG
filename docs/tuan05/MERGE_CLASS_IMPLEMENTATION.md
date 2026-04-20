# Tài liệu Triển khai và Kiểm thử Chức năng Gộp lớp

Tài liệu này tổng hợp lại các thay đổi hệ thống và hướng dẫn kiểm thử cho chức năng **Gộp lớp học** (Merge Class) đã được triển khai.

## 1. Tổng quan thay đổi

Chức năng cho phép admin gộp một lớp học (lớp nguồn) vào một lớp học khác (lớp đích) một cách thủ công ngay từ trang chi tiết lớp học.

### Danh mục file thay đổi:
- **Contract:** `app/Contracts/Admin/KhoaHoc/LopHocServiceInterface.php`
- **Service:** `app/Services/Admin/KhoaHoc/LopHocService.php`
- **Controller:** `app/Http/Controllers/Admin/KhoaHoc/LopHocController.php`
- **Routes:** `routes/web.php`
- **View:** `resources/views/admin/lop-hoc/show.blade.php`

---

## 2. Quy tắc nghiệp vụ (Business Rules)

### A. Điều kiện của Lớp Nguồn (Lớp muốn bỏ)
Để nút "Gộp lớp" khả dụng, lớp hiện tại phải thỏa mãn:
1. **Trạng thái:** Thuộc nhóm `Sắp mở`, `Đang tuyển sinh` hoặc `Chốt danh sách`.
2. **Sĩ số:** Số học viên đang giữ chỗ phải `< 50%` sĩ số tối đa của lớp.
3. **Thời gian:** Ngày bắt đầu lớp phải nằm trong khoảng từ hôm nay đến `+30 ngày`.
4. **Vận hành:** Chưa có bất kỳ buổi học nào ở trạng thái `Đã hoàn thành`.

### B. Điều kiện tìm kiếm Lớp Đích (Lớp nhận học viên)
Lớp đích chỉ xuất hiện trong danh sách đề xuất nếu:
1. **Cơ sở & Khóa học:** Phải cùng `khoaHocId` và cùng `coSoId`.
2. **Trạng thái:** Thuộc nhóm `Sắp mở`, `Đang tuyển sinh` hoặc `Chốt danh sách`.
3. **Sức chứa:** Sĩ số hiện tại + số học viên sẽ chuyển sang phải `<= Sĩ số tối đa` lớp đích.
4. **Chính sách giá:** Phải **khớp hoàn toàn** với lớp nguồn về:
    - Loại thu (Trọn gói / Theo đợt).
    - Học phí niêm yết.
    - Số buổi cam kết.
    - Chi tiết các đợt thu (thứ tự, số tiền, hạn thanh toán mẫu).

---

## 3. Kịch bản kiểm thử (Test Plan)

### Case 1: Kiểm tra hiển thị nút Gộp lớp (UI Eligibility)
- **Bước:** Chọn một lớp đã kết thúc hoặc đã hủy.
- **Kết quả mong đợi:** Nút "Gộp lớp" bị Disabled, rê chuột vào hiện lý do không đủ điều kiện.
- **Bước:** Chọn một lớp sắp mở, đủ điều kiện.
- **Kết quả mong đợi:** Nút "Gộp lớp" màu cam, bấm vào mở được Modal.

### Case 2: Kiểm tra danh sách lớp đích (Target Matching)
- **Bước:** Mở Modal gộp lớp.
- **Kết quả mong đợi:**
    - Nếu có lớp thỏa mãn các điều kiện ở mục 2B -> Hiển thị danh sách để chọn.
    - Nếu không có lớp nào thỏa mãn -> Hiển thị thông báo "Không tìm thấy lớp đích phù hợp".

### Case 3: Gộp lớp thành công (Happy Path)
- **Chuẩn bị:** Lớp A (nguồn) có 2 học viên, lớp B (đích) còn trống 10 chỗ. Các buổi học lớp A đều là "Sắp diễn ra".
- **Bước:** Thực hiện gộp A vào B.
- **Kết quả mong đợi:**
    - Web redirect sang trang chi tiết lớp B kèm thông báo thành công.
    - Lớp A chuyển sang trạng thái "Đã hủy".
    - Các buổi học của lớp A chuyển sang "Đã hủy".
    - 2 học viên của lớp A hiện đã biến mất khỏi lớp A và xuất hiện trong danh sách học viên lớp B.
    - Kiểm tra hóa đơn của 2 học viên này: Thông tin tiền bạc không đổi, chỉ mã lớp trong đăng ký đã đổi.

### Case 4: Kiểm tra ràng buộc sức chứa (Capacity Check)
- **Chuẩn bị:** Lớp A có 5 học viên. Lớp B chỉ còn dư 3 chỗ trống.
- **Kết quả mong đợi:** Lớp B không xuất hiện trong danh sách lớp đích của lớp A trong Modal.

### Case 5: Kiểm tra an toàn Transaction (Concurrent Merge)
- **Bước:** Mở 2 trình duyệt cùng thao tác gộp 1 lớp vào 2 lớp đích khác nhau cùng lúc.
- **Kết quả mong đợi:** Chỉ 1 lệnh thành công, lệnh còn lại sẽ báo lỗi "Lớp nguồn không đủ điều kiện" (do lệnh 1 đã chuyển nó sang trạng thái Đã hủy).

---

## 4. Lưu ý kỹ thuật
- Toàn bộ quá trình gộp được bao bọc trong `DB::transaction` với mức độ khóa `lockForUpdate()` trên cả lớp nguồn và lớp đích để tránh thất thoát học viên hoặc gộp quá sức chứa trong môi trường đa người dùng.
- Hệ thống **không chuyển** các đăng ký ở trạng thái `Bảo lưu`, `Đã hủy` hoặc `Hoàn thành` để giữ đúng lịch sử truy vết cho lớp cũ.
