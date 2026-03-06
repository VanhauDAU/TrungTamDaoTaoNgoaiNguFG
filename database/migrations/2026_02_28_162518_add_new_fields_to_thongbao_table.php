<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('thongbao', 'loaiGui')) {
            Schema::table('thongbao', function (Blueprint $table) {
                // Loại thông báo (danh mục)
                $table->tinyInteger('loaiGui')->default(0)->after('trangThai')->comment('0=Hệ thống,1=Học tập,2=Tài chính,3=Sự kiện,4=Khẩn cấp');
            });
        }
        if (!Schema::hasColumn('thongbao', 'uuTien')) {
            Schema::table('thongbao', function (Blueprint $table) {
                // Mức độ ưu tiên: 0=Bình thường, 1=Quan trọng, 2=Khẩn cấp
                $table->tinyInteger('uuTien')->default(0)->after('loaiGui')->comment('0=Bình thường,1=Quan trọng,2=Khẩn cấp');
            });
        }
        if (!Schema::hasColumn('thongbao', 'ghim')) {
            Schema::table('thongbao', function (Blueprint $table) {
                // Ghim lên đầu
                $table->boolean('ghim')->default(false)->after('uuTien');
            });
        }
        if (!Schema::hasColumn('thongbao', 'hinhAnh')) {
            Schema::table('thongbao', function (Blueprint $table) {
                // Ảnh đính kèm (optional)
                $table->string('hinhAnh')->nullable()->after('ghim');
            });
        }
    }

    public function down(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            $drops = [];
            foreach (['loaiGui', 'uuTien', 'ghim', 'hinhAnh'] as $col) {
                if (Schema::hasColumn('thongbao', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
