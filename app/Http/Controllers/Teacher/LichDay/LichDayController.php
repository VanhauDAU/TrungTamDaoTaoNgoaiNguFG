<?php

namespace App\Http\Controllers\Teacher\LichDay;

use App\Http\Controllers\Controller;
use App\Models\Education\BuoiHoc;
use Illuminate\Http\Request;

class LichDayController extends Controller
{
    public function index(Request $request)
    {
        return view('teacher.lich-day.index', [
            'sessions' => BuoiHoc::query()
                ->with(['lopHoc.khoaHoc', 'phongHoc', 'caHoc'])
                ->whereHas('lopHoc', fn ($query) => $query->where('taiKhoanId', $request->user()->getAuthIdentifier()))
                ->orderBy('ngayHoc')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
}
