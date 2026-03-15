<?php

namespace App\Http\Controllers\Admin\HocVien;

use App\Contracts\Admin\HocVien\DangKyHocServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DangKyHocController extends Controller
{
    public function __construct(
        protected DangKyHocServiceInterface $dangKyHocService
    ) {
        $this->middleware('permission:dang_ky,xem')->only('index', 'create');
        $this->middleware('permission:dang_ky,them')->only('store');
        $this->middleware('permission:dang_ky,sua')->only('confirm', 'hold', 'restore', 'transfer');
        $this->middleware('permission:dang_ky,xoa')->only('cancel');
    }

    public function index(Request $request)
    {
        return view('admin.dang-ky.index', $this->dangKyHocService->getList($request));
    }

    public function create()
    {
        return view('admin.dang-ky.create', $this->dangKyHocService->getCreateFormData());
    }

    public function store(Request $request)
    {
        $registration = $this->dangKyHocService->store($request);

        return redirect()
            ->route('admin.dang-ky.index')
            ->with('success', 'Đã tạo đăng ký cho học viên «' . ($registration->taiKhoan?->hoSoNguoiDung?->hoTen ?? $registration->taiKhoan?->taiKhoan ?? '—') . '».');
    }

    public function confirm(int $id)
    {
        $this->dangKyHocService->confirm($id);

        return redirect()
            ->route('admin.dang-ky.index')
            ->with('success', 'Đã xác nhận đăng ký học.');
    }

    public function cancel(int $id)
    {
        $this->dangKyHocService->cancel($id);

        return redirect()
            ->route('admin.dang-ky.index')
            ->with('success', 'Đã hủy đăng ký học.');
    }

    public function hold(int $id)
    {
        $this->dangKyHocService->hold($id);

        return redirect()
            ->route('admin.dang-ky.index')
            ->with('success', 'Đã chuyển đăng ký sang trạng thái bảo lưu.');
    }

    public function restore(int $id)
    {
        $this->dangKyHocService->restore($id);

        return redirect()
            ->route('admin.dang-ky.index')
            ->with('success', 'Đã khôi phục đăng ký học.');
    }

    public function transfer(Request $request, int $id)
    {
        try {
            $newRegistration = $this->dangKyHocService->transfer($request, $id);

            return redirect()
                ->route('admin.dang-ky.index')
                ->with('success', 'Đã điều chuyển học viên sang lớp «' . ($newRegistration->lopHoc?->tenLopHoc ?? '—') . '».');
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.dang-ky.index')
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first() ?: 'Không thể điều chuyển đăng ký.');
        }
    }
}
