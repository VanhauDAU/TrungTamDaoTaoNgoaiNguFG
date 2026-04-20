<?php

namespace App\Http\Controllers\Teacher\LopHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;

class LopHocController extends Controller
{
    public function index(Request $request)
    {
        return view('teacher.lop-hoc.index', [
            'classes' => LopHoc::query()
                ->with(['khoaHoc', 'coSo', 'caHoc'])
                ->withCount('dangKyLopHocs')
                ->where('taiKhoanId', $request->user()->getAuthIdentifier())
                ->latest('ngayBatDau')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }
}
