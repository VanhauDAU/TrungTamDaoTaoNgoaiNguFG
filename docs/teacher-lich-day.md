# 📅 Tính năng Lịch Dạy Giáo Viên

> **Route:** `GET /teacher/lich-day`  
> **Branch:** `teaching_schedule`  
> **Ngày triển khai:** 2026-04-21

---

## 1. Tổng quan chức năng

Trang **Lịch Dạy** (`/teacher/lich-day`) cung cấp cho giáo viên cái nhìn toàn diện về lịch giảng dạy của mình theo từng tuần, với các tính năng:

| Tính năng | Mô tả |
|---|---|
| **Thời khóa biểu tuần** | Bảng 7 cột (T2–CN) × N hàng ca học, tham khảo thiết kế giao diện học viên |
| **Điều hướng tuần** | Nút *Tuần trước / Hôm nay / Tuần tiếp* |
| **Highlight hôm nay** | Cột ngày hiện tại được tô xanh nổi bật |
| **Card buổi dạy** | Hiển thị tên lớp, khóa học, phòng, cơ sở, trạng thái màu |
| **Đề xuất dạy bù** | Modal gửi đề xuất, chờ duyệt |
| **Đề xuất tạm ngưng** | Modal cảnh báo + gửi lý do |
| **Đề xuất đổi lịch** | Modal chọn ngày mới + lý do |
| **Chi tiết buổi học** | Modal thông tin đầy đủ kèm nút tác vụ nhanh |
| **Toast thông báo** | Phản hồi thành công / lỗi sau mỗi đề xuất |

---

## 2. Cấu trúc file

```
app/Http/Controllers/Teacher/LichDay/
└── LichDayController.php          ← Controller chính (đã cập nhật)

resources/views/teacher/lich-day/
└── index.blade.php                ← Blade view (đã viết lại)

app/Models/
└── TeacherScheduleProposal.php    ← Model lưu đề xuất (Mới)

database/migrations/
└── 2026_04_21_..._create_teacher_schedule_proposals_table.php

public/assets/teacher/css/
└── lich-day.css                   ← CSS riêng cho trang giảng dạy

routes/web.php
└── prefix('lich-day')→            ← Route GET + 3 route POST đề xuất
```

---

## 3. Controller – `LichDayController`

### 3.1 `index(Request $request)`

Tham số query string: `?week=YYYY-MM-DD` (ngày bất kỳ trong tuần cần xem).  
Nếu thiếu → dùng tuần hiện tại.

**Dữ liệu trả về view:**

| Biến | Kiểu | Mô tả |
|---|---|---|
| `$caHocs` | Collection | Ca học đang hoạt động, sort theo `gioBatDau` |
| `$sessions` | Collection | Buổi dạy trong tuần, eager-load với `lopHoc`, `phongHoc`, `caHoc` |
| `$schedule` | array | Map `[thu][caHocId] => [BuoiHoc]` để render nhanh |
| `$weekDays` | array | 7 items: `['thu', 'label', 'date']` |
| `$startOfWeek` | Carbon | Thứ Hai của tuần đang xem |
| `$endOfWeek` | Carbon | Chủ Nhật của tuần đang xem |
| `$baseDate` | Carbon | Điểm neo tuần |
| `$hasSessions` | bool | Có buổi dạy trong tuần không |

### 3.2 Các endpoints đề xuất (JSON)

| Method | Route | Xử lý |
|---|---|---|
| `POST` | `/teacher/lich-day/de-xuat/day-bu/{id}` | `proposeCompensation` |
| `POST` | `/teacher/lich-day/de-xuat/tam-ngung/{id}` | `proposeSuspension` |
| `POST` | `/teacher/lich-day/de-xuat/doi-lich/{id}` | `proposeReschedule` |

**Guard:** Controller kiểm tra `lopHoc.taiKhoanId === auth()->id()` trước khi xử lý, trả 403 nếu sai.

> **Trạng thái dữ liệu:** Các đề xuất đã được lưu vào bảng `teacher_schedule_proposals` với trạng thái mặc định là `pending`.

---

## 4. Routes

