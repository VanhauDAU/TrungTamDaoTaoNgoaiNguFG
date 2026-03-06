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
        Schema::table('danhmuckhoahoc', function (Blueprint $table) {
            $table->string('maDanhMuc', 20)->unique()->nullable()->after('danhMucId');
        });

        Schema::table('khoahoc', function (Blueprint $table) {
            $table->string('maKhoaHoc', 20)->unique()->nullable()->after('khoaHocId');
        });

        Schema::table('lophoc', function (Blueprint $table) {
            $table->string('maLopHoc', 20)->unique()->nullable()->after('lopHocId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('danhmuckhoahoc', function (Blueprint $table) {
            $table->dropColumn('maDanhMuc');
        });

        Schema::table('khoahoc', function (Blueprint $table) {
            $table->dropColumn('maKhoaHoc');
        });

        Schema::table('lophoc', function (Blueprint $table) {
            $table->dropColumn('maLopHoc');
        });
    }
};
