<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lienhe_phanhoi', function (Blueprint $table) {
            $table->id('phanHoiId');
            $table->unsignedBigInteger('lienHeId');
            $table->text('noiDung');
            $table->enum('loai', ['noi_bo', 'email'])->default('noi_bo');
            $table->unsignedBigInteger('nguoiGuiId')->nullable();
            $table->string('tenNguoiGui', 200)->nullable();
            $table->boolean('daGuiEmail')->default(false);
            $table->timestamps();

            $table->index('lienHeId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lienhe_phanhoi');
    }
};
