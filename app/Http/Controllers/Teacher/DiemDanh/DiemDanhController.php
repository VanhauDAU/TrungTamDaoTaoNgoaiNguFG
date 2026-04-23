<?php

namespace App\Http\Controllers\Teacher\DiemDanh;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Teacher\DiemDanh\TeacherAttendanceService;
use Illuminate\Http\Request;

class DiemDanhController extends Controller
{
    public function __construct(
        private readonly TeacherAttendanceService $teacherAttendanceService
    ) {}

    public function index(Request $request)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return view('teacher.diem-danh.index', $this->teacherAttendanceService->getIndexData($request, $teacher));
    }

    public function store(Request $request, int $buoiHocId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();
        $result = $this->teacherAttendanceService->saveAttendance($request, $teacher, $buoiHocId);

        return redirect()
            ->route('teacher.attendance.index', [
                'khoaHocId' => $result['khoaHocId'],
                'lopHocId' => $result['lopHocId'],
                'buoiHocId' => $result['buoiHocId'],
            ])
            ->with('success', $result['message']);
    }

    public function export(Request $request, int $buoiHocId)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return $this->teacherAttendanceService->exportAttendance($teacher, $buoiHocId);
    }

    public function classes(Request $request)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        return response()->json([
            'data' => $this->teacherAttendanceService->getClassesForCourse(
                $teacher,
                $request->integer('khoaHocId') ?: null
            ),
        ]);
    }

    public function sessions(Request $request)
    {
        /** @var TaiKhoan $teacher */
        $teacher = $request->user();

        $request->validate([
            'lopHocId' => 'required|integer',
        ]);

        return response()->json([
            'data' => $this->teacherAttendanceService->getSessionsForClass(
                $teacher,
                $request->integer('lopHocId')
            ),
        ]);
    }
}
