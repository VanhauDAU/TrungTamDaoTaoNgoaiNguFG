# Kế hoạch Module Chat Client kiểu Messenger

> Ghi chú 2026-03-09: đây là tài liệu phân tích ban đầu.
> Triển khai thực tế đã hoàn thành bản đầu tiên và có khác biệt quan trọng:
> - dùng short-poll thay vì WebSocket
> - không dùng mật khẩu room trong flow hiện tại
> - đã có direct chat, reply, reaction, recall, unread và member panel
> - attachment, delete-for-me, typing indicator vẫn chưa bật trên API/UI
> Tài liệu implementation chuẩn xem tại `docs/04-api/chat.md`.

> Ngày tạo: 2026-03-07  
> Phạm vi: Website client học viên, tích hợp với lớp học, giáo viên và tài khoản nội bộ hiện có trong hệ thống Laravel.

## 1. Mục tiêu

Xây dựng module chat cho phần client theo trải nghiệm gần giống Messenger, phục vụ giao tiếp nội bộ trong phạm vi học tập:

- Học viên tham gia nhóm chat của lớp học bằng mật khẩu.
- Học viên nhắn tin riêng với bạn cùng lớp hoặc giáo viên phụ trách lớp.
- Hỗ trợ gửi văn bản, file, ảnh, vị trí.
- Hỗ trợ phản ứng cảm xúc, trả lời tin nhắn, xóa phía cá nhân, thu hồi cho mọi người trong 24 giờ.
- Cập nhật realtime, unread badge và trạng thái đã đọc.

## 2. Bối cảnh tích hợp hiện tại

Project hiện đã có các thành phần có thể tận dụng:

- Auth người dùng và role trong `taikhoan`.
- Quan hệ lớp học trong `lophoc`, `dangKyLopHoc`, `taikhoan`.
- Khu vực client cho học viên tại `/hoc-vien`.
- Hạ tầng thông báo realtime dạng SSE cho client.

Kết luận:

- Chat phải là module mới, không gộp trực tiếp vào `ThongBao`.
- Có thể tái sử dụng auth, layout client, header badge và một phần kinh nghiệm realtime hiện có.
- Nếu muốn trải nghiệm giống Messenger, nên dùng WebSocket cho chat; SSE chỉ nên giữ cho fallback notification.

## 3. Phạm vi chức năng

### 3.1 Nhóm chat lớp học

- Mỗi lớp học có một phòng chat nhóm mặc định.
- Học viên chỉ nhìn thấy nhóm của các lớp mình đang tham gia hợp lệ.
- Khi vào nhóm lần đầu, học viên phải nhập mật khẩu.
- Mật khẩu được giáo viên hoặc admin cấp/reset.
- Sau khi join thành công, hệ thống ghi nhận thành viên và không bắt nhập lại trừ khi mật khẩu bị đổi.

### 3.2 Chat cá nhân

- Học viên được nhắn tin riêng cho:
    - học viên cùng lớp,
    - giáo viên đang phụ trách lớp mình học.
- Không cho phép nhắn tin tới người ngoài phạm vi lớp học liên quan.
- Hội thoại direct được tạo tự động khi gửi tin nhắn đầu tiên.

### 3.3 Tin nhắn

- Loại tin nhắn:
    - văn bản,
    - ảnh,
    - file đính kèm,
    - vị trí.
- Tính năng trong message:
    - trả lời tin nhắn,
    - bày tỏ cảm xúc,
    - xóa phía mình,
    - thu hồi cho mọi người trong vòng 24 giờ,
    - hiển thị trạng thái đã thu hồi.

### 3.4 Trạng thái và realtime

- Tin nhắn mới cập nhật realtime.
- Cập nhật realtime cho:
    - tin nhắn mới,
    - reaction,
    - thu hồi tin nhắn,
    - đã đọc,
    - đang nhập.
- Có bộ đếm chưa đọc ở danh sách hội thoại và header.

## 4. Quy tắc nghiệp vụ

