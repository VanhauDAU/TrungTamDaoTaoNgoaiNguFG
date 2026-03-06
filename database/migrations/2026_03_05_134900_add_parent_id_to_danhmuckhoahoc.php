<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('danhmuckhoahoc', 'parent_id')) {
            Schema::table('danhmuckhoahoc', function (Blueprint $table) {
                // Thêm parent_id sau cột trangThai
                $table->integer('parent_id')->nullable()->default(null)->after('trangThai');
                $table->index('parent_id', 'idx_danhmuc_parent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('danhmuckhoahoc', 'parent_id')) {
            Schema::table('danhmuckhoahoc', function (Blueprint $table) {
                $table->dropIndex('idx_danhmuc_parent');
                $table->dropColumn('parent_id');
            });
        }
    }
};
