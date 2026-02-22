<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', '7'); // 7 ngày mặc định cho chart
        $days = (int) in_array($period, ['7', '14', '30']) ? $period : 7;

        $totalStudent = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count();
        $activeClasses = $this->dashboard->getActiveClassesCount();
        $newRegistrationsToday = $this->dashboard->getNewRegistrationsToday();
        $newRegistrationsYesterday = $this->dashboard->getNewRegistrationsYesterday();
        $revenueMonth = $this->dashboard->getRevenueCurrentMonth();
        $monthlyComparison = $this->dashboard->getMonthlyRevenueComparison();
        $revenueChartData = $this->dashboard->getRevenueChartData($days);
        $classesByShift = $this->dashboard->getClassesByShift();
        $upcomingRegistrations = $this->dashboard->getUpcomingRegistrations(5);
        $pendingInvoices = $this->dashboard->getPendingInvoicesCount();

        // Trend % đăng ký hôm nay vs hôm qua
        $registrationTrend = $newRegistrationsYesterday > 0
            ? round((($newRegistrationsToday - $newRegistrationsYesterday) / $newRegistrationsYesterday) * 100, 1)
            : ($newRegistrationsToday > 0 ? 100 : 0);

        return view('admin.dashboard', [
            'totalStudent' => $totalStudent,
            'activeClasses' => $activeClasses,
            'newRegistrationsToday' => $newRegistrationsToday,
            'registrationTrend' => $registrationTrend,
            'revenueMonth' => $revenueMonth,
            'monthlyComparison' => $monthlyComparison,
            'revenueChartData' => $revenueChartData,
            'classesByShift' => $classesByShift,
            'upcomingRegistrations' => $upcomingRegistrations,
            'pendingInvoices' => $pendingInvoices,
            'periodDays' => $days,
        ]);
    }
}
