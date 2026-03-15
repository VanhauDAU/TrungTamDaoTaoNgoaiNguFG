<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nhansu_goi_luong')) {
            Schema::create('nhansu_goi_luong', function (Blueprint $table) {
                $table->id('nhanSuGoiLuongId');
                $table->integer('taiKhoanId');
                $table->string('loaiLuong', 30);
                $table->decimal('luongChinh', 15, 2)->nullable();
                $table->date('hieuLucTu');
                $table->date('hieuLucDen')->nullable();
                $table->text('ghiChu')->nullable();
                $table->tinyInteger('trangThai')->default(1);
                $table->timestamps();

                $table->index(['taiKhoanId', 'trangThai'], 'idx_nsgl_taikhoan_trangthai');
                $table->foreign('taiKhoanId')->references('taiKhoanId')->on('taikhoan')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('nhansu_goi_luong_chi_tiet')) {
            Schema::create('nhansu_goi_luong_chi_tiet', function (Blueprint $table) {
                $table->id('nhanSuGoiLuongChiTietId');
                $table->unsignedBigInteger('nhanSuGoiLuongId');
                $table->string('loai', 30);
                $table->string('tenKhoan', 150);
                $table->decimal('soTien', 15, 2);
                $table->text('ghiChu')->nullable();
                $table->unsignedInteger('sortOrder')->default(0);
                $table->timestamps();

                $table->foreign('nhanSuGoiLuongId', 'fk_nsglct_goi_luong')
                    ->references('nhanSuGoiLuongId')
                    ->on('nhansu_goi_luong')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nhansu_goi_luong_chi_tiet')) {
            Schema::table('nhansu_goi_luong_chi_tiet', function (Blueprint $table) {
                $table->dropForeign('fk_nsglct_goi_luong');
            });
            Schema::dropIfExists('nhansu_goi_luong_chi_tiet');
        }

        if (Schema::hasTable('nhansu_goi_luong')) {
            Schema::table('nhansu_goi_luong', function (Blueprint $table) {
                $table->dropForeign(['taiKhoanId']);
            });
            Schema::dropIfExists('nhansu_goi_luong');
        }
    }
};
