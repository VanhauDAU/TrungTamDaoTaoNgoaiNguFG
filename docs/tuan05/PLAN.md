# Kế hoạch triển khai chức năng gộp lớp

## Tóm tắt
Triển khai chức năng gộp lớp thủ công ngay tại trang chi tiết lớp học (`show`) cho admin. V1 dùng server-rendered modal, không thêm AJAX endpoint riêng: dữ liệu ứng viên gộp được tính sẵn trong `getDetail()`, còn thao tác gộp dùng 1 route mutate duy nhất.

Luồng chuẩn:
1. Mở trang chi tiết lớp nguồn.
2. Hệ thống đánh giá lớp nguồn có đủ điều kiện gộp hay không.
3. Nếu đủ điều kiện, hiển thị nút `Gộp lớp` và danh sách lớp đích tương thích.
4. Admin chọn lớp đích và xác nhận.
5. Hệ thống chạy transaction: khóa dữ liệu, chuyển đăng ký hiệu lực sang lớp đích, hủy buổi học chưa diễn ra của lớp nguồn, cập nhật lớp nguồn sang `Đã hủy`, giữ nguyên dữ liệu lịch sử.

## Thay đổi chính

### 1. Service và contract
Trong `LopHocServiceInterface`, thêm 1 method mutate:
- `mergeClass(string $sourceSlug, int $targetLopHocId): array`

`getDetail()` được mở rộng để trả thêm context cho UI:
- `mergeEligible`: bool
- `mergeBlockers`: danh sách lý do lớp nguồn chưa được phép gộp
- `mergeCandidates`: danh sách lớp đích hợp lệ, kèm thông tin sức chứa, ngày bắt đầu, sĩ số hiện tại
- `mergeStats`: dữ liệu tóm tắt phục vụ modal như số đăng ký sẽ chuyển, số buổi sẽ hủy

Trong `LopHocService`, tách helper nội bộ rõ trách nhiệm:
- `evaluateMergeSourceEligibility(LopHoc $source): array`
- `findMergeCandidates(LopHoc $source): Collection`
- `assertTargetEligibleForMerge(LopHoc $source, LopHoc $target, int $transferCount): void`
- `countBlockingRegistrations(LopHoc $class): int`
- `hasEquivalentPricingPolicy(LopHoc $source, LopHoc $target): bool`

Không thêm migration, không đổi schema.

### 2. Quy tắc nghiệp vụ được khóa cứng
Lớp nguồn chỉ được gộp khi:
- `trangThai` thuộc `Sắp mở`, `Đang tuyển sinh`, `Chốt danh sách`
- số đăng ký hiệu lực `< 50%` `soHocVienToiDa`
- `ngayBatDau` trong khoảng từ hôm nay đến `+30 ngày`
- không bị soft delete
- có `soHocVienToiDa > 0`
- không có buổi học đã hoàn thành hoặc đang diễn ra; nếu phát hiện dữ liệu lệch trạng thái thực tế thì chặn merge và báo lỗi

Đăng ký được chuyển:
- dùng đúng định nghĩa `đang giữ chỗ` hiện có của hệ thống: `scopeBlockingSeat()`
- tức là chuyển các trạng thái `Chờ thanh toán`, `Đã xác nhận`, `Đang học`, `Tạm dừng do nợ học phí`
- không chuyển `Bảo lưu`, `Hoàn thành`, `Đã hủy`

Lớp đích chỉ hợp lệ khi:
- khác lớp nguồn
- cùng `khoaHocId`
- cùng `coSoId`
- `trangThai` thuộc `Sắp mở`, `Đang tuyển sinh`, `Chốt danh sách`
- không bị soft delete
- còn đủ chỗ: `currentBlockingSeats + transferCount <= soHocVienToiDa`
- có chính sách giá tương thích với lớp nguồn

Quy tắc tương thích chính sách giá:
- bắt buộc trùng về dữ liệu tính tiền chính: `loaiThu`, `hocPhiNiemYet`, `soBuoiCamKetHieuDung`
- nếu là thu theo đợt, active `dotThus` phải trùng theo bộ: `thuTu`, `soTien`, `hanThanhToan`, `batBuoc`
- không so khớp `ghiChuChinhSach`
- không khóa theo `phuPhis` ở v1; phụ phí snapshot/hóa đơn cũ được giữ nguyên như dữ liệu lịch sử

### 3. Luồng merge trong transaction
`mergeClass()` dùng `DB::transaction(..., 3)` và `lockForUpdate()` cho:
- lớp nguồn
- lớp đích
- toàn bộ đăng ký hiệu lực của lớp nguồn

