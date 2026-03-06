<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('baiviet', 'deleted_at')) {
            Schema::table('baiviet', function (Blueprint $table) {
                $table->softDeletes(); // adds nullable deleted_at column
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('baiviet', 'deleted_at')) {
            Schema::table('baiviet', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
