<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            $table->tinyInteger('sendTrangThai')
                ->default(2)
                ->after('ghim')
                ->comment('0=Nháp,1=Đã lên lịch,2=Đã gửi,3=Gửi lỗi');
            $table->timestamp('scheduled_at')->nullable()->after('sendTrangThai');
            $table->timestamp('sent_at')->nullable()->after('scheduled_at');
            $table->timestamp('failed_at')->nullable()->after('sent_at');
            $table->string('failure_reason', 500)->nullable()->after('failed_at');

            $table->index('sendTrangThai');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('thongbao', function (Blueprint $table) {
            $table->dropIndex(['sendTrangThai']);
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn([
                'sendTrangThai',
                'scheduled_at',
                'sent_at',
                'failed_at',
                'failure_reason',
            ]);
        });
    }
};

