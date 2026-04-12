<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CauHinhHeThong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CauHinhController extends Controller
{
    // ── Hiển thị trang cấu hình ──────────────────────────────────
    public function index(Request $request)
    {
        $nhomMeta  = CauHinhHeThong::labelNhom();
        $nhomHienTai = $request->query('nhom', 'he_thong');

        // Đảm bảo nhóm hợp lệ
        if (! array_key_exists($nhomHienTai, $nhomMeta)) {
            $nhomHienTai = 'he_thong';
        }

        // Đếm số cấu hình mỗi nhóm
        $demTheoNhom = CauHinhHeThong::hienThi()
            ->selectRaw('nhom, count(*) as total')
            ->groupBy('nhom')
            ->pluck('total', 'nhom')
            ->toArray();

        // Lấy cấu hình của nhóm hiện tại
        $cauHinhs = CauHinhHeThong::nhom($nhomHienTai)->hienThi()->get();

        return view('admin.cau-hinh.index', compact(
            'nhomMeta',
            'nhomHienTai',
            'demTheoNhom',
            'cauHinhs',
        ));
    }

    // ── Lưu nhiều cấu hình cùng lúc ─────────────────────────────
    public function update(Request $request)
    {
        $data = $request->input('cau_hinh', []);

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu để lưu.',
            ], 422);
        }

        $khoas = array_keys($data);
        $rows  = CauHinhHeThong::whereIn('khoa', $khoas)->get()->keyBy('khoa');

        foreach ($data as $khoa => $giaTriMoi) {
            $row = $rows->get($khoa);
            if (! $row) {
                continue;
            }

            // Xử lý boolean: checkbox không gửi nếu không chọn
            if ($row->kieu_du_lieu === 'boolean') {
                $giaTriMoi = isset($data[$khoa]) ? '1' : '0';
            }

            $row->gia_tri = is_string($giaTriMoi) ? trim($giaTriMoi) : $giaTriMoi;
            $row->save();
        }

        // Xử lý các boolean không được gửi lên (checkbox uncheck)
        $nhom = $request->input('nhom_hien_tai', 'he_thong');
        $rowsNhom = CauHinhHeThong::nhom($nhom)->where('kieu_du_lieu', 'boolean')->get();
        foreach ($rowsNhom as $row) {
            if (! array_key_exists($row->khoa, $data)) {
                $row->gia_tri = '0';
                $row->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu cấu hình thành công!',
        ]);
    }

    // ── Reset về mặc định ────────────────────────────────────────
    public function reset(Request $request)
    {
        $nhom = $request->input('nhom');

        if ($nhom && array_key_exists($nhom, CauHinhHeThong::labelNhom())) {
            CauHinhHeThong::where('nhom', $nhom)->update([
                'gia_tri' => \DB::raw('gia_tri_mac_dinh'),
            ]);
            // Xóa cache tất cả khóa trong nhóm
            CauHinhHeThong::where('nhom', $nhom)->each(function ($row) {
                Cache::forget("cauhinh:{$row->khoa}");
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã khôi phục cấu hình về mặc định!',
        ]);
    }
}
