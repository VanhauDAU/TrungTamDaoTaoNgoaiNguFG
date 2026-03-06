<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('thongbao', 'sendTrangThai')) {
            Schema::table('thongbao', function (Blueprint $table) {
                $table->tinyInteger('sendTrangThai')
                    ->default(2)
                    ->after('ghim')
                    ->comment('0=Nháp,1=Đã lên lịch,2=Đã gửi,3=Gửi lỗi');
                $table->index('sendTrangThai');
            });
        }
        if (!Schema::hasColumn('thongbao', 'scheduled_at')) {
            Schema::table('thongbao', function (Blueprint $table) {
                $table->timestamp('scheduled_at')->nullable()->after('sendTrangThai');
                $table->index('scheduled_at');
            });
        }
        if (!Schema::hasColumn('thongbao', 'sent_at')) {
            Schema::table('thongbao', function (Blueprint $table) {
                $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            });
        }
        if (!Schema::hasColumn('thongbao', 'failed_at')) {
            Schema::table('thongbao', function (Blueprint $table) {
                $table->timestamp('failed_at')->nullable()->after('sent_at');
            });
        }
        if (!Schema::hasColumn('thongbao', 'failure_reason')) {
            Schema::table('thongbao', function (Blueprint $table) {
                $table->string('failure_reason', 500)->nullable()->after('failed_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            $drops = [];
            foreach (['sendTrangThai', 'scheduled_at', 'sent_at', 'failed_at', 'failure_reason'] as $col) {
                if (Schema::hasColumn('thongbao', $col)) $drops[] = $col;
            }
            if (!empty($drops)) $table->dropColumn($drops);
        });
    }
};
