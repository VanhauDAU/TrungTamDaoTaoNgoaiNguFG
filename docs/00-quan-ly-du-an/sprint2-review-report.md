# BÁO CÁO ĐÁNH GIÁ SPRINT 2
*(Sprint Review Report)*

| Tên dự án | Nghiên cứu Laravel xây dựng hệ thống Website Trung tâm Đào tạo Ngoại ngữ | Sprint số | 2 |
| :--- | :--- | :--- | :--- |
| Nhóm | FiveGenius | Ngày báo cáo | 09/04/2026 |

---

## 1. MỤC TIÊU SPRINT

Hoàn thiện hệ thống **Quản lý người dùng** (CRUD tài khoản, hồ sơ người dùng, khóa/kích hoạt tài khoản) và xây dựng module **Khóa học** hoàn chỉnh (quản lý khóa học, phân loại danh mục dạng cây, hiển thị danh sách + chi tiết khóa học phía Client theo SEO slug).

---

## 2. CÁC TÍNH NĂNG ĐÃ HOÀN THÀNH

| # | Tính năng / Công việc | Demo? | Ghi chú |
| :---: | :--- | :---: | :--- |
| 1 | Quản lý người dùng (Học viên/Giáo viên/Nhân viên): tạo, sửa, xóa mềm, khôi phục | ☑ | Đã có route + service + giao diện admin cho từng nhóm người dùng |
| 2 | Quản lý hồ sơ người dùng và bảo mật tài khoản | ☑ | Có cập nhật hồ sơ, đổi mật khẩu, khóa/mở khóa, rotate `remember_token`, ghi log bảo mật |
| 3 | Quản lý khóa học (Admin CRUD) | ☑ | Có CRUD khóa học, upload ảnh, soft delete/restore, slug duy nhất |
| 4 | Quản lý danh mục khóa học dạng cây đệ quy | ☑ | Có cây danh mục nhiều cấp, kiểm tra vòng lặp cha-con, kéo thả sắp xếp cùng cấp |
| 5 | Trang Client `/khoa-hoc` và `/khoa-hoc/{slug}` | ☑ | Có danh sách + tìm kiếm + sắp xếp + phân trang + chi tiết khóa học theo slug; đã tích hợp cache public (Redis) |

---

## 3. CÁC CÔNG VIỆC CHƯA HOÀN THÀNH

| # | Công việc | Lý do chưa hoàn thành | Kế hoạch |
| :---: | :--- | :--- | :--- |
| 1 | Hoàn thiện SEO meta (description/canonical/OG) cho trang khóa học | View hiện tại mới có `title`, chưa có bộ meta SEO đầy đủ | Bổ sung khối meta động trong layout/partial cho trang list + detail khóa học |
| 2 | Bổ sung Schema markup (`application/ld+json`) cho chi tiết khóa học | Chưa thấy JSON-LD trong view chi tiết khóa học | Thêm schema `Course` + dữ liệu lớp đang mở trong Sprint tiếp theo |
| 3 | Mở rộng test coverage cho user story Sprint 2 | Đã có test cho một số luồng (nhân sự, cache) nhưng chưa bao phủ đủ list/detail khóa học phía client | Viết thêm feature test cho `/khoa-hoc`, `/khoa-hoc/{slug}`, và các luồng khóa/mở khóa tài khoản |

---

## 4. BÀI HỌC KINH NGHIỆM

### 😊 Điều làm tốt

- Tách **Service Layer** rõ ràng giúp controller mỏng, dễ bảo trì.
- Duy trì **Soft Delete** cho entity quan trọng giúp an toàn dữ liệu.
- Tích hợp **Redis cache** cho nội dung public giúp tối ưu hiệu năng truy cập trang khóa học.
- Các luồng quản lý nhân sự có tính hoàn thiện tốt (hồ sơ, bàn giao tài khoản, tài liệu, PDF).

### 😞 Điều cần cải thiện

- Cần chốt checklist nghiệm thu SEO sớm hơn để tránh thiếu meta/schema ở cuối Sprint.
- Cần mở rộng test cho các user story phía client của module khóa học.
- Cần đồng bộ tài liệu và phạm vi hoàn thành theo từng sprint để dễ đánh giá burn-down và demo.

---

## 5. KẾ HOẠCH SPRINT TIẾP THEO

1. Hoàn tất technical SEO cho module khóa học (meta/canonical/OG + JSON-LD).
2. Bổ sung test tự động cho toàn bộ user story chính của Sprint 2.
3. Triển khai các hạng mục đang phát triển ưu tiên cao: dashboard báo cáo, đánh giá giáo viên sau khóa học, và nghiệp vụ tài chính mở rộng (hoàn tiền/công nợ).
4. Tiếp tục chuẩn hóa tài liệu vận hành cho các module mới.

---

## 6. ĐÁNH GIÁ TỔNG THỂ

**Mục tiêu Sprint:** ☐ Đạt hoàn toàn &nbsp;&nbsp; ☑ Đạt một phần &nbsp;&nbsp; ☐ Không đạt

**Nhận định:** Các mục tiêu cốt lõi về quản lý người dùng và module khóa học đã triển khai được phần lớn chức năng nghiệp vụ; tuy nhiên các đầu việc SEO nâng cao (meta/schema) và độ bao phủ kiểm thử cho phần client vẫn cần hoàn thiện ở Sprint kế tiếp.
