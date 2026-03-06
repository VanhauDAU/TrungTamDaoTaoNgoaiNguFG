<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('thongbao_tepdinh')) {
            Schema::create('thongbao_tepdinh', function (Blueprint $table) {
                $table->id('tepDinhId');
                $table->integer('thongBaoId')->comment('FK → thongbao.thongBaoId');
                $table->string('tenFile', 255)->comment('Tên file gốc của người dùng');
                $table->string('tenFileLuu', 255)->comment('Tên file lưu trên server (uuid + ext)');
                $table->string('duongDan', 500)->comment('Relative path trong storage/public');
                $table->string('loaiFile', 100)->nullable()->comment('MIME type');
                $table->unsignedBigInteger('kichThuoc')->default(0)->comment('Kích thước byte');
                $table->timestamps();

                $table->index('thongBaoId');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('thongbao_tepdinh');
    }
};
