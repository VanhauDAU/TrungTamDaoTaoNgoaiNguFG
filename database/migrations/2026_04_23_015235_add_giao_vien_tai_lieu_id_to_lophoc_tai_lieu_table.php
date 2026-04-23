<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lophoc_tai_lieu')) {
            return;
        }

        if (Schema::hasColumn('lophoc_tai_lieu', 'giaoVienTaiLieuId')) {
            return;
        }

        Schema::table('lophoc_tai_lieu', function (Blueprint $table) {
            // Nullable: tài liệu có thể upload thẳng vào lớp (null)
            // hoặc chia sẻ từ thư viện cá nhân (not null)
            $table->unsignedInteger('giaoVienTaiLieuId')
                  ->nullable()
                  ->after('lopHocId');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lophoc_tai_lieu')) {
            return;
        }

        if (!Schema::hasColumn('lophoc_tai_lieu', 'giaoVienTaiLieuId')) {
            return;
        }

        Schema::table('lophoc_tai_lieu', function (Blueprint $table) {
            $table->dropColumn('giaoVienTaiLieuId');
        });
    }
};
