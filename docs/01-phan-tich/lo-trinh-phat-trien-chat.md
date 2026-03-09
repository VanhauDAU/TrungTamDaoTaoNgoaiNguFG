# Lộ trình phát triển chức năng Chat

> Cập nhật lần cuối: 2026-03-09
> Căn cứ: implementation hiện tại trong `docs/04-api/chat.md`
> Mục tiêu: đưa chat từ mức "đã dùng được" lên mức "đầy đủ, ổn định, sẵn sàng vận hành"

---

## 1. Mục tiêu tổng thể

Phát triển module chat theo 4 tiêu chí:

- Đầy đủ tính năng nhắn tin cho lớp học và trao đổi riêng.
- Phù hợp nghiệp vụ trung tâm ngoại ngữ, không chỉ giống Messenger ở giao diện.
- Ổn định trên hạ tầng hiện tại dùng XAMPP + short-poll.
- Có lộ trình mở rộng rõ ràng sang kiến trúc realtime mạnh hơn khi cần.

Kết quả mong muốn cuối cùng:

- Học viên và giáo viên trao đổi thuận tiện trong room lớp và direct chat.
- Tin nhắn hỗ trợ text, ảnh, file, reply, reaction, recall, delete-for-me, search.
- Có quyền quản trị room cho giáo viên.
- Có logging, test, monitoring và quy trình vận hành rõ ràng.

---

## 2. Hiện trạng

### Đã có trong hệ thống

- Room lớp học (`class_group`)
- Direct chat (`direct`)
- Danh sách room theo quyền truy cập
- Join room lớp
- Load lịch sử tin nhắn
- Short-poll lấy tin mới
- Gửi text message
- Reply
- Reaction emoji
- Recall trong 24 giờ
- Đánh dấu đã đọc, unread count
- Danh sách thành viên room
- Audit log cho send, recall, reaction

### Có schema nhưng chưa hoàn thiện trên API/UI

- Attachment upload
- Delete for me
- Password room
- Typing indicator
- Presence/online state

### Đánh giá hiện trạng

Module chat đã qua mức proof-of-concept, nhưng chưa đạt mức "full-featured" vì còn thiếu:

- media flow hoàn chỉnh
- UX room lớn và lịch sử dài
- moderation cho giáo viên
- lifecycle room theo trạng thái lớp
- test coverage và monitoring

---

## 3. Nguyên tắc triển khai

1. Không chuyển sang WebSocket ngay trong giai đoạn đầu.
   Lý do: hạ tầng hiện tại chưa phù hợp, trong khi short-poll vẫn đáp ứng được nếu tối ưu đúng.

2. Ưu tiên hoàn thiện các phần đã có schema trước khi thêm tính năng mới.
   Lý do: giảm nợ kỹ thuật và tận dụng thiết kế hiện có.

3. Phân tách rõ:
   - tính năng lõi của tin nhắn
   - tính năng quản trị room
   - tối ưu hiệu năng
   - nâng cấp hạ tầng realtime

4. Không ưu tiên password room cho lớp học.
   Lý do: quyền truy cập hiện đã phụ thuộc vào `DangKyLopHoc` và `LopHoc`; password làm tăng ma sát sử dụng nhưng không mang lại nhiều giá trị nghiệp vụ.

---

## 4. Roadmap đề xuất

### Giai đoạn 1 - Hoàn thiện lõi nhắn tin

### Mục tiêu

Lấp hết các khoảng trống lớn nhất để chat đạt mức sử dụng trọn vẹn hằng ngày.

### Phạm vi

- Gửi ảnh
- Gửi file
- Delete for me
- Tải thêm lịch sử bằng infinite scroll
- Chuẩn hóa trạng thái tin nhắn ở UI
- Bổ sung system message cơ bản

### Công việc chính

#### Backend

- Mở API upload attachment trong `ClientChatController`
- Mở xử lý attachment trong `ChatMessageService`
- Lưu metadata vào `chat_message_attachments`
- Mở API delete-for-me dùng `chat_message_deletes`
- Bổ sung transform payload cho attachment

#### Frontend

- Thêm chọn file/ảnh trong composer
- Preview file trước khi gửi
- Render bubble cho ảnh và file
- Thêm hành động "Xóa phía tôi"
- Infinite scroll khi lên đầu danh sách tin nhắn

