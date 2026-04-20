<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use App\Models\Education\BuoiHoc;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithUserNotifications;

    public function __invoke(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $classQuery = LopHoc::query()->where('taiKhoanId', $teacherId);

        return view('teacher.dashboard.index', [
            'totalClasses' => (clone $classQuery)->count(),
            'activeClasses' => (clone $classQuery)->whereIn('trangThai', [
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                LopHoc::TRANG_THAI_DANG_HOC,
            ])->count(),
            'upcomingSessionsCount' => BuoiHoc::query()
                ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $teacherId))
                ->whereDate('ngayHoc', '>=', today())
                ->count(),
            'unreadNotifications' => $this->notificationQueryFor($request)->where('daDoc', false)->count(),
            'upcomingSessions' => BuoiHoc::query()
                ->with(['lopHoc.khoaHoc', 'phongHoc', 'caHoc'])
                ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $teacherId))
                ->orderBy('ngayHoc')
                ->limit(6)
                ->get(),
        ]);
    }
}