1. Chỉ học viên thuộc lớp mới được vào nhóm chat của lớp đó.
2. Chỉ học viên có đăng ký lớp ở trạng thái hợp lệ mới được gửi tin.
3. Học viên chỉ được chat direct với người có quan hệ lớp học hợp lệ.
4. Giáo viên được tự động là thành viên phòng chat của lớp mình dạy.
5. Tin nhắn chỉ được thu hồi cho mọi người trong vòng 24 giờ kể từ thời điểm gửi.
6. Sau 24 giờ, người gửi chỉ được xóa phía cá nhân.
7. Xóa phía cá nhân không làm mất dữ liệu của người khác.
8. File upload phải qua kiểm tra MIME, dung lượng và blacklist phần mở rộng nguy hiểm.
9. Mật khẩu nhóm lớp phải được lưu dạng hash.
10. Toàn bộ thao tác quan trọng phải có audit log.

## 5. Thiết kế kiến trúc

### 5.1 Domain đề xuất

Tạo module mới trong nhóm `Interaction/Chat`:

- `ChatRoom`
- `ChatRoomMember`
- `ChatMessage`
- `ChatMessageAttachment`
- `ChatMessageReaction`
- `ChatMessageDelete`
- `ChatAuditLog`

### 5.2 Services đề xuất

- `ChatAccessService`
    - kiểm tra user có quyền vào room hay không,
    - kiểm tra điều kiện tạo direct message.
- `ChatRoomService`
    - tạo room lớp học,
    - join bằng mật khẩu,
    - lấy danh sách hội thoại.
- `ChatMessageService`
    - gửi tin nhắn,
    - reply,
    - recall,
    - delete-for-me,
    - đánh dấu đã đọc.
- `ChatAttachmentService`
    - upload file,
    - sinh thumbnail,
    - validate loại file.
- `ChatRealtimeService`
    - phát sự kiện realtime,
    - broadcast unread count,
    - typing indicator.

### 5.3 Realtime

Khuyến nghị:

- Dùng WebSocket ngay từ đầu cho module chat.
- Dùng queue cho xử lý ảnh, thumbnail, push event.
- Giữ SSE hiện tại làm fallback cho các badge hoặc thông báo đơn giản nếu cần.

Lý do:

- Chat kiểu Messenger cần tương tác hai chiều, đọc tin nhắn, typing, reaction, recall theo thời gian thực.
- SSE phù hợp cho notification feed hơn là chat hoàn chỉnh.

## 6. Thiết kế cơ sở dữ liệu đề xuất

### 6.1 Bảng `chat_rooms`

- `chatRoomId`
- `loai` (`class_group`, `direct`)
- `tenPhong`
- `lopHocId` nullable
- `matKhauHash` nullable
- `taoBoiId`
- `lastMessageId` nullable
- `trangThai`
- `created_at`
- `updated_at`

### 6.2 Bảng `chat_room_members`

- `chatRoomMemberId`
- `chatRoomId`
- `taiKhoanId`
- `vaiTro` (`member`, `teacher`, `owner`)
- `joinedAt`
- `joinedByPasswordAt` nullable
- `lastReadMessageId` nullable
- `lastSeenAt` nullable
- `isMuted`
- `roiAt` nullable
- `created_at`
- `updated_at`

### 6.3 Bảng `chat_messages`

- `chatMessageId`
- `chatRoomId`
- `nguoiGuiId`
- `replyToMessageId` nullable
- `loai` (`text`, `image`, `file`, `location`, `system`)
- `noiDung` nullable
- `metaJson` nullable
- `guiLuc`
- `deadlineThuHoi`
- `thuHoiLuc` nullable
- `xoaLuc` nullable
- `created_at`
- `updated_at`

### 6.4 Bảng `chat_message_attachments`

- `chatAttachmentId`
- `chatMessageId`
- `disk`
- `path`
- `thumbnailPath` nullable
- `tenGoc`
- `mime`
- `size`
- `width` nullable
- `height` nullable
- `created_at`
- `updated_at`

### 6.5 Bảng `chat_message_reactions`

- `chatReactionId`
- `chatMessageId`
- `taiKhoanId`
- `emoji`
- `created_at`
- unique (`chatMessageId`, `taiKhoanId`, `emoji`)