Trình tự xử lý:
1. Nạp lại và lock lớp nguồn, lớp đích.
2. Re-check toàn bộ điều kiện lớp nguồn và lớp đích trong transaction.
3. Lấy danh sách đăng ký cần chuyển bằng `blockingSeat()`.
4. Update `lopHocId` của các đăng ký này sang lớp đích.
5. Không update các trường snapshot và tài chính lịch sử:
   - giữ nguyên `lopHocChinhSachGiaId`
   - giữ nguyên `lopHocDotThuId`
   - giữ nguyên `lopHocPhuPhiId`
   - giữ nguyên hóa đơn, phiếu thu, snapshot học phí/phụ phí
6. Hủy các buổi học chưa diễn ra của lớp nguồn bằng cách update `trangThai = BuoiHoc::TRANG_THAI_DA_HUY` cho các buổi còn ở trạng thái `Sắp diễn ra` hoặc `Đổi lịch`.
7. Cập nhật lớp nguồn `trangThai = LopHoc::TRANG_THAI_DA_HUY`.
8. Không gọi `syncRegistrationStatuses($source)` để tránh vô tình đổi `Bảo lưu` còn lại thành `Đã hủy`.
9. Có thể gọi `syncRegistrationStatuses($target)` sau khi chuyển; với lớp đích pre-start thì method này không đổi dữ liệu nhưng vẫn an toàn.
10. Trả về payload kết quả gồm lớp nguồn, lớp đích, số đăng ký đã chuyển, số buổi đã hủy.

### 4. Controller, route, view
Controller:
- thêm action `merge(Request $request, string $slug)`
- validate request tối thiểu:
  - `targetLopHocId`: required|integer|exists:lophoc,lopHocId
- gọi service, redirect lại trang `show` của lớp đích hoặc lớp nguồn tùy UX
- flash message nên nêu rõ: tên lớp nguồn, tên lớp đích, số đăng ký đã chuyển, số buổi đã hủy

Route:
- thêm 1 route mutate:
  - `PATCH /admin/lop-hoc/{slug}/gop`
  - name: `admin.lop-hoc.merge`

View `resources/views/admin/lop-hoc/show.blade.php`:
- thêm nút `Gộp lớp` ở hero action
- nếu `mergeEligible = false`, nút disabled và hiển thị lý do không đủ điều kiện
- modal gồm:
  - thông tin lớp nguồn
  - số đăng ký sẽ chuyển
  - số chỗ còn thiếu/dư của từng lớp đích
  - select hoặc radio list lớp đích
  - cảnh báo xác nhận không hoàn tác
- nếu không có `mergeCandidates`, modal vẫn mở được nhưng ở trạng thái read-only với thông báo “không có lớp đích phù hợp”
- nếu submit lỗi validate/business, trang quay lại `show` và modal tự mở lại theo `old()`/session error

## Kiểm thử và tiêu chí chấp nhận
Các case cần test:
- lớp nguồn sai trạng thái, quá 30 ngày, đủ 50% sĩ số trở lên, không có `soHocVienToiDa`, đã có buổi hoàn thành, bị soft delete
- lớp đích khác khóa học, khác cơ sở, hết chỗ, sai trạng thái, chính sách giá không tương thích
- happy path với nhiều đăng ký `blockingSeat()`: tất cả được chuyển đúng `lopHocId`
- các đăng ký `Bảo lưu`, `Hoàn thành`, `Đã hủy` không bị chuyển
- buổi học lớp nguồn ở trạng thái `Sắp diễn ra`/`Đổi lịch` bị chuyển sang `Đã hủy`
- lớp nguồn sau merge có `trangThai = Đã hủy`
- snapshot học phí, hóa đơn, phiếu thu, snapshot phụ phí không bị sửa
- concurrent merge: 2 admin thao tác cùng lúc, chỉ 1 transaction thành công
- view `show` hiển thị đúng nút, modal, thông báo lỗi và flash success

Tiêu chí chấp nhận:
- không có đăng ký hiệu lực nào còn sót lại ở lớp nguồn sau merge
- không vượt sĩ số lớp đích
- không mất dữ liệu lịch sử
- không phát sinh sửa dữ liệu tài chính ngoài `lopHocId` của đăng ký

## Giả định đã khóa
- V1 chỉ hỗ trợ gộp thủ công từ trang chi tiết lớp, không làm batch job và không thêm màn hình danh sách riêng.
- `Đăng ký active` được hiểu là đúng theo `scopeBlockingSeat()`.
- Dữ liệu tài chính/snapshot là lịch sử và được giữ nguyên; điều kiện “trùng chính sách giá” được dùng để bảo đảm tính nhất quán khi chỉ đổi `lopHocId`.
- `phuPhis` không được dùng làm tiêu chí chặn gộp ở v1.
- Sau khi gộp, các đăng ký không thuộc diện chuyển vẫn ở lớp nguồn để phục vụ truy vết lịch sử.
