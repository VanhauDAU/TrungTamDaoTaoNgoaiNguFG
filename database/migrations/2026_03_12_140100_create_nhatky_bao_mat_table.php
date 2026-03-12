<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nhatky_bao_mat')) {
            return;
        }

        Schema::create('nhatky_bao_mat', function (Blueprint $table) {
            $table->id('nhatKyBaoMatId');
            $table->integer('taiKhoanId')->nullable();
            $table->unsignedBigInteger('phienDangNhapId')->nullable();
            $table->string('sessionId', 255)->nullable();
            $table->string('suKien', 50);
            $table->string('moTa', 255)->nullable();
            $table->string('ipAddress', 45)->nullable();
            $table->text('userAgent')->nullable();
            $table->json('duLieu')->nullable();
            $table->timestamp('thoiGian')->useCurrent()->index();

            $table->index(['taiKhoanId', 'suKien'], 'idx_nkbm_taikhoan_sukien');
            $table->index(['phienDangNhapId', 'suKien'], 'idx_nkbm_phien_sukien');

            $table->foreign('taiKhoanId')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->nullOnDelete();

            $table->foreign('phienDangNhapId')
                ->references('phienDangNhapId')
                ->on('phien_dang_nhap')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nhatky_bao_mat')) {
            return;
        }

        Schema::table('nhatky_bao_mat', function (Blueprint $table) {
            $table->dropForeign(['taiKhoanId']);
            $table->dropForeign(['phienDangNhapId']);
        });

        Schema::dropIfExists('nhatky_bao_mat');
    }
};
