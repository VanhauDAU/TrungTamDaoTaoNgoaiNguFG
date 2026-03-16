<?php

namespace App\Services\Maintenance;

use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DiemDanh;
use App\Models\Finance\HoaDon;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchMaintenanceService
{
    public function getOverdueInvoices(Carbon $today): Collection
    {
        return HoaDon::with([
            'dangKyLopHoc.lopHoc.buoiHocs',
            'dangKyLopHoc.hoaDons.lopHocDotThu',
            'lopHocDotThu',
            'taiKhoan.hoSoNguoiDung',
        ])
            ->whereNotNull('ngayHetHan')
            ->whereDate('ngayHetHan', '<', $today)
            ->where('nguonThu', HoaDon::NGUON_THU_HOC_PHI)
            ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)
            ->get();
    }

    public function getUniqueOverdueRegistrationIds(Carbon $today): Collection
    {
        return HoaDon::query()
            ->whereNotNull('ngayHetHan')
            ->whereDate('ngayHetHan', '<', $today)
            ->where('nguonThu', HoaDon::NGUON_THU_HOC_PHI)
            ->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)
            ->whereNotNull('dangKyLopHocId')
            ->pluck('dangKyLopHocId')
            ->unique()
            ->values();
    }

    public function processOverdueRegistration(int $dangKyLopHocId, Carbon $today): array
    {
        return DB::transaction(function () use ($dangKyLopHocId, $today) {
            $dangKy = DangKyLopHoc::query()
                ->whereKey($dangKyLopHocId)
                ->lockForUpdate()
                ->with([
                    'lopHoc.buoiHocs',
                    'hoaDons.lopHocDotThu',
                ])
                ->first();

            if (!$dangKy) {
                return [
                    'status' => 'missing_registration',
                    'attendance_locked' => 0,
                ];
            }

            $overdueMandatoryInvoices = $dangKy->hoaDons->filter(function (HoaDon $hoaDon) use ($today) {
                return $hoaDon->nguonThu === HoaDon::NGUON_THU_HOC_PHI
                    && $hoaDon->ngayHetHan !== null
                    && Carbon::parse($hoaDon->ngayHetHan)->startOfDay()->lt($today->copy()->startOfDay())
                    && (int) $hoaDon->trangThai !== HoaDon::TRANG_THAI_DA_TT;
            });

            if ($overdueMandatoryInvoices->isEmpty()) {
                return [
                    'status' => 'no_overdue_invoice',
                    'attendance_locked' => 0,
                ];
            }

            $dangKy->recalculatePaymentStatus();
            $dangKy->refresh();
            $dangKy->loadMissing('lopHoc');

            if ((int) $dangKy->trangThai !== DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI) {
                return [
                    'status' => 'not_suspended',
                    'attendance_locked' => 0,
                ];
            }

            $lockedCount = 0;

            if ($dangKy->lopHoc) {
                $buoiHocsTuongLai = $dangKy->lopHoc->buoiHocs()
                    ->whereDate('ngayHoc', '>=', $today)
                    ->openForAttendance()
                    ->get();

                foreach ($buoiHocsTuongLai as $buoi) {
                    $existing = DiemDanh::query()
                        ->where('buoiHocId', $buoi->buoiHocId)
                        ->where('taiKhoanId', $dangKy->taiKhoanId)
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    DiemDanh::create([
                        'buoiHocId' => $buoi->buoiHocId,
                        'taiKhoanId' => $dangKy->taiKhoanId,
                        'dangKyLopHocId' => $dangKy->dangKyLopHocId,
                        'trangThai' => DiemDanh::BI_KHOA_NO_HP,
                        'coMat' => 0,
                        'lyDo' => 'Nợ học phí – tự động hệ thống',
                        'thoiGianDiemDanh' => now(),
                    ]);

                    $lockedCount++;
                }
            }

            return [
                'status' => 'suspended',
                'attendance_locked' => $lockedCount,
            ];
        });
    }

    public function getExpiredPendingRegistrations(Carbon $now): Collection
    {
        return DangKyLopHoc::with([
            'taiKhoan.hoSoNguoiDung',
            'lopHoc',
            'hoaDons',
        ])
            ->where('trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)
            ->whereNotNull('ngayHetHanGiuCho')
            ->where('ngayHetHanGiuCho', '<', $now)
            ->orderBy('ngayHetHanGiuCho')
            ->get();
    }

    public function getExpiredPendingRegistrationIds(Carbon $now): Collection
    {
        return DangKyLopHoc::query()
            ->where('trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)
            ->whereNotNull('ngayHetHanGiuCho')
            ->where('ngayHetHanGiuCho', '<', $now)
            ->orderBy('ngayHetHanGiuCho')
            ->pluck('dangKyLopHocId');
    }

    public function processExpiredPendingRegistration(int $dangKyLopHocId): array
    {
        return DB::transaction(function () use ($dangKyLopHocId) {
            $lockedRegistration = DangKyLopHoc::query()
                ->whereKey($dangKyLopHocId)
                ->lockForUpdate()
                ->with('hoaDons')
                ->first();

            if (!$lockedRegistration) {
                return [
                    'status' => 'missing_registration',
                ];
            }

            $lockedRegistration->recalculatePaymentStatus();
            $lockedRegistration->refresh();
            $lockedRegistration->load('hoaDons');

            if (!$lockedRegistration->isPendingPayment()) {
                return [
                    'status' => 'not_pending_payment',
                ];
            }

            if (!$lockedRegistration->isHoldExpired()) {
                return [
                    'status' => 'hold_not_expired',
                ];
            }

            $totalPaid = (float) $lockedRegistration->hoaDons->sum('daTra');

            if ($totalPaid > 0) {
                return [
                    'status' => 'skipped_paid',
                ];
            }

            $lockedRegistration->update([
                'trangThai' => DangKyLopHoc::TRANG_THAI_HUY,
                'ngayHetHanGiuCho' => null,
            ]);

            foreach ($lockedRegistration->hoaDons as $invoice) {
                $note = trim((string) $invoice->ghiChu);
                $systemNote = 'Tự động hủy giữ chỗ do quá hạn thanh toán';

                if (str_contains($note, $systemNote)) {
                    continue;
                }

                $invoice->update([
                    'ghiChu' => trim($note !== '' ? $note . ' | ' . $systemNote : $systemNote),
                ]);
            }

            return [
                'status' => 'cancelled',
            ];
        });
    }
}
