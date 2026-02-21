<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tinhthanh', function (Blueprint $table) {
            // Thêm mã API từ provinces.open-api.vn (code tỉnh)
            $table->unsignedInteger('maAPI')->nullable()->unique()->after('tinhThanhId');
            $table->string('division_type', 50)->nullable()->after('slug');
            $table->string('codename', 100)->nullable()->after('division_type');
        });
    }

    public function down(): void
    {
        Schema::table('tinhthanh', function (Blueprint $table) {
            $table->dropColumn(['maAPI', 'division_type', 'codename']);
        });
    }
};
