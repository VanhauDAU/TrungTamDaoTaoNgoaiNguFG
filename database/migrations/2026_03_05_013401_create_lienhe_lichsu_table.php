<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lienhe_lichsu', function (Blueprint $table) {
            $table->id('lichSuId');
            $table->unsignedBigInteger('lienHeId');
            $table->string('hanhDong', 100);
            $table->text('noiDung')->nullable();
            $table->string('giaTriCu', 200)->nullable();
            $table->string('giaTriMoi', 200)->nullable();
            $table->unsignedBigInteger('nguoiThucHienId')->nullable();
            $table->string('tenNguoiThucHien', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('lienHeId');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lienhe_lichsu');
    }
};
