# Module Chat Client - Tài liệu kỹ thuật

> Cập nhật lần cuối: 2026-03-09
> Phiên bản: v2.1
> Phạm vi: implementation hiện tại trong codebase

---

## 1. Tổng quan

Module chat client phục vụ trao đổi nội bộ giữa học viên và giáo viên trong phạm vi lớp học.

Triển khai hiện tại hỗ trợ hai loại hội thoại:

- `class_group`: nhóm chat theo lớp học.
- `direct`: đoạn chat riêng giữa hai thành viên có ít nhất một lớp hợp lệ chung.

Route giao diện:

- `GET /hoc-vien/chat`

Route API nội bộ:

- Base prefix: `/api/chat`
- Middleware: `auth`

### Kiến trúc realtime hiện tại

Module chat đang dùng short-poll thay cho WebSocket/SSE:

```text
Browser
  -> GET /hoc-vien/chat
  -> GET /api/chat/rooms
  -> GET /api/chat/rooms/{id}/messages
  -> GET /api/chat/poll?room={id}&after={messageId} (mỗi 1.5s)
  -> POST /api/chat/messages
  -> POST /api/chat/messages/{id}/react
  -> POST /api/chat/messages/{id}/recall
```

Lý do:

- Project chạy trên XAMPP/Apache prefork, giữ kết nối dài bằng PHP không phù hợp.
- Short-poll trả về ngay, nhẹ hơn và không giữ worker lâu.
- `ClientChatController::poll()` đóng session sớm để tránh block request khác.

### Tính năng đã có

- Danh sách phòng chat khả dụng theo user.
- Tự động tạo room lớp khi user có lớp hợp lệ hoặc chạy command bootstrap.
- Join room lớp.
- Tạo hoặc mở direct chat.
- Load lịch sử tin nhắn theo phân trang ngược.
- Poll tin mới theo `after`.
- Gửi tin nhắn text, ảnh và file.
- Reply tin nhắn.
- Reaction emoji.
- Thu hồi tin nhắn trong 24 giờ.
- Xóa tin nhắn phía mình.
- Đánh dấu đã đọc và tính unread count.
- Infinite scroll lịch sử qua `before`.
- System message cơ bản khi thành viên tham gia room lớp.
- Panel thành viên trong đoạn chat.

### Tính năng đã có schema nhưng chưa bật đầy đủ trên API/UI

- Mật khẩu phòng chat.
- Typing indicator.
- WebSocket/SSE cho chat.

---

## 2. Database schema

Migration nguồn:

- `database/migrations/2026_03_07_120000_create_chat_tables.php`

### 2.1 `chat_rooms`

| Cột | Kiểu | Mô tả |
| --- | --- | --- |
| `chatRoomId` | bigint PK | ID phòng chat |
| `loai` | varchar(20) | `class_group` hoặc `direct` |
| `tenPhong` | varchar(150) nullable | Tên phòng, room direct thường để `null` |
| `lopHocId` | int nullable | Liên kết `lophoc.lopHocId`, chỉ dùng cho room lớp |
| `matKhauHash` | varchar(255) nullable | Chưa dùng trong implementation hiện tại |
| `taoBoiId` | int nullable | Tài khoản tạo room |
| `lastMessageId` | bigint nullable | Tin nhắn gần nhất |
| `trangThai` | tinyint | `0: inactive`, `1: active`, `2: archived` |
| `created_at` | timestamp | Laravel timestamps |
| `updated_at` | timestamp | Laravel timestamps |

Ràng buộc chính:

- unique `lopHocId`: mỗi lớp tối đa 1 room nhóm.
- index `loai, trangThai`.
- index `taoBoiId`, `lastMessageId`.

### 2.2 `chat_room_members`

