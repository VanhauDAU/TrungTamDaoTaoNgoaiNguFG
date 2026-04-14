<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CauHinhHeThongSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [

            // ══════════════════════════════════════════
            // NHÓM: HỆ THỐNG
            // ══════════════════════════════════════════
            ['nhom' => 'he_thong', 'khoa' => 'ten_trung_tam',         'ten_hien_thi' => 'Tên trung tâm',                  'gia_tri' => 'Trung Tâm Đào Tạo Ngoại Ngữ FG',     'kieu_du_lieu' => 'text',    'mo_ta' => 'Tên đầy đủ của trung tâm, hiển thị trên tiêu đề trang.',               'yeu_cau' => true,  'thu_tu' => 1],
            ['nhom' => 'he_thong', 'khoa' => 'ten_ngan',              'ten_hien_thi' => 'Tên viết tắt',                   'gia_tri' => 'FG Language Center',                  'kieu_du_lieu' => 'text',    'mo_ta' => 'Tên ngắn gọn dùng trong email/badge.',                                  'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'he_thong', 'khoa' => 'ma_trung_tam',          'ten_hien_thi' => 'Mã trung tâm',                   'gia_tri' => 'FGLC-2024',                           'kieu_du_lieu' => 'text',    'mo_ta' => 'Mã định danh nội bộ.',                                                  'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'he_thong', 'khoa' => 'dia_chi_chinh',         'ten_hien_thi' => 'Địa chỉ chính',                  'gia_tri' => '123 Nguyễn Văn Linh, Q.7, TP.HCM',   'kieu_du_lieu' => 'textarea','mo_ta' => 'Địa chỉ trụ sở chính của trung tâm.',                                   'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'he_thong', 'khoa' => 'email_lien_he',         'ten_hien_thi' => 'Email liên hệ',                  'gia_tri' => 'info@fglanguage.edu.vn',              'kieu_du_lieu' => 'email',   'mo_ta' => 'Email hiển thị trên trang liên hệ và footer.',                          'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'he_thong', 'khoa' => 'so_dien_thoai',         'ten_hien_thi' => 'Số điện thoại',                  'gia_tri' => '028 3123 4567',                       'kieu_du_lieu' => 'text',    'mo_ta' => 'Số điện thoại liên hệ chính.',                                          'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'he_thong', 'khoa' => 'website',               'ten_hien_thi' => 'Website',                        'gia_tri' => 'https://fglanguage.edu.vn',           'kieu_du_lieu' => 'url',     'mo_ta' => 'Địa chỉ website chính thức.',                                           'yeu_cau' => false, 'thu_tu' => 7],
            ['nhom' => 'he_thong', 'khoa' => 'mu_phut_time_zone',     'ten_hien_thi' => 'Múi giờ',                        'gia_tri' => 'Asia/Ho_Chi_Minh',                   'kieu_du_lieu' => 'select',  'mo_ta' => 'Múi giờ mặc định của hệ thống.',                       'yeu_cau' => true,  'thu_tu' => 8,
                'tuy_chon' => json_encode([
                    ['label' => 'Việt Nam (UTC+7)', 'value' => 'Asia/Ho_Chi_Minh'],
                    ['label' => 'Singapore (UTC+8)', 'value' => 'Asia/Singapore'],
                    ['label' => 'UTC', 'value' => 'UTC'],
                ])],
            ['nhom' => 'he_thong', 'khoa' => 'ngon_ngu_mac_dinh',     'ten_hien_thi' => 'Ngôn ngữ mặc định',             'gia_tri' => 'vi',                                  'kieu_du_lieu' => 'select',  'mo_ta' => 'Ngôn ngữ hiển thị mặc định của hệ thống.',         'yeu_cau' => true,  'thu_tu' => 9,
                'tuy_chon' => json_encode([
                    ['label' => 'Tiếng Việt', 'value' => 'vi'],
                    ['label' => 'English', 'value' => 'en'],
                ])],
            ['nhom' => 'he_thong', 'khoa' => 'che_do_bao_tri',        'ten_hien_thi' => 'Chế độ bảo trì',                'gia_tri' => '0',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Khi bật, người dùng sẽ thấy trang bảo trì thay vì nội dung thường.',    'yeu_cau' => false, 'thu_tu' => 10],

            // ══════════════════════════════════════════
            // NHÓM: GIÁO DỤC
            // ══════════════════════════════════════════
            ['nhom' => 'giao_duc', 'khoa' => 'so_hoc_vien_toi_da_lop','ten_hien_thi' => 'Sĩ số tối đa mặc định/lớp',    'gia_tri' => '20',                                  'kieu_du_lieu' => 'number',  'mo_ta' => 'Số học viên tối đa trong một lớp học (mặc định khi tạo mới).',           'yeu_cau' => true,  'thu_tu' => 1],
            ['nhom' => 'giao_duc', 'khoa' => 'so_buoi_hoc_toi_thieu', 'ten_hien_thi' => 'Số buổi học tối thiểu/khóa',   'gia_tri' => '10',                                  'kieu_du_lieu' => 'number',  'mo_ta' => 'Số buổi học tối thiểu để một khóa học được xuất bằng.',                 'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'giao_duc', 'khoa' => 'ty_le_diem_danh_dat',   'ten_hien_thi' => 'Tỷ lệ chuyên cần tối thiểu (%)', 'gia_tri' => '80',                               'kieu_du_lieu' => 'number',  'mo_ta' => 'Tỷ lệ phần trăm buổi học tối thiểu học viên phải tham dự.',            'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'giao_duc', 'khoa' => 'cho_phep_hoc_thu',      'ten_hien_thi' => 'Cho phép học thử',              'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Cho phép học viên tham gia buổi học thử trước khi đăng ký chính thức.', 'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'giao_duc', 'khoa' => 'so_buoi_hoc_thu',       'ten_hien_thi' => 'Số buổi học thử miễn phí',     'gia_tri' => '1',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Số buổi học viên được thử miễn phí.',                                   'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'giao_duc', 'khoa' => 'han_dang_ky_truoc_ngay','ten_hien_thi' => 'Hạn đăng ký trước khai giảng (ngày)', 'gia_tri' => '3',                            'kieu_du_lieu' => 'number',  'mo_ta' => 'Số ngày trước khi lớp khai giảng mà học viên vẫn có thể đăng ký.',      'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'giao_duc', 'khoa' => 'loai_ngon_ngu',         'ten_hien_thi' => 'Ngôn ngữ đào tạo chính',        'gia_tri' => 'Tiếng Anh',                           'kieu_du_lieu' => 'text',    'mo_ta' => 'Ngôn ngữ chính mà trung tâm đào tạo (VD: Tiếng Anh, Tiếng Nhật).',     'yeu_cau' => false, 'thu_tu' => 7],
            ['nhom' => 'giao_duc', 'khoa' => 'cap_do_trinh_do',       'ten_hien_thi' => 'Các cấp độ trình độ',           'gia_tri' => 'Sơ cấp,Trung cấp,Cao cấp,IELTS,TOEIC', 'kieu_du_lieu' => 'textarea', 'mo_ta' => 'Danh sách cấp độ, cách nhau bởi dấu phẩy.',                         'yeu_cau' => false, 'thu_tu' => 8],
            ['nhom' => 'giao_duc', 'khoa' => 'tu_dong_tao_buoi_hoc',  'ten_hien_thi' => 'Tự động tạo buổi học',         'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Tự động sinh lịch buổi học khi tạo lớp mới.',                            'yeu_cau' => false, 'thu_tu' => 9],
            ['nhom' => 'giao_duc', 'khoa' => 'cho_phep_chuyen_lop',   'ten_hien_thi' => 'Cho phép chuyển lớp',          'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Cho phép học viên chuyển sang lớp khác trong cùng khóa học.',           'yeu_cau' => false, 'thu_tu' => 10],
            ['nhom' => 'giao_duc', 'khoa' => 'phi_chuyen_lop',        'ten_hien_thi' => 'Phí chuyển lớp (VNĐ)',         'gia_tri' => '0',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Phí phát sinh khi học viên yêu cầu chuyển lớp. Nhập 0 nếu miễn phí.',  'yeu_cau' => false, 'thu_tu' => 11],
            ['nhom' => 'giao_duc', 'khoa' => 'cho_phep_bao_luu',      'ten_hien_thi' => 'Cho phép bảo lưu',             'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Cho phép học viên bảo lưu đăng ký khi chưa hoàn thành khóa học.',      'yeu_cau' => false, 'thu_tu' => 12],
            ['nhom' => 'giao_duc', 'khoa' => 'thoi_han_bao_luu_thang','ten_hien_thi' => 'Thời hạn bảo lưu (tháng)',     'gia_tri' => '6',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Thời hạn tối đa mà học viên có thể bảo lưu đăng ký.',                  'yeu_cau' => false, 'thu_tu' => 13],

            // ══════════════════════════════════════════
            // NHÓM: BẢO MẬT
            // ══════════════════════════════════════════
            ['nhom' => 'bao_mat', 'khoa' => 'mat_khau_do_dai_toi_thieu', 'ten_hien_thi' => 'Độ dài mật khẩu tối thiểu', 'gia_tri' => '8',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Số ký tự tối thiểu cho mật khẩu người dùng.',                           'yeu_cau' => true,  'thu_tu' => 1],
            ['nhom' => 'bao_mat', 'khoa' => 'bat_buoc_doi_mat_khau',  'ten_hien_thi' => 'Bắt buộc đổi mật khẩu lần đầu', 'gia_tri' => '1',                                  'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Yêu cầu đổi mật khẩu khi đăng nhập lần đầu (tài khoản tạo bởi admin).',  'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'bao_mat', 'khoa' => 'thoi_gian_phien_phut',   'ten_hien_thi' => 'Thời gian phiên đăng nhập (phút)', 'gia_tri' => '120',                              'kieu_du_lieu' => 'number',  'mo_ta' => 'Phiên đăng nhập hết hạn sau bao nhiêu phút không hoạt động.',           'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'bao_mat', 'khoa' => 'so_thiet_bi_dang_nhap',  'ten_hien_thi' => 'Số thiết bị đăng nhập tối đa', 'gia_tri' => '3',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Số thiết bị tối đa mà một tài khoản có thể đăng nhập đồng thời.',       'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'bao_mat', 'khoa' => 'cho_phep_dang_nhap_google', 'ten_hien_thi' => 'Đăng nhập bằng Google',     'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Cho phép học viên đăng nhập/đăng ký bằng tài khoản Google.',            'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'bao_mat', 'khoa' => 'gio_khoa_tai_khoan',     'ten_hien_thi' => 'Khóa tài khoản sau X lần sai', 'gia_tri' => '5',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Tài khoản bị khóa tạm thời sau số lần nhập sai mật khẩu này.',          'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'bao_mat', 'khoa' => 'thoi_gian_khoa_phut',    'ten_hien_thi' => 'Thời gian khóa tài khoản (phút)', 'gia_tri' => '15',                               'kieu_du_lieu' => 'number',  'mo_ta' => 'Tài khoản bị khóa trong bao nhiêu phút khi bị tạm khóa.',              'yeu_cau' => false, 'thu_tu' => 7],
            ['nhom' => 'bao_mat', 'khoa' => 'log_hoat_dong',          'ten_hien_thi' => 'Ghi log hoạt động người dùng',  'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Lưu lại nhật ký đăng nhập và các hoạt động quan trọng.',               'yeu_cau' => false, 'thu_tu' => 8],

            // ══════════════════════════════════════════
            // NHÓM: THÔNG BÁO
            // ══════════════════════════════════════════
            ['nhom' => 'thong_bao', 'khoa' => 'email_gui_tu',         'ten_hien_thi' => 'Email gửi đi (From)',           'gia_tri' => 'no-reply@fglanguage.edu.vn',          'kieu_du_lieu' => 'email',   'mo_ta' => 'Địa chỉ email được dùng làm người gửi khi hệ thống gửi email.',         'yeu_cau' => false, 'thu_tu' => 1],
            ['nhom' => 'thong_bao', 'khoa' => 'ten_nguoi_gui',        'ten_hien_thi' => 'Tên người gửi email',           'gia_tri' => 'FG Language Center',                  'kieu_du_lieu' => 'text',    'mo_ta' => 'Tên hiển thị trong email gửi đi.',                                      'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'thong_bao', 'khoa' => 'tb_dang_ky_moi',       'ten_hien_thi' => 'Thông báo đăng ký mới',        'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Gửi email khi có đăng ký học mới.',                                     'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'thong_bao', 'khoa' => 'tb_xac_nhan_dang_ky',  'ten_hien_thi' => 'Xác nhận đăng ký cho học viên', 'gia_tri' => '1',                                  'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Tự động gửi email xác nhận đăng ký thành công cho học viên.',          'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'thong_bao', 'khoa' => 'tb_nhac_hoc_phi',      'ten_hien_thi' => 'Nhắc nhở học phí trước hạn',   'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Tự động gửi nhắc nhở khi học phí sắp đến hạn.',                        'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'thong_bao', 'khoa' => 'so_ngay_nhac_hoc_phi', 'ten_hien_thi' => 'Nhắc học phí trước (ngày)',    'gia_tri' => '7',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Số ngày trước hạn thanh toán sẽ gửi email nhắc nhở.',                  'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'thong_bao', 'khoa' => 'tb_khai_giang',        'ten_hien_thi' => 'Thông báo khai giảng lớp',     'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Gửi thông báo cho học viên trước ngày khai giảng lớp học.',            'yeu_cau' => false, 'thu_tu' => 7],
            ['nhom' => 'thong_bao', 'khoa' => 'so_ngay_truoc_khai_giang', 'ten_hien_thi' => 'Thông báo trước khai giảng (ngày)', 'gia_tri' => '1',                          'kieu_du_lieu' => 'number',  'mo_ta' => 'Số ngày trước khai giảng sẽ gửi thông báo cho học viên.',             'yeu_cau' => false, 'thu_tu' => 8],

            // ══════════════════════════════════════════
            // NHÓM: TÀI CHÍNH
            // ══════════════════════════════════════════
            ['nhom' => 'tai_chinh', 'khoa' => 'don_vi_tien_te',       'ten_hien_thi' => 'Đơn vị tiền tệ',               'gia_tri' => 'VNĐ',                                 'kieu_du_lieu' => 'text',    'mo_ta' => 'Đơn vị tiền tệ hiển thị trên hóa đơn và giao diện.',                   'yeu_cau' => true,  'thu_tu' => 1],
            ['nhom' => 'tai_chinh', 'khoa' => 'ty_le_vat',            'ten_hien_thi' => 'Thuế VAT (%)',                  'gia_tri' => '10',                                  'kieu_du_lieu' => 'number',  'mo_ta' => 'Tỷ lệ thuế VAT áp dụng trên hóa đơn (0 nếu không áp dụng).',          'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'tai_chinh', 'khoa' => 'phuong_thuc_thanh_toan', 'ten_hien_thi' => 'Phương thức thanh toán',     'gia_tri' => 'Tiền mặt,Chuyển khoản,VNPay',        'kieu_du_lieu' => 'textarea','mo_ta' => 'Các phương thức thanh toán được chấp nhận, cách nhau bởi dấu phẩy.',    'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'tai_chinh', 'khoa' => 'han_thanh_toan_ngay',  'ten_hien_thi' => 'Hạn thanh toán (ngày)',        'gia_tri' => '7',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Số ngày kể từ ngày xuất hóa đơn đến hạn thanh toán.',                   'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'tai_chinh', 'khoa' => 'ty_le_giam_gia_toi_da','ten_hien_thi' => 'Giảm giá tối đa (%)',          'gia_tri' => '50',                                  'kieu_du_lieu' => 'number',  'mo_ta' => 'Tỷ lệ % tối đa được phép giảm giá trên một hóa đơn.',                  'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'tai_chinh', 'khoa' => 'chinh_sach_hoan_tien', 'ten_hien_thi' => 'Chính sách hoàn tiền',         'gia_tri' => 'Hoàn 100% nếu hủy trước 3 ngày khai giảng. Hoàn 50% nếu hủy sau khi khai giảng.', 'kieu_du_lieu' => 'textarea', 'mo_ta' => 'Nội dung chính sách hoàn tiền hiển thị cho học viên.', 'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'tai_chinh', 'khoa' => 'so_tai_khoan_ngan_hang', 'ten_hien_thi' => 'Số tài khoản ngân hàng',    'gia_tri' => '0123456789',                          'kieu_du_lieu' => 'text',    'mo_ta' => 'Số tài khoản ngân hàng để học viên chuyển khoản.',                      'yeu_cau' => false, 'thu_tu' => 7],
            ['nhom' => 'tai_chinh', 'khoa' => 'ten_ngan_hang',         'ten_hien_thi' => 'Ngân hàng thụ hưởng',          'gia_tri' => 'Vietcombank',                         'kieu_du_lieu' => 'text',    'mo_ta' => 'Tên ngân hàng thụ hưởng.',                                              'yeu_cau' => false, 'thu_tu' => 8],
            ['nhom' => 'tai_chinh', 'khoa' => 'chu_tai_khoan',         'ten_hien_thi' => 'Chủ tài khoản',                'gia_tri' => 'TRUNG TAM DAO TAO NGOAI NGU FG',     'kieu_du_lieu' => 'text',    'mo_ta' => 'Tên chủ tài khoản ngân hàng.',                                          'yeu_cau' => false, 'thu_tu' => 9],

            // ══════════════════════════════════════════
            // NHÓM: GIAO DIỆN
            // ══════════════════════════════════════════
            ['nhom' => 'giao_dien', 'khoa' => 'mau_chinh',            'ten_hien_thi' => 'Màu chủ đề chính',             'gia_tri' => '#7c3aed',                             'kieu_du_lieu' => 'color',   'mo_ta' => 'Màu chủ đạo của giao diện admin.',                                      'yeu_cau' => false, 'thu_tu' => 1],
            ['nhom' => 'giao_dien', 'khoa' => 'mau_thu_hai',          'ten_hien_thi' => 'Màu chủ đề thứ hai',           'gia_tri' => '#0891b2',                             'kieu_du_lieu' => 'color',   'mo_ta' => 'Màu phụ bổ sung cho giao diện.',                                        'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'giao_dien', 'khoa' => 'hien_thi_logo',        'ten_hien_thi' => 'Hiển thị logo trên sidebar',   'gia_tri' => '1',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Bật/tắt logo trung tâm trên sidebar admin.',                            'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'giao_dien', 'khoa' => 'so_hang_moi_trang',    'ten_hien_thi' => 'Số hàng mỗi trang (phân trang)', 'gia_tri' => '15',                                'kieu_du_lieu' => 'select',  'mo_ta' => 'Số bản ghi hiển thị mặc định trên mỗi trang danh sách.', 'yeu_cau' => false, 'thu_tu' => 4,
                'tuy_chon' => json_encode([
                    ['label' => '10 hàng', 'value' => '10'],
                    ['label' => '15 hàng', 'value' => '15'],
                    ['label' => '20 hàng', 'value' => '20'],
                    ['label' => '25 hàng', 'value' => '25'],
                    ['label' => '50 hàng', 'value' => '50'],
                ])],
            ['nhom' => 'giao_dien', 'khoa' => 'hien_thi_footer_info', 'ten_hien_thi' => 'Hiển thị thông tin hệ thống footer', 'gia_tri' => '1',                             'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Hiển thị phiên bản Laravel/PHP ở footer trang admin.',                  'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'giao_dien', 'khoa' => 'sidebar_thu_gon',      'ten_hien_thi' => 'Sidebar thu gọn mặc định',     'gia_tri' => '0',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Mặc định thu gọn sidebar khi tải trang.',                               'yeu_cau' => false, 'thu_tu' => 6],

            // ══════════════════════════════════════════
            // NHÓM: TÍCH HỢP API
            // ══════════════════════════════════════════
            ['nhom' => 'tich_hop', 'khoa' => 'vietqr_bat',            'ten_hien_thi' => 'Kích hoạt VietQR (tra cứu CCCD)', 'gia_tri' => '1',                                'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Bật tính năng tra cứu CCCD/CMND qua VietQR API khi tạo học viên.',    'yeu_cau' => false, 'thu_tu' => 1],
            ['nhom' => 'tich_hop', 'khoa' => 'vietqr_timeout',        'ten_hien_thi' => 'VietQR Timeout (giây)',         'gia_tri' => '8',                                   'kieu_du_lieu' => 'number',  'mo_ta' => 'Thời gian chờ tối đa khi gọi VietQR API.',                              'yeu_cau' => false, 'thu_tu' => 2],
            ['nhom' => 'tich_hop', 'khoa' => 'google_recaptcha_bat',  'ten_hien_thi' => 'Kích hoạt Google reCAPTCHA',   'gia_tri' => '0',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Bật Google reCAPTCHA trên form đăng ký/liên hệ.',                       'yeu_cau' => false, 'thu_tu' => 3],
            ['nhom' => 'tich_hop', 'khoa' => 'google_maps_bat',       'ten_hien_thi' => 'Kích hoạt Google Maps',        'gia_tri' => '0',                                   'kieu_du_lieu' => 'boolean', 'mo_ta' => 'Hiển thị bản đồ Google Maps trên trang liên hệ/cơ sở.',                'yeu_cau' => false, 'thu_tu' => 4],
            ['nhom' => 'tich_hop', 'khoa' => 'facebook_pixel_id',     'ten_hien_thi' => 'Facebook Pixel ID',            'gia_tri' => '',                                    'kieu_du_lieu' => 'text',    'mo_ta' => 'Facebook Pixel ID để theo dõi chuyển đổi (để trống nếu không dùng).',  'yeu_cau' => false, 'thu_tu' => 5],
            ['nhom' => 'tich_hop', 'khoa' => 'google_analytics_id',   'ten_hien_thi' => 'Google Analytics ID (GA4)',    'gia_tri' => '',                                    'kieu_du_lieu' => 'text',    'mo_ta' => 'Google Analytics Measurement ID (G-XXXXXXXXXX), để trống nếu không dùng.', 'yeu_cau' => false, 'thu_tu' => 6],
            ['nhom' => 'tich_hop', 'khoa' => 'zalo_oa_id',            'ten_hien_thi' => 'Zalo OA ID',                   'gia_tri' => '',                                    'kieu_du_lieu' => 'text',    'mo_ta' => 'Zalo Official Account ID để tích hợp Zalo Chat.',                       'yeu_cau' => false, 'thu_tu' => 7],
        ];

        foreach ($rows as $row) {
            $tuyChon = $row['tuy_chon'] ?? null;
            unset($row['tuy_chon']);

            DB::table('cau_hinh_he_thong')->updateOrInsert(
                ['khoa' => $row['khoa']],
                array_merge($row, [
                    'gia_tri_mac_dinh' => $row['gia_tri'],
                    'tuy_chon'         => $tuyChon,
                    'an_trong_ui'      => false,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ])
            );
        }
    }
}
