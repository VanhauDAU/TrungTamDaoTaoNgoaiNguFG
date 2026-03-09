<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nhatky_dangnhap', function (Blueprint $table) {
            $table->id();
            $table->string('taiKhoan', 100)->comment('Tên đăng nhập hoặc email đã nhập');
            $table->string('ip', 45)->nullable()->comment('Địa chỉ IP');
            $table->boolean('thanhCong')->default(false)->comment('true = thành công, false = thất bại');
            $table->text('userAgent')->nullable()->comment('Trình duyệt / thiết bị');
            $table->timestamp('thoiGian')->useCurrent()->comment('Thời điểm đăng nhập');

            $table->index(['taiKhoan', 'thanhCong', 'thoiGian']);
            $table->index(['ip', 'thanhCong', 'thoiGian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhatky_dangnhap');
    }
};
