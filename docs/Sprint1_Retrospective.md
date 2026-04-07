# BÁO CÁO NHÌN LẠI CHẠY NƯỚC RÚT
*(Sprint Retrospective Report)*

| Tên dự án | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ | **Sprint số** | 1 |
| :--- | :--- | :--- | :--- |
| **Nhóm** | FiveGenius | **Ngày** | 21/03/2026 |

---

| 🚀 BẮT ĐẦU LÀM *(Start Doing)* | 🛑 DỪNG LÀM *(Stop Doing)* | ✅ TIẾP TỤC LÀM *(Continue Doing)* |
| :--- | :--- | :--- |
| *Việc chưa làm nhưng nên bắt đầu* | *Việc đang làm nhưng không hiệu quả* | *Việc đang làm tốt, cần duy trì* |
| 1. Thiết lập quy trình **code review** — mỗi PR cần ít nhất 1 người review trước khi merge | 1. Dừng code xong rồi mới viết tài liệu — dẫn đến docs bị trễ so với tiến độ thực tế | 1. Sử dụng **Service Layer** tách biệt logic nghiệp vụ khỏi Controller — code gọn và dễ maintain |
| 2. Viết **feature test** song song ngay khi hoàn thành từng user story, không dồn cuối sprint | 2. Dừng dùng mật khẩu suy đoán được (CCCD) khi tạo tài khoản nhân sự — đã đổi sang random password | 2. Áp dụng **Joi validation** ở frontend đồng bộ với Laravel validation backend |
| 3. Đồng bộ **validation rules** giữa Joi (frontend) và Laravel (backend) ngay từ đầu khi tạo form mới | 3. Dừng để một người làm cả frontend + backend + docs cùng lúc cho một module — cần phân chia rõ ràng hơn | 3. Commit code thường xuyên với message rõ ràng và cập nhật **CHANGELOG.md** theo từng mốc |
| 4. Bổ sung **Redis cache** cho các trang public để cải thiện hiệu năng | 4. Dừng sử dụng trực tiếp `readonly` keyword khi chưa kiểm tra phiên bản PHP — gây lỗi syntax trên PHP < 8.1 | 4. Sử dụng **Soft Delete** cho tất cả các entity quan trọng (học viên, giáo viên, nhân viên, khóa học) |
| 5. Kiểm tra **tương thích PHP version** trước khi sử dụng các tính năng ngôn ngữ mới | 5. Dừng hard-code cấu hình nhạy cảm — chuyển sang biến môi trường `.env` | 5. Ghi **audit log** cho các hành động quan trọng (đăng nhập, đổi mật khẩu, khóa tài khoản) |
| 6. Tạo **seed data** mẫu cho môi trường dev để thành viên mới dễ setup | 6. Dừng bỏ qua validation ràng buộc xóa — cần kiểm tra quan hệ phụ thuộc trước khi xóa (soft-delete constraints) | 6. Tách cổng đăng nhập theo **role** (học viên, giáo viên, nhân sự) — rõ ràng và bảo mật hơn |
| 7. Thiết lập **CI pipeline** tự động chạy test khi push code | 7. Dừng include asset không tồn tại (ví dụ: `register.js` 404) — cần kiểm tra kỹ trước khi deploy | 7. Cập nhật **progress.md** theo dõi tiến độ dự án liên tục |
| 8. Chuẩn bị **tài liệu onboarding** cho thành viên mới tham gia dự án | 8. Dừng đặt hidden input reCAPTCHA ngoài thẻ form — đã sửa, cần rút kinh nghiệm kiểm tra DOM kỹ hơn | 8. Sử dụng **migration** quản lý thay đổi database — dễ rollback và đồng bộ giữa các thành viên |

---

## CAM KẾT HÀNH ĐỘNG CHO SPRINT 2

| STT | Cam kết cải tiến | Người chịu trách nhiệm | Cách đo lường |
| :---: | :--- | :---: | :--- |
| 1 | Thiết lập quy trình code review — mọi PR phải có ít nhất 1 approval trước khi merge | Trưởng nhóm | 100% PR được review trước khi merge; số lỗi phát hiện sau merge giảm ≥ 50% |
| 2 | Viết feature test song song với code — mỗi user story hoàn thành phải kèm test tương ứng | Cả nhóm | Mỗi user story trong Sprint 2 có ít nhất 1 feature test; test coverage tăng so với Sprint 1 |

---

| **Trưởng nhóm ký** | **Thành viên xác nhận** |
| :---: | :---: |
| *(Ký và ghi rõ họ tên)* | *(Tất cả thành viên ký)* |
| <br><br><br><br> | <br><br><br><br> |
