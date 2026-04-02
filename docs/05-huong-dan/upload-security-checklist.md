# Bảo mật Upload File — Checklist & Phân tích

> Phạm vi chính: **Upload ảnh đại diện học viên** (`POST /hoc-vien/anh-dai-dien`)  
> Cập nhật: 2026-04-02

---

## Tổng quan kiến trúc upload hiện tại

| Lớp | File | Vai trò |
|-----|------|---------|
| Route avatar | `routes/web.php` | Route `/hoc-vien/anh-dai-dien`, có `auth` + `verified.student` + `throttle:5,1` |
| Route generic | `routes/web.php` | Route `POST /api/uploads/images`, yêu cầu `auth` |
| Controller | `StudentController::updateAvatar()` | Nhận request, gọi service, trả JSON |
| Generic controller | `ImageUploadController::store()` | Nhận `preset`, gọi service upload dùng chung |
| Service dùng chung | `ImageUploadService` | Validate, transform, lưu file, trả metadata thống nhất |
| Service avatar | `StudentService::updateAvatar()` | Gọi preset `avatar`, xóa ảnh cũ, cập nhật DB |
| Config preset | `config/uploads.php` | Khai báo preset `avatar`, `content_image` |
| Storage | disk `public` → `storage/app/public/anh-dai-dien/` | Lưu ngoài webroot, symlink ra `public/storage` |
| Frontend | `x-upload.image` | Component upload ảnh dùng chung cho `instant` và `deferred` |

---

## Checklist bảo mật — Trạng thái hiện tại

### ✅ 1. Kiểm tra MIME type

**Trạng thái: ĐẠT**

Validation nay da duoc dua ve service upload dung chung thong qua preset `avatar` trong `config/uploads.php`.

- Rule `image`: Laravel dùng `finfo` / `getimagesize()` để kiểm tra content thực sự của file, **không chỉ dựa vào extension hay `Content-Type` header** của HTTP request.
- Rule `mimes:jpg,jpeg,png,gif,webp`: kiểm tra MIME type thực tế bằng `finfo_file()`.
- **Kết quả**: file giả mạo (ví dụ `.php` đổi tên thành `.jpg`) sẽ bị từ chối ở tầng validation.

---

### ✅ 2. Tự tạo tên file ngẫu nhiên

**Trạng thái: ĐẠT**

Preset `avatar` hiện re-encode ảnh về JPEG và dùng UUID làm tên file. Ví dụ:

```
anh-dai-dien/3f8a2b91-c47d-4e0f-a562-1d9f3b2c8e4a.jpg
```

- Tên file gốc của người dùng **không bao giờ được dùng** làm tên lưu.
- Loại bỏ hoàn toàn rủi ro path traversal và filename injection.

---

### ✅ 3. Lưu file ngoài webroot

**Trạng thái: ĐẠT (với lưu ý)**

