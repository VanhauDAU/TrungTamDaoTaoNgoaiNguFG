<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('khoahoc', 'deleted_at')) {
            Schema::table('khoahoc', function (Blueprint $table) {
                $table->softDeletes(); // adds deleted_at TIMESTAMP NULL
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('khoahoc', 'deleted_at')) {
            Schema::table('khoahoc', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
