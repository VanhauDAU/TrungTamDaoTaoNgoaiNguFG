<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Display the About Us page
     */
    public function index()
    {
        // Lấy top giảng viên với eager loading để tránh N+1
        $topGiaoVien = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', 1)
            ->take(4)
            ->get();

        // Lấy danh sách khóa học đang hoạt động
        $khoaHocs = KhoaHoc::where('trangThai', 1)->take(6)->get();

        return view('clients.aboutUs.index', compact('topGiaoVien', 'khoaHocs'));
    }
}
