<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('phonghoc_taisan');
    }

    public function down(): void
    {
        if (Schema::hasTable('phonghoc_taisan')) {
            return;
        }

        Schema::create('phonghoc_taisan', function (Blueprint $table) {
            $table->increments('phongHocTaiSanId');
            $table->unsignedInteger('phongHocId');
            $table->string('maTaiSan', 50)->nullable();
            $table->string('tenTaiSan', 150);
            $table->string('loaiTaiSan', 100)->nullable();
            $table->unsignedInteger('soLuong')->default(1);
            $table->tinyInteger('tinhTrang')->default(1);
            $table->string('ghiChu', 500)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('phongHocId');
            $table->index('tinhTrang');
            $table->index('loaiTaiSan');
        });
    }
};
