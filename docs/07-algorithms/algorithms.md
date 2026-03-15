# 07 — Thuật toán & Logic Nghiệp vụ

## 1. Cây danh mục khóa học (Recursive Tree)

### Cấu trúc

Bảng `danhmuckhoahoc` dùng mô hình **Adjacency List** (self-join):

```sql
parent_id INT NULL REFERENCES danhmuckhoahoc(danhMucId)
```

### Thuật toán buildFlatTree (tạo dropdown thụt lề)

```php
/**
 * Trả về danh sách phẳng dạng [['node' => Model, 'depth' => int]]
 * Đệ quy theo childrenRecursive (vô hạn cấp)
 */
public static function buildFlatTree(?int $excludeId = null): Collection
{
    $roots = static::with('childrenRecursive')
                   ->whereNull('parent_id')
                   ->orderBy('tenDanhMuc')
                   ->get();

    $result = collect();
    foreach ($roots as $root) {
        static::flattenNode($root, 0, $excludeId, $result);
    }
    return $result;
}

private static function flattenNode(self $node, int $depth, ?int $excludeId, Collection &$result): void
{
    if ($excludeId && $node->danhMucId === $excludeId) return;
    $result->push(['node' => $node, 'depth' => $depth]);
    foreach ($node->childrenRecursive as $child) {
        static::flattenNode($child, $depth + 1, $excludeId, $result);
    }
}
```

### Thuật toán allDescendantIds (filter khóa học)

```php
/**
 * Trả về tất cả ID con cháu (bao gồm chính nó)
 * Dùng để filter khóa học theo danh mục cha + toàn bộ cây con
 */
public function allDescendantIds(): array
{
    $ids = [$this->danhMucId];
    $this->loadMissing('childrenRecursive');
    foreach ($this->childrenRecursive as $child) {
        foreach ($child->allDescendantIds() as $id) {
            $ids[] = $id;
        }
    }
    return array_unique($ids);
}
```

### Phát hiện vòng lặp (Cycle Detection)

Khi update danh mục cha, kiểm tra:

```php
// Không cho đặt descendant làm cha → tạo vòng lặp
$candidate = DanhMucKhoaHoc::with('childrenRecursive')->find($parentId);
$descendantIds = $candidate->allDescendantIds();
if (in_array($currentId, $descendantIds)) {
    // → Lỗi: vòng lặp
}
```

---

## 2. Tự động sinh Buổi học từ Ca học

### Input

- `LopHoc`: `ngayBatDau`, `lichHoc`, `soBuoiDuKien`
- `CaHoc`: giờ bắt đầu/kết thúc

### Thuật toán

```php
// Pseudo-code
$current = $ngayBatDau->copy();
$targets = explode(',', $lichHoc); // vd: 2,4,6
$created = 0;

while ($created < $soBuoiDuKien) {
    $thu = mapCarbonDayToBusinessDay($current);

    if (in_array($thu, $targets, true)) {
        BuoiHoc::create([
            'lopHocId' => $lopId,
            'ngayHoc' => $current->toDateString(),
            'trangThai' => 'cho_hoc',
        ]);
        $created++;
    }

    $current->addDay();
}

// Sau moi lan them/sua/xoa buoi hoc:
$lopHoc->ngayKetThuc = ngayHocLonNhatCuaBuoiHocConHieuLuc;
```

### Quy tắc

- `ngayKetThuc` cua lop khong nhap tay trong flow moi.
- `ngayKetThuc` chi la ket qua van hanh duoc suy ra tu buoi hoc cuoi cung.
- Thay doi `ngayKetThuc` khong duoc phep tac dong nguoc lai den hoc phi.

---

## 3. Tạo snapshot học phí khi đăng ký lớp

### Luồng

```
DangKyLopHoc::create()
  → Đọc chính sách giá của lớp (LopHocChinhSachGia)
  → Snapshot vào dangkylophoc:
        - loaiThuSnapshot
        - hocPhiNiemYetSnapshot
        - giamGiaSnapshot
        - hocPhiPhaiThuSnapshot
        - soBuoiCamKetSnapshot
        - ngayHetHanGiuCho
  → Nếu `TRON_GOI`: tạo 1 hóa đơn học phí
  → Nếu `THEO_DOT`: tạo nhiều hóa đơn học phí theo `lophoc_dotthu`
  → Nếu có phụ phí mặc định: tạo thêm hóa đơn phụ phí
```

### Quy tắc

- Không đọc lại học phí từ lớp sau khi học viên đã đăng ký.
- Thay đổi `LopHocChinhSachGia` chỉ áp dụng cho đăng ký mới.
- `soBuoiThucTe` và `buoihoc` không tự tính lại hóa đơn.
- `LOAI_THU_THEO_THANG` không còn được hỗ trợ trong runtime mới.
- Đăng ký được bảo vệ bằng:
  - unique index `taiKhoanId + lopHocId`
  - transaction + `lockForUpdate()` trên lớp học

