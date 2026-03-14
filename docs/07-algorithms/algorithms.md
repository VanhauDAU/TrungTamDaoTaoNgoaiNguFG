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

- `LopHoc`: ngày bắt đầu, ngày kết thúc
- `CaHoc`: thứ trong tuần (1–7), giờ bắt đầu/kết thúc

### Thuật toán

```php
// Pseudo-code
$current = $ngayBatDau;
while ($current <= $ngayKetThuc) {
    if ($current->dayOfWeek === $caHoc->thu) {
        BuoiHoc::create([
            'lopHocId' => $lopId,
            'ngayHoc'  => $current,
            'trangThai' => 'cho_hoc',
        ]);
    }
    $current->addDay();
}
```

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
  → HoaDon::create([
        'dangKyLopHocId' => $dangKyId,
        'tongTien'       => $snapshot->hocPhiPhaiThuSnapshot,
        'trangThai'   => 'chua_thanh_toan',
    ])
```

### Quy tắc

- Không đọc lại học phí từ lớp sau khi học viên đã đăng ký.
- Thay đổi `LopHocChinhSachGia` chỉ áp dụng cho đăng ký mới.
- `soBuoiThucTe` và `buoihoc` không tự tính lại hóa đơn.

---

## 4. Rule mở tuyển sinh

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

## 5. Real-time Thông báo (Server-Sent Events / Polling)

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

## 6. Slug Generation (SEO-friendly URL)

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

## 7. Cascade Delete Logic

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
