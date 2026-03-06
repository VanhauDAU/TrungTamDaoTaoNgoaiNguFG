<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cosodaotao', 'maPhuongXa')) {
            Schema::table('cosodaotao', function (Blueprint $table) {
                $table->unsignedInteger('maPhuongXa')->nullable()->after('tinhThanhId');
            });
        }
        if (!Schema::hasColumn('cosodaotao', 'tenPhuongXa')) {
            Schema::table('cosodaotao', function (Blueprint $table) {
                $table->string('tenPhuongXa', 150)->nullable()->after('maPhuongXa');
            });
        }
        if (!Schema::hasColumn('cosodaotao', 'viDo')) {
            Schema::table('cosodaotao', function (Blueprint $table) {
                $table->decimal('viDo', 10, 7)->nullable()->after('tenPhuongXa');
            });
        }
        if (!Schema::hasColumn('cosodaotao', 'kinhDo')) {
            Schema::table('cosodaotao', function (Blueprint $table) {
                $table->decimal('kinhDo', 10, 7)->nullable()->after('viDo');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cosodaotao', function (Blueprint $table) {
            $drops = [];
            foreach (['maPhuongXa', 'tenPhuongXa', 'viDo', 'kinhDo'] as $col) {
                if (Schema::hasColumn('cosodaotao', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
