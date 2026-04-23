<?php

namespace App\Http\Controllers\Teacher\LichDay;

use App\Http\Controllers\Controller;
use App\Models\Education\BuoiHoc;
use App\Models\Education\CaHoc;
use App\Models\TeacherScheduleProposal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LichDayController extends Controller
{
    /**
     * Hiển thị thời khóa biểu tuần của giáo viên.
     */
    public function index(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();

        // ── Tính khoảng tuần ────────────────────────────────
        $baseDate = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::today()->startOfWeek(Carbon::MONDAY);

        $startOfWeek = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = $baseDate->copy()->endOfWeek(Carbon::SUNDAY);

        // ── Lấy ca học ─────────────────────────────────────
        $caHocs = CaHoc::where('trangThai', 1)
            ->orderBy('gioBatDau')
            ->get();

        // ── Lấy buổi dạy trong tuần ─────────────────────────
        $sessions = BuoiHoc::with([
            'lopHoc.khoaHoc',
            'lopHoc.coSo',
            'lopHoc.caHoc',
            'phongHoc',
            'caHoc',
            'taiKhoan.hoSoNguoiDung',
        ])
            ->whereHas('lopHoc', fn ($q) => $q->where('taiKhoanId', $teacherId))
            ->whereBetween('ngayHoc', [
                $startOfWeek->toDateString(),
                $endOfWeek->toDateString(),
            ])
            ->orderBy('ngayHoc')
            ->orderBy('caHocId')
            ->get();

        // ── Xây dựng lịch theo (thu => caHocId => [buoiHoc]) ──
        $schedule = [];
        foreach ($sessions as $buoi) {
            $thu    = Carbon::parse($buoi->ngayHoc)->dayOfWeekIso; // 1=Mon … 7=Sun
            $caId   = $buoi->caHocId;
            $schedule[$thu][$caId][] = $buoi;
        }

        // ── Dữ liệu 7 ngày trong tuần ───────────────────────
        $dayLabels = [1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN'];
        $weekDays  = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i - 1);
            $weekDays[] = [
                'thu'   => $i,
                'label' => $dayLabels[$i],
                'date'  => $date,
            ];
        }

        // ── Kiểm tra tuần có buổi dạy không ─────────────────
        $hasSessions = $sessions->isNotEmpty();

        return view('teacher.lich-day.index', compact(
            'sessions',
            'caHocs',
            'schedule',
            'weekDays',
            'startOfWeek',
            'endOfWeek',
            'baseDate',
            'hasSessions',
        ));
    }

    /**
     * API: Đề xuất dạy bù một buổi học.
     * Trả JSON – logic nghiệp vụ thực tế sẽ tạo yêu cầu vào bảng riêng.
     */
    public function proposeCompensation(Request $request, int $buoiHocId)
    {
        $validated = $request->validate([
            'ly_do'     => 'required|string|max:1000',
            'ngay_bu'   => 'nullable|date|after_or_equal:today',
            'ca_hoc_id' => 'nullable|integer|exists:cahoc,caHocId',
        ]);

        $buoi = BuoiHoc::findOrFail($buoiHocId);
        $teacherId = $request->user()->getAuthIdentifier();

        if ($buoi->lopHoc?->taiKhoanId !== $teacherId) {
            return response()->json(['message' => 'Bạn không có quyền đề xuất cho buổi học này.'], 403);
        }

        TeacherScheduleProposal::create([
            'buoiHocId'  => $buoiHocId,
            'taiKhoanId' => $teacherId,
            'loaiDeXuat' => 'compensation',
            'lyDo'       => $validated['ly_do'],
            'ngayBu'     => $validated['ngay_bu'] ?? null,
            'caHocId'    => $validated['ca_hoc_id'] ?? null,
            'trangThai'  => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đề xuất dạy bù đã được ghi nhận. Vui lòng chờ phê duyệt từ quản lý.',
        ]);
    }

    /**
     * API: Đề xuất tạm ngưng / hủy một buổi học.
     */
    public function proposeSuspension(Request $request, int $buoiHocId)
    {
        $validated = $request->validate([
            'ly_do' => 'required|string|max:1000',
        ]);

        $buoi = BuoiHoc::findOrFail($buoiHocId);
        $teacherId = $request->user()->getAuthIdentifier();

        if ($buoi->lopHoc?->taiKhoanId !== $teacherId) {
            return response()->json(['message' => 'Bạn không có quyền đề xuất cho buổi học này.'], 403);
        }

        TeacherScheduleProposal::create([
            'buoiHocId'  => $buoiHocId,
            'taiKhoanId' => $teacherId,
            'loaiDeXuat' => 'suspension',
            'lyDo'       => $validated['ly_do'],
            'trangThai'  => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đề xuất tạm ngưng buổi học đã được ghi nhận.',
        ]);
    }
}
