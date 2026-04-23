<?php

namespace App\Http\Controllers\Staff\KhoaHoc;

use App\Contracts\Admin\HocVien\DangKyHocServiceInterface;
use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Http\Controllers\Admin\KhoaHoc\LopHocController as AdminLopHocController;
use App\Models\Education\LopHoc;
use App\Services\Staff\KhoaHoc\ClassEnrollmentDocumentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LopHocController extends AdminLopHocController
{
    public function __construct(
        LopHocServiceInterface $lopHocService,
        protected DangKyHocServiceInterface $dangKyHocService,
        protected ClassEnrollmentDocumentService $classEnrollmentDocumentService
    ) {
        parent::__construct($lopHocService);
    }

    protected function viewPrefix(): string
    {
        return 'staff.lop-hoc';
    }

    public function searchStudents(Request $request, string $slug)
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();
        $students = $this->dangKyHocService->searchEligibleStudentsForClass(
            (int) $lopHoc->lopHocId,
            $request->query('q'),
            (int) $request->query('limit', 12)
        );

        return response()->json([
            'success' => true,
            'students' => $students,
        ]);
    }

    public function quickAddStudents(Request $request, string $slug)
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();
        $payload = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:taikhoan,taiKhoanId',
            'payment_method' => 'required|in:1,2,3',
        ], [
            'student_ids.required' => 'Vui lòng chọn ít nhất một học viên.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        try {
            $result = $this->dangKyHocService->quickAddStudentsToClass(
                (int) $lopHoc->lopHocId,
                $payload['student_ids'],
                (int) $payload['payment_method'],
                'Thêm nhanh từ lớp ' . $lopHoc->tenLopHoc
            );

            $createdCount = $result['created']->count();
            $errorCount = $result['errors']->count();
            $message = $createdCount > 0
                ? "Đã thêm {$createdCount} học viên vào lớp."
                : 'Không có học viên nào được thêm do dữ liệu chưa hợp lệ.';

            if ($errorCount > 0) {
                $message .= " Có {$errorCount} trường hợp bị bỏ qua.";
            }

            return redirect()
                ->route('staff.lop-hoc.show', $lopHoc->slug)
                ->with($createdCount > 0 ? 'success' : 'warning', $message)
                ->with('registrationErrors', $result['errors']->all());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('staff.lop-hoc.show', $lopHoc->slug)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first() ?: 'Không thể thêm học viên vào lớp.');
        }
    }

    public function promoteStudents(Request $request, string $slug)
    {
        $sourceClass = LopHoc::where('slug', $slug)->firstOrFail();
        $payload = $request->validate([
            'target_lop_hoc_id' => 'required|integer|exists:lophoc,lopHocId',
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'integer|exists:dangKyLopHoc,dangKyLopHocId',
            'payment_method' => 'required|in:1,2,3',
        ], [
            'target_lop_hoc_id.required' => 'Vui lòng chọn lớp đích.',
            'registration_ids.required' => 'Vui lòng chọn ít nhất một học viên để lên lớp tiếp theo.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        try {
            $result = $this->dangKyHocService->promoteStudentsToNextClass(
                (int) $sourceClass->lopHocId,
                (int) $payload['target_lop_hoc_id'],
                $payload['registration_ids'],
                (int) $payload['payment_method']
            );

            $createdCount = $result['created']->count();
            $errorCount = $result['errors']->count();
            $message = $createdCount > 0
                ? "Đã tạo {$createdCount} đăng ký cho lớp tiếp theo."
                : 'Chưa thể tạo đăng ký lớp tiếp theo cho học viên nào.';

            if ($errorCount > 0) {
                $message .= " Có {$errorCount} trường hợp cần xử lý thủ công.";
            }

            return redirect()
                ->route('staff.lop-hoc.show', $sourceClass->slug)
                ->with($createdCount > 0 ? 'success' : 'warning', $message)
                ->with('promotionErrors', $result['errors']->all());
        } catch (ValidationException $exception) {
            return redirect()
                ->route('staff.lop-hoc.show', $sourceClass->slug)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first() ?: 'Không thể tạo đăng ký lớp tiếp theo.');
        }
    }

    public function createStudentAndEnroll(Request $request, string $slug)
    {
        $lopHoc = LopHoc::where('slug', $slug)->firstOrFail();
        $payload = $request->validate([
            'hoTen' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'soDienThoai' => 'nullable|string|max:20',
            'cccd' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'nguoiGiamHo' => 'nullable|string|max:100',
            'sdtGuardian' => 'nullable|string|max:20',
            'moiQuanHe' => 'nullable|string|max:50',
            'trinhDoHienTai' => 'nullable|string|max:30',
            'ngonNguMucTieu' => 'nullable|string|max:50',
            'ghiChu' => 'nullable|string',
            'payment_method' => 'required|in:1,2,3',
        ], [
            'hoTen.required' => 'Vui lòng nhập họ và tên học viên.',
            'email.required' => 'Vui lòng nhập email học viên.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        try {
            $result = $this->dangKyHocService->createStudentAndEnrollInClass(
                (int) $lopHoc->lopHocId,
                $payload,
                (int) $payload['payment_method']
            );

            return $this->classEnrollmentDocumentService->streamEnrollmentContract(
                $result['registration'],
                $result['student'],
                $result['temporaryPassword']
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->route('staff.lop-hoc.show', $lopHoc->slug)
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first() ?: 'Không thể tạo học viên và ghi danh vào lớp.');
        }
    }
}
