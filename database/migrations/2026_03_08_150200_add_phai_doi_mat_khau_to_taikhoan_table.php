<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taikhoan', function (Blueprint $table) {
            $table->tinyInteger('phaiDoiMatKhau')->default(0)
                ->after('trangThai')
                ->comment('1 = phải đổi mật khẩu khi đăng nhập lần đầu, 0 = không');
        });
    }

    public function down(): void
    {
        Schema::table('taikhoan', function (Blueprint $table) {
            $table->dropColumn('phaiDoiMatKhau');
        });
    }
};
