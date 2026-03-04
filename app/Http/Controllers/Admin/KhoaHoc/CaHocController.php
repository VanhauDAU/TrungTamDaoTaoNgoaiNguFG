<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\CaHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\BuoiHoc;
use Illuminate\Http\Request;


class CaHocController extends Controller
{
    /** Danh sách ca học */
    public function index(Request $request)
    {
        $query = CaHoc::withCount(['lopHocs', 'buoiHocs']);

        // ── Tìm kiếm ─────────────────────────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenCa', 'like', "%{$search}%")
                    ->orWhere('moTa', 'like', "%{$search}%");
            });
        }

        // ── Lọc trạng thái ───────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ──────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'gioBatDau');
        $dir = $request->get('dir', 'asc');
        if (in_array($orderBy, ['caHocId', 'tenCa', 'gioBatDau'])) {
            $query->orderBy($orderBy, $dir === 'desc' ? 'desc' : 'asc');
        }

        $caHocs = $query->paginate(15)->withQueryString();

        // ── Stats ─────────────────────────────────────────────
        $tongCa = CaHoc::count();
        $dangHoatDong = CaHoc::where('trangThai', 1)->count();
        $ngungHoatDong = CaHoc::where('trangThai', 0)->count();
        $tongLopSuDung = LopHoc::distinct('caHocId')->count('caHocId');

        return view('admin.ca-hoc.index', compact(
            'caHocs',
            'tongCa',
            'dangHoatDong',
            'ngungHoatDong',
            'tongLopSuDung'
        ));
    }

    /** Tạo ca học mới */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tenCa'      => 'required|string|max:100',
            'gioBatDau'  => 'required|date_format:H:i',
            'gioKetThuc' => 'required|date_format:H:i|after:gioBatDau',
            'moTa'       => 'nullable|string|max:500',
            'trangThai'  => 'required|in:0,1',
        ], [
            'tenCa.required'        => 'Vui lòng nhập tên ca học.',
            'tenCa.max'             => 'Tên ca tối đa 100 ký tự.',
            'gioBatDau.required'    => 'Vui lòng chọn giờ bắt đầu.',
            'gioBatDau.date_format' => 'Giờ bắt đầu không hợp lệ.',
            'gioKetThuc.required'   => 'Vui lòng chọn giờ kết thúc.',
            'gioKetThuc.date_format'=> 'Giờ kết thúc không hợp lệ.',
            'gioKetThuc.after'      => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ]);

        // Kiểm tra trùng tổ hợp (tenCa + gioBatDau + gioKetThuc)
        $trung = CaHoc::where('tenCa', $data['tenCa'])
            ->where('gioBatDau', $data['gioBatDau'] . ':00')
            ->where('gioKetThuc', $data['gioKetThuc'] . ':00')
            ->exists();
        if ($trung) {
            return response()->json([
                'success' => false,
                'errors'  => ['tenCa' => ['Ca học «' . $data['tenCa'] . '» với khung giờ ' . $data['gioBatDau'] . '–' . $data['gioKetThuc'] . ' đã tồn tại.']],
            ], 422);
        }

        $ca = CaHoc::create($data);
        $ca->loadCount(['lopHocs', 'buoiHocs']);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm ca học «{$ca->tenCa}» thành công.",
            'caHoc'   => $this->formatCaHoc($ca),
        ]);
    }

    /** Cập nhật ca học */
    public function update(Request $request, int $id)
    {
        $ca = CaHoc::findOrFail($id);

        $data = $request->validate([
            'tenCa'      => 'required|string|max:100',
            'gioBatDau'  => 'required|date_format:H:i',
            'gioKetThuc' => 'required|date_format:H:i|after:gioBatDau',
            'moTa'       => 'nullable|string|max:500',
            'trangThai'  => 'required|in:0,1',
        ], [
            'tenCa.required'        => 'Vui lòng nhập tên ca học.',
            'tenCa.max'             => 'Tên ca tối đa 100 ký tự.',
            'gioBatDau.required'    => 'Vui lòng chọn giờ bắt đầu.',
            'gioBatDau.date_format' => 'Giờ bắt đầu không hợp lệ.',
            'gioKetThuc.required'   => 'Vui lòng chọn giờ kết thúc.',
            'gioKetThuc.date_format'=> 'Giờ kết thúc không hợp lệ.',
            'gioKetThuc.after'      => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ]);

        // Kiểm tra trùng tổ hợp (tenCa + gioBatDau + gioKetThuc), bỏ qua chính record đang sửa
        $trung = CaHoc::where('tenCa', $data['tenCa'])
            ->where('gioBatDau', $data['gioBatDau'] . ':00')
            ->where('gioKetThuc', $data['gioKetThuc'] . ':00')
            ->where('caHocId', '!=', $id)
            ->exists();
        if ($trung) {
            return response()->json([
                'success' => false,
                'errors'  => ['tenCa' => ['Ca học «' . $data['tenCa'] . '» với khung giờ ' . $data['gioBatDau'] . '–' . $data['gioKetThuc'] . ' đã tồn tại.']],
            ], 422);
        }

        $ca->update($data);
        $ca->loadCount(['lopHocs', 'buoiHocs']);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật ca học «{$ca->tenCa}» thành công.",
            'caHoc'   => $this->formatCaHoc($ca),
        ]);
    }

    /** Xóa ca học */
    public function destroy(int $id)
    {
        $ca = CaHoc::withCount(['lopHocs', 'buoiHocs'])->findOrFail($id);

        if ($ca->lop_hocs_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa! Ca học «{$ca->tenCa}» đang được sử dụng bởi {$ca->lop_hocs_count} lớp học.",
            ], 422);
        }

        if ($ca->buoi_hocs_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa! Ca học «{$ca->tenCa}» đang có {$ca->buoi_hocs_count} buổi học liên kết.",
            ], 422);
        }

        $ten = $ca->tenCa;
        $ca->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa ca học «{$ten}» thành công.",
            'id'      => $id,
        ]);
    }

    /** Toggle trạng thái */
    public function toggleStatus(int $id)
    {
        $ca = CaHoc::findOrFail($id);
        $ca->trangThai = $ca->trangThai ? 0 : 1;
        $ca->save();

        $label = $ca->trangThai ? 'Hoạt động' : 'Ngừng';
        return response()->json([
            'success'   => true,
            'message'   => "Đã chuyển ca học «{$ca->tenCa}» sang «{$label}».",
            'trangThai' => $ca->trangThai,
        ]);
    }

    /** Format dữ liệu ca học cho JSON */
    private function formatCaHoc(CaHoc $ca): array
    {
        // Tính thời lượng phút
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $ca->gioBatDau);
        $end   = \Carbon\Carbon::createFromFormat('H:i:s', $ca->gioKetThuc);
        $minutes = $start->diffInMinutes($end);
        $thoiLuong = $minutes >= 60
            ? intdiv($minutes, 60) . ' giờ ' . ($minutes % 60 > 0 ? ($minutes % 60) . ' phút' : '')
            : $minutes . ' phút';

        return [
            'caHocId'        => $ca->caHocId,
            'tenCa'          => $ca->tenCa,
            'gioBatDau'      => substr($ca->gioBatDau, 0, 5),
            'gioKetThuc'     => substr($ca->gioKetThuc, 0, 5),
            'moTa'           => $ca->moTa,
            'trangThai'      => $ca->trangThai,
            'thoiLuong'      => trim($thoiLuong),
            'soLop'          => $ca->lop_hocs_count ?? 0,
            'soBuoi'         => $ca->buoi_hocs_count ?? 0,
        ];
    }
}
