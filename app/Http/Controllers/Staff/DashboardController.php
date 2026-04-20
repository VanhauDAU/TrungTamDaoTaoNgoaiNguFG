<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Admin\Dashboard\DashboardService;
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

        return view('staff.dashboard.index', [
            'totalStudent' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
            'activeClasses' => $this->dashboardService->getActiveClassesCount(),
            'newRegistrationsToday' => $this->dashboardService->getNewRegistrationsToday(),
            'pendingInvoices' => $this->dashboardService->getPendingInvoicesCount(),
            'revenueMonth' => $this->dashboardService->getRevenueCurrentMonth(),
            'upcomingRegistrations' => $this->dashboardService->getUpcomingRegistrations(6),
            'unreadNotifications' => $this->notificationQueryFor($request)->where('daDoc', false)->count(),
            'periodDays' => $days,
        ]);
    }
}
