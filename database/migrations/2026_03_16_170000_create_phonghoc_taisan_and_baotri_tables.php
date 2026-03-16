<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::create('phonghoc_baotri', function (Blueprint $table) {
            $table->increments('phongHocBaoTriId');
            $table->unsignedInteger('phongHocId');
            $table->unsignedInteger('coSoId');
            $table->string('maPhieu', 30)->unique();
            $table->string('tieuDe', 150);
            $table->text('moTa')->nullable();
            $table->tinyInteger('mucDoUuTien')->default(1);
            $table->tinyInteger('trangThai')->default(0);
            $table->unsignedInteger('createdById')->nullable();
            $table->unsignedInteger('assignedToId')->nullable();
            $table->dateTime('ngayYeuCau');
            $table->dateTime('ngayBatDau')->nullable();
            $table->dateTime('ngayHoanTat')->nullable();
            $table->text('ketQuaXuLy')->nullable();
            $table->timestamps();

            $table->index('phongHocId');
            $table->index('coSoId');
            $table->index('trangThai');
            $table->index('mucDoUuTien');
            $table->index('assignedToId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phonghoc_baotri');
        Schema::dropIfExists('phonghoc_taisan');
    }
};
