<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redesign bảng diemDanh theo chuẩn hệ thống giáo dục.
 *
 * Lưu ý: Các bảng tham chiếu (buoihoc, taikhoan, dangkylophoc) dùng int(11) signed
 * nên ta dùng integer() (không phải unsignedInteger()) để FK khớp kiểu.
 *
 * Trạng thái điểm danh (trangThai):
 *   0 = Vắng mặt không phép
 *   1 = Có mặt
 *   2 = Đi trễ
 *   3 = Vắng có phép
 *   4 = Bị khóa – Nợ học phí
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('diemDanh');

        Schema::create('diemDanh', function (Blueprint $table) {
            $table->bigIncrements('diemDanhId');

            // ── Khoá ngoại cốt lõi (int signed để khớp PK của bảng gốc) ──
            $table->integer('buoiHocId')->comment('Buổi học được điểm danh');
            $table->integer('taiKhoanId')->comment('Học viên');
            $table->integer('dangKyLopHocId')->nullable()->comment('Liên kết đăng ký lớp');

            // ── Trạng thái điểm danh ─────────────────────────────────────
            $table->tinyInteger('trangThai')->default(1)
                ->comment('0=Vắng, 1=Có mặt, 2=Đi trễ, 3=Có phép, 4=Bị khóa(Nợ HP)');

            $table->tinyInteger('coMat')->default(0)
                ->comment('1 nếu có mặt hoặc đi trễ; dùng để thống kê nhanh');

            // ── Chi tiết ─────────────────────────────────────────────────
            $table->smallInteger('phutDiTre')->nullable()
                ->comment('Số phút đi trễ (chỉ điền khi trangThai=2)');

            $table->string('lyDo', 500)->nullable()
                ->comment('Lý do vắng / trễ / có phép / nợ HP');

            // ── Hình thức học ─────────────────────────────────────────────
            $table->tinyInteger('hinhThuc')->default(0)
                ->comment('0=Trực tiếp, 1=Online');

            // ── Người ghi & thời điểm ────────────────────────────────────
            $table->integer('nguoiDiemDanhId')->nullable()
                ->comment('Tài khoản GV/admin thực hiện điểm danh');

            $table->dateTime('thoiGianDiemDanh')->nullable()
                ->comment('Thời điểm ghi nhận điểm danh');

            // ── Ghi chú ──────────────────────────────────────────────────
            $table->text('ghiChu')->nullable();

            // ── Timestamps ───────────────────────────────────────────────
            $table->timestamps();

            // ── Unique constraint ─────────────────────────────────────────
            $table->unique(['buoiHocId', 'taiKhoanId'], 'uq_diemdanh_buoi_hocvien');

            // ── Indexes ──────────────────────────────────────────────────
            $table->index('taiKhoanId');
            $table->index('dangKyLopHocId');
            $table->index('trangThai');
            $table->index('nguoiDiemDanhId');

            // ── Foreign Keys ──────────────────────────────────────────────
            $table->foreign('buoiHocId')
                ->references('buoiHocId')->on('buoihoc')
                ->onDelete('cascade');

            $table->foreign('taiKhoanId')
                ->references('taiKhoanId')->on('taikhoan')
                ->onDelete('cascade');

            $table->foreign('dangKyLopHocId')
                ->references('dangKyLopHocId')->on('dangkylophoc')
                ->onDelete('set null');

            $table->foreign('nguoiDiemDanhId', 'diemdanh_nguoidiemdanh_foreign')
                ->references('taiKhoanId')->on('taikhoan')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diemDanh');

        Schema::create('diemDanh', function (Blueprint $table) {
            $table->string('diemDanhId', 50)->primary();
            $table->integer('taiKhoanId')->nullable();
            $table->integer('buoiHocId')->nullable();
            $table->tinyInteger('trangThai')->nullable();
            $table->text('ghiChu')->nullable();
            $table->timestamps();
        });
    }
};