---

## 4. Tự động hủy giữ chỗ quá hạn

### Dữ liệu dùng

- `dangkylophoc.trangThai = CHO_THANH_TOAN`
- `dangkylophoc.ngayHetHanGiuCho`
- Tổng số tiền đã thu từ các `phieuthu` hợp lệ của các hóa đơn thuộc đăng ký

### Thuật toán

```php
// Pseudo-code
$dangKys = DangKyLopHoc::where('trangThai', CHO_THANH_TOAN)
    ->whereNotNull('ngayHetHanGiuCho')
    ->where('ngayHetHanGiuCho', '<', now())
    ->get();

foreach ($dangKys as $dangKy) {
    lock registration row;
    recalculatePaymentStatus();

    if ($dangKy->tongDaThu > 0) {
        skip;
    }

    $dangKy->trangThai = HUY;
    $dangKy->ngayHetHanGiuCho = null;
    append system note to invoices;
}
```

### Quy tắc

- Chỉ tự hủy nếu chưa thu được tiền.
- Nếu đã có tiền vào, job bỏ qua để tránh hủy sai nghiệp vụ.
- Sau khi hủy, đăng ký không còn chiếm chỗ.

---

## 5. Rule mở tuyển sinh

Trước khi lớp được chuyển sang các trạng thái vận hành, hệ thống phải kiểm tra:

```php
$requiresPricing = in_array($lopHoc->trangThai, [
    LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
    LopHoc::TRANG_THAI_CHOT_DANH_SACH,
    LopHoc::TRANG_THAI_DANG_HOC,
    LopHoc::TRANG_THAI_DA_KET_THUC,
], true);

if ($requiresPricing && ! $lopHoc->hasValidPricingPolicy()) {
    throw ValidationException::withMessages([
        'hocPhiNiemYet' => 'Lớp học phải có chính sách giá hợp lệ trước khi mở tuyển sinh.',
    ]);
}
```

---

## 6. Recalculate hóa đơn và đăng ký

### Khi tạo hoặc hủy phiếu thu

```php
PhieuThu::create(...)
    → HoaDon::recalculate()
        → cập nhật daTra
        → cập nhật trangThai hóa đơn
        → DangKyLopHoc::recalculatePaymentStatus()
```

### Khi admin sửa hóa đơn

```php
$hoaDon->update($data);
$hoaDon->recalculate();
```

### Quy tắc

- Không cho phép thay đổi dữ liệu hóa đơn mà không recalculate lại.
- `recalculate()` là điểm trung tâm để đồng bộ:
  - tiền đã thu
  - trạng thái hóa đơn
  - trạng thái đăng ký

---

## 7. Real-time Thông báo (Server-Sent Events / Polling)

### Cơ chế

Hệ thống dùng **HTTP Polling** (không phải WebSocket):

```javascript
// Client poll mỗi 30 giây
setInterval(() => {
    fetch("/api/thong-bao/chua-doc")
        .then((r) => r.json())
        .then((data) => updateBadge(data.unread));
}, 30000);
```

### Dropdown thông báo

```javascript
// Lấy 5 thông báo gần nhất khi hover vào icon
fetch("/api/thong-bao/dropdown")
    .then((r) => r.json())
    .then((data) => renderNotifications(data.notifications));
```

---

## 8. Slug Generation (SEO-friendly URL)

```php
// Tạo slug duy nhất, tránh trùng lặp
private function generateUniqueSlug(string $name, ?int $existingId = null): string
{
    $base = Str::slug($name);
    $slug = $base;
    $i = 1;
    while (
        DanhMucKhoaHoc::where('slug', $slug)
            ->when($existingId, fn($q) => $q->where('danhMucId', '!=', $existingId))
            ->exists()
    ) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}
```

---

## 9. Cascade Delete Logic

Soft delete được áp dụng cho: `khoahoc`, `baiviet`, `lienhe`, `hocvien`.

Khi xóa danh mục khóa học:

- Phải **không có khóa học nào** thuộc danh mục → Block
- Phải **không có danh mục con** → Block

```php
$soKhoaHoc = $dm->khoaHocs()->count();
$soKon     = $dm->childrenRecursive()->count();
if ($soKhoaHoc > 0 || $soKon > 0) abort(422, 'Không thể xóa');
```

---

## 8. Phân quyền (Authorization)

### Middleware `isAdmin`

```php
// Kiểm tra role trong bảng users
if (Auth::user()->role < 2) {
    return redirect('/')->with('error', 'Không có quyền truy cập');
}
```

### Role

| role | Loại                  |
| ---- | --------------------- |
| 1    | Học viên              |
| 2    | Giáo viên / Nhân viên |
| 3    | Admin                 |
