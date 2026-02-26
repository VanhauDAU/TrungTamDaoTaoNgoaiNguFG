<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Http\Controllers\Controller;
use App\Models\Education\BuoiHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\CaHoc;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BuoiHocController extends Controller
{
    /** Ánh xạ thứ trong tuần → số (PHP: 0=CN, 1=T2 ... 6=T7) */
    const THU_MAP = [
        '2' => Carbon::MONDAY,
        '3' => Carbon::TUESDAY,
        '4' => Carbon::WEDNESDAY,
        '5' => Carbon::THURSDAY,
        '6' => Carbon::FRIDAY,
        '7' => Carbon::SATURDAY,
        'CN'=> Carbon::SUNDAY,
    ];

    /** Thêm buổi học thủ công */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lopHocId'    => 'required|exists:lophoc,lopHocId',
            'tenBuoiHoc'  => 'nullable|string|max:255',
            'ngayHoc'     => 'required|date',
            'caHocId'     => 'required|exists:cahoc,caHocId',
            'phongHocId'  => 'nullable|exists:phonghoc,phongHocId',
            'taiKhoanId'  => 'nullable|exists:taikhoan,taiKhoanId',
            'ghiChu'      => 'nullable|string',
            'trangThai'   => 'nullable|in:0,1,2',
        ], [
            'lopHocId.required'   => 'Vui lòng chọn lớp học.',
            'ngayHoc.required'    => 'Vui lòng chọn ngày học.',
            'caHocId.required'    => 'Vui lòng chọn ca học.',
        ]);

        $data['daHoanThanh'] = 0;
        $data['daDiemDanh']  = 0;
        $data['trangThai']   = $data['trangThai'] ?? 0;

        if (empty($data['tenBuoiHoc'])) {
            $lopHoc = LopHoc::find($data['lopHocId']);
            $soBuoi = BuoiHoc::where('lopHocId', $data['lopHocId'])->count() + 1;
            $data['tenBuoiHoc'] = "Buổi {$soBuoi}: {$lopHoc->tenLopHoc}";
        }

        BuoiHoc::create($data);

        return redirect()->route('admin.lop-hoc.show', $request->lopHocId)
            ->with('success', 'Đã thêm buổi học thành công.');
    }

    /** Cập nhật buổi học */
    public function update(Request $request, int $id)
    {
        $buoiHoc = BuoiHoc::findOrFail($id);

        $data = $request->validate([
            'tenBuoiHoc'   => 'nullable|string|max:255',
            'ngayHoc'      => 'required|date',
            'caHocId'      => 'required|exists:cahoc,caHocId',
            'phongHocId'   => 'nullable|exists:phonghoc,phongHocId',
            'taiKhoanId'   => 'nullable|exists:taikhoan,taiKhoanId',
            'ghiChu'       => 'nullable|string',
            'daHoanThanh'  => 'nullable|in:0,1',
            'daDiemDanh'   => 'nullable|in:0,1',
            'trangThai'    => 'nullable|in:0,1,2',
        ]);

        $buoiHoc->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật buổi học.']);
        }

        return redirect()->route('admin.lop-hoc.show', $buoiHoc->lopHocId)
            ->with('success', 'Đã cập nhật buổi học thành công.');
    }

    /** Xóa buổi học */
    public function destroy(int $id)
    {
        $buoiHoc = BuoiHoc::findOrFail($id);
        $lopHocId = $buoiHoc->lopHocId;
        $buoiHoc->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.lop-hoc.show', $lopHocId)
            ->with('success', 'Đã xóa buổi học.');
    }

    /**
     * Tự động sinh buổi học từ lịch học của lớp.
     * lichHoc: "2,4,6" (Thứ 2, Thứ 4, Thứ 6)
     */
    public function autoGenerate(Request $request, int $lopHocId)
    {
        $lopHoc = LopHoc::with('caHoc')->findOrFail($lopHocId);

        if (empty($lopHoc->lichHoc) || empty($lopHoc->ngayBatDau) || empty($lopHoc->ngayKetThuc)) {
            return redirect()->route('admin.lop-hoc.show', $lopHocId)
                ->with('error', 'Lớp học chưa thiết lập đầy đủ lịch học, ngày bắt đầu / kết thúc.');
        }

        // Xóa buổi học cũ chưa hoàn thành
        $request->validate([
            'xoa_cu' => 'nullable|in:0,1',
        ]);

        if ($request->xoa_cu) {
            BuoiHoc::where('lopHocId', $lopHocId)->where('daHoanThanh', 0)->delete();
        }

        // Parse lịch học
        $thuList = array_map('trim', explode(',', $lopHoc->lichHoc));
        $thuDays = [];
        foreach ($thuList as $thu) {
            if (isset(self::THU_MAP[$thu])) {
                $thuDays[] = self::THU_MAP[$thu];
            }
        }

        if (empty($thuDays)) {
            return redirect()->route('admin.lop-hoc.show', $lopHocId)
                ->with('error', 'Lịch học không hợp lệ. Ví dụ: 2,4,6');
        }

        $start  = Carbon::parse($lopHoc->ngayBatDau);
        $end    = Carbon::parse($lopHoc->ngayKetThuc);
        $count  = 0;
        $soBuoi = BuoiHoc::where('lopHocId', $lopHocId)->count();

        $current = $start->copy();
        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, $thuDays)) {
                // Kiểm tra chưa tồn tại buổi học ngày đó
                $exists = BuoiHoc::where('lopHocId', $lopHocId)
                    ->whereDate('ngayHoc', $current->toDateString())
                    ->exists();

                if (!$exists) {
                    $soBuoi++;
                    BuoiHoc::create([
                        'lopHocId'    => $lopHocId,
                        'tenBuoiHoc'  => "Buổi {$soBuoi}: " . $lopHoc->tenLopHoc,
                        'ngayHoc'     => $current->toDateString(),
                        'caHocId'     => $lopHoc->caHocId,
                        'phongHocId'  => $lopHoc->phongHocId,
                        'taiKhoanId'  => $lopHoc->taiKhoanId,
                        'daHoanThanh' => 0,
                        'daDiemDanh'  => 0,
                        'trangThai'   => 0,
                    ]);
                    $count++;
                }
            }
            $current->addDay();
        }

        // Cập nhật số buổi dự kiến
        if ($lopHoc->soBuoiDuKien != $soBuoi) {
            $lopHoc->update(['soBuoiDuKien' => $soBuoi]);
        }

        return redirect()->route('admin.lop-hoc.show', $lopHocId)
            ->with('success', "Đã tự động tạo {$count} buổi học thành công.");
    }
}
