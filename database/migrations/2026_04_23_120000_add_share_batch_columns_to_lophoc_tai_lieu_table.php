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

        Schema::table('lophoc_tai_lieu', function (Blueprint $table) {
            if (!Schema::hasColumn('lophoc_tai_lieu', 'dotChiaSeKey')) {
                $table->string('dotChiaSeKey', 64)
                    ->nullable()
                    ->after('giaoVienTaiLieuId');
            }

            if (!Schema::hasColumn('lophoc_tai_lieu', 'dotChiaSeTieuDe')) {
                $table->string('dotChiaSeTieuDe', 255)
                    ->nullable()
                    ->after('dotChiaSeKey');
            }

            if (!Schema::hasColumn('lophoc_tai_lieu', 'dotChiaSeAt')) {
                $table->timestamp('dotChiaSeAt')
                    ->nullable()
                    ->after('dotChiaSeTieuDe');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lophoc_tai_lieu')) {
            return;
        }

        Schema::table('lophoc_tai_lieu', function (Blueprint $table) {
            foreach (['dotChiaSeAt', 'dotChiaSeTieuDe', 'dotChiaSeKey'] as $column) {
                if (Schema::hasColumn('lophoc_tai_lieu', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
