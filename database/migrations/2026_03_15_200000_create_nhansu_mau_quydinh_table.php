<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nhansu_mau_quydinh')) {
            return;
        }

        Schema::create('nhansu_mau_quydinh', function (Blueprint $table) {
            $table->id('nhanSuMauQuyDinhId');
            $table->string('maMau', 50)->unique();
            $table->string('tieuDe', 255);
            $table->string('phamViApDung', 20)->default('both');
            $table->string('loaiHopDongApDung', 30)->nullable();
            $table->longText('noiDung');
            $table->unsignedInteger('phienBan')->default(1);
            $table->tinyInteger('trangThai')->default(1);
            $table->integer('createdById')->nullable();
            $table->integer('updatedById')->nullable();
            $table->timestamps();

            $table->foreign('createdById')->references('taiKhoanId')->on('taikhoan')->nullOnDelete();
            $table->foreign('updatedById')->references('taiKhoanId')->on('taikhoan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nhansu_mau_quydinh')) {
            return;
        }

        Schema::table('nhansu_mau_quydinh', function (Blueprint $table) {
            $table->dropForeign(['createdById']);
            $table->dropForeign(['updatedById']);
        });

        Schema::dropIfExists('nhansu_mau_quydinh');
    }
};
