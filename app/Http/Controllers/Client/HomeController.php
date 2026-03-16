<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Content\BaiViet;
use App\Models\Course\KhoaHoc;
use App\Services\Client\PublicContentCacheService;

class HomeController extends Controller
{
    public function __construct(
        protected PublicContentCacheService $publicContentCache
    ) {
    }

    public function index()
    {
        $payload = $this->publicContentCache->remember(
            'home.index',
            [],
            function (): array {
                return [
                    'khoaHocs' => KhoaHoc::query()
                        ->with('danhMuc')
                        ->orderByDesc('khoaHocId')
                        ->get(),
                    'topGiaoVien' => TaiKhoan::query()
                        ->with(['hoSoNguoiDung', 'nhanSu'])
                        ->where('role', 1)
                        ->take(4)
                        ->get(),
                    'danhSachKhoaHoc' => KhoaHoc::query()
                        ->where('trangThai', 1)
                        ->orderBy('tenKhoaHoc')
                        ->get(),
                    'baiViets' => BaiViet::query()
                        ->with(['danhMucs', 'tags'])
                        ->where('trangThai', 1)
                        ->latest()
                        ->take(5)
                        ->get(),
                ];
            }
        );

        return view('clients.trang-chu.index', $payload);
    }
}
