# Module Chat Lớp Học – Tài liệu kỹ thuật

> **Cập nhật lần cuối:** 2026-03-08  
> **Phiên bản:** v2.0 (Short-Poll Realtime)

---

## 1. Tổng quan

Module Chat Lớp Học cho phép học viên và giáo viên nhắn tin theo nhóm lớp học trong thời gian thực. Mỗi lớp học có một **phòng chat (ChatRoom)** riêng.

### Kiến trúc

```
Browser (JS)                    PHP/Laravel (XAMPP)
     │                                  │
     │── GET /hoc-vien/chat ──────────> │ Render blade (gọn nhẹ, không load messages)
     │<─────────────────────────────────│
     │
     │── GET /api/chat/rooms/{id}/messages ──> Load lịch sử tin nhắn (AJAX)
     │<──────────────────────────────────────
     │
     │   [ Vòng lặp Short-Poll mỗi 1.5 giây ]
     │── GET /api/chat/poll?room=X&after=Y ──> Kiểm tra tin mới (trả ngay, ~20ms)
     │<──────────────────────────────────────
     │
     │   [ Gửi tin nhắn ]
     │── Optimistic append (hiện ngay trên UI)
     │── POST /api/chat/messages ─────────> Server lưu tin nhắn
     │<──────────────────────────── Confirm (replace optimistic với real data)
```

**Tại sao dùng Short-Poll thay vì WebSocket/SSE?**

XAMPP dùng Apache MPM Prefork – mỗi kết nối WebSocket/SSE giữ trọn 1 PHP thread. Với nhiều user, toàn bộ thread pool bị chiếm → các trang khác bị lag/treo. Short-Poll (mỗi request hoàn thành trong ~20ms, không giữ thread) phù hợp nhất với môi trường này.

---

## 2. Database Schema

### Bảng chính

| Bảng                       | Mô tả                                        |
| -------------------------- | -------------------------------------------- |
| `chat_rooms`               | Phòng chat, 1-1 với `lop_hocs`               |
| `chat_room_members`        | Thành viên phòng chat (học viên + giáo viên) |
| `chat_messages`            | Tin nhắn văn bản                             |
| `chat_message_attachments` | File đính kèm (dự kiến)                      |
| `chat_message_reactions`   | React cảm xúc (dự kiến)                      |
| `chat_message_deletes`     | Xoá tin nhắn phía mình                       |
| `chat_audit_logs`          | Log hoạt động                                |

### Các trường quan trọng – `chat_rooms`

| Cột             | Kiểu      | Mô tả                       |
| --------------- | --------- | --------------------------- |
| `chatRoomId`    | bigint PK |                             |
| `lopHocId`      | bigint FK | Liên kết với lớp học        |
| `lastMessageId` | bigint FK | ID tin nhắn gần nhất        |
| `updated_at`    | timestamp | Thời gian cập nhật gần nhất |

### Các trường quan trọng – `chat_messages`

| Cột              | Kiểu      | Mô tả                                   |
| ---------------- | --------- | --------------------------------------- |
| `chatMessageId`  | bigint PK |                                         |
| `chatRoomId`     | bigint FK |                                         |
| `nguoiGuiId`     | bigint FK | `taiKhoanId` người gửi                  |
| `loai`           | enum      | `text`, `image`, `file`, `system`       |
| `noiDung`        | text      | Nội dung tin nhắn                       |
| `guiLuc`         | timestamp | Thời điểm gửi                           |
| `thuHoiLuc`      | timestamp | Thời điểm thu hồi (null = chưa thu hồi) |
| `deadlineThuHoi` | timestamp | Hết hạn được phép thu hồi               |
| `replyToId`      | bigint FK | Trả lời tin nhắn khác                   |

### Các trường quan trọng – `chat_room_members`

| Cột                 | Kiểu      | Mô tả                                 |
| ------------------- | --------- | ------------------------------------- |
| `chatRoomId`        | bigint PK |                                       |
| `taiKhoanId`        | bigint PK |                                       |
| `vaiTro`            | enum      | `member`, `teacher`, `moderator`      |
| `lastReadMessageId` | bigint    | Tin nhắn cuối đã đọc (để tính unread) |
| `lastSeenAt`        | timestamp |                                       |
| `joinedAt`          | timestamp |                                       |
| `roiAt`             | timestamp | null = đang trong phòng               |

---

## 3. API Endpoints

Base path: `/api/chat/` – tất cả yêu cầu đăng nhập (`middleware: auth`).

### GET `/api/chat/poll`

Short-poll endpoint – **trả về ngay lập tức** (không có sleep/blocking).

**Query params:**

| Param   | Kiểu | Mô tả                                |
| ------- | ---- | ------------------------------------ |
| `room`  | int  | ID phòng chat                        |
| `after` | int  | Chỉ lấy tin nhắn có ID > giá trị này |

**Response (có tin mới):**

```json
{
  "status": "ok",
  "roomId": 5,
  "messages": [
    {
      "id": 42,
      "content": "Xin chào lớp!",
      "isMine": false,
      "senderName": "Nguyễn Văn A",
      "sentAtLabel": "18:30 08/03/2026",
      "replyTo": null
    }
  ],
  "room": { "id": 5, "unreadCount": 1, "lastMessagePreview": "Xin chào lớp!", ... }
}
```

**Response (không có tin mới):**

```json
{ "status": "ok", "roomId": 5, "messages": [], "room": { ... } }
```

**Các status khác:**

| Status      | Ý nghĩa                                               |
| ----------- | ----------------------------------------------------- |
| `no_room`   | Không truyền `room` param                             |
| `not_found` | Phòng chat không tồn tại hoặc user không có quyền xem |
| `no_access` | User chưa tham gia phòng chat                         |

