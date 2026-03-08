# Trạng Thái Model

Tài liệu này tổng hợp trạng thái hiện tại của các model đang dùng trong hệ thống, dựa trên code hiện hành. Với các model còn thiếu state machine rõ ràng, tài liệu cũng ghi nhận khoảng trống và đề xuất chuẩn hóa.

Nguồn tham chiếu chính:

- `app/Models/Education/LopHoc.php`
- `app/Models/Education/DangKyLopHoc.php`
- `app/Models/Education/BuoiHoc.php`
- `app/Models/Interaction/LienHe.php`
- `app/Models/Finance/HoaDon.php`
- `app/Models/Finance/PhieuThu.php`

## LopHoc

### Mục đích

`LopHoc.trangThai` là state machine vòng đời của một lớp học.

### Trạng thái hiện tại

| Giá trị | Mã trạng thái | Nhãn hiển thị | Ý nghĩa |
| --- | --- | --- | --- |
| `0` | `SAP_MO` | Sắp mở | Lớp đã được tạo nhưng chưa mở tuyển sinh. |
| `1` | `DANG_TUYEN_SINH` | Đang tuyển sinh | Lớp đang nhận đăng ký. |
| `2` | `CHOT_DANH_SACH` | Chốt danh sách | Đã ngừng tuyển sinh, chuẩn bị khai giảng. |
| `3` | `DA_HUY` | Đã hủy | Lớp bị hủy, không tiếp tục vận hành. |
| `4` | `DANG_HOC` | Đang học | Lớp đang diễn ra. |
| `5` | `DA_KET_THUC` | Đã kết thúc | Lớp đã học xong. |

### Transition đề xuất chuẩn

Luồng chính:

`SAP_MO -> DANG_TUYEN_SINH -> CHOT_DANH_SACH -> DANG_HOC -> DA_KET_THUC`

Luồng ngoại lệ:

- `SAP_MO -> DA_HUY`
- `DANG_TUYEN_SINH -> DA_HUY`
- `CHOT_DANH_SACH -> DA_HUY`
- `DANG_HOC -> DA_HUY`

### Quy tắc nghiệp vụ hiện đang gắn với trạng thái

- Chỉ trạng thái `DANG_TUYEN_SINH` được mở đăng ký.
- `CHOT_DANH_SACH` và `DANG_HOC` cho phép học viên vào room chat.
- Chỉ `DANG_HOC` cho phép học viên gửi tin nhắn và học lịch chính thức.
- `DA_KET_THUC` là trạng thái kết vòng đời lớp.

### Cải tiến nên làm thêm

- Không cho cập nhật trạng thái tự do từ form; mọi chuyển trạng thái nên đi qua service hoặc action có kiểm tra guard.
- Bổ sung log chuyển trạng thái: ai đổi, đổi lúc nào, lý do gì.
- Nên có thêm các mốc thời gian như `moTuyenSinhLuc`, `chotDanhSachLuc`, `batDauHocLuc`, `ketThucLuc`, `huyLuc`.

## DangKyLopHoc

### Mục đích

`DangKyLopHoc.trangThai` là state machine vòng đời của một học viên trong một lớp cụ thể.

### Trạng thái hiện tại

| Giá trị | Mã trạng thái | Nhãn hiển thị | Ý nghĩa |
| --- | --- | --- | --- |
| `0` | `CHO_THANH_TOAN` | Chờ thanh toán | Đã tạo đăng ký nhưng chưa hoàn tất điều kiện thanh toán. |
| `1` | `DA_XAC_NHAN` | Đã xác nhận | Đăng ký hợp lệ, giữ chỗ, chờ lớp vào học. |
| `2` | `DANG_HOC` | Đang học | Học viên đang theo học thực tế. |
| `3` | `TAM_DUNG_NO_HOC_PHI` | Tạm dừng do nợ học phí | Tạm khóa quyền học vì chưa hoàn tất học phí. |
| `4` | `BAO_LUU` | Bảo lưu | Học viên tạm dừng theo diện bảo lưu. |
| `5` | `HOAN_THANH` | Hoàn thành | Học viên đã hoàn tất lớp học. |
| `6` | `HUY` | Đã hủy | Đăng ký bị hủy. |

### Transition đề xuất chuẩn

Luồng chính:

`CHO_THANH_TOAN -> DA_XAC_NHAN -> DANG_HOC -> HOAN_THANH`

Luồng ngoại lệ:

- `CHO_THANH_TOAN -> HUY`
- `DA_XAC_NHAN -> HUY`
- `DANG_HOC -> TAM_DUNG_NO_HOC_PHI`
- `TAM_DUNG_NO_HOC_PHI -> DANG_HOC`
- `DANG_HOC -> BAO_LUU`
- `BAO_LUU -> DANG_HOC`
- `DANG_HOC -> HUY`

