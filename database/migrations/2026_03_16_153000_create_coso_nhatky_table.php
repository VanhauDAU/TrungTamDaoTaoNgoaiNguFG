<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coso_nhatky', function (Blueprint $table) {
            $table->increments('coSoNhatKyId');
            $table->unsignedInteger('coSoId');
            $table->unsignedInteger('phongHocId')->nullable();
            $table->unsignedInteger('taiKhoanId')->nullable();
            $table->string('hanhDong', 80);
            $table->string('moTa', 255);
            $table->json('duLieu')->nullable();
            $table->timestamps();

            $table->index('coSoId');
            $table->index('phongHocId');
            $table->index('taiKhoanId');
            $table->index(['coSoId', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coso_nhatky');
    }
};