### 6.6 Bảng `chat_message_deletes`

- `chatMessageDeleteId`
- `chatMessageId`
- `taiKhoanId`
- `deletedAt`
- `created_at`

### 6.7 Bảng `chat_audit_logs`

- `chatAuditLogId`
- `chatRoomId` nullable
- `chatMessageId` nullable
- `taiKhoanId`
- `hanhDong`
- `duLieuCu` nullable
- `duLieuMoi` nullable
- `created_at`

## 7. Quan hệ với bảng hiện có

- `chat_rooms.lopHocId` liên kết `lophoc.lopHocId`.
- `chat_room_members.taiKhoanId` liên kết `taikhoan.taiKhoanId`.
- Điều kiện thành viên lớp kiểm tra qua `dangKyLopHoc`.
- Giáo viên lớp xác định từ `lophoc.taiKhoanId`.

Ràng buộc truy cập chính:

- Nhóm lớp: từ `DangKyLopHoc` và `LopHoc`.
- Direct chat: phải có ít nhất một `lopHocId` chung hợp lệ giữa hai người, hoặc quan hệ học viên-giáo viên trong cùng lớp.

## 8. API đề xuất

Base prefix đề xuất: `/api/chat`

### 8.1 Room

- `GET /api/chat/rooms`
    - Lấy danh sách hội thoại của user.
- `GET /api/chat/rooms/{id}`
    - Lấy thông tin room, thành viên, quyền thao tác.
- `POST /api/chat/rooms/{id}/join`
    - Join room nhóm lớp bằng mật khẩu.
- `GET /api/chat/rooms/{id}/messages`
    - Lấy tin nhắn theo cursor pagination.
- `POST /api/chat/rooms/direct`
    - Tạo hoặc lấy direct room với người dùng hợp lệ.

### 8.2 Message

- `POST /api/chat/messages`
    - Gửi tin nhắn mới.
- `POST /api/chat/messages/{id}/react`
    - Thêm hoặc bỏ reaction.
- `POST /api/chat/messages/{id}/read`
    - Đánh dấu đã đọc đến message hiện tại.
- `POST /api/chat/messages/{id}/delete-for-me`
    - Xóa phía cá nhân.
- `POST /api/chat/messages/{id}/recall`
    - Thu hồi cho mọi người trong 24 giờ.

### 8.3 Contacts

- `GET /api/chat/contacts`
    - Lấy danh sách bạn cùng lớp và giáo viên có thể chat direct.
- `GET /api/chat/search`
    - Tìm hội thoại, thành viên, tin nhắn gần đây.

## 9. Giao diện client đề xuất

### 9.1 Điều hướng

- Thêm menu `Chat` vào sidebar tài khoản học viên.
- Khi bấm vào menu chat, chuyển sang trang `/hoc-vien/chat`.
- Toàn bộ trang sẽ hiển thị giao diện chat, không có sidebar, có nút quay lại trang trước.
- Có badge số tin chưa đọc trên header.

### 9.2 Layout

Thiết kế 3 khu vực:

- Cột trái: danh sách hội thoại, filter, search.
- Cột giữa: khung chat chính.
- Cột phải: thông tin nhóm, file đã gửi, thành viên.

### 9.3 Composer

Hỗ trợ:

- nhập text,
- chọn file,
- chụp ảnh nhanh trên mobile,
- gửi vị trí sau khi cấp quyền,
- reply message,
- hiển thị preview file trước khi gửi.

### 9.4 Tin nhắn

Mỗi bubble cần hỗ trợ:

- nội dung,
- file/ảnh,
- reply preview,
- reaction,
- menu thao tác,
- trạng thái đã gửi/đã đọc,
- hiển thị `Tin nhắn đã được thu hồi` khi recall.

## 10. Bảo mật và hiệu năng

### 10.1 Bảo mật

- Hash mật khẩu nhóm bằng `Hash::make`.
- Kiểm tra quyền thao tác qua Policy hoặc Service layer.
- Rate limit:
    - join room bằng mật khẩu,
    - gửi tin nhắn,
    - upload file.