#### Database

- Rà soát index cho `chat_message_attachments`
- Kiểm tra quota upload và chính sách lưu file

#### Test

- Feature test cho upload ảnh
- Feature test cho upload file
- Feature test cho delete-for-me
- Test visibility giữa các user khác nhau

### Acceptance criteria

- Gửi text, ảnh, file hoạt động ổn định
- Xóa phía mình không ảnh hưởng người khác
- Xem lịch sử dài mà không reload trang

### Ước lượng

- 1 đến 2 sprint

---

### Giai đoạn 2 - Nâng UX lên mức production

### Mục tiêu

Giảm cảm giác "AJAX demo", tăng tính mượt và dễ dùng.

### Phạm vi

- Search trong room
- Divider "tin chưa đọc"
- Cải thiện reply
- Paste ảnh từ clipboard
- Shortcut Enter / Shift+Enter
- Typing indicator mức cơ bản
- Tối ưu mobile layout

### Công việc chính

#### Backend

- API search message theo room
- API mark-read chính xác hơn theo room active
- Endpoint typing nhẹ nếu cần

#### Frontend

- Search box trong khung chat
- Jump tới tin gốc khi bấm reply
- Divider unread
- Composer shortcut
- Hiển thị typing indicator
- Tối ưu toggle panel trên mobile

#### Test

- Test mark-read và unread count
- Test search quyền truy cập
- Test scroll position khi load thêm tin

### Acceptance criteria

- Người dùng tìm lại tin nhắn được
- Reply và unread rõ ràng, không gây rối
- Mobile dùng được ổn định trên màn hình nhỏ

### Ước lượng

- 1 sprint

---

### Giai đoạn 3 - Moderation và nghiệp vụ lớp học

### Mục tiêu

Làm chat phù hợp ngữ cảnh trung tâm đào tạo, có công cụ quản trị cho giáo viên.

### Phạm vi

- Giáo viên khóa mở quyền gửi tin cho học viên
- Chế độ chỉ giáo viên gửi announcement
- Pin message
- Mute room
- Archive room khi lớp kết thúc
- Đồng bộ membership theo trạng thái lớp/đăng ký

### Công việc chính

#### Backend

- Mở rộng `ChatRoomService` với room settings
- Mở rộng `ChatAccessService` theo policy mới
- Tạo system message cho các sự kiện room
- Đồng bộ room/member khi `LopHoc` hoặc `DangKyLopHoc` đổi trạng thái

#### Frontend

- UI cài đặt room cho giáo viên
- UI pin message
- UI room archived / read-only
- Badge trạng thái room rõ ràng

#### Nghiệp vụ cần chốt trước

- Khi lớp `DA_KET_THUC`, room sẽ:
  - chỉ đọc
  - hay vẫn cho nhắn tiếp?
- Khi học viên `HUY` hoặc `BAO_LUU`, direct chat cũ có giữ không?
- Có cần admin can thiệp room lớp hay chỉ giáo viên?

### Acceptance criteria

- Quyền gửi tin phản ánh đúng trạng thái lớp và đăng ký
- Giáo viên có đủ công cụ quản lý room
- Room kết thúc không bị trạng thái mơ hồ

### Ước lượng

- 1 đến 2 sprint

---

### Giai đoạn 4 - Ổn định, hiệu năng, vận hành

### Mục tiêu

Làm chat chịu tải tốt hơn và dễ theo dõi khi có lỗi.

### Phạm vi

- Tối ưu polling
- Tối ưu query unread và room list
- Background job cho thumbnail
- Metrics và log kỹ hơn
- Cleanup file tạm

### Công việc chính

#### Backend

- Giảm số query dư trong room list và poll
- Tách xử lý thumbnail sang queue nếu triển khai ảnh
- Thêm metric:
  - poll latency
  - send latency
  - upload failure
  - số room active

#### Frontend

- Tối ưu render khi room có nhiều tin
- Chỉ refresh phần cần thiết
- Chống duplicate message khi poll + optimistic update

#### Test

- Regression test cho optimistic send
- Test phòng nhiều tin
- Test concurrent reaction / recall

### Acceptance criteria