```php
Route::prefix('lich-day')->name('schedule.')->group(function () {
    Route::get('/',  [TeacherLichDayController::class, 'index'])->name('index');

    Route::post('/de-xuat/day-bu/{buoiHocId}',
        [TeacherLichDayController::class, 'proposeCompensation'])->name('propose.compensation');

    Route::post('/de-xuat/tam-ngung/{buoiHocId}',
        [TeacherLichDayController::class, 'proposeSuspension'])->name('propose.suspension');

    Route::post('/de-xuat/doi-lich/{buoiHocId}',
        [TeacherLichDayController::class, 'proposeReschedule'])->name('propose.reschedule');
});
```

**Named routes:**

| Tên | URL |
|---|---|
| `teacher.schedule.index` | `/teacher/lich-day` |
| `teacher.schedule.propose.compensation` | `/teacher/lich-day/de-xuat/day-bu/{id}` |
| `teacher.schedule.propose.suspension` | `/teacher/lich-day/de-xuat/tam-ngung/{id}` |
| `teacher.schedule.propose.reschedule` | `/teacher/lich-day/de-xuat/doi-lich/{id}` |

---

## 5. Blade View – Sơ đồ layout

```
[TOOLBAR]  Lịch dạy tuần  dd/mm – dd/mm/YYYY   [< Prev] [Today] [Next >]
[PROPOSAL BAR]  Đề xuất nhanh: [Dạy bù] [Tạm ngưng] [Đổi lịch]
[BẢNG THỜI KHÓA BIỂU]
  | Ca học  | T2 | T3 | T4 | T5 | T6 | T7 | CN |
  |---------|-----|----|----|----|----|----|----|
  | Ca Sáng | [card] |  |[c]|    |    |    |   |
  | Ca Chiều|    |[c] |   |[c] |   |    |   |
[LEGEND]  sắp diễn ra · đang · đã hoàn thành · đã hủy · đổi lịch
```

**Card buổi dạy (khi hover):**  
`[Chi tiết] [Dạy bù] [Tạm ngưng] [Đổi lịch]`

---

## 6. Màu trạng thái

| Trạng thái | CSS class | Màu |
|---|---|---|
| Sắp diễn ra | `.sap-dien-ra` | Xám nhạt |
| Đang diễn ra | `.dang-dien-ra` | Xanh lá nhạt |
| Đã hoàn thành | `.da-hoan-thanh` | Xanh mint |
| Đã hủy | `.da-huy` | Đỏ nhạt |
| Đổi lịch | `.doi-lich` | Vàng nhạt |

---

## 7. Luồng đề xuất (Sequence)

```
Giáo viên → Nhấp card → Modal chi tiết
          → Chọn "Dạy bù / Tạm ngưng / Đổi lịch"
          → Modal tương ứng → Nhập lý do + dữ liệu → Gửi
          → fetch() POST JSON → Server validate + check auth
          → JSON { success, message } → Toast hiển thị
          → [Tương lai] Lưu DB + thông báo quản lý
```

---

## 8. Checklist mở rộng (TODO)

- [x] Tạo migration + model `teacher_schedule_proposals` để lưu đề xuất
- [ ] Gửi thông báo nội bộ tới quản lý khi có đề xuất mới
- [ ] Cho phép quản lý duyệt/từ chối đề xuất trong admin/staff panel
- [ ] Thêm bộ lọc lớp học trong toolbar
- [ ] Export lịch dạy ra PDF / iCal
- [ ] Widget "Buổi dạy hôm nay" trên Dashboard

---

## 9. Ghi chú kỹ thuật

> [!NOTE]
> Trang sử dụng layout `layouts.internal` (→ extends `layouts.admin`), đảm bảo nhất quán với các trang teacher khác.

> [!IMPORTANT]
> Các đề xuất hiện tại đã được **lưu vào cơ sở dữ liệu**. Tuy nhiên, logic để Quản lý (Admin) duyệt và cập nhật ngược lại vào bảng `buoihoc` cần được triển khai ở module Admin.

> [!TIP]
> Để thêm ca học mới vào timetable, vào **Admin → Ca học** và đảm bảo trường `trangThai = 1` (đang hoạt động).
