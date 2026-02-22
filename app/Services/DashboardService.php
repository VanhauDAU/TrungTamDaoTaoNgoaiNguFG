<?php

namespace App\Services;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Finance\HoaDon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Lớp đang hoạt động.
     * DB LopHoc.trangThai: 0=sắp mở, 1=đang học, 2=kết thúc, 3=hủy.
     */
    public function getActiveClassesCount(): int
    {
        return LopHoc::where('trangThai', '1')->count();
    }

    /** Đăng ký mới trong ngày (theo ngày đăng ký) */
    public function getNewRegistrationsToday(): int
    {
        $today = Carbon::today();
        return DangKyLopHoc::whereDate('ngayDangKy', $today)->count();
    }

    /** Đăng ký mới trong ngày hôm trước (để tính trend) */
    public function getNewRegistrationsYesterday(): int
    {
        $yesterday = Carbon::yesterday();
        return DangKyLopHoc::whereDate('ngayDangKy', $yesterday)->count();
    }

    /**
     * Doanh thu tháng hiện tại.
     * DB HoaDon.trangThai: 0=Chưa thanh toán, 1=Đã thanh toán một phần, 2=Đã thanh toán đủ.
     */
    public function getRevenueCurrentMonth(): float
    {
        return (float) HoaDon::whereIn('trangThai', [1, 2])
            ->whereMonth('ngayLap', Carbon::now()->month)
            ->whereYear('ngayLap', Carbon::now()->year)
            ->sum('daTra');
    }

    /** Doanh thu tháng trước (trangThai 1 hoặc 2) */
    public function getRevenueLastMonth(): float
    {
        $last = Carbon::now()->subMonth();
        return (float) HoaDon::whereIn('trangThai', [1, 2])
            ->whereMonth('ngayLap', $last->month)
            ->whereYear('ngayLap', $last->year)
            ->sum('daTra');
    }

    /** So sánh doanh thu tháng này vs tháng trước (% tăng trưởng) */
    public function getMonthlyRevenueComparison(): array
    {
        $current = $this->getRevenueCurrentMonth();
        $last = $this->getRevenueLastMonth();
        $growth = $last > 0 ? round((($current - $last) / $last) * 100, 1) : ($current > 0 ? 100 : 0);
        return [
            'currentMonth' => $current,
            'lastMonth' => $last,
            'growth' => $growth,
        ];
    }

    /** Dữ liệu biểu đồ doanh thu theo ngày (N ngày gần nhất) */
    public function getRevenueChartData(int $days = 7): array
    {
        $end = Carbon::today();
        $start = Carbon::today()->subDays($days - 1);
        $data = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $sum = (float) HoaDon::whereIn('trangThai', [1, 2])
                ->whereDate('ngayLap', $d)
                ->sum('daTra');
            $bookings = DangKyLopHoc::whereDate('ngayDangKy', $d)->count();
            $data[] = [
                'date' => $d->format('d/m'),
                'revenue' => $sum,
                'bookings' => $bookings,
            ];
        }
        return $data;
    }

    /** Phân bổ lớp đang học theo ca (sáng / trưa / tối) cho Donut. LopHoc.trangThai = 1 (đang học). */
    public function getClassesByShift(): array
    {
        $classes = LopHoc::where('trangThai', '1')
            ->with('caHoc')
            ->get();
        $sang = 0;
        $trua = 0;
        $toi = 0;
        foreach ($classes as $lop) {
            $ca = $lop->caHoc;
            if (!$ca) {
                $trua++;
                continue;
            }
            $gioStr = is_object($ca->gioBatDau) ? $ca->gioBatDau->format('H') : substr((string) ($ca->gioBatDau ?? '08:00'), 0, 2);
            $gio = (int) $gioStr;
            if ($gio < 12) {
                $sang++;
            } elseif ($gio < 17) {
                $trua++;
            } else {
                $toi++;
            }
        }
        return [
            ['label' => 'Sáng (trước 12h)', 'value' => $sang, 'color' => '#10b981'],
            ['label' => 'Trưa (12h–17h)', 'value' => $trua, 'color' => '#f59e0b'],
            ['label' => 'Tối (sau 17h)', 'value' => $toi, 'color' => '#6366f1'],
        ];
    }

    /** Đăng ký sắp tới / gần đây: 5 đăng ký mới nhất có lớp + học viên */
    public function getUpcomingRegistrations(int $limit = 5)
    {
        return DangKyLopHoc::with(['lopHoc.khoaHoc', 'taiKhoan'])
            ->orderByDesc('ngayDangKy')
            ->limit($limit)
            ->get();
    }

    /** Số hóa đơn chờ thanh toán. HoaDon.trangThai = 0 (Chưa thanh toán). */
    public function getPendingInvoicesCount(): int
    {
        return HoaDon::where('trangThai', 0)->count();
    }
}
