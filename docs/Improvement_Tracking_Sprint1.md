# BẢNG THEO DÕI CẢI TIẾN
*(Improvement Tracking)*

| Tên dự án | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ |
| :--- | :--- |
| **Nhóm** | FiveGenius |
| **Sprint** | 1 |

---

## DANH SÁCH CAM KẾT CẢI TIẾN

| STT | Cam kết cải tiến | Trạng thái | Ghi chú |
| :---: | :--- | :--- | :--- |
| 1 | Áp dụng **Joi validation** ở frontend cho tất cả form Auth (login, register, forgot password, đổi mật khẩu) để giảm request không hợp lệ đến server | ☑ Hoàn thành | Đã triển khai Joi schema cho các form Auth, đồng bộ với Laravel validation backend |
| 2 | Tách **Service Layer** riêng biệt khỏi Controller để code dễ bảo trì và test hơn | ☑ Hoàn thành | Đã tách services vào `app/Services/Auth`, `app/Services/Admin`, `app/Services/Client` |
| 3 | Bổ sung **reCAPTCHA v3** cho các form public để chống bot/spam | ☑ Hoàn thành | Áp dụng cho login học viên, đăng ký, quên mật khẩu |
| 4 | Cải thiện **bảo mật phiên đăng nhập** — rotate remember_token khi đổi/reset mật khẩu, audit log cho phiên | ☑ Hoàn thành | Thêm bảng `phien_dang_nhap`, `nhatky_bao_mat`, rotate token tự động |
| 5 | Viết **tài liệu vận hành** cho các module đã hoàn thành để thành viên mới dễ tiếp cận | ☐ Đang làm | Đã có docs cho Auth, học phí, đăng ký, nhân sự; cần bổ sung thêm cho các module còn lại |

---

## ĐÁNH GIÁ HIỆU QUẢ CẢI TIẾN

| 😊 Cải tiến có hiệu quả | 🔄 Cần điều chỉnh thêm |
| :--- | :--- |
| *(Những gì đã thay đổi tốt hơn)* | *(Những gì chưa hiệu quả)* |
| 1. **Joi validation frontend** giúp giảm đáng kể số lượng request lỗi gửi về server, cải thiện trải nghiệm người dùng với phản hồi lỗi tức thì | 1. Một số **rule validation** giữa Joi (frontend) và Laravel (backend) chưa đồng bộ hoàn toàn (ví dụ: regex phone 10 số, tên không chứa số) — đã phát hiện và sửa giữa sprint |
| 2. **Tách Service Layer** giúp Controller gọn hơn, logic nghiệp vụ tập trung, dễ viết unit test và tái sử dụng | 2. **Tài liệu kỹ thuật** viết chưa kịp tiến độ code — một số module hoàn thành nhưng chưa có hướng dẫn vận hành đầy đủ |
| 3. **Audit log phiên đăng nhập** giúp tracking được thiết bị đăng nhập, phát hiện truy cập bất thường | 3. Chưa có quy trình **code review** chính thức giữa các thành viên — dẫn đến một số lỗi syntax (`readonly` PHP 8.1) được phát hiện muộn |
| 4. **Google Login + Email verification** hoạt động ổn định, nâng cao bảo mật cho học viên tự đăng ký | 4. Cần bổ sung thêm **feature test** cho các luồng nghiệp vụ quan trọng ngoài module nhân sự |

---

## KẾ HOẠCH CHO SPRINT TIẾP THEO

1. **Hoàn thiện module Quản lý người dùng:** CRUD tài khoản toàn diện, quản lý hồ sơ, chức năng khóa/kích hoạt tài khoản với audit log.
2. **Xây dựng module Khóa học:** Quản lý khóa học (CRUD), phân loại danh mục dạng cây đệ quy, hiển thị danh sách & chi tiết khóa học phía Client với SEO slug.
3. **Đồng bộ validation:** Đảm bảo mọi form mới đều có Joi validation frontend khớp với Laravel validation backend ngay từ đầu.
4. **Thiết lập quy trình code review:** Mỗi pull request cần ít nhất 1 thành viên review trước khi merge để phát hiện lỗi sớm.
5. **Viết test song song:** Viết feature test cho từng user story ngay khi hoàn thành, không để dồn cuối sprint.
