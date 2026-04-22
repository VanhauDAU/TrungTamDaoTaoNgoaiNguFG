<?php

namespace App\Http\Controllers\Teacher\TaiLieu;

use App\Http\Controllers\Controller;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;

class TaiLieuController extends Controller
{
    /**
     * teacher.materials.index – trang tổng hợp tài liệu.
     * Hiển thị danh sách các lớp của giáo viên và link vào từng lớp.
     */
    public function index(Request $request)
    {
        $teacherId = $request->user()->getAuthIdentifier();

        $classes = LopHoc::where('taiKhoanId', $teacherId)
            ->with(['khoaHoc'])
            ->withCount('lopHocTaiLieus')
            ->latest('ngayBatDau')
            ->get();

        return view('teacher.tai-lieu.index', compact('classes'));
    }
}
