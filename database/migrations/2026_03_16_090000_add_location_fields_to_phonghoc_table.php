<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phonghoc', function (Blueprint $table) {
            if (!Schema::hasColumn('phonghoc', 'khuBlock')) {
                $table->string('khuBlock', 50)->nullable()->after('trangThietBi');
            }

            if (!Schema::hasColumn('phonghoc', 'tang')) {
                $table->unsignedTinyInteger('tang')->nullable()->after('khuBlock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phonghoc', function (Blueprint $table) {
            if (Schema::hasColumn('phonghoc', 'tang')) {
                $table->dropColumn('tang');
            }

            if (Schema::hasColumn('phonghoc', 'khuBlock')) {
                $table->dropColumn('khuBlock');
            }
        });
    }
};
