<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Admin\Dashboard\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard
        )
    {
    }

    public function index(Request $request)
    {
        $revenueSummary = $this->dashboard->getMonthlyRevenueComparison();
        $revenueChartData = $this->dashboard->getRevenueChartData(14);
        $recentRegistrations = $this->dashboard->getUpcomingRegistrations(6);
        $newRegistrationsToday = $this->dashboard->getNewRegistrationsToday();
        $newRegistrationsYesterday = $this->dashboard->getNewRegistrationsYesterday();
        $registrationTrend = $newRegistrationsYesterday > 0
            ? round((($newRegistrationsToday - $newRegistrationsYesterday) / $newRegistrationsYesterday) * 100, 1)
            : ($newRegistrationsToday > 0 ? 100.0 : 0.0);

        return view('admin.dashboard.index', [
            'totalStudent' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
            'totalTeacher' => TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)->count(),
            'totalStaff' => TaiKhoan::where('role', TaiKhoan::ROLE_NHAN_VIEN)->count(),
            'activeClasses' => $this->dashboard->getActiveClassesCount(),
            'revenueMonth' => $revenueSummary['currentMonth'],
            'revenueSummary' => $revenueSummary,
            'pendingInvoicesCount' => $this->dashboard->getPendingInvoicesCount(),
            'newRegistrationsToday' => $newRegistrationsToday,
            'registrationTrend' => $registrationTrend,
            'revenueChartData' => $revenueChartData,
            'classesByShift' => $this->dashboard->getClassesByShift(),
            'recentRegistrations' => $recentRegistrations,
            'dashboardGeneratedAt' => Carbon::now(),
        ]);
    }
}
