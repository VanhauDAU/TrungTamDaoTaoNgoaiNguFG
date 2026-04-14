<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cau_hinh_he_thong', function (Blueprint $table) {
            $table->id();
            $table->string('nhom', 80)->comment('Nhóm cấu hình: he_thong | giao_duc | bao_mat | thong_bao | tai_chinh | giao_dien | tich_hop');
            $table->string('khoa', 120)->unique()->comment('Khóa cấu hình duy nhất (snake_case)');
            $table->string('ten_hien_thi', 200)->comment('Tên hiển thị tiếng Việt');
            $table->text('gia_tri')->nullable()->comment('Giá trị lưu trữ');
            $table->string('kieu_du_lieu', 30)->default('text')->comment('text | number | boolean | select | textarea | color | email | url | json');
            $table->text('mo_ta')->nullable()->comment('Mô tả chi tiết cấu hình');
            $table->text('gia_tri_mac_dinh')->nullable()->comment('Giá trị mặc định');
            $table->json('tuy_chon')->nullable()->comment('Tùy chọn cho kiểu select: [{label, value}]');
            $table->boolean('yeu_cau')->default(false)->comment('Bắt buộc hay không');
            $table->integer('thu_tu')->default(0)->comment('Thứ tự hiển thị trong nhóm');
            $table->boolean('an_trong_ui')->default(false)->comment('Ẩn khỏi giao diện (chỉ dùng nội bộ)');
            $table->timestamps();

            $table->index('nhom');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_thong');
    }
};
