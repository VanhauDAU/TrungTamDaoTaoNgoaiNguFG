<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('phien_dang_nhap')) {
            return;
        }

        Schema::create('phien_dang_nhap', function (Blueprint $table) {
            $table->id('phienDangNhapId');
            $table->integer('taiKhoanId');
            $table->string('sessionId', 255)->unique();
            $table->string('portal', 20)->nullable();
            $table->string('loginMethod', 20)->default('password');
            $table->boolean('remembered')->default(false);
            $table->string('ipAddress', 45)->nullable();
            $table->text('userAgent')->nullable();
            $table->string('deviceName', 150)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('browser', 80)->nullable();
            $table->timestamp('lastSeenAt')->nullable()->index();
            $table->timestamp('revokedAt')->nullable()->index();
            $table->string('revokeReason', 100)->nullable();
            $table->timestamps();

            $table->index(['taiKhoanId', 'revokedAt'], 'idx_pdn_taikhoan_revoked');
            $table->index(['taiKhoanId', 'lastSeenAt'], 'idx_pdn_taikhoan_lastseen');

            $table->foreign('taiKhoanId')
                ->references('taiKhoanId')
                ->on('taikhoan')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('phien_dang_nhap')) {
            return;
        }

        Schema::table('phien_dang_nhap', function (Blueprint $table) {
            $table->dropForeign(['taiKhoanId']);
        });

        Schema::dropIfExists('phien_dang_nhap');
    }
};