```php
// config/filesystems.php dòng 41-48
'public' => [
    'driver' => 'local',
    'root'   => storage_path('app/public'),   // ← ngoài webroot
    'url'    => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

File được lưu tại:
```
{project_root}/storage/app/public/anh-dai-dien/<uuid>.jpg
```

Thư mục này **nằm ngoài `public/`** (webroot), trình duyệt không thể truy cập trực tiếp.  
Được phục vụ qua symlink `public/storage → storage/app/public` và URL `/storage/anh-dai-dien/...`.

> **Lưu ý môi trường XAMPP:** Nếu symlink chưa được tạo, cần chạy `php artisan storage:link`. Trên Windows XAMPP có thể cần quyền Admin để tạo symlink.

> **Cải tiến đề xuất (production):** Dùng disk `local` (private) + serve file qua signed URL hoặc controller để tránh khả năng liệt kê directory. Disk `public` hiện tại vẫn chấp nhận được cho môi trường học tập.

---

### ✅ 4. Giới hạn kích thước và số lượng

**Trạng thái: ĐẠT**

| Giới hạn | Giá trị | Cơ chế |
|----------|---------|--------|
| Kích thước file tối đa | **2 MB** | Laravel rule `max:2048` (KB) |
| Số ảnh mỗi lần upload | **1 file** | Input `file` đơn, không dùng `multiple` |
| Số lần upload | Không giới hạn theo session | — (xem đề xuất bên dưới) |

Frontend component `x-upload.image` cũng kiểm tra trước khi upload/chọn file:
```js
const MAX_SIZE = 2 * 1024 * 1024; // 2MB
if (file.size > MAX_SIZE) { ... }
```

---

### ✅ 5. Yêu cầu xác thực trước upload

**Trạng thái: ĐẠT**

```php
// StudentController.php dòng 39-44
public function updateAvatar(Request $request)
{
    $user = Auth::user();
    if (!$user instanceof TaiKhoan)
        abort(403);
    ...
}
```

Route được bảo vệ bởi:
1. `auth` middleware → phải đăng nhập
2. `verified.student` middleware → email đã xác thực
3. Type-check `instanceof TaiKhoan` → tránh null/wrong-type
4. Chỉ cập nhật được avatar của chính mình (lấy từ `Auth::user()`, không nhận `userId` từ request)

---

## Tóm tắt

| # | Yêu cầu | Trạng thái | Ghi chú |
|---|---------|------------|---------|
| 1 | Kiểm tra MIME type | ✅ Đạt | `image` + `mimes` rule kiểm tra content thực |
| 2 | Tên file ngẫu nhiên | ✅ Đạt | `store()` sinh UUID tự động |
| 3 | Lưu ngoài webroot | ✅ Đạt | `storage/app/public/`, phục vụ qua symlink |
| 4 | Giới hạn kích thước | ✅ Đạt | 2MB backend + frontend |
| 5 | Yêu cầu xác thực | ✅ Đạt | `auth` + `verified.student` + type-check |

**Kết luận: Toàn bộ 5 tiêu chí đều ĐẠT cho môi trường học tập / staging.**

---

## Danh sách cải tiến — **Đã triển khai** (2026-04-02)

| Ư u tiên | Cải tiến | Trạng thái |
|---------|----------|------------|
| 🔴 Cao | Rate limit 5 req/phút cho route upload avatar | ✅ Xong — `throttle:5,1` trên route `/anh-dai-dien` |
| 🟡 Trung | Giới hạn kích thước pixel | ✅ Xong — `dimensions:max_width=5000,max_height=5000` |
| 🟡 Trung | Resize ảnh về 400×400px | ✅ Xong — `intervention/image v4` + `scaleDown(400, 400)` |
| 🟢 Thấp | Strip EXIF metadata | ✅ Xong — re-encode sang JPEG qua `JpegEncoder(quality:85)` |

---

## Chi tiết triển khai

### 1. Rate limit upload avatar

```php
// routes/web.php
Route::post('/anh-dai-dien', [StudentController::class, 'updateAvatar'])
    ->middleware('throttle:5,1')   // tối đa 5 lần upload/phút/người dùng
    ->name('update-avatar');
```

Laravel sử dụng cache để đếm số request theo `{userId}:{route}`. Khi vượt giới hạn trả HTTP `429 Too Many Requests`. Frontend nhận status 429 và hiển thị thông báo lỗi qua feedback panel.

### 2. Giới hạn kích thước pixel (gigapixel DoS)

Giới hạn này hiện được khai báo trong preset `avatar` và được `ImageUploadService` convert thành rule `dimensions:max_width=5000,max_height=5000`.

Ảnh lớn hơn 5000×5000px sẽ bị từ chối trước khi xử lý, bảo vệ server khỏi nhûng ảnh hàng trăm megapixel có thể gây OOM.

### 3. Resize 400×400px + Strip EXIF

Việc resize và strip EXIF hiện được thực hiện tập trung trong `ImageUploadService`, không còn nằm rải rác trong từng service nghiệp vụ.

**Tại sao re-encode = strip EXIF?**
Khi đọc file gốc vào bộ nhớ rồi encode lại sang JPEG mới, thư viện chỉ ghi dữ liệu pixel — không gỏi lại EXIF từ file gốc. Kết quả: toàn bộ EXIF (GPS, camera model, timestamp) bị loại bỏ.

### Lưu ý

- File luôn được lưu dưới dạng `.jpg` (UUID.jpg) dù ảnh gốc là PNG/WebP.
- GIF animated sẽ bị flatten thành JPEG tĩnh (đây là hành vi mịnh mốí vì avatar không cần animated).
- `quality: 85` cân bằng giữa chất lượng vị trích xuất và kích thước file nhỏ.

---

## Liên kết

- `app/Services/Support/Uploads/ImageUploadService.php` — service upload ảnh dùng chung
- `app/Http/Controllers/Upload/ImageUploadController.php` — API generic upload ảnh
- `app/Services/Client/HocVien/StudentService.php` — flow avatar học viên dùng preset `avatar`
- `config/uploads.php` — preset upload ảnh
- `config/filesystems.php` — cấu hình disk storage
- `resources/views/components/upload/image.blade.php` — component upload ảnh dùng chung
- `resources/js/components/image-upload.js` — logic preview / drag-drop / progress
- `resources/views/clients/hoc-vien/profile/index.blade.php` — màn avatar học viên