---

### GET `/api/chat/rooms/{id}/messages`

Load lịch sử tin nhắn (50 tin gần nhất).

**Query params:** `before` (int, optional) – phân trang ngược.

**Response:**

```json
{
  "room": { ... },
  "messages": [ { "id": 1, "content": "...", "isMine": true, ... } ]
}
```

---

### POST `/api/chat/messages`

Gửi tin nhắn.

**Body:**

```json
{ "roomId": 5, "message": "Nội dung tin nhắn" }
```

**Response:**

```json
{
  "message": "Đã gửi tin nhắn.",
  "chatMessage": { "id": 43, "content": "Nội dung tin nhắn", ... },
  "room": { ... }
}
```

---

### POST `/api/chat/rooms/{id}/join`

Tham gia phòng chat.

**Response:**

```json
{ "message": "Tham gia nhóm chat thành công.", "room": { ... } }
```

---

### POST `/api/chat/rooms/{id}/read`

Đánh dấu đã đọc.

**Body:** `{ "lastMessageId": 42 }`

---

### GET `/api/chat/rooms`

Lấy danh sách phòng chat của user (dùng để refresh sidebar mỗi 15s).

---

## 4. Services

### `ChatRoomService`

| Method                                                  | Mô tả                                  |
| ------------------------------------------------------- | -------------------------------------- |
| `getVisibleRoomsForUser($user, $accessService)`         | Lấy tất cả phòng chat user có thể thấy |
| `getVisibleRoomForUser($roomId, $user, $accessService)` | Lấy 1 phòng cụ thể                     |
| `buildRoomPayload($room, $user, $accessService)`        | Chuyển model → array JSON-ready        |
| `joinClassRoom($room, $user)`                           | Thêm user vào phòng                    |

### `ChatMessageService`

| Method                                              | Mô tả                        |
| --------------------------------------------------- | ---------------------------- |
| `getMessagesForUser($room, $user, $before, $limit)` | Load lịch sử (50 tin)        |
| `getMessagesAfterForUser($room, $user, $afterId)`   | Lấy tin nhắn mới hơn ID      |
| `sendTextMessage($room, $user, $content)`           | Gửi tin nhắn + cập nhật room |
| `markRoomRead($room, $user, $lastId)`               | Cập nhật lastReadMessageId   |

### `ChatAccessService`

| Method                         | Mô tả                                   |
| ------------------------------ | --------------------------------------- |
| `canAccessRoom($user, $room)`  | Đã tham gia (isMember && roiAt is null) |
| `canJoinRoom($user, $room)`    | Có thể tham gia (là học viên của lớp)   |
| `canSendMessage($user, $room)` | canAccess && phòng chưa bị khoá         |

---

## 5. Frontend (chat.js)

File: `public/assets/client/js/pages/chat/chat.js`

### Luồng chính

```
1. renderApp()            → Dựng khung HTML đầy đủ
2. loadMessages(roomId)   → AJAX lấy lịch sử tin nhắn
3. schedulePoll(200ms)    → Bắt đầu vòng poll ngay sau khi load xong
4. doPoll() mỗi 1.5s      → GET /api/chat/poll → appendNewMessages()
```

### Chiến lược render

| Tình huống               | Hành động                                                      |
| ------------------------ | -------------------------------------------------------------- |
| Khởi tạo / chuyển phòng  | `renderApp()` – full re-render                                 |
| Nhận tin nhắn mới (poll) | `appendNewMessages()` – chỉ append node mới vào DOM            |
| Gửi tin nhắn             | Append optimistic bubble ngay → replace sau khi server confirm |
| Đổi trạng thái submit    | Chỉ toggle `disabled` trên nút Gửi                             |
| Làm mới sidebar          | `renderRoomList()` – chỉ re-render phần sidebar                |

### Optimistic Send

```
User nhấn Gửi
  ├── input.value = "" (clear ngay)
  ├── Tạo bubble giả {_pending: true, id: "p_..."}
  ├── Append vào DOM ngay lập tức
  ├── POST /api/chat/messages
  │     ├── OK  → replacePendingMessage(pendingId, realMsg)
  │     └── ERR → xoá bubble giả, restore text vào input
  └── input.focus()
```

### Vòng poll

```javascript
schedulePoll(delay)         // setTimeout → doPoll sau `delay` ms
doPoll()
  ├── fetch /api/chat/poll?room=X&after=Y
  ├── Nhận response ngay (~20-50ms)
  ├── Nếu có messages → appendNewMessages() + renderRoomList()
  └── schedulePoll(1500)    // Lên lịch poll tiếp
```

> **Lưu ý:** Poll bị pause khi `document.hidden` (tab ẩn), resume ngay khi tab active lại.

---

## 6. Phân quyền truy cập phòng chat

| Điều kiện                           | canJoin | canAccess | canSend |
| ----------------------------------- | ------- | --------- | ------- |
| Học viên chưa đăng ký lớp           | ✗       | ✗         | ✗       |
| Học viên đã đăng ký, chưa join chat | ✓       | ✗         | ✗       |
| Học viên đã join chat               | ✗       | ✓         | ✓       |
| Giáo viên của lớp                   | ✗       | ✓         | ✓       |
| Phòng chat bị khoá                  | ✗       | ✓         | ✗       |

---

## 7. Khởi tạo phòng chat

Khi tạo lớp học, chạy command:

```bash
php artisan chat:init-class-rooms
```

Command này tạo `ChatRoom` cho tất cả lớp học chưa có phòng chat.

Migration: `database/migrations/2026_03_07_120000_create_chat_tables.php`
