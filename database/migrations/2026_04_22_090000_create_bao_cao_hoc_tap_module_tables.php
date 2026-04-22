<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bao_cao_hoc_tap_mau', function (Blueprint $table) {
            $table->bigIncrements('baoCaoHocTapMauId');
            $table->string('tenMau', 150);
            $table->text('moTa')->nullable();
            $table->string('phienBan', 20)->default('1.0');
            $table->boolean('macDinh')->default(true);
            $table->boolean('kichHoat')->default(true);
            $table->timestamps();
        });

        Schema::create('bao_cao_hoc_tap_mau_tieu_chi', function (Blueprint $table) {
            $table->bigIncrements('baoCaoHocTapMauTieuChiId');
            $table->unsignedBigInteger('baoCaoHocTapMauId');
            $table->string('nhom', 150);
            $table->string('maTieuChi', 120);
            $table->string('tenTieuChi', 200);
            $table->string('loaiDuLieu', 40);
            $table->json('danhSachMuc')->nullable();
            $table->json('tuyChon')->nullable();
            $table->boolean('batBuoc')->default(false);
            $table->boolean('isReadonly')->default(false);
            $table->unsignedInteger('thuTu')->default(0);
            $table->timestamps();

            $table->unique(['baoCaoHocTapMauId', 'maTieuChi'], 'uq_bcht_mau_tieu_chi');
            $table->foreign('baoCaoHocTapMauId', 'fk_bcht_mau_tieu_chi_mau')
                ->references('baoCaoHocTapMauId')
                ->on('bao_cao_hoc_tap_mau')
                ->onDelete('cascade');
        });

        Schema::create('bao_cao_hoc_tap_dot_danh_gia', function (Blueprint $table) {
            $table->bigIncrements('dotDanhGiaId');
            $table->integer('lopHocId');
            $table->unsignedBigInteger('baoCaoHocTapMauId')->nullable();
            $table->string('tenDot', 150);
            $table->date('tuNgay')->nullable();
            $table->date('denNgay')->nullable();
            $table->date('hanNop')->nullable();
            $table->date('hanDuyet')->nullable();
            $table->string('trangThai', 40)->default('collecting');
            $table->integer('createdById')->nullable();
            $table->timestamp('publishedAt')->nullable();
            $table->timestamp('closedAt')->nullable();
            $table->timestamps();

            $table->index(['lopHocId', 'trangThai'], 'idx_bcht_dot_lop_status');
            $table->foreign('lopHocId', 'fk_bcht_dot_lophoc')
                ->references('lopHocId')
                ->on('lophoc')
                ->onDelete('cascade');
            $table->foreign('baoCaoHocTapMauId', 'fk_bcht_dot_mau')
                ->references('baoCaoHocTapMauId')
                ->on('bao_cao_hoc_tap_mau')
                ->onDelete('set null');
            $table->foreign('createdById', 'fk_bcht_dot_creator')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->onDelete('set null');
        });

        Schema::create('bao_cao_hoc_tap', function (Blueprint $table) {
            $table->bigIncrements('baoCaoHocTapId');
            $table->unsignedBigInteger('dotDanhGiaId');
            $table->integer('dangKyLopHocId');
            $table->integer('giaoVienId')->nullable();
            $table->integer('nguoiDuyetId')->nullable();
            $table->unsignedBigInteger('parentBaoCaoHocTapId')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('trangThai', 40)->default('draft');
            $table->text('staffReviewNote')->nullable();
            $table->json('metadataSnapshot')->nullable();
            $table->timestamp('submittedAt')->nullable();
            $table->timestamp('approvedAt')->nullable();
            $table->timestamp('publishedAt')->nullable();
            $table->timestamps();

            $table->unique(['dotDanhGiaId', 'dangKyLopHocId', 'version'], 'uq_bcht_dot_registration_version');
            $table->index(['trangThai', 'giaoVienId'], 'idx_bcht_status_teacher');
            $table->foreign('dotDanhGiaId', 'fk_bcht_report_dot')
                ->references('dotDanhGiaId')
                ->on('bao_cao_hoc_tap_dot_danh_gia')
                ->onDelete('cascade');
            $table->foreign('dangKyLopHocId', 'fk_bcht_report_registration')
                ->references('dangKyLopHocId')
                ->on('dangkylophoc')
                ->onDelete('cascade');
            $table->foreign('giaoVienId', 'fk_bcht_report_teacher')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->onDelete('set null');
            $table->foreign('nguoiDuyetId', 'fk_bcht_report_reviewer')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->onDelete('set null');
            $table->foreign('parentBaoCaoHocTapId', 'fk_bcht_report_parent')
                ->references('baoCaoHocTapId')
                ->on('bao_cao_hoc_tap')
                ->onDelete('set null');
        });

        Schema::create('bao_cao_hoc_tap_tieu_chi', function (Blueprint $table) {
            $table->bigIncrements('baoCaoHocTapTieuChiId');
            $table->unsignedBigInteger('baoCaoHocTapId');
            $table->unsignedBigInteger('baoCaoHocTapMauTieuChiId')->nullable();
            $table->string('nhom', 150);
            $table->string('maTieuChi', 120);
            $table->string('tenTieuChi', 200);
            $table->string('loaiDuLieu', 40);
            $table->string('giaTriMucDanhGia', 100)->nullable();
            $table->decimal('giaTriSo', 10, 2)->nullable();
            $table->text('noiDungNhanXet')->nullable();
            $table->json('tuyChon')->nullable();
            $table->boolean('batBuoc')->default(false);
            $table->boolean('isReadonly')->default(false);
            $table->unsignedInteger('thuTu')->default(0);
            $table->timestamps();

            $table->index(['baoCaoHocTapId', 'thuTu'], 'idx_bcht_criteria_report_order');
            $table->foreign('baoCaoHocTapId', 'fk_bcht_criteria_report')
                ->references('baoCaoHocTapId')
                ->on('bao_cao_hoc_tap')
                ->onDelete('cascade');
            $table->foreign('baoCaoHocTapMauTieuChiId', 'fk_bcht_criteria_template')
                ->references('baoCaoHocTapMauTieuChiId')
                ->on('bao_cao_hoc_tap_mau_tieu_chi')
                ->onDelete('set null');
        });

        Schema::create('bao_cao_hoc_tap_lich_su', function (Blueprint $table) {
            $table->bigIncrements('baoCaoHocTapLichSuId');
            $table->unsignedBigInteger('baoCaoHocTapId');
            $table->string('hanhDong', 80);
            $table->string('trangThaiTruoc', 40)->nullable();
            $table->string('trangThaiSau', 40)->nullable();
            $table->integer('nguoiThucHienId')->nullable();
            $table->text('ghiChu')->nullable();
            $table->json('duLieu')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['baoCaoHocTapId', 'created_at'], 'idx_bcht_history_report_created');
            $table->foreign('baoCaoHocTapId', 'fk_bcht_history_report')
                ->references('baoCaoHocTapId')
                ->on('bao_cao_hoc_tap')
                ->onDelete('cascade');
            $table->foreign('nguoiThucHienId', 'fk_bcht_history_actor')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->onDelete('set null');
        });

        $templateId = DB::table('bao_cao_hoc_tap_mau')->insertGetId([
            'tenMau' => 'Mẫu báo cáo học tập mặc định',
            'moTa' => 'Rubric mặc định cho portal giáo viên và quy trình duyệt/phát hành báo cáo học tập.',
            'phienBan' => '1.0',
            'macDinh' => true,
            'kichHoat' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ratingOptions = json_encode([
            'Chưa đạt',
            'Đạt tối thiểu',
            'Khá',
            'Tốt',
            'Rất tốt',
        ], JSON_UNESCAPED_UNICODE);

        $rows = [
            ['nhom' => 'Đánh giá đầu vào / trước khi học', 'maTieuChi' => 'input_pronunciation', 'tenTieuChi' => 'Phát âm', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 10],
            ['nhom' => 'Đánh giá đầu vào / trước khi học', 'maTieuChi' => 'input_vocabulary', 'tenTieuChi' => 'Từ vựng', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 20],
            ['nhom' => 'Đánh giá đầu vào / trước khi học', 'maTieuChi' => 'input_grammar', 'tenTieuChi' => 'Ngữ pháp', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 30],
            ['nhom' => 'Đánh giá đầu vào / trước khi học', 'maTieuChi' => 'input_listening', 'tenTieuChi' => 'Kỹ năng nghe', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 40],
            ['nhom' => 'Đánh giá đầu vào / trước khi học', 'maTieuChi' => 'input_speaking', 'tenTieuChi' => 'Kỹ năng nói', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 50],

            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_total_sessions', 'tenTieuChi' => 'Số buổi học', 'loaiDuLieu' => 'readonly_system', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 1, 'thuTu' => 110],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_absent_excused', 'tenTieuChi' => 'Số buổi vắng có phép', 'loaiDuLieu' => 'number', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 120],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_absent_unexcused', 'tenTieuChi' => 'Số buổi vắng không phép', 'loaiDuLieu' => 'readonly_system', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 1, 'thuTu' => 130],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_care_sessions', 'tenTieuChi' => 'Số buổi care', 'loaiDuLieu' => 'number', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 140],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_homework_submitted', 'tenTieuChi' => 'Số bài tập đã nộp', 'loaiDuLieu' => 'number', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 150],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_homework_missing', 'tenTieuChi' => 'Số bài không nộp', 'loaiDuLieu' => 'number', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 160],
            ['nhom' => 'Trong thời gian học / Chuyên cần', 'maTieuChi' => 'attendance_homework_quality', 'tenTieuChi' => 'Chất lượng đầu tư bài tập', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 170],

            ['nhom' => 'Trong thời gian học / Biểu hiện học tập', 'maTieuChi' => 'learning_attitude', 'tenTieuChi' => 'Thái độ học tập', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 210],
            ['nhom' => 'Trong thời gian học / Biểu hiện học tập', 'maTieuChi' => 'learning_absorption', 'tenTieuChi' => 'Mức độ tiếp thu', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 220],
            ['nhom' => 'Trong thời gian học / Biểu hiện học tập', 'maTieuChi' => 'learning_interaction', 'tenTieuChi' => 'Tương tác thực hành', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 230],

            ['nhom' => 'Trong thời gian học / Tuân thủ hợp đồng', 'maTieuChi' => 'compliance_absent_limit', 'tenTieuChi' => 'Vắng không phép / mức quy định', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 310],
            ['nhom' => 'Trong thời gian học / Tuân thủ hợp đồng', 'maTieuChi' => 'compliance_homework_limit', 'tenTieuChi' => 'Không nộp bài / mức quy định', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 320],
            ['nhom' => 'Trong thời gian học / Tuân thủ hợp đồng', 'maTieuChi' => 'compliance_skip_exam_limit', 'tenTieuChi' => 'Bỏ thi / mức quy định', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 0, 'isReadonly' => 0, 'thuTu' => 330],
            ['nhom' => 'Trong thời gian học / Tuân thủ hợp đồng', 'maTieuChi' => 'compliance_conclusion', 'tenTieuChi' => 'Kết luận tuân thủ', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 340],

            ['nhom' => 'Trong thời gian học / Năng lực tại thời điểm đánh giá', 'maTieuChi' => 'current_pronunciation', 'tenTieuChi' => 'Phát âm', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 410],
            ['nhom' => 'Trong thời gian học / Năng lực tại thời điểm đánh giá', 'maTieuChi' => 'current_vocabulary', 'tenTieuChi' => 'Từ vựng', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 420],
            ['nhom' => 'Trong thời gian học / Năng lực tại thời điểm đánh giá', 'maTieuChi' => 'current_grammar', 'tenTieuChi' => 'Ngữ pháp', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 430],
            ['nhom' => 'Trong thời gian học / Năng lực tại thời điểm đánh giá', 'maTieuChi' => 'current_practice', 'tenTieuChi' => 'Kỹ năng thực hành', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 440],

            ['nhom' => 'Kết luận', 'maTieuChi' => 'progress_level', 'tenTieuChi' => 'Mức độ tiến bộ', 'loaiDuLieu' => 'rating', 'danhSachMuc' => $ratingOptions, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 510],
            ['nhom' => 'Kết luận', 'maTieuChi' => 'improvement_highlight', 'tenTieuChi' => 'Điểm cải thiện nổi bật', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 520],
            ['nhom' => 'Kết luận', 'maTieuChi' => 'next_recommendation', 'tenTieuChi' => 'Đề xuất / khuyến nghị tiếp theo', 'loaiDuLieu' => 'text', 'danhSachMuc' => null, 'batBuoc' => 1, 'isReadonly' => 0, 'thuTu' => 530],
        ];

        foreach ($rows as $row) {
            DB::table('bao_cao_hoc_tap_mau_tieu_chi')->insert([
                'baoCaoHocTapMauId' => $templateId,
                'nhom' => $row['nhom'],
                'maTieuChi' => $row['maTieuChi'],
                'tenTieuChi' => $row['tenTieuChi'],
                'loaiDuLieu' => $row['loaiDuLieu'],
                'danhSachMuc' => $row['danhSachMuc'],
                'tuyChon' => null,
                'batBuoc' => $row['batBuoc'],
                'isReadonly' => $row['isReadonly'],
                'thuTu' => $row['thuTu'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bao_cao_hoc_tap_lich_su');
        Schema::dropIfExists('bao_cao_hoc_tap_tieu_chi');
        Schema::dropIfExists('bao_cao_hoc_tap');
        Schema::dropIfExists('bao_cao_hoc_tap_dot_danh_gia');
        Schema::dropIfExists('bao_cao_hoc_tap_mau_tieu_chi');
        Schema::dropIfExists('bao_cao_hoc_tap_mau');
    }
};
