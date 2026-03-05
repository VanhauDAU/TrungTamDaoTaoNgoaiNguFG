<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lienhe', function (Blueprint $table) {
            // Phân loại liên hệ
            $table->enum('loaiLienHe', ['tu_van', 'ho_tro', 'khieu_nai', 'khac'])
                  ->default('tu_van')
                  ->after('trangThai')
                  ->comment('Loại liên hệ: tu_van, ho_tro, khieu_nai, khac');

            // Ghi chú nội bộ (chỉ admin/nhân viên thấy)
            $table->text('ghiChuNoiBo')->nullable()->after('loaiLienHe');

            // Người phụ trách
            $table->unsignedBigInteger('nguoiPhuTrachId')->nullable()->after('ghiChuNoiBo');

            // Thời gian xử lý xong
            $table->timestamp('thoiGianXuLy')->nullable()->after('nguoiPhuTrachId');
        });
    }

    public function down(): void
    {
        Schema::table('lienhe', function (Blueprint $table) {
            $table->dropColumn(['loaiLienHe', 'ghiChuNoiBo', 'nguoiPhuTrachId', 'thoiGianXuLy']);
        });
    }
};
