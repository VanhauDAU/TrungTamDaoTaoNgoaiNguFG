<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nhansu_hoso')) {
            return;
        }

        Schema::create('nhansu_hoso', function (Blueprint $table) {
            $table->id('nhanSuHoSoId');
            $table->integer('taiKhoanId')->unique();
            $table->string('maHoSo', 50)->unique();
            $table->unsignedBigInteger('nhanSuMauQuyDinhId')->nullable();
            $table->string('tieuDeMauSnapshot', 255)->nullable();
            $table->longText('noiDungQuyDinhSnapshot')->nullable();
            $table->string('trangThaiHoSo', 20)->default('draft');
            $table->text('ghiChuHoSo')->nullable();
            $table->timestamps();

            $table->foreign('taiKhoanId')->references('taiKhoanId')->on('taikhoan')->cascadeOnDelete();
            $table->foreign('nhanSuMauQuyDinhId')->references('nhanSuMauQuyDinhId')->on('nhansu_mau_quydinh')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nhansu_hoso')) {
            return;
        }

        Schema::table('nhansu_hoso', function (Blueprint $table) {
            $table->dropForeign(['taiKhoanId']);
            $table->dropForeign(['nhanSuMauQuyDinhId']);
        });

        Schema::dropIfExists('nhansu_hoso');
    }
};
