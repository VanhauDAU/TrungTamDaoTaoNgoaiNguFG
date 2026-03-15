<?php

use App\Models\Education\DangKyLopHoc;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addHoldExpiryToRegistrations();
        $this->backfillHoldExpiryForPendingRegistrations();
        $this->guardAgainstDuplicateRegistrations();
        $this->addRegistrationIndexes();
        $this->normalizeReceiptOwnership();
    }

    public function down(): void
    {
        if (Schema::hasTable('dangkylophoc')) {
            Schema::table('dangkylophoc', function (Blueprint $table) {
                if ($this->indexExists('dangkylophoc', 'uq_dangkylophoc_student_class')) {
                    $table->dropUnique('uq_dangkylophoc_student_class');
                }

                if ($this->indexExists('dangkylophoc', 'idx_dangkylophoc_hold_status')) {
                    $table->dropIndex('idx_dangkylophoc_hold_status');
                }

                if (Schema::hasColumn('dangkylophoc', 'ngayHetHanGiuCho')) {
                    $table->dropColumn('ngayHetHanGiuCho');
                }
            });
        }
    }

    private function addHoldExpiryToRegistrations(): void
    {
        if (!Schema::hasTable('dangkylophoc') || Schema::hasColumn('dangkylophoc', 'ngayHetHanGiuCho')) {
            return;
        }

        Schema::table('dangkylophoc', function (Blueprint $table) {
            $table->dateTime('ngayHetHanGiuCho')->nullable()->after('ngayDangKy');
        });
    }

    private function backfillHoldExpiryForPendingRegistrations(): void
    {
        if (!Schema::hasTable('dangkylophoc') || !Schema::hasColumn('dangkylophoc', 'ngayHetHanGiuCho')) {
            return;
        }

        $pendingRegistrations = DB::table('dangkylophoc as dk')
            ->leftJoin('hoadon as hd', function ($join) {
                $join->on('hd.dangKyLopHocId', '=', 'dk.dangKyLopHocId')
                    ->where('hd.nguonThu', '=', 'hoc_phi');
            })
            ->selectRaw('dk.dangKyLopHocId, dk.ngayDangKy, MIN(hd.ngayHetHan) as nearest_due_date')
            ->where('dk.trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)
            ->whereNull('dk.ngayHetHanGiuCho')
            ->groupBy('dk.dangKyLopHocId', 'dk.ngayDangKy')
            ->get();

        foreach ($pendingRegistrations as $registration) {
            $fallbackBase = $registration->ngayDangKy
                ? Carbon::parse($registration->ngayDangKy)
                : now();

            $holdExpiry = $registration->nearest_due_date
                ? Carbon::parse($registration->nearest_due_date)->endOfDay()
                : $fallbackBase->copy()->addDays(3)->endOfDay();

            DB::table('dangkylophoc')
                ->where('dangKyLopHocId', $registration->dangKyLopHocId)
                ->update(['ngayHetHanGiuCho' => $holdExpiry]);
        }
    }

    private function guardAgainstDuplicateRegistrations(): void
    {
        if (!Schema::hasTable('dangkylophoc') || $this->indexExists('dangkylophoc', 'uq_dangkylophoc_student_class')) {
            return;
        }

        $duplicates = DB::table('dangkylophoc')
            ->select('taiKhoanId', 'lopHocId', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('taiKhoanId', 'lopHocId')
            ->havingRaw('COUNT(*) > 1')
            ->limit(5)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates->map(fn ($row) => "{$row->taiKhoanId}-{$row->lopHocId} ({$row->duplicate_count})")->implode(', ');
            throw new RuntimeException(
                'Không thể thêm unique index cho đăng ký lớp vì đang tồn tại đăng ký trùng. Vui lòng xử lý trước các cặp: ' . $sample
            );
        }
    }

    private function addRegistrationIndexes(): void
    {
        if (!Schema::hasTable('dangkylophoc')) {
            return;
        }

        Schema::table('dangkylophoc', function (Blueprint $table) {
            if (!$this->indexExists('dangkylophoc', 'uq_dangkylophoc_student_class')) {
                $table->unique(['taiKhoanId', 'lopHocId'], 'uq_dangkylophoc_student_class');
            }

            if (
                Schema::hasColumn('dangkylophoc', 'ngayHetHanGiuCho')
                && !$this->indexExists('dangkylophoc', 'idx_dangkylophoc_hold_status')
            ) {
                $table->index(['trangThai', 'ngayHetHanGiuCho'], 'idx_dangkylophoc_hold_status');
            }
        });
    }

    private function normalizeReceiptOwnership(): void
    {
        if (!Schema::hasTable('phieuthu') || !Schema::hasTable('hoadon')) {
            return;
        }

        DB::table('phieuthu')
            ->whereNull('nguoiDuyetId')
            ->whereNotNull('taiKhoanId')
            ->update(['nguoiDuyetId' => DB::raw('taiKhoanId')]);

        $receipts = DB::table('phieuthu as pt')
            ->join('hoadon as hd', 'hd.hoaDonId', '=', 'pt.hoaDonId')
            ->select('pt.phieuThuId', 'pt.taiKhoanId as current_owner_id', 'hd.taiKhoanId as invoice_owner_id')
            ->whereNotNull('hd.taiKhoanId')
            ->get();

        foreach ($receipts as $receipt) {
            if ((int) $receipt->current_owner_id === (int) $receipt->invoice_owner_id) {
                continue;
            }

            DB::table('phieuthu')
                ->where('phieuThuId', $receipt->phieuThuId)
                ->update(['taiKhoanId' => $receipt->invoice_owner_id]);
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return match (DB::getDriverName()) {
            'mysql' => DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists(),
            'sqlite' => collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($row) => ($row->name ?? null) === $index),
            default => false,
        };
    }
};