| Cột | Kiểu | Mô tả |
| --- | --- | --- |
| `chatRoomMemberId` | bigint PK | ID thành viên room |
| `chatRoomId` | bigint | FK tới room |
| `taiKhoanId` | int | FK tới tài khoản |
| `vaiTro` | varchar(20) | `member`, `teacher`, `owner` |
| `joinedAt` | timestamp nullable | Thời điểm tham gia |
| `joinedByPasswordAt` | timestamp nullable | Chưa dùng |
| `lastReadMessageId` | bigint nullable | Tin cuối đã đọc |
| `lastSeenAt` | timestamp nullable | Lần hoạt động cuối |
| `isMuted` | boolean | Cờ tắt tiếng, chưa có UI |
| `roiAt` | timestamp nullable | `null` nghĩa là còn trong room |
| `created_at` | timestamp | Laravel timestamps |
| `updated_at` | timestamp | Laravel timestamps |

Ràng buộc chính:

- unique `chatRoomId + taiKhoanId`
- index `taiKhoanId`, `lastReadMessageId`, `chatRoomId + roiAt`

### 2.3 `chat_messages`

| Cột | Kiểu | Mô tả |
| --- | --- | --- |
| `chatMessageId` | bigint PK | ID tin nhắn |
| `chatRoomId` | bigint | FK room |
| `nguoiGuiId` | int | Người gửi |
| `replyToMessageId` | bigint nullable | Tin nhắn được trả lời |
| `loai` | varchar(20) | `text`, `image`, `file`, `location`, `system` |
| `noiDung` | longText nullable | Nội dung tin nhắn |
| `metaJson` | json nullable | Metadata bổ sung |
| `guiLuc` | timestamp | Thời điểm gửi |
| `deadlineThuHoi` | timestamp nullable | Hạn thu hồi |
| `thuHoiLuc` | timestamp nullable | Nếu khác `null` thì tin đã thu hồi |
| `xoaLuc` | timestamp nullable | Chưa dùng trong flow hiện tại |
| `created_at` | timestamp | Laravel timestamps |
| `updated_at` | timestamp | Laravel timestamps |

Ghi chú:

- Gửi text hiện đặt `deadlineThuHoi = now() + 1 day`.
- Tin thu hồi vẫn giữ record, UI hiển thị placeholder `Tin nhắn đã được thu hồi`.

### 2.4 Các bảng bổ trợ

| Bảng | Vai trò | Trạng thái triển khai |
| --- | --- | --- |
| `chat_message_attachments` | File/ảnh đính kèm | Đang dùng |
| `chat_message_reactions` | Reaction emoji | Đang dùng |
| `chat_message_deletes` | Xóa phía mình | Đang dùng |
| `chat_audit_logs` | Audit các hành động chat | Đang dùng cho send/recall/react |

---

## 3. Quy tắc truy cập

Nguồn luật chính:

- `app/Services/ChatAccessService.php`
- `app/Models/Education/LopHoc.php`
- `app/Models/Education/DangKyLopHoc.php`

### 3.1 Nhóm chat lớp học

- Giáo viên phụ trách lớp luôn có thể truy cập và gửi tin.
- Học viên chỉ thấy room lớp nếu có đăng ký hợp lệ và lớp ở trạng thái phù hợp.
- Học viên có thể join chat khi:
  - `DangKyLopHoc.trangThai` là `DA_XAC_NHAN` hoặc `DANG_HOC`
  - và `LopHoc.trangThai` là `CHOT_DANH_SACH` hoặc `DANG_HOC`
- Học viên chỉ được gửi tin khi:
  - `DangKyLopHoc.trangThai` là `DANG_HOC`
  - và `LopHoc.trangThai` là `DANG_HOC`

### 3.2 Direct chat

- Không cho phép tự tạo chat với chính mình.
- Chỉ tạo direct chat nếu hai user có ít nhất một `lopHocId` chung trong tập lớp chat hợp lệ.
- Direct chat không gắn `lopHocId` trực tiếp, nhưng payload có thể trả `directContextLabel` để giải thích kết nối chung.

### 3.3 Ma trận quyền tóm tắt

