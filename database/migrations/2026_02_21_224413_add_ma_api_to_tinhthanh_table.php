<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tinhthanh', 'maAPI')) {
            Schema::table('tinhthanh', function (Blueprint $table) {
                $table->unsignedInteger('maAPI')->nullable()->unique()->after('tinhThanhId');
            });
        }
        if (!Schema::hasColumn('tinhthanh', 'division_type')) {
            Schema::table('tinhthanh', function (Blueprint $table) {
                $table->string('division_type', 50)->nullable()->after('slug');
            });
        }
        if (!Schema::hasColumn('tinhthanh', 'codename')) {
            Schema::table('tinhthanh', function (Blueprint $table) {
                $table->string('codename', 100)->nullable()->after('division_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tinhthanh', function (Blueprint $table) {
            $drops = [];
            foreach (['maAPI', 'division_type', 'codename'] as $col) {
                if (Schema::hasColumn('tinhthanh', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
