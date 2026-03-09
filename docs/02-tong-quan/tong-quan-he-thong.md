# 02 — Tổng quan Hệ thống

## 1. Tên dự án

**Hệ thống Quản lý Trung tâm Đào tạo Ngoại ngữ Five Genius**

## 2. Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────┐
│                  Trình duyệt (Browser)               │
│        Client Website │ Admin Panel                  │
└───────────────────────┬─────────────────────────────┘
                        │  HTTPS
              ┌─────────▼─────────┐
              │   Laravel 10 App  │
              │  (PHP 8.2, MVC)   │
              │  ┌─────────────┐  │
              │  │  Blade      │  │  ← Views (Blade templates)
              │  │  Controllers│  │  ← Routing & Business logic
              │  │  Models     │  │  ← Eloquent ORM
              │  └─────────────┘  │
              └─────────┬─────────┘
                        │
              ┌─────────▼─────────┐
              │    MySQL Database  │
              │  (XAMPP / Amazon  │
              │   RDS in prod)    │
              └───────────────────┘
```

## 3. Tech Stack

| Layer         | Công nghệ                    | Phiên bản  |
| ------------- | ---------------------------- | ---------- |
| Backend       | PHP / Laravel                | 8.2 / 10.x |
| Database      | MySQL                        | 8.0        |
| Frontend      | Bootstrap + Vanilla JS       | 5 / ES6    |
| CSS           | Custom CSS + Bootstrap       | —          |
| Rich Text     | TinyMCE                      | 6          |
| Icons         | Font Awesome                 | 6          |
| Hosting (dev) | XAMPP                        | 8.x        |
| Storage       | Laravel Storage / local disk | —          |
| Auth          | Laravel Breeze (tùy chỉnh)   | —          |

## 4. Cấu trúc thư mục chính

```
DACNCNPM_TrungTamNN/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← Controllers cho admin panel
│   │   │   ├── Auth/           ← Authentication controllers
│   │   │   └── Client/         ← Controllers cho website khách hàng
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Auth/               ← TaiKhoan, NhanSu, HoSoNguoiDung...
│   │   ├── Course/             ← KhoaHoc, LopHoc, DanhMucKhoaHoc...
│   │   ├── Education/          ← BuoiHoc, CaHoc, DiemDanh...
│   │   ├── Facility/           ← CoSoDaoTao, PhongHoc, TinhThanh
│   │   ├── Finance/            ← HoaDon, PhieuThu, Luong...
│   │   ├── Content/            ← BaiViet, DanhMucBaiViet, Tag...
│   │   └── Interaction/        ← ThongBao, LienHe, PhanHoi, Chat...
│   └── Services/
├── database/
│   ├── migrations/             ← Schema migrations
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/            ← Layout admin + client
│       ├── admin/              ← Views admin panel
│       └── clients/            ← Views website
├── routes/
│   └── web.php                 ← Toàn bộ routes
├── public/assets/
│   ├── admin/css/              ← CSS admin theo page
│   └── client/css/             ← CSS client theo page
└── docs/                       ← Tài liệu dự án (thư mục này)
```

## 5. Phân hệ chính

```
┌─────────────────────────────────────────────────────────────────┐
│                        ADMIN PANEL                              │
│  ┌──────────┐ ┌──────────┐ ┌─────────┐ ┌──────────┐           │
│  │ Auth &   │ │ Đào tạo  │ │ Tài     │ │ Nội dung │           │
│  │ Phân     │ │ (Khóa,   │ │ chính   │ │ (Blog,   │           │
│  │ quyền   │ │ Lớp,     │ │ (Hóa    │ │ Thông    │           │
│  │          │ │ Buổi)    │ │ đơn)    │ │ báo)     │           │
│  └──────────┘ └──────────┘ └─────────┘ └──────────┘           │
│  ┌──────────┐ ┌──────────┐ ┌─────────────────────┐            │
│  │ Cơ sở   │ │ Người    │ │      CRM             │            │
│  │ vật chất │ │ dùng     │ │ (Liên hệ, Tư vấn)   │            │
│  └──────────┘ └──────────┘ └─────────────────────┘            │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                      WEBSITE CLIENT                             │
│  Trang chủ │ Khóa học │ Lớp học │ Blog │ Liên hệ │ Học viên │ Chat │
└─────────────────────────────────────────────────────────────────┘
```

## 6. Luồng chính

### Học viên đăng ký lớp học

```
Học viên chọn lớp → Xác nhận thông tin
 → POST /lop-hoc/{slug}/{lopSlug}/xac-nhan-dang-ky
 → DangKyLopHoc::create()
 → HoaDon::create()  ← tự động tạo hóa đơn
 → ThongBao gửi cho học viên + admin
 → Redirect trang hồ sơ học viên
```

### Admin gửi thông báo

```
Admin soạn thông báo + chọn nhóm nhận + upload file
 → POST /admin/thong-bao
 → ThongBaoController::store()
 → ThongBao::create()
 → ThongBaoNguoiDung::create() cho từng người nhận
 → ThongBaoTepDinh::create() cho từng file đính kèm
```

### Học viên sử dụng chat lớp học

```text
Học viên vào /hoc-vien/chat
 → ClientChatController::index()
 → ChatRoomService::getVisibleRoomsForUser()
 → Chọn room và load /api/chat/rooms/{id}/messages
 → chat.js short-poll /api/chat/poll mỗi 1.5 giây
 → POST /api/chat/messages khi gửi tin
 → ChatMessageService cập nhật room, unread, audit log
```
