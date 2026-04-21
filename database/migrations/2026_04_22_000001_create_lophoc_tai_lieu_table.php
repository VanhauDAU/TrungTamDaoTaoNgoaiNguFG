<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lophoc_tai_lieu')) {
            return;
        }

        Schema::create('lophoc_tai_lieu', function (Blueprint $table) {
            $table->increments('lopHocTaiLieuId');       // int(11) unsigned auto_increment
            $table->integer('lopHocId');                  // int(11) → match lophoc.lopHocId
            $table->string('tieuDe', 255);
            $table->text('moTa')->nullable();
            $table->string('nhomTaiLieu', 40)->default('tai_lieu');
            $table->string('disk', 30)->default('local');
            $table->string('duongDan', 500);
            $table->string('tenGoc', 255);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('kichThuoc')->default(0);
            $table->integer('nguoiTaiLenId')->nullable();  // int(11) → match taikhoan.taiKhoanId
            $table->timestamp('publishedAt')->nullable();
            $table->unsignedSmallInteger('sortOrder')->default(0);
            $table->tinyInteger('trangThai')->default(1);
            $table->timestamps();

            $table->index(['lopHocId', 'trangThai', 'sortOrder'], 'idx_lhtl_lophoc_trangthai_sort');
            $table->foreign('lopHocId')
                  ->references('lopHocId')->on('lophoc')
                  ->cascadeOnDelete();
            $table->foreign('nguoiTaiLenId')
                  ->references('taiKhoanId')->on('taikhoan')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lophoc_tai_lieu')) {
            return;
        }

        Schema::table('lophoc_tai_lieu', function (Blueprint $table) {
            $table->dropForeign(['lopHocId']);
            $table->dropForeign(['nguoiTaiLenId']);
        });

        Schema::dropIfExists('lophoc_tai_lieu');
    }
};
