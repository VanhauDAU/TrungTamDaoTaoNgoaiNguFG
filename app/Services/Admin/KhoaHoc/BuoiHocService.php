<?php

namespace App\Services\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\BuoiHocServiceInterface;
use App\Models\Education\BuoiHoc;
use App\Models\Education\LopHoc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $data = $this->validateUpdatePayload($request, $buoiHoc);
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
                'message' => 'Lớp học chưa thiết lập đầy đủ lịch học, ngày bắt đầu hoặc ngày kết thúc.',
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

        if ($end->lt($start)) {
            return [
                'lopHocSlug' => $lopHoc->slug,
                'message' => 'Ngày kết thúc lớp học đang nhỏ hơn ngày bắt đầu, chưa thể tự động tạo buổi học.',
                'flashType' => 'error',
            ];
        }

        $count = 0;
        $skipped = 0;
        $current = $start->copy();
        $safetyLimit = 0;
        $sessionNumber = BuoiHoc::where('lopHocId', $lopHocId)
            ->whereNotIn('trangThai', [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH])
            ->count();

        while ($current->lte($end) && $safetyLimit < 3660) {
            if (in_array($current->dayOfWeek, $thuDays, true)) {
                $exists = BuoiHoc::where('lopHocId', $lopHocId)
                    ->whereDate('ngayHoc', $current->toDateString())
                    ->whereNotIn('trangThai', [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH])
                    ->exists();

                if (! $exists) {
                    $sessionNumber++;
                    BuoiHoc::create([
                        'lopHocId' => $lopHocId,
                        'tenBuoiHoc' => "Buổi {$sessionNumber}: {$lopHoc->tenLopHoc}",
                        'ngayHoc' => $current->toDateString(),
                        'caHocId' => $lopHoc->caHocId,
                        'phongHocId' => $lopHoc->phongHocId,
                        'taiKhoanId' => $lopHoc->taiKhoanId,
                        'daHoanThanh' => 0,
                        'daDiemDanh' => 0,
                        'trangThai' => BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
                    ]);
                    $count++;
                } else {
                    $skipped++;
                }
            }

            $current->addDay();
            $safetyLimit++;
        }

        if ($count === 0) {
            return [
                'lopHocSlug' => $lopHoc->slug,
                'message' => 'Không có buổi học mới nào được tạo trong khoảng ngày của lớp. Có thể các ngày hợp lệ đã tồn tại sẵn.',
                'flashType' => 'success',
            ];
        }

        return [
            'lopHocSlug' => $lopHoc->slug,
            'message' => "Đã tự động tạo {$count} buổi học trong khoảng {$start->format('d/m/Y')} - {$end->format('d/m/Y')}" . ($skipped > 0 ? ", bỏ qua {$skipped} ngày đã có buổi." : '.'),
            'flashType' => 'success',
        ];
    }

    private function validateStorePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
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

        $validator->after(function ($validator) use ($request) {
            $lopHocId = (int) $request->input('lopHocId');
            if ($lopHocId <= 0 || ! $request->filled('ngayHoc')) {
                return;
            }

            $lopHoc = LopHoc::find($lopHocId);
            if (! $lopHoc) {
                return;
            }

            $this->validateSessionDateAgainstClass(
                $validator,
                $lopHoc,
                (string) $request->input('ngayHoc')
            );
        });

        return $validator->validate();
    }

    private function validateUpdatePayload(Request $request, BuoiHoc $buoiHoc): array
    {
        $validator = Validator::make($request->all(), [
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

        $validator->after(function ($validator) use ($request, $buoiHoc) {
            if (! $request->filled('ngayHoc')) {
                return;
            }

            $lopHoc = LopHoc::find($buoiHoc->lopHocId);
            if (! $lopHoc) {
                return;
            }

            $this->validateSessionDateAgainstClass(
                $validator,
                $lopHoc,
                (string) $request->input('ngayHoc'),
                $buoiHoc->buoiHocId
            );
        });

        return $validator->validate();
    }

    private function validateSessionDateAgainstClass(
        $validator,
        LopHoc $lopHoc,
        string $ngayHoc,
        ?int $ignoreBuoiHocId = null
    ): void {
        $date = Carbon::parse($ngayHoc)->startOfDay();

        if ($lopHoc->ngayBatDau && $date->lt(Carbon::parse($lopHoc->ngayBatDau)->startOfDay())) {
            $validator->errors()->add('ngayHoc', 'Ngày học không được nhỏ hơn ngày bắt đầu của lớp.');
        }

        if ($lopHoc->ngayKetThuc && $date->gt(Carbon::parse($lopHoc->ngayKetThuc)->startOfDay())) {
            $validator->errors()->add('ngayHoc', 'Ngày học không được lớn hơn ngày kết thúc của lớp.');
        }

        if (! empty($lopHoc->lichHoc)) {
            $allowedDays = collect(explode(',', (string) $lopHoc->lichHoc))
                ->map(fn ($item) => trim($item))
                ->filter(fn ($item) => isset(self::THU_MAP[$item]))
                ->map(fn ($item) => self::THU_MAP[$item])
                ->values()
                ->all();

            if ($allowedDays !== [] && ! in_array($date->dayOfWeek, $allowedDays, true)) {
                $validator->errors()->add('ngayHoc', 'Ngày học phải khớp với lịch học đã cấu hình cho lớp.');
            }
        }

        $duplicateQuery = BuoiHoc::query()
            ->where('lopHocId', $lopHoc->lopHocId)
            ->whereDate('ngayHoc', $date->toDateString())
            ->whereNotIn('trangThai', [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH]);

        if ($ignoreBuoiHocId !== null) {
            $duplicateQuery->where('buoiHocId', '!=', $ignoreBuoiHocId);
        }

        if ($duplicateQuery->exists()) {
            $validator->errors()->add('ngayHoc', 'Lớp đã có buổi học ở ngày này.');
        }
    }
}
