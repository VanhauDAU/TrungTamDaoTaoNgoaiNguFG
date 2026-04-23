<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_schedule_proposals', function (Blueprint $table) {
            $table->id('proposalId');
            $table->integer('buoiHocId');
            $table->integer('taiKhoanId'); // Giáo viên đề xuất
            $table->enum('loaiDeXuat', ['compensation', 'suspension']); // compensation: dạy bù, suspension: tạm ngưng
            $table->text('lyDo');
            $table->date('ngayBu')->nullable();
            $table->integer('caHocId')->nullable();
            $table->enum('trangThai', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('ghiChuAdmin')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('buoiHocId')->references('buoiHocId')->on('buoihoc')->onDelete('cascade');
            $table->foreign('taiKhoanId')->references('taiKhoanId')->on('taikhoan')->onDelete('cascade');
            $table->foreign('caHocId')->references('caHocId')->on('cahoc')->onDelete('set null');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_schedule_proposals');
    }

};