- Room nhiều tin vẫn dùng mượt
- Poll không tạo tải bất thường
- Có dữ liệu đủ để debug production

### Ước lượng

- 1 sprint

---

### Giai đoạn 5 - Nâng cấp lên "đầy đủ"

### Mục tiêu

Bổ sung các tính năng nâng cao sau khi lõi đã ổn định.

### Backlog nâng cao

- Search toàn bộ hội thoại
- Forward message
- Multi-image send
- Link preview
- Voice note
- Pinned files / media gallery
- Reminder message cho giáo viên
- Export chat theo lớp
- Presence chuẩn hơn
- Realtime abstraction để sẵn sàng chuyển WebSocket

### Ghi chú

Chỉ nên bắt đầu giai đoạn này khi:

- attachment ổn định
- moderation rõ ràng
- unread và polling đã tối ưu
- test regression đủ an toàn

---

## 5. Thứ tự ưu tiên khuyến nghị

1. Attachment upload
2. Delete-for-me
3. Infinite scroll lịch sử
4. Search trong room
5. Mark-read / unread UX hoàn chỉnh
6. Typing indicator cơ bản
7. Moderation cho giáo viên
8. Lifecycle room theo lớp học
9. Tối ưu hiệu năng và monitoring
10. Tính năng nâng cao và realtime abstraction

---

## 6. Phân rã kỹ thuật theo khu vực code

### Backend chính

- `app/Http/Controllers/Client/ClientChatController.php`
- `app/Services/ChatMessageService.php`
- `app/Services/ChatRoomService.php`
- `app/Services/ChatAccessService.php`
- `app/Models/Interaction/Chat/*`

### Frontend chính

- `resources/views/clients/hoc-vien/chat/index.blade.php`
- `public/assets/client/js/pages/chat/chat.js`
- `public/assets/client/css/pages/chat/chat.css`

### Database

- `database/migrations/2026_03_07_120000_create_chat_tables.php`

### Tài liệu liên quan

- `docs/04-api/chat.md`
- `docs/01-phan-tich/ke-hoach-module-chat-client.md`
- `docs/01-phan-tich/trang-thai-model.md`

---

## 7. Rủi ro chính và cách giảm thiểu

### Rủi ro 1 - Chuyển WebSocket quá sớm

Hệ quả:

- tăng độ phức tạp hạ tầng
- khó vận hành trên môi trường dev hiện tại

Giảm thiểu:

- giữ short-poll trong 3 đến 4 giai đoạn đầu
- tách abstraction realtime trước khi đổi transport

### Rủi ro 2 - Logic quyền truy cập bị rải rác

Hệ quả:

- room hiển thị một kiểu, API xử lý một kiểu
- lỗi sai quyền khó debug

Giảm thiểu:

- gom toàn bộ policy vào `ChatAccessService`
- thêm test cho từng tổ hợp trạng thái lớp và đăng ký

### Rủi ro 3 - Upload file làm chậm request

Hệ quả:

- gửi tin nhắn lag
- timeout trên file lớn

Giảm thiểu:

- giới hạn kích thước ngay từ client
- xử lý thumbnail bất đồng bộ
- tách upload và send nếu cần

### Rủi ro 4 - Nợ UX trên mobile

Hệ quả:

- dùng được trên desktop nhưng khó dùng trên điện thoại

Giảm thiểu:

- xem mobile là yêu cầu bắt buộc từ giai đoạn 2
- test riêng các flow open room, reply, upload, toggle panel

---

## 8. Definition of Done cho từng giai đoạn

Một giai đoạn chỉ được coi là xong khi đủ cả 4 điều kiện:

- Code chạy đúng ở happy path và edge case chính
- Có test regression cho backend quan trọng
- UI mobile và desktop đều dùng được
- Tài liệu `docs/04-api/chat.md` được cập nhật lại theo implementation mới

---

## 9. Đề xuất thực thi ngay

Nếu bắt đầu triển khai ngay, nên mở sprint đầu theo thứ tự:

1. Attachment upload cho ảnh
2. Attachment upload cho file
3. Delete-for-me
4. Infinite scroll lịch sử
5. Regression test cho send, recall, react, delete-for-me, attachment

Đây là tập công việc có tỷ lệ giá trị/chi phí tốt nhất và làm nền cho toàn bộ phần còn lại.
