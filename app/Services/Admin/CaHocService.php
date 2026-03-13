<?php

namespace App\Services\Admin;

use App\Contracts\Admin\CaHocServiceInterface;
use App\Models\Education\CaHoc;
use App\Models\Education\LopHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CaHocService implements CaHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = CaHoc::withCount(['lopHocs', 'buoiHocs']);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('tenCa', 'like', "%{$search}%")
                    ->orWhere('moTa', 'like', "%{$search}%");
            });
        }

        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'gioBatDau');
        $dir = $request->get('dir', 'asc');
        if (in_array($orderBy, ['caHocId', 'tenCa', 'gioBatDau'], true)) {
            $query->orderBy($orderBy, $dir === 'desc' ? 'desc' : 'asc');
        }

        return [
            'caHocs' => $query->paginate(15)->withQueryString(),
            'tongCa' => CaHoc::count(),
            'dangHoatDong' => CaHoc::where('trangThai', 1)->count(),
            'ngungHoatDong' => CaHoc::where('trangThai', 0)->count(),
            'tongLopSuDung' => LopHoc::distinct('caHocId')->count('caHocId'),
        ];
    }

    public function store(Request $request): array
    {
        $data = $this->validateCaHoc($request);

        if ($this->isDuplicateFrame($data['tenCa'], $data['gioBatDau'], $data['gioKetThuc'])) {
            return $this->duplicateResponse($data['tenCa'], $data['gioBatDau'], $data['gioKetThuc']);
        }

        $caHoc = CaHoc::create($data);
        $caHoc->loadCount(['lopHocs', 'buoiHocs']);

        return [
            'success' => true,
            'message' => "Đã thêm ca học «{$caHoc->tenCa}» thành công.",
            'caHoc' => $this->formatCaHoc($caHoc),
            'status' => 200,
        ];
    }

    public function update(Request $request, int $id): array
    {
        $caHoc = CaHoc::findOrFail($id);
        $data = $this->validateCaHoc($request);

        if ($this->isDuplicateFrame($data['tenCa'], $data['gioBatDau'], $data['gioKetThuc'], $id)) {
            return $this->duplicateResponse($data['tenCa'], $data['gioBatDau'], $data['gioKetThuc']);
        }

        $caHoc->update($data);
        $caHoc->loadCount(['lopHocs', 'buoiHocs']);

        return [
            'success' => true,
            'message' => "Đã cập nhật ca học «{$caHoc->tenCa}» thành công.",
            'caHoc' => $this->formatCaHoc($caHoc),
            'status' => 200,
        ];
    }

    public function destroy(int $id): array
    {
        $caHoc = CaHoc::withCount(['lopHocs', 'buoiHocs'])->findOrFail($id);

        if ($caHoc->lop_hocs_count > 0) {
            return [
                'success' => false,
                'message' => "Không thể xóa! Ca học «{$caHoc->tenCa}» đang được sử dụng bởi {$caHoc->lop_hocs_count} lớp học.",
                'status' => 422,
            ];
        }

        if ($caHoc->buoi_hocs_count > 0) {
            return [
                'success' => false,
                'message' => "Không thể xóa! Ca học «{$caHoc->tenCa}» đang có {$caHoc->buoi_hocs_count} buổi học liên kết.",
                'status' => 422,
            ];
        }

        $ten = $caHoc->tenCa;
        $caHoc->delete();

        return [
            'success' => true,
            'message' => "Đã xóa ca học «{$ten}» thành công.",
            'id' => $id,
            'status' => 200,
        ];
    }

    public function toggleStatus(int $id): array
    {
        $caHoc = CaHoc::findOrFail($id);
        $caHoc->trangThai = $caHoc->trangThai ? 0 : 1;
        $caHoc->save();

        $label = $caHoc->trangThai ? 'Hoạt động' : 'Ngừng';

        return [
            'success' => true,
            'message' => "Đã chuyển ca học «{$caHoc->tenCa}» sang «{$label}».",
            'trangThai' => $caHoc->trangThai,
            'status' => 200,
        ];
    }

    private function validateCaHoc(Request $request): array
    {
        return $request->validate([
            'tenCa' => 'required|string|max:100',
            'gioBatDau' => 'required|date_format:H:i',
            'gioKetThuc' => 'required|date_format:H:i|after:gioBatDau',
            'moTa' => 'nullable|string|max:500',
            'trangThai' => 'required|in:0,1',
        ], [
            'tenCa.required' => 'Vui lòng nhập tên ca học.',
            'tenCa.max' => 'Tên ca tối đa 100 ký tự.',
            'gioBatDau.required' => 'Vui lòng chọn giờ bắt đầu.',
            'gioBatDau.date_format' => 'Giờ bắt đầu không hợp lệ.',
            'gioKetThuc.required' => 'Vui lòng chọn giờ kết thúc.',
            'gioKetThuc.date_format' => 'Giờ kết thúc không hợp lệ.',
            'gioKetThuc.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ]);
    }

    private function isDuplicateFrame(string $tenCa, string $gioBatDau, string $gioKetThuc, ?int $excludeId = null): bool
    {
        $query = CaHoc::where('tenCa', $tenCa)
            ->where('gioBatDau', $gioBatDau . ':00')
            ->where('gioKetThuc', $gioKetThuc . ':00');

        if ($excludeId !== null) {
            $query->where('caHocId', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function duplicateResponse(string $tenCa, string $gioBatDau, string $gioKetThuc): array
    {
        return [
            'success' => false,
            'errors' => [
                'tenCa' => [
                    "Ca học «{$tenCa}» với khung giờ {$gioBatDau}–{$gioKetThuc} đã tồn tại.",
                ],
            ],
            'status' => 422,
        ];
    }

    private function formatCaHoc(CaHoc $caHoc): array
    {
        $start = Carbon::createFromFormat('H:i:s', $caHoc->gioBatDau);
        $end = Carbon::createFromFormat('H:i:s', $caHoc->gioKetThuc);
        $minutes = $start->diffInMinutes($end);
        $thoiLuong = $minutes >= 60
            ? intdiv($minutes, 60) . ' giờ ' . ($minutes % 60 > 0 ? ($minutes % 60) . ' phút' : '')
            : $minutes . ' phút';

        return [
            'caHocId' => $caHoc->caHocId,
            'tenCa' => $caHoc->tenCa,
            'gioBatDau' => substr($caHoc->gioBatDau, 0, 5),
            'gioKetThuc' => substr($caHoc->gioKetThuc, 0, 5),
            'moTa' => $caHoc->moTa,
            'trangThai' => $caHoc->trangThai,
            'thoiLuong' => trim($thoiLuong),
            'soLop' => $caHoc->lop_hocs_count ?? 0,
            'soBuoi' => $caHoc->buoi_hocs_count ?? 0,
        ];
    }
}
