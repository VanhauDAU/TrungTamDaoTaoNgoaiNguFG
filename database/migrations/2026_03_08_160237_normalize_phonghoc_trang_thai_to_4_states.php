<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Facility\PhongHoc;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chuyển các phòng học đang 'bảo trì' (0 cũ) sang 'bảo trì' chuẩn mới (3)
        // Các phòng đã 'sẵn sàng' (1 cũ) thì vẫn là 1
        DB::table('phonghoc')
            ->where('trangThai', 0)
            ->update(['trangThai' => 3]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Chuyển lại từ 3 về 0
        DB::table('phonghoc')
            ->where('trangThai', 3)
            ->update(['trangThai' => 0]);
    }
};
