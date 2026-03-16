<?php

namespace App\Http\Controllers\Admin\KhoaHoc;

use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Education\LopHoc;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LopHocController extends Controller
{
    public function __construct(
        protected LopHocServiceInterface $lopHocService
    ) {}

    public function index(Request $request)
    {
        return view('admin.lop-hoc.index', $this->lopHocService->getList($request));
    }

    public function trash(Request $request)
    {
        return view('admin.lop-hoc.trash', $this->lopHocService->getTrashList($request));
    }

    public function create(Request $request)
    {
        return view('admin.lop-hoc.create', $this->lopHocService->getCreateFormData($request));
    }

    public function store(Request $request)
    {
        $lopHoc = $this->lopHocService->store($request);

        return redirect()->route('admin.lop-hoc.index')
            ->with('success', 'Đã thêm lớp học «' . $lopHoc->tenLopHoc . '» thành công.');
    }

    public function show(string $slug)
    {
        return view('admin.lop-hoc.show', $this->lopHocService->getDetail($slug));
    }

    public function edit(string $slug)
    {
        return view('admin.lop-hoc.edit', $this->lopHocService->getEditFormData($slug));
    }

    public function update(Request $request, string $slug)
    {
        $lopHoc = $this->lopHocService->update($request, $slug);

        return redirect()->route('admin.lop-hoc.show', $lopHoc->slug)
            ->with('success', 'Đã cập nhật lớp học «' . $lopHoc->tenLopHoc . '» thành công.');
    }

    public function updateStatus(Request $request, string $slug)
    {
        $payload = $request->validate([
            'trangThai' => 'required|integer',
        ]);

        try {
            $lopHoc = $this->lopHocService->updateStatus($slug, (int) $payload['trangThai']);
            $allowedTransitions = LopHoc::allowedStatusTransitions()[(int) $lopHoc->trangThai] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái lớp học.',
                'data' => [
                    'trangThai' => (int) $lopHoc->trangThai,
                    'trangThaiLabel' => $lopHoc->trangThaiLabel,
                    'allowedTransitions' => array_values($allowedTransitions),
                    'trangThaiOptions' => LopHoc::trangThaiOptions(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Không thể cập nhật trạng thái lớp học.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(string $slug)
    {
        try {
            $ten = $this->lopHocService->destroy($slug);
            return redirect()->route('admin.lop-hoc.index')
                ->with('success', "Đã chuyển lớp học «{$ten}» vào trạng thái xóa mềm.");
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.lop-hoc.index')
                ->with('error', $e->getMessage());
        }
    }

    public function restore(string $slug)
    {
        $lopHoc = $this->lopHocService->restore($slug);

        return redirect()->route('admin.lop-hoc.show', $lopHoc->slug)
            ->with('success', "Đã khôi phục lớp học «{$lopHoc->tenLopHoc}» thành công.");
    }

    public function getPhongByCoso(int $coSoId)
    {
        return response()->json($this->lopHocService->getPhongByCoso($coSoId));
    }

    public function getGiaoVienByCoso(int $coSoId)
    {
        return response()->json($this->lopHocService->getGiaoVienByCoso($coSoId));
    }

    public function previewSchedulingConflicts(Request $request)
    {
        return response()->json($this->lopHocService->previewSchedulingConflicts($request));
    }
}
