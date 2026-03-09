<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột ghi chú bảo trì và ngày bảo trì vào bảng phonghoc.
     * Đồng thời bật timestamps (created_at, updated_at) nếu chưa có.
     */
    public function up(): void
    {
        Schema::table('phonghoc', function (Blueprint $table) {
            // Soft delete
            if (!Schema::hasColumn('phonghoc', 'deleted_at')) {
                $table->softDeletes()->after('trangThietBi');
            }
            // Ghi chú khi chuyển sang bảo trì
            if (!Schema::hasColumn('phonghoc', 'ghiChuBaoTri')) {
                $table->text('ghiChuBaoTri')->nullable()->after('deleted_at');
            }
            // Ngày bắt đầu bảo trì
            if (!Schema::hasColumn('phonghoc', 'ngayBaoTri')) {
                $table->timestamp('ngayBaoTri')->nullable()->after('ghiChuBaoTri');
            }
            // Timestamps nếu chưa có
            if (!Schema::hasColumn('phonghoc', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('ngayBaoTri');
            }
            if (!Schema::hasColumn('phonghoc', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phonghoc', function (Blueprint $table) {
            $table->dropColumn(['ghiChuBaoTri', 'ngayBaoTri']);
        });
    }
};
