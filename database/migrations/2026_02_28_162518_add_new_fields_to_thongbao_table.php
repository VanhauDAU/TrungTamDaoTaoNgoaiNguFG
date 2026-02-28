<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            // Loại thông báo (danh mục)
            // 0=Hệ thống, 1=Học tập, 2=Tài chính, 3=Sự kiện, 4=Khẩn cấp
            $table->tinyInteger('loaiGui')->default(0)->after('trangThai')->comment('0=Hệ thống,1=Học tập,2=Tài chính,3=Sự kiện,4=Khẩn cấp');
            // Mức độ ưu tiên: 0=Bình thường, 1=Quan trọng, 2=Khẩn cấp
            $table->tinyInteger('uuTien')->default(0)->after('loaiGui')->comment('0=Bình thường,1=Quan trọng,2=Khẩn cấp');
            // Ghim lên đầu
            $table->boolean('ghim')->default(false)->after('uuTien');
            // Ảnh đính kèm (optional)
            $table->string('hinhAnh')->nullable()->after('ghim');
        });
    }

    public function down(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            $table->dropColumn(['loaiGui', 'uuTien', 'ghim', 'hinhAnh']);
        });
    }
};
