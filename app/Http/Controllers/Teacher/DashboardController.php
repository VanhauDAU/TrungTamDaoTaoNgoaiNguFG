<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DiemDanh;
use App\Models\Education\LopHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InteractsWithUserNotifications;

    public function __invoke(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();
        $classQuery = LopHoc::query()->where('taiKhoanId', $teacherId);
        $sessionBaseQuery = BuoiHoc::query()
            ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $teacherId));

        $upcomingSessions = BuoiHoc::query()
            ->with(['lopHoc.khoaHoc', 'phongHoc', 'caHoc'])
            ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $teacherId))
            ->whereDate('ngayHoc', '>=', today())
            ->orderBy('ngayHoc')
            ->limit(6)
            ->get();

        $weekWindowStart = today();
        $weekWindowEnd = today()->copy()->addDays(6);
        $weeklySessions = (clone $sessionBaseQuery)
            ->whereBetween('ngayHoc', [
                $weekWindowStart->copy()->startOfDay(),
                $weekWindowEnd->copy()->endOfDay(),
            ])
            ->get()
            ->groupBy(fn (BuoiHoc $session) => Carbon::parse($session->ngayHoc)->format('Y-m-d'));

        $weeklyScheduleData = collect(range(0, 6))
            ->map(function (int $offset) use ($weekWindowStart, $weeklySessions) {
                $date = $weekWindowStart->copy()->addDays($offset);
                $key = $date->format('Y-m-d');

                return [
                    'label' => $date->format('d/m'),
                    'count' => $weeklySessions->get($key, collect())->count(),
                ];
            })
            ->all();

        $classStatusData = [
            [
                'label' => LopHoc::trangThaiLabels()[LopHoc::TRANG_THAI_DANG_TUYEN_SINH],
                'value' => (clone $classQuery)->where('trangThai', LopHoc::TRANG_THAI_DANG_TUYEN_SINH)->count(),
                'color' => '#27c4b5',
            ],
            [
                'label' => LopHoc::trangThaiLabels()[LopHoc::TRANG_THAI_CHOT_DANH_SACH],
                'value' => (clone $classQuery)->where('trangThai', LopHoc::TRANG_THAI_CHOT_DANH_SACH)->count(),
                'color' => '#4299e1',
            ],
            [
                'label' => LopHoc::trangThaiLabels()[LopHoc::TRANG_THAI_DANG_HOC],
                'value' => (clone $classQuery)->where('trangThai', LopHoc::TRANG_THAI_DANG_HOC)->count(),
                'color' => '#ed8936',
            ],
            [
                'label' => LopHoc::trangThaiLabels()[LopHoc::TRANG_THAI_DA_KET_THUC],
                'value' => (clone $classQuery)->where('trangThai', LopHoc::TRANG_THAI_DA_KET_THUC)->count(),
                'color' => '#10b981',
            ],
        ];

        $attendanceBaseQuery = DiemDanh::query()
            ->whereHas('buoiHoc.lopHoc', fn ($query) => $query->where('taiKhoanId', $teacherId));

        $attendanceSummary = [
            'present' => (clone $attendanceBaseQuery)->where('trangThai', DiemDanh::CO_MAT)->count(),
            'absent' => (clone $attendanceBaseQuery)->whereIn('trangThai', [
                DiemDanh::VANG_KHONG_PHEP,
                DiemDanh::BI_KHOA_NO_HP,
            ])->count(),
            'locked' => (clone $attendanceBaseQuery)->where('trangThai', DiemDanh::BI_KHOA_NO_HP)->count(),
        ];

        return view('teacher.dashboard.index', [
            'totalClasses' => (clone $classQuery)->count(),
            'activeClasses' => (clone $classQuery)->whereIn('trangThai', [
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                LopHoc::TRANG_THAI_DANG_HOC,
            ])->count(),
            'todaySessionsCount' => (clone $sessionBaseQuery)
                ->whereDate('ngayHoc', today())
                ->count(),
            'upcomingSessionsCount' => (clone $sessionBaseQuery)
                ->whereDate('ngayHoc', '>=', today())
                ->count(),
            'liveSessionsCount' => (clone $sessionBaseQuery)
                ->where('trangThai', BuoiHoc::TRANG_THAI_DANG_DIEN_RA)
                ->count(),
            'unreadNotifications' => $this->notificationQueryFor($request)->where('daDoc', false)->count(),
            'upcomingSessions' => $upcomingSessions,
            'classStatusData' => $classStatusData,
            'attendanceSummary' => $attendanceSummary,
            'weeklyScheduleData' => $weeklyScheduleData,
            'dashboardGeneratedAt' => Carbon::now(),
        ]);
    }
}