### Quy tắc nghiệp vụ hiện đang gắn với trạng thái

- `CHO_THANH_TOAN`, `DA_XAC_NHAN`, `DANG_HOC`, `TAM_DUNG_NO_HOC_PHI` đang giữ chỗ trong lớp.
- Chỉ `DANG_HOC` được học lịch chính thức.
- `DA_XAC_NHAN` và `DANG_HOC` có thể vào chat nếu lớp cho phép.
- Chỉ `DANG_HOC` được gửi tin nhắn chat.
- `HUY` không còn chặn xóa lớp và không còn là đăng ký vận hành.

### Cải tiến nên làm thêm

- Thêm các cột thời gian trạng thái: `xacNhanLuc`, `vaoHocLuc`, `tamDungLuc`, `baoLuuLuc`, `hoanThanhLuc`, `huyLuc`.
- Thêm `lyDoTamDung`, `lyDoBaoLuu`, `lyDoHuy` để tránh mất ngữ cảnh nghiệp vụ.
- Không nên để controller hoặc cron job update số trạng thái trực tiếp; nên gom vào một service như `DangKyLopHocStateService`.
- Nếu cần tính học lại hoặc chuyển lớp, nên bổ sung event thay vì thêm trạng thái chồng chéo vào state machine này.

## BuoiHoc

### Mục đích

`BuoiHoc` đại diện cho từng buổi học cụ thể của một lớp.

### Hiện trạng

Model hiện có cột `trangThai`, nhưng chưa có:

- hằng số trạng thái trong model
- nhãn hiển thị
- helper methods
- scope theo trạng thái

Trong controller hiện chỉ validate `trangThai` thuộc tập `0,1,2,3,4`, nghĩa là hệ thống đang ngầm tồn tại 5 trạng thái nhưng chưa có source of truth chính thức trong model.

Ngoài `trangThai`, model còn có 2 cờ riêng:

- `daDiemDanh`
- `daHoanThanh`

Hai cờ này đang phản ánh một phần tiến độ vận hành của buổi học.

### Vấn đề hiện tại

- `trangThai` đang là magic number.
- Ý nghĩa giữa `trangThai`, `daDiemDanh`, `daHoanThanh` đang chồng nhau.
- Docs và code dễ lệch nhau vì controller đang giữ luật thay cho model.

### Bộ trạng thái chuẩn đề xuất

| Giá trị | Mã trạng thái | Nhãn hiển thị | Ý nghĩa |
| --- | --- | --- | --- |
| `0` | `SAP_DIEN_RA` | Sắp diễn ra | Buổi học đã lên lịch nhưng chưa bắt đầu. |
| `1` | `DANG_DIEN_RA` | Đang diễn ra | Buổi học đang diễn ra. |
| `2` | `DA_HOAN_THANH` | Đã hoàn thành | Buổi học đã xong. |
| `3` | `DA_HUY` | Đã hủy | Buổi học bị hủy. |
| `4` | `DOI_LICH` | Đổi lịch | Buổi học được dời lịch hoặc tạm hoãn. |

### Transition đề xuất chuẩn

Luồng chính:

`SAP_DIEN_RA -> DANG_DIEN_RA -> DA_HOAN_THANH`

Luồng ngoại lệ:

- `SAP_DIEN_RA -> DOI_LICH`
- `DOI_LICH -> SAP_DIEN_RA`
- `SAP_DIEN_RA -> DA_HUY`
- `DANG_DIEN_RA -> DA_HUY`

### Cải tiến nên làm thêm

- Đưa toàn bộ enum và label vào model `BuoiHoc`.
- Hạn chế dùng đồng thời `trangThai` và `daHoanThanh`; nếu giữ cả hai thì phải định nghĩa rất rõ vai trò của từng cột.
- Tốt nhất:
  - `trangThai` giữ vòng đời buổi học
  - `daDiemDanh` chỉ là cờ nghiệp vụ đã chốt điểm danh hay chưa
  - bỏ phụ thuộc vào `daHoanThanh` nếu `trangThai = DA_HOAN_THANH` đã đủ nghĩa

## LienHe

### Mục đích

`LienHe.trangThai` phản ánh tiến độ xử lý một yêu cầu liên hệ từ khách hàng hoặc học viên.

### Trạng thái hiện tại

| Giá trị | Nhãn hiển thị | Ý nghĩa |
| --- | --- | --- |
| `0` | Chưa xử lý | Liên hệ mới tạo, chưa được tiếp nhận. |
| `1` | Đang xử lý | Đã có người nhận và đang xử lý. |
| `2` | Đã xử lý | Yêu cầu đã được giải quyết xong. |
| `3` | Đã từ chối | Yêu cầu bị từ chối hoặc không tiếp nhận. |

### Phân loại liên hệ hiện tại

