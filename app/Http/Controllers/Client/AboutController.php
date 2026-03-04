<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Display the About Us page
     */
    public function index()
    {
        // Lấy top giảng viên với eager loading, chỉ lấy những tài khoản có đủ thông tin
        $topGiaoVien = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', 1)
            ->where('trangThai', 1) // Chỉ lấy tài khoản active
            ->whereHas('hoSoNguoiDung') // Phải có hồ sơ người dùng
            ->whereHas('nhanSu') // Phải có nhân sự
            ->take(4)
            ->get();

        // Lấy danh sách khóa học đang hoạt động
        $khoaHocs = KhoaHoc::where('trangThai', 1)->take(6)->get();
        $provinces = TinhThanh::whereHas('coSoDaoTao', function ($query) {
            $query->where('trangThai', 1);
        })->get();
        $branches = CoSoDaoTao::where('trangThai', 1)->with('tinhThanh')->get();
        return view('clients.gioi-thieu.index', compact('topGiaoVien', 'khoaHocs', 'provinces', 'branches'));
    }
}
