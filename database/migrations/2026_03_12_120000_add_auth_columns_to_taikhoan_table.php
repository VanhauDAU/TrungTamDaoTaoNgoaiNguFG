<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taikhoan')) {
            return;
        }

        Schema::table('taikhoan', function (Blueprint $table) {
            if (!Schema::hasColumn('taikhoan', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('taikhoan', 'auth_provider')) {
                $table->string('auth_provider', 20)->default('local')->after('phaiDoiMatKhau');
            }

            if (!Schema::hasColumn('taikhoan', 'google_id')) {
                $table->string('google_id', 191)->nullable()->unique()->after('auth_provider');
            }

            if (!Schema::hasColumn('taikhoan', 'google_avatar')) {
                $table->string('google_avatar', 500)->nullable()->after('google_id');
            }
        });

        DB::table('taikhoan')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('taikhoan')) {
            return;
        }

        Schema::table('taikhoan', function (Blueprint $table) {
            if (Schema::hasColumn('taikhoan', 'google_avatar')) {
                $table->dropColumn('google_avatar');
            }

            if (Schema::hasColumn('taikhoan', 'google_id')) {
                $table->dropUnique('taikhoan_google_id_unique');
                $table->dropColumn('google_id');
            }

            if (Schema::hasColumn('taikhoan', 'auth_provider')) {
                $table->dropColumn('auth_provider');
            }

            if (Schema::hasColumn('taikhoan', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
