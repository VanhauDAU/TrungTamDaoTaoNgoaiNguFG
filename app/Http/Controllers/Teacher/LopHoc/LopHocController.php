<?php

namespace App\Http\Controllers\Teacher\LopHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\LopHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Interaction\Chat\ChatRoom;
use App\Services\Education\LopHocTaiLieuService;
use Illuminate\Http\Request;

class LopHocController extends Controller
{
    public function __construct(
        private readonly LopHocTaiLieuService $lopHocTaiLieuService
    ) {
    }

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

    public function show(Request $request, string $slug)
    {
        $teacherId = $request->user()->getAuthIdentifier();

        $lopHoc = LopHoc::query()
            ->with(['khoaHoc', 'coSo', 'caHoc', 'phongHoc', 'chinhSachGia', 'lopHocTaiLieus.giaoVienTaiLieu'])
            ->where('slug', $slug)
            ->where('taiKhoanId', $teacherId)
            ->firstOrFail();

        $dangKyLopHocs = DangKyLopHoc::query()
            ->with(['taiKhoan.hoSoNguoiDung'])
            ->where('lopHocId', $lopHoc->lopHocId)
            ->get();

        $buoiHocs = BuoiHoc::query()
            ->with(['phongHoc', 'caHoc'])
            ->where('lopHocId', $lopHoc->lopHocId)
            ->orderBy('ngayHoc')
            ->get();

        $chatRoom = ChatRoom::query()
            ->where('lopHocId', $lopHoc->lopHocId)
            ->where('loai', ChatRoom::TYPE_CLASS_GROUP)
            ->first();

        $taiLieus = $lopHoc->lopHocTaiLieus;
        $taiLieuGroups = $this->lopHocTaiLieuService->groupForDisplay($taiLieus);

        return view('teacher.lop-hoc.show', compact('lopHoc', 'dangKyLopHocs', 'buoiHocs', 'chatRoom', 'taiLieus', 'taiLieuGroups'));
    }
}