`loaiLienHe` không phải trạng thái, nhưng là phân loại nghiệp vụ đi kèm:

- `tu_van`
- `ho_tro`
- `khieu_nai`
- `khac`

### Transition đề xuất chuẩn

Luồng chính:

`CHUA_XU_LY -> DANG_XU_LY -> DA_XU_LY`

Luồng ngoại lệ:

- `CHUA_XU_LY -> DA_TU_CHOI`
- `DANG_XU_LY -> DA_TU_CHOI`

### Cải tiến nên làm thêm

- Khai báo hằng số thay vì chỉ dùng mảng `TRANG_THAI_LABELS`.
- Bổ sung `tiepNhanLuc`, `batDauXuLyLuc`, `dongXuLyLuc`, `tuChoiLuc`.
- Nếu cần CRM rõ hơn, nên có thêm `mucDoUuTien` và `kenhTiepNhan`, không nhồi vào `trangThai`.

## HoaDon

### Mục đích

`HoaDon.trangThai` là state machine thanh toán của hóa đơn.

### Trạng thái hiện tại

| Giá trị | Mã trạng thái | Nhãn hiển thị | Ý nghĩa |
| --- | --- | --- | --- |
| `0` | `CHUA_TT` | Chưa thanh toán | Chưa thu được tiền. |
| `1` | `MOT_PHAN` | Thanh toán một phần | Đã thu một phần, chưa đủ công nợ. |
| `2` | `DA_TT` | Đã thanh toán đủ | Đã thanh toán đủ hóa đơn. |

### Loại hóa đơn hiện tại

`loaiHoaDon` là phân loại nghiệp vụ, không phải trạng thái:

| Giá trị | Mã loại | Nhãn hiển thị |
| --- | --- | --- |
| `0` | `DANG_KY_MOI` | Đăng ký mới |
| `1` | `GIA_HAN` | Gia hạn |
| `2` | `KHAC` | Khác |

### Transition đề xuất chuẩn

Luồng chính:

`CHUA_TT -> MOT_PHAN -> DA_TT`

Luồng rút gọn:

`CHUA_TT -> DA_TT`

### Quy tắc nghiệp vụ hiện đang gắn với trạng thái

- `DA_TT` có thể mở lại trạng thái đăng ký lớp từ `CHO_THANH_TOAN` hoặc `TAM_DUNG_NO_HOC_PHI`.
- Hóa đơn chưa thanh toán đủ có thể mang thêm thông tin “sắp hết hạn” hoặc “quá hạn”, nhưng đây là derived state, không phải trạng thái chính.

### Cải tiến nên làm thêm

- Nếu cần theo dõi quy trình thu ngân rõ hơn, có thể bổ sung state machine đầy đủ hơn:
  - `MOI_TAO`
  - `CHO_XAC_NHAN_THANH_TOAN`
  - `MOT_PHAN`
  - `DA_TT`
  - `HUY`
- Nếu không cần phức tạp hơn, giữ 3 trạng thái hiện tại là đủ, nhưng nên ghi rõ rằng `sapHetHan` và `quaHan` là trạng thái suy diễn.

## PhieuThu

### Mục đích

`PhieuThu.trangThai` phản ánh tính hợp lệ của từng phiếu thu.

### Trạng thái hiện tại

| Giá trị | Mã trạng thái | Ý nghĩa |
| --- | --- | --- |
| `0` | `HUY` | Phiếu thu bị hủy, không được tính vào thực thu. |
| `1` | `HOP_LE` | Phiếu thu hợp lệ, được tính vào thực thu. |

### Transition đề xuất chuẩn

Luồng chính:

`HOP_LE -> HUY`

Ghi chú:

- Thực tế phiếu thu thường được tạo ra ở trạng thái hợp lệ rồi mới bị hủy nếu có sai sót.
- Nếu quy trình duyệt chặt hơn, có thể mở rộng thêm trạng thái `CHO_DUYET`.

### Cải tiến nên làm thêm

- Bổ sung `nguoiHuyId`, `huyLuc`, `lyDoHuy`.
- Nếu quy trình duyệt thu ngân cần chặt, nên chuyển sang:
  - `CHO_DUYET`
  - `HOP_LE`
  - `HUY`

## Khuyến nghị chung cho toàn bộ state machine

1. Mỗi model chỉ nên có một state machine chính cho vòng đời cốt lõi.
2. Phân biệt rõ:
   - trạng thái chính
   - trạng thái suy diễn
   - phân loại nghiệp vụ
3. Mọi enum phải được khai báo trong model hoặc enum class, không để controller giữ magic number.
4. Mọi transition quan trọng nên đi qua service chuyên trách, không update số trực tiếp từ nhiều nơi.
5. Nên có audit log hoặc ít nhất là timestamp và reason cho các trạng thái ngoại lệ như hủy, tạm dừng, từ chối.
