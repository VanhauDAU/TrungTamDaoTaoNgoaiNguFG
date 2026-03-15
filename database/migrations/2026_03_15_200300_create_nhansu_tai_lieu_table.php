<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nhansu_tai_lieu')) {
            return;
        }

        Schema::create('nhansu_tai_lieu', function (Blueprint $table) {
            $table->id('nhanSuTaiLieuId');
            $table->integer('taiKhoanId');
            $table->string('loaiTaiLieu', 30);
            $table->string('tenHienThi', 255);
            $table->string('tenGoc', 255);
            $table->string('duongDan', 500);
            $table->string('disk', 30)->default('local');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('phienBan')->default(1);
            $table->integer('duocTaiLenBoiId')->nullable();
            $table->string('trangThai', 20)->default('active');
            $table->text('ghiChu')->nullable();
            $table->timestamp('archivedAt')->nullable();
            $table->timestamps();

            $table->index(['taiKhoanId', 'loaiTaiLieu', 'trangThai'], 'idx_nstl_taikhoan_loai_trangthai');
            $table->foreign('taiKhoanId')->references('taiKhoanId')->on('taikhoan')->cascadeOnDelete();
            $table->foreign('duocTaiLenBoiId')->references('taiKhoanId')->on('taikhoan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nhansu_tai_lieu')) {
            return;
        }

        Schema::table('nhansu_tai_lieu', function (Blueprint $table) {
            $table->dropForeign(['taiKhoanId']);
            $table->dropForeign(['duocTaiLenBoiId']);
        });

        Schema::dropIfExists('nhansu_tai_lieu');
    }
};
