<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('giao_vien_tai_lieu')) {
            return;
        }

        Schema::create('giao_vien_tai_lieu', function (Blueprint $table) {
            $table->increments('giaoVienTaiLieuId');
            $table->integer('nguoiTaiLenId');             // FK → taikhoan.taiKhoanId
            $table->string('tieuDe', 255);
            $table->text('moTa')->nullable();
            $table->string('nhomTaiLieu', 40)->default('tai_lieu');
            $table->string('disk', 30)->default('local');
            $table->string('duongDan', 500);
            $table->string('tenGoc', 255);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
            $table->timestamps();

            $table->index(['nguoiTaiLenId', 'nhomTaiLieu'], 'idx_gvtl_nguoi_nhom');
            $table->foreign('nguoiTaiLenId')
                  ->references('taiKhoanId')->on('taikhoan')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('giao_vien_tai_lieu')) {
            return;
        }

        Schema::table('giao_vien_tai_lieu', function (Blueprint $table) {
            $table->dropForeign(['nguoiTaiLenId']);
        });

        Schema::dropIfExists('giao_vien_tai_lieu');
    }
};
