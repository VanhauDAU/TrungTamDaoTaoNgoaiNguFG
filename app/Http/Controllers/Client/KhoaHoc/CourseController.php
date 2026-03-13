<?php

namespace App\Http\Controllers\Client\KhoaHoc;

use App\Contracts\Client\KhoaHoc\CourseServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseServiceInterface $courseService
        )
    {
    }

    public function index(Request $request)
    {
        return view('clients.khoa-hoc.index', $this->courseService->getList($request));
    }

    public function show(string $slug)
    {
        return view('clients.khoa-hoc.show', $this->courseService->getDetail($slug));
    }

    public function showClass(string $slug, string $slugLopHoc)
    {
        return view('clients.lop-hoc.show', $this->courseService->getClassDetail($slug, $slugLopHoc));
    }

    public function confirmRegistration(Request $request, string $slug, string $slugLopHoc)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đăng ký khóa học.');
        }
        $user = auth()->user();
        if ($user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', compact('slug', 'slugLopHoc'))
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
        }
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi đăng ký lớp học.');
        }

        try {
            $data = $this->courseService->getConfirmRegistrationData($slug, $slugLopHoc);
        }
        catch (\RuntimeException $e) {
            return redirect()->route('home.classes.show', compact('slug', 'slugLopHoc'))
                ->with('error', $e->getMessage());
        }

        return view('clients.lop-hoc.checkout', $data);
    }

    public function processRegistration(Request $request, string $slug, string $slugLopHoc)
    {
        if (!auth()->check())
            return redirect()->route('login');

        $user = auth()->user();
        if ($user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', compact('slug', 'slugLopHoc'))
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
        }
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi đăng ký lớp học.');
        }

        try {
            $this->courseService->processRegistration($request, $slug, $slugLopHoc);
            return redirect()->route('home.classes.show', compact('slug', 'slugLopHoc'))
                ->with('success', 'Đăng ký thành công! Vui lòng hoàn tất thanh toán để giữ chỗ.');
        }
        catch (\RuntimeException $e) {
            return redirect()->route('home.classes.show', compact('slug', 'slugLopHoc'))
                ->with('error', $e->getMessage());
        }
        catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}