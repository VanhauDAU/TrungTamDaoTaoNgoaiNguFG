<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cosodaotao', function (Blueprint $table) {
            // Mã phường/xã từ API (integer code)
            $table->unsignedInteger('maPhuongXa')->nullable()->after('tinhThanhId');
            // Tên phường/xã lưu text để không phụ thuộc vào DB table
            $table->string('tenPhuongXa', 150)->nullable()->after('maPhuongXa');
            // Tọa độ để hiển thị trên bản đồ
            $table->decimal('viDo', 10, 7)->nullable()->after('tenPhuongXa');
            $table->decimal('kinhDo', 10, 7)->nullable()->after('viDo');
        });
    }

    public function down(): void
    {
        Schema::table('cosodaotao', function (Blueprint $table) {
            $table->dropColumn(['maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo']);
        });
    }
};
