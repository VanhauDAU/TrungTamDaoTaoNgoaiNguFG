# 04 — Tài liệu API

> Tất cả routes đều thuộc `routes/web.php`.
> API nội bộ (AJAX) dùng JSON response — không phải REST API public.

---

## 1. Public API (không cần đăng nhập)

### GET `/api/phuong-xa/{maTinh}`

Lấy danh sách quận/phường của tỉnh từ API provinces.open-api.vn (proxy).

**Params:** `maTinh` — mã tỉnh  
**Response:**

```json
[
    { "code": "001", "name": "Quận 1" },
    { "code": "002", "name": "Quận 2" }
]
```

### GET `/api/co-so`

Danh sách cơ sở đào tạo (có thể filter theo tỉnh).

**Query:** `?tinh_thanh_id=1`  
**Response:**

```json
[{ "coSoId": 1, "tenCoSo": "Cơ sở Quận 1", "diaChi": "..." }]
```

### GET `/api/phong-hoc/{coSoId}`

Phòng học theo cơ sở (dùng trong form lớp học).

### GET `/api/giao-vien/{coSoId}`

Giáo viên theo cơ sở.

---

## 2. Client API (yêu cầu đăng nhập — auth)

Base prefix: `/api/thong-bao`

| Method | Endpoint                       | Mô tả                       |
| ------ | ------------------------------ | --------------------------- |
| GET    | `/api/thong-bao/stream`        | SSE stream thông báo mới    |
| GET    | `/api/thong-bao/dropdown`      | 5 thông báo gần nhất (JSON) |
| GET    | `/api/thong-bao/chua-doc`      | Số thông báo chưa đọc       |
| PATCH  | `/api/thong-bao/{id}/da-doc`   | Đánh dấu 1 thông báo đã đọc |
| PATCH  | `/api/thong-bao/da-doc-tat-ca` | Đánh dấu tất cả đã đọc      |

**Response mẫu GET `/api/thong-bao/chua-doc`:**

```json
{ "unread": 3 }
```

**Response mẫu GET `/api/thong-bao/dropdown`:**

```json
{
    "notifications": [
        {
            "id": 12,
            "tieuDe": "Lịch khai giảng tháng 4",
            "daDoc": false,
            "thoiGian": "5 phút trước",
            "tepDinh": []
        }
    ],
    "unread": 3
}
```

---

## 3. Admin API (auth + isAdmin middleware)

### Cơ sở / Phân cấp địa điểm

| Method | Endpoint                                   | Mô tả                  |
| ------ | ------------------------------------------ | ---------------------- |
| GET    | `/admin/api/phuong-xa-co-so/{tinhThanhId}` | Phường xã có cơ sở     |
| GET    | `/admin/api/co-so-by-location`             | Cơ sở theo quận/phường |
| GET    | `/admin/api/hoc-phi/{khoaHocId}`           | Gói học phí theo khóa  |

### Thông báo Admin

| Method | Endpoint                             | Mô tả                     |
| ------ | ------------------------------------ | ------------------------- |
| GET    | `/admin/api/thong-bao/nguoi-nhan`    | Danh sách người nhận AJAX |
| GET    | `/admin/api/thong-bao/chua-doc`      | Số TB chưa đọc (admin)    |
| GET    | `/admin/api/thong-bao/dropdown`      | Dropdown thông báo        |
| PATCH  | `/admin/api/thong-bao/{id}/da-doc`   | Mark read                 |
| PATCH  | `/admin/api/thong-bao/da-doc-tat-ca` | Mark all read             |

### Tags (bài viết)

| Method | Endpoint               | Mô tả              |
| ------ | ---------------------- | ------------------ |
| GET    | `/admin/api/tags`      | Tìm kiếm tag       |
| POST   | `/admin/api/tags`      | Tạo tag mới (AJAX) |
| DELETE | `/admin/api/tags/{id}` | Xóa tag            |

---

## 4. Admin Routes — CRUD đầy đủ

| Module      | Prefix                     | Các action                                                          |
| ----------- | -------------------------- | ------------------------------------------------------------------- |
| Học viên    | `/admin/hoc-vien`          | index, create, store, edit, update, destroy, trash, restore         |
| Giáo viên   | `/admin/giao-vien`         | index, create, store, edit, update, destroy, trash, restore         |
| Nhân viên   | `/admin/nhan-vien`         | index, create, store, edit, update, destroy, trash, restore         |
| Liên hệ CRM | `/admin/lien-he`           | index, show, update, destroy, restore, trash, reply, assign, bulk   |
| Cơ sở       | `/admin/co-so`             | index, create, store, show, edit, update, destroy                   |
| Phòng học   | `/admin/phong-hoc`         | store, update, destroy (AJAX)                                       |
| Danh mục KH | `/admin/danh-muc-khoa-hoc` | index, create, store, edit, update, destroy                         |
| Khóa học    | `/admin/khoa-hoc`          | index, create, store, show, edit, update, destroy, restore          |
| Lớp học     | `/admin/lop-hoc`           | index, create, store, show, edit, update, destroy                   |
| Buổi học    | `/admin/buoi-hoc`          | store, update, destroy, auto-generate                               |
| Ca học      | `/admin/ca-hoc`            | index, store, update, destroy, toggle-status                        |
| Học phí     | `/admin/hoc-phi`           | store, update, destroy, toggle-status                               |
| Hóa đơn     | `/admin/hoa-don`           | index, show, update, phieu-thu.store, phieu-thu.destroy             |
| Bài viết    | `/admin/bai-viet`          | full CRUD + trash + bulk + toggle-status + upload-image             |
| Thông báo   | `/admin/thong-bao`         | index, create, store, show, edit, update, destroy, toggle-pin, bulk |
| Phân quyền  | `/admin/phan-quyen`        | index, create, store, edit, update, destroy                         |
| Tài khoản   | `/admin/tai-khoan`         | index, update-nhom-quyen, toggle-status, reset-password             |

---

## 5. Client Routes

| Route                                             | Mô tả                          |
| ------------------------------------------------- | ------------------------------ |
| GET `/`                                           | Trang chủ                      |
| GET `/khoa-hoc`                                   | Danh sách khóa học (có filter) |
| GET `/khoa-hoc/{slug}`                            | Trang chi tiết khóa học        |
| GET `/lop-hoc/{slug}/{lopSlug}`                   | Chi tiết lớp học               |
| GET `/lop-hoc/{slug}/{lopSlug}/dang-ky`           | Form xác nhận đăng ký          |
| POST `/lop-hoc/{slug}/{lopSlug}/xac-nhan-dang-ky` | Xử lý đăng ký                  |
| GET `/blog`                                       | Danh sách bài viết             |
| GET `/blog/{slug}`                                | Chi tiết bài viết              |
| GET `/lien-he`                                    | Trang liên hệ / tư vấn         |
| POST `/lien-he/tu-van`                            | Gửi form tư vấn                |
| GET `/hoc-vien`                                   | Hồ sơ học viên (auth)          |
| GET `/hoc-vien/lop-hoc`                           | Lớp học của tôi (auth)         |
| GET `/hoc-vien/lich-hoc`                          | Lịch học cá nhân (auth)        |
| GET `/hoc-vien/hoa-don`                           | Hóa đơn của tôi (auth)         |
| GET `/thong-bao`                                  | Trang thông báo (auth)         |
