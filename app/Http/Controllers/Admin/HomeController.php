<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Admin\Dashboard\DashboardService;
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
        return view('admin.dashboard.index', [
            'totalStudent' => TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count(),
            'activeClasses' => $this->dashboard->getActiveClassesCount(),
            'revenueMonth' => $this->dashboard->getRevenueCurrentMonth(),
            'classesByShift' => $this->dashboard->getClassesByShift(),
        ]);
    }
}