| Trường hợp | `canJoin` | `canAccess` | `canSend` |
| --- | --- | --- | --- |
| Học viên không có đăng ký hợp lệ | `false` | `false` | `false` |
| Học viên `DA_XAC_NHAN`, lớp `CHOT_DANH_SACH` | `true` | `true` | `false` |
| Học viên `DANG_HOC`, lớp `DANG_HOC` | `true` hoặc đã join | `true` | `true` |
| Giáo viên phụ trách lớp | `true` | `true` | `true` |
| Thành viên direct chat hợp lệ | `false` | `true` | `true` |

Ghi chú:

- `canAccessRoom()` hiện cho phép truy cập room lớp nếu user đủ điều kiện join, kể cả chưa có record trong `chat_room_members`.
- Khi gửi tin hoặc đánh dấu đã đọc, hệ thống sẽ `updateOrCreate` membership nếu cần.

---

## 4. Payload chính

### 4.1 Room payload

`ChatRoomService::buildRoomPayload()` trả các field chính:

```json
{
  "id": 12,
  "name": "Anh Van Giao Tiep 01",
  "type": "class_group",
  "lopHocId": 5,
  "className": "Anh Van Giao Tiep 01",
  "courseName": "Giao tiếp cơ bản",
  "teacherName": "Nguyen Van A",
  "canJoin": true,
  "canAccess": true,
  "canSend": false,
  "isMember": false,
  "memberRole": null,
  "directPeerId": null,
  "lastMessagePreview": "Chao ca lop",
  "lastMessageAt": "2026-03-09T08:00:00+07:00",
  "lastMessageAtLabel": "1 minute ago",
  "unreadCount": 2,
  "updatedAt": "2026-03-09T08:00:00+07:00"
}
```

Field riêng cho room direct:

- `directPeerId`
- `directPeerName`
- `directContextClassName`
- `directContextCourseName`
- `directContextLabel`

### 4.2 Message payload

`ChatMessageService::transformMessage()` trả các field chính:

```json
{
  "id": 101,
  "roomId": 12,
  "type": "text",
  "content": "Nop bai tap truoc 20h nhe",
  "isMine": false,
  "senderId": 33,
  "senderName": "Tran Thi B",
  "replyTo": {
    "id": 96,
    "senderName": "Nguyen Van A",
    "content": "Ca lop luu y lich hoc moi",
    "isRecalled": false
  },
  "isRecalled": false,
  "sentAt": "2026-03-09T08:01:00+07:00",
  "sentAtLabel": "08:01 09/03/2026",
  "canRecall": false,
  "reactions": [
    {
      "emoji": "👍",
      "count": 2,
      "reactedByMe": true,
      "userNames": ["Tran Thi B", "Le Van C"]
    }
  ]
}
```

---

## 5. API endpoints

Tất cả endpoint dưới đây nằm trong `routes/web.php`, prefix `/api/chat`, middleware `auth`.

### 5.1 GET `/api/chat/rooms`

Lấy toàn bộ room user nhìn thấy.

Response:

```json
{
  "rooms": [
    { "id": 12, "type": "class_group", "name": "Anh Van Giao Tiep 01" }
  ]
}
```

### 5.2 GET `/api/chat/poll`

Short-poll lấy tin nhắn mới cho room đang mở.

Query params:

| Param | Bắt buộc | Mô tả |
| --- | --- | --- |
| `room` | Có | ID room |
| `after` | Không | Chỉ lấy tin có `chatMessageId > after` |

Các status có thể trả về:

| `status` | Ý nghĩa |
| --- | --- |
| `ok` | Thành công |
| `no_room` | Thiếu `room` |
| `not_found` | Room không nhìn thấy được hoặc không tồn tại |
| `no_access` | Có room nhưng user chưa thể truy cập |

Response mẫu:

```json
{
  "status": "ok",
  "roomId": 12,
  "messages": [],
  "room": { "id": 12, "unreadCount": 0 }
}
```

### 5.3 GET `/api/chat/rooms/{id}/messages`

Load lịch sử tin nhắn, mặc định 50 tin gần nhất.