- Chặn file nguy hiểm: `php`, `exe`, `bat`, `cmd`, `js`, `sh`.
- Kiểm MIME thật thay vì chỉ dựa trên đuôi file.

### 10.2 Hiệu năng

- Dùng cursor pagination cho message history.
- Có index cho:
    - `chatRoomId`,
    - `nguoiGuiId`,
    - `guiLuc`,
    - `lastReadMessageId`.
- Upload file vào `storage/app/public/chat/...`.
- Tách queue cho thumbnail và xử lý media.

## 11. Lộ trình triển khai

### Giai đoạn 1: Nền tảng chat nhóm lớp

- Tạo schema database.
- Tạo model, service, policy cơ bản.
- Sinh room nhóm lớp từ dữ liệu lớp học.
- Join room bằng mật khẩu.
- Gửi và nhận text message.
- Danh sách hội thoại và unread badge.

### Giai đoạn 2: Chat cá nhân

- Danh sách contacts hợp lệ.
- Tạo direct room.
- Đọc tin nhắn và cập nhật read receipt.
- Search hội thoại cơ bản.

### Giai đoạn 3: Media và tiện ích

- Upload ảnh và file.
- Gửi vị trí.
- Reply message.
- Tối ưu mobile composer.

### Giai đoạn 4: Messenger-like features

- Reaction.
- Delete for me.
- Recall trong 24 giờ.
- Typing indicator.
- Presence hoặc last seen.

### Giai đoạn 5: Hardening

- Audit log.
- Rate limit.
- Test tải cơ bản.
- Kiểm thử phân quyền.
- Rollout production.

## 12. Test cần có

### 12.1 Feature test

- Học viên ngoài lớp không vào được room.
- Join sai mật khẩu bị chặn.
- Join đúng mật khẩu được thêm thành viên.
- Học viên không thể chat direct với người ngoài lớp.
- Giáo viên lớp có quyền vào nhóm lớp.
- Thu hồi thành công khi chưa quá 24 giờ.
- Thu hồi thất bại khi quá 24 giờ.
- Delete-for-me không ảnh hưởng người khác.

### 12.2 Upload test

- Chặn file sai MIME.
- Chặn file vượt kích thước.
- Chặn phần mở rộng nguy hiểm.

### 12.3 Realtime test

- Tin nhắn mới đẩy đúng room.
- Reaction cập nhật đúng message.
- Read receipt cập nhật đúng member state.

## 13. Tiêu chí nghiệm thu

- Học viên đăng nhập có thể vào trang chat và thấy đúng hội thoại liên quan.
- Học viên join nhóm lớp bằng mật khẩu thành công.
- Học viên nhắn tin được cho nhóm lớp và cá nhân hợp lệ.
- Tin nhắn hỗ trợ text, ảnh, file, vị trí.
- Có reply, reaction, delete-for-me, recall trước 24 giờ.
- Badge chưa đọc và trạng thái đã đọc hoạt động đúng.
- Không có lỗ hổng cho user ngoài lớp truy cập room hoặc direct chat trái phép.

## 14. Khuyến nghị triển khai trong project hiện tại

1. Tạo module chat như một domain riêng trong `Interaction`.
2. Không sửa chồng lên module `ThongBao`, chỉ tích hợp unread badge ở header client hiện có.
3. Nếu team chưa sẵn sàng cho WebSocket ngay, có thể chia 2 bước:
    - bước 1 dùng polling hoặc SSE ngắn hạn cho MVP,
    - bước 2 nâng cấp sang WebSocket cho production-grade chat.
4. Nên triển khai teacher inbox cùng thời điểm với direct message để tránh phải refactor quyền truy cập lần hai.

## 15. Deliverables kỹ thuật đề xuất

- Migration cho toàn bộ bảng chat.
- Model và service cho chat domain.
- Route và controller API chat.
- Giao diện `/hoc-vien/chat`.
- Realtime channel hoặc broadcast layer.
- Feature test và tài liệu API chi tiết.
