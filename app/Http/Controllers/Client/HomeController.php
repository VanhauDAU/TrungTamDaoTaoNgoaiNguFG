<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Content\BaiViet;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index()
    {
        $khoaHocs = KhoaHoc::all();
        $topGiaoVien = TaiKhoan::where('role', 1)->take(4)->get();
        $danhSachKhoaHoc = KhoaHoc::where('trangThai', 1)->get();
        $baiViets = BaiViet::with(['danhMucs', 'tags'])
            ->where('trangThai', 1)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        return view('clients.trang-chu.index', compact('khoaHocs', 'topGiaoVien', 'danhSachKhoaHoc', 'baiViets'));
    }
}