Query params:

| Param | Bắt buộc | Mô tả |
| --- | --- | --- |
| `before` | Không | Phân trang ngược theo `chatMessageId` |

Hành vi:

- Nếu user có quyền truy cập, API trả `room` và `messages`.
- Nếu có tin nhắn, hệ thống tự đánh dấu đã đọc tới tin cuối của batch vừa load.
- Response có thêm `hasMore` để client biết còn lịch sử cũ hơn hay không.

### 5.4 GET `/api/chat/rooms/{id}/members`

Lấy danh sách thành viên room đang truy cập.

Response:

```json
{
  "members": [
    {
      "id": 33,
      "name": "Tran Thi B",
      "initials": "TB",
      "roleLabel": "Hoc vien",
      "isMe": false,
      "canDirect": true
    }
  ]
}
```

### 5.5 POST `/api/chat/rooms/{id}/join`

Join room lớp.

Response:

```json
{
  "message": "Tham gia nhóm chat thành công.",
  "room": { "id": 12, "canAccess": true }
}
```

### 5.6 POST `/api/chat/rooms/direct`

Tạo hoặc mở direct chat với user khác.

Request body:

```json
{
  "targetUserId": 45
}
```

Các lỗi chính:

- `422`: target là chính mình.
- `403`: không có lớp hợp lệ chung để direct chat.

### 5.7 POST `/api/chat/rooms/{id}/read`

Đánh dấu đã đọc.

Request body:

```json
{
  "lastMessageId": 101
}
```

Response:

```json
{
  "success": true
}
```

### 5.8 POST `/api/chat/messages`

Gửi tin nhắn text, ảnh hoặc file.

Request body:

```json
{
  "roomId": 12,
  "message": "Noi dung tin nhan",
  "replyToMessageId": 96,
  "attachments": []
}
```

Validation:

