<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\DangKyLopHoc;
use App\Services\Admin\Dashboard\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithUserNotifications;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function __invoke(Request $request)
    {
        $period = $request->string('period', '7')->value();
        $days = in_array($period, ['7', '14', '30'], true) ? (int) $period : 7;
        $revenueSummary = $this->dashboardService->getMonthlyRevenueComparison();
        $newRegistrationsYesterday = $this->dashboardService->getNewRegistrationsYesterday();
        $newRegistrationsToday = $this->dashboardService->getNewRegistrationsToday();
        $registrationTrend = $newRegistrationsYesterday > 0
            ? round((($newRegistrationsToday - $newRegistrationsYesterday) / $newRegistrationsYesterday) * 100, 1)
            : ($newRegistrationsToday > 0 ? 100.0 : 0.0);

        $registrationStatusData = [
            [
                'label' => DangKyLopHoc::trangThaiOptions()[DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN],
                'value' => DangKyLopHoc::where('trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)->count(),
                'color' => '#4299e1',
            ],
            [
                'label' => DangKyLopHoc::trangThaiOptions()[DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN],
                'value' => DangKyLopHoc::where('trangThai', DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN)->count(),
                'color' => '#27c4b5',
            ],
            [
                'label' => DangKyLopHoc::trangThaiOptions()[DangKyLopHoc::TRANG_THAI_DANG_HOC],
                'value' => DangKyLopHoc::where('trangThai', DangKyLopHoc::TRANG_THAI_DANG_HOC)->count(),
                'color' => '#10b981',
            ],
            [
                'label' => DangKyLopHoc::trangThaiOptions()[DangKyLopHoc::TRANG_THAI_BAO_LUU],
                'value' => DangKyLopHoc::where('trangThai', DangKyLopHoc::TRANG_THAI_BAO_LUU)->count(),
                'color' => '#f59e0b',
            ],
        ];

        return view('staff.dashboard.index', [
            'totalStudent' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
            'activeClasses' => $this->dashboardService->getActiveClassesCount(),
            'newRegistrationsToday' => $newRegistrationsToday,
            'pendingInvoices' => $this->dashboardService->getPendingInvoicesCount(),
            'revenueMonth' => $revenueSummary['currentMonth'],
            'revenueSummary' => $revenueSummary,
            'registrationTrend' => $registrationTrend,
            'upcomingRegistrations' => $this->dashboardService->getUpcomingRegistrations(6),
            'unreadNotifications' => $this->notificationQueryFor($request)->where('daDoc', false)->count(),
            'periodDays' => $days,
            'revenueChartData' => $this->dashboardService->getRevenueChartData($days),
            'classesByShift' => $this->dashboardService->getClassesByShift(),
            'registrationStatusData' => $registrationStatusData,
            'dashboardGeneratedAt' => Carbon::now(),
        ]);
    }
}
