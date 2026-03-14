<?php

namespace App\Services\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\BuoiHocServiceInterface;
use App\Models\Education\BuoiHoc;
use App\Models\Education\LopHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BuoiHocService implements BuoiHocServiceInterface
{
    private const THU_MAP = [
        '2' => Carbon::MONDAY,
        '3' => Carbon::TUESDAY,
        '4' => Carbon::WEDNESDAY,
        '5' => Carbon::THURSDAY,
        '6' => Carbon::FRIDAY,
        '7' => Carbon::SATURDAY,
        'CN' => Carbon::SUNDAY,
    ];

    public function store(Request $request): array
    {
        $data = $this->validateStorePayload($request);
        $data['daDiemDanh'] = 0;
        $data = BuoiHoc::normalizeStatePayload($data);

        $lopHoc = LopHoc::select(['lopHocId', 'slug', 'tenLopHoc'])->findOrFail($data['lopHocId']);

        if (empty($data['tenBuoiHoc'])) {
            $soBuoi = BuoiHoc::where('lopHocId', $data['lopHocId'])->count() + 1;
            $data['tenBuoiHoc'] = "Buổi {$soBuoi}: {$lopHoc->tenLopHoc}";
        }

        BuoiHoc::create($data);

        return [
            'lopHocSlug' => $lopHoc->slug,
            'message' => 'Đã thêm buổi học thành công.',
            'flashType' => 'success',
        ];
    }

    public function update(Request $request, int $id): array
    {
        $buoiHoc = BuoiHoc::with('lopHoc:lopHocId,slug')->findOrFail($id);
        $data = $this->validateUpdatePayload($request);
        $data = BuoiHoc::normalizeStatePayload($data, $buoiHoc);
        $buoiHoc->update($data);

        return [
            'lopHocSlug' => $buoiHoc->lopHoc?->slug,
            'message' => 'Đã cập nhật buổi học thành công.',
            'json' => ['success' => true, 'message' => 'Đã cập nhật buổi học.'],
        ];
    }

    public function destroy(int $id): array
    {
        $buoiHoc = BuoiHoc::with('lopHoc:lopHocId,slug')->findOrFail($id);
        $lopHocSlug = $buoiHoc->lopHoc?->slug;
        $buoiHoc->delete();

        return [
            'lopHocSlug' => $lopHocSlug,
            'message' => 'Đã xóa buổi học.',
            'json' => ['success' => true],
        ];
    }

    public function autoGenerate(Request $request, int $lopHocId): array
    {
        $lopHoc = LopHoc::with('caHoc')->findOrFail($lopHocId);

        if (empty($lopHoc->lichHoc) || empty($lopHoc->ngayBatDau) || empty($lopHoc->ngayKetThuc)) {
            return [
                'lopHocSlug' => $lopHoc->slug,
                'message' => 'Lớp học chưa thiết lập đầy đủ lịch học, ngày bắt đầu / kết thúc.',
                'flashType' => 'error',
            ];
        }

        $request->validate([
            'xoa_cu' => 'nullable|in:0,1',
        ]);

        if ($request->xoa_cu) {
            BuoiHoc::where('lopHocId', $lopHocId)
                ->where('trangThai', '!=', BuoiHoc::TRANG_THAI_DA_HOAN_THANH)
                ->delete();
        }

        $thuDays = collect(explode(',', $lopHoc->lichHoc))
            ->map(fn ($thu) => trim($thu))
            ->filter(fn ($thu) => isset(self::THU_MAP[$thu]))
            ->map(fn ($thu) => self::THU_MAP[$thu])
            ->values()
            ->all();

        if ($thuDays === []) {
            return [
                'lopHocSlug' => $lopHoc->slug,
                'message' => 'Lịch học không hợp lệ. Ví dụ: 2,4,6',
                'flashType' => 'error',
            ];
        }

        $start = Carbon::parse($lopHoc->ngayBatDau);
        $end = Carbon::parse($lopHoc->ngayKetThuc);
        $count = 0;
        $soBuoi = BuoiHoc::where('lopHocId', $lopHocId)->count();

        $current = $start->copy();
        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, $thuDays, true)) {
                $exists = BuoiHoc::where('lopHocId', $lopHocId)
                    ->whereDate('ngayHoc', $current->toDateString())
                    ->exists();

                if (! $exists) {
                    $soBuoi++;
                    BuoiHoc::create([
                        'lopHocId' => $lopHocId,
                        'tenBuoiHoc' => "Buổi {$soBuoi}: {$lopHoc->tenLopHoc}",
                        'ngayHoc' => $current->toDateString(),
                        'caHocId' => $lopHoc->caHocId,
                        'phongHocId' => $lopHoc->phongHocId,
                        'taiKhoanId' => $lopHoc->taiKhoanId,
                        'daHoanThanh' => 0,
                        'daDiemDanh' => 0,
                        'trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
                    ]);
                    $count++;
                }
            }

            $current->addDay();
        }

        if ((int) $lopHoc->soBuoiDuKien !== $soBuoi) {
            $lopHoc->update(['soBuoiDuKien' => $soBuoi]);
        }

        return [
            'lopHocSlug' => $lopHoc->slug,
            'message' => "Đã tự động tạo {$count} buổi học thành công.",
            'flashType' => 'success',
        ];
    }

    private function validateStorePayload(Request $request): array
    {
        return $request->validate([
            'lopHocId' => 'required|exists:lophoc,lopHocId',
            'tenBuoiHoc' => 'nullable|string|max:255',
            'ngayHoc' => 'required|date',
            'caHocId' => 'required|exists:cahoc,caHocId',
            'phongHocId' => 'nullable|exists:phonghoc,phongHocId',
            'taiKhoanId' => 'nullable|exists:taikhoan,taiKhoanId',
            'ghiChu' => 'nullable|string',
            'trangThai' => 'nullable|in:' . implode(',', BuoiHoc::validTrangThaiValues()),
        ], [
            'lopHocId.required' => 'Vui lòng chọn lớp học.',
            'ngayHoc.required' => 'Vui lòng chọn ngày học.',
            'caHocId.required' => 'Vui lòng chọn ca học.',
        ]);
    }

    private function validateUpdatePayload(Request $request): array
    {
        return $request->validate([
            'tenBuoiHoc' => 'nullable|string|max:255',
            'ngayHoc' => 'sometimes|required|date',
            'caHocId' => 'sometimes|required|exists:cahoc,caHocId',
            'phongHocId' => 'nullable|exists:phonghoc,phongHocId',
            'taiKhoanId' => 'nullable|exists:taikhoan,taiKhoanId',
            'ghiChu' => 'nullable|string',
            'daHoanThanh' => 'nullable|in:0,1',
            'daDiemDanh' => 'nullable|in:0,1',
            'trangThai' => 'nullable|in:' . implode(',', BuoiHoc::validTrangThaiValues()),
        ]);
    }
}
