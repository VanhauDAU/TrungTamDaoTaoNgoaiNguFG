<?php

namespace App\Http\Controllers\Admin\NhanVien;

use App\Http\Controllers\Controller;
use App\Models\Auth\NhanSuMauQuyDinh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NhanSuMauQuyDinhController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:nhan_su,xem')->only('index');
        $this->middleware('permission:nhan_su,them')->only('create', 'store');
        $this->middleware('permission:nhan_su,sua')->only('edit', 'update');
        $this->middleware('permission:nhan_su,xoa')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = NhanSuMauQuyDinh::query();

        if ($keyword = $request->q) {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('maMau', 'like', "%{$keyword}%")
                    ->orWhere('tieuDe', 'like', "%{$keyword}%");
            });
        }

        return view('admin.nhan-su.mau-quy-dinh.index', [
            'templates' => $query->orderByDesc('updated_at')->paginate(12)->withQueryString(),
            'phamViOptions' => NhanSuMauQuyDinh::phamViOptions(),
        ]);
    }

    public function create()
    {
        return view('admin.nhan-su.mau-quy-dinh.create', [
            'template' => new NhanSuMauQuyDinh(),
            'phamViOptions' => NhanSuMauQuyDinh::phamViOptions(),
            'loaiHopDongOptions' => $this->contractOptions(),
            'formMode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        NhanSuMauQuyDinh::create([
            ...$validated,
            'createdById' => auth()->id(),
            'updatedById' => auth()->id(),
        ]);

        return redirect()->route('admin.nhan-su.mau-quy-dinh.index')
            ->with('success', 'Đã tạo mẫu quy định nhân sự mới.');
    }

    public function edit(int $id)
    {
        return view('admin.nhan-su.mau-quy-dinh.edit', [
            'template' => NhanSuMauQuyDinh::findOrFail($id),
            'phamViOptions' => NhanSuMauQuyDinh::phamViOptions(),
            'loaiHopDongOptions' => $this->contractOptions(),
            'formMode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $template = NhanSuMauQuyDinh::findOrFail($id);
        $validated = $this->validateTemplate($request, $template);

        $template->update([
            ...$validated,
            'updatedById' => auth()->id(),
        ]);

        return redirect()->route('admin.nhan-su.mau-quy-dinh.index')
            ->with('success', 'Đã cập nhật mẫu quy định.');
    }

    public function destroy(int $id)
    {
        $template = NhanSuMauQuyDinh::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.nhan-su.mau-quy-dinh.index')
            ->with('success', 'Đã xóa mẫu quy định.');
    }

    private function validateTemplate(Request $request, ?NhanSuMauQuyDinh $template = null): array
    {
        return $request->validate([
            'maMau' => [
                'required',
                'string',
                'max:30',
                Rule::unique('nhansu_mau_quydinh', 'maMau')->ignore($template?->nhanSuMauQuyDinhId, 'nhanSuMauQuyDinhId'),
            ],
            'tieuDe' => ['required', 'string', 'max:150'],
            'phamViApDung' => ['required', Rule::in(array_keys(NhanSuMauQuyDinh::phamViOptions()))],
            'loaiHopDongApDung' => ['required', Rule::in(array_keys($this->contractOptions()))],
            'phienBan' => ['required', 'integer', 'min:1'],
            'trangThai' => ['required', Rule::in(['0', '1', 0, 1])],
            'noiDung' => ['required', 'string'],
        ], [
            'maMau.required' => 'Vui lòng nhập mã mẫu.',
            'maMau.unique' => 'Mã mẫu đã tồn tại.',
            'tieuDe.required' => 'Vui lòng nhập tiêu đề mẫu.',
            'phamViApDung.required' => 'Vui lòng chọn phạm vi áp dụng.',
            'loaiHopDongApDung.required' => 'Vui lòng chọn loại hợp đồng áp dụng.',
            'phienBan.required' => 'Vui lòng nhập phiên bản.',
            'phienBan.min' => 'Phiên bản phải lớn hơn hoặc bằng 1.',
            'noiDung.required' => 'Vui lòng nhập nội dung mẫu quy định.',
        ]);
    }

    private function contractOptions(): array
    {
        return [
            'ALL' => 'Tất cả',
            'FULL_TIME' => 'Toàn thời gian',
            'PART_TIME' => 'Bán thời gian',
            'PROBATION' => 'Thử việc',
            'VISITING' => 'Thỉnh giảng',
        ];
    }
}
