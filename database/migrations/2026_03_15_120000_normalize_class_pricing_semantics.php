<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeSoBuoiCamKetOverrides();

        if (Schema::hasTable('hoadon')) {
            Schema::table('hoadon', function (Blueprint $table) {
                if (!$this->indexExists('hoadon', 'idx_hoadon_registration_status_due')) {
                    $table->index(['dangKyLopHocId', 'trangThai', 'ngayHetHan'], 'idx_hoadon_registration_status_due');
                }
            });
        }

        if (Schema::hasTable('phieuthu')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                if (!$this->indexExists('phieuthu', 'idx_phieuthu_invoice_status_date')) {
                    $table->index(['hoaDonId', 'trangThai', 'ngayThu'], 'idx_phieuthu_invoice_status_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hoadon') && $this->indexExists('hoadon', 'idx_hoadon_registration_status_due')) {
            Schema::table('hoadon', function (Blueprint $table) {
                $table->dropIndex('idx_hoadon_registration_status_due');
            });
        }

        if (Schema::hasTable('phieuthu') && $this->indexExists('phieuthu', 'idx_phieuthu_invoice_status_date')) {
            Schema::table('phieuthu', function (Blueprint $table) {
                $table->dropIndex('idx_phieuthu_invoice_status_date');
            });
        }
    }

    private function normalizeSoBuoiCamKetOverrides(): void
    {
        if (!Schema::hasTable('lophoc_chinhsachgia') || !Schema::hasTable('lophoc')) {
            return;
        }

        DB::table('lophoc_chinhsachgia')
            ->join('lophoc', 'lophoc.lopHocId', '=', 'lophoc_chinhsachgia.lopHocId')
            ->whereNotNull('lophoc_chinhsachgia.soBuoiCamKet')
            ->whereColumn('lophoc_chinhsachgia.soBuoiCamKet', 'lophoc.soBuoiDuKien')
            ->update([
                'lophoc_chinhsachgia.soBuoiCamKet' => null,
                'lophoc_chinhsachgia.updated_at' => now(),
            ]);
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        if ($driver !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();

        $indexes = DB::select(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $index]
        );

        return !empty($indexes);
    }
};