- `roomId`: required integer
- `message`: nullable string, max `2000`
- `replyToMessageId`: nullable integer
- `attachments`: nullable array, tối đa 5 file
- `attachments.*`: file, tối đa 10MB, cho phép `jpg`, `jpeg`, `png`, `gif`, `webp`, `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `txt`, `zip`, `rar`

Response:

```json
{
  "message": "Đã gửi tin nhắn.",
  "chatMessage": { "id": 101, "content": "Noi dung tin nhan" },
  "room": { "id": 12, "lastMessagePreview": "Noi dung tin nhan" }
}
```

### 5.9 POST `/api/chat/messages/{id}/recall`

Thu hồi tin nhắn.

Request body:

```json
{
  "roomId": 12
}
```

Điều kiện:

- Tin nhắn thuộc room đang chọn.
- User là người gửi.
- `deadlineThuHoi` vẫn còn hiệu lực.
- Tin chưa bị thu hồi trước đó.

### 5.10 POST `/api/chat/messages/{id}/react`

Thêm hoặc bỏ reaction.

Request body:

```json
{
  "roomId": 12,
  "emoji": "👍"
}
```

Emoji hợp lệ hiện tại:

- `👍`
- `❤️`
- `😂`
- `😮`
- `😢`
- `🔥`
- `😡`

Response:

```json
{
  "message": "Đã thêm cảm xúc.",
  "chatMessage": { "id": 101, "reactions": [] },
  "reacted": true,
  "room": { "id": 12 }
}
```

### 5.11 POST `/api/chat/messages/{id}/delete-for-me`

Ẩn tin nhắn khỏi chế độ xem của user hiện tại.

Request body:

```json
{
  "roomId": 12
}
```

Response:

```json
{
  "message": "Đã xóa tin nhắn khỏi chế độ xem của bạn.",
  "deletedMessageId": 101,
  "room": { "id": 12 }
}
```

---

## 6. Services chính

### `ChatRoomService`

| Method | Vai trò |
| --- | --- |
| `getVisibleRoomsForUser()` | Trả danh sách room user nhìn thấy |
| `getVisibleRoomForUser()` | Lấy 1 room hợp lệ theo user |
| `buildRoomPayload()` | Build JSON-ready payload cho room |
| `getRoomMembersPayload()` | Build payload thành viên room |
| `findOrCreateClassRoom()` | Tạo room lớp nếu chưa có |
| `findOrCreateDirectRoom()` | Tạo hoặc tái sử dụng room direct |
| `joinClassRoom()` | Ghi nhận thành viên tham gia room lớp |
| `bootstrapClassRooms()` | Đồng bộ room lớp hàng loạt |

### `ChatMessageService`

| Method | Vai trò |
| --- | --- |
| `getMessagesForUser()` | Lấy lịch sử 50 tin gần nhất |
| `getMessagesAfterForUser()` | Lấy tin mới sau một ID |
| `findVisibleMessageForUser()` | Tìm tin nhắn còn nhìn thấy được |
| `sendTextMessage()` | Gửi tin text và cập nhật room/audit |
| `toggleReaction()` | Toggle reaction |
| `recallMessage()` | Thu hồi tin nhắn |
| `markRoomRead()` | Cập nhật `lastReadMessageId` |
| `transformMessage()` | Chuẩn hóa payload tin nhắn |

### `ChatAccessService`

| Method | Vai trò |
| --- | --- |
| `getAccessibleClassIds()` | Tập lớp chat hợp lệ theo user |
| `canJoinRoom()` | Có thể tham gia room lớp hay không |
| `canAccessRoom()` | Có thể truy cập room hay không |
| `canSendMessage()` | Có thể gửi tin hay không |
| `canCreateDirectConversation()` | Có thể mở direct chat hay không |

---

## 7. Frontend

File chính:

- `resources/views/clients/hoc-vien/chat/index.blade.php`
- `public/assets/client/js/pages/chat/chat.js`
- `public/assets/client/css/pages/chat/chat.css`

### 7.1 Luồng trang

1. Render blade `hoc-vien/chat`.
2. Blade inject sẵn:
   - `window.chatConfig.routes`
   - `window.chatConfig.rooms`
   - `window.chatConfig.selectedRoom`
   - `window.chatConfig.emojis`
3. `chat.js` dựng layout 3 cột:
   - sidebar room
   - message board
   - info panel
4. Khi chọn room:
   - load messages
   - load members khi mở info panel
   - bắt đầu poll định kỳ

### 7.2 Hành vi client đáng chú ý

- Gửi tin dùng optimistic UI.
- Composer hỗ trợ chọn nhiều ảnh/tệp trước khi gửi.
- Poll mặc định mỗi 1.5 giây.
- Poll tạm dừng khi tab ẩn, resume khi quay lại.
- Khi cuộn lên đầu khung chat, client tự tải thêm lịch sử cũ hơn.
- Chuyển room sẽ re-render khối chính nhưng không reload toàn trang.
- Mobile có panel toggle riêng cho room list và info panel.

### 7.3 Composer emoji

Danh sách emoji nhập nhanh lấy từ `ChatMessageService::composerEmojis()`.

---

## 8. Command đồng bộ room lớp

Command:

```bash
php artisan chat:init-class-rooms
```

Tùy chọn xem trước:

```bash
php artisan chat:init-class-rooms --dry-run
```

Command sẽ:

- duyệt toàn bộ `lophoc`
- tạo room nhóm cho lớp đủ điều kiện nếu chưa có
- đồng bộ thành viên giáo viên cho room hiện có

---

## 9. Khác biệt so với tài liệu kế hoạch cũ

Triển khai thực tế hiện tại khác với bản kế hoạch ban đầu ở các điểm sau:

- dùng short-poll thay vì WebSocket
- chưa bật mật khẩu room lớp
- đã bật upload attachment
- đã bật delete-for-me
- đã có direct chat, reaction, recall, infinite scroll và member panel

Khi cập nhật tài liệu khác, ưu tiên lấy implementation hiện tại từ code thay vì tài liệu kế hoạch cũ.
