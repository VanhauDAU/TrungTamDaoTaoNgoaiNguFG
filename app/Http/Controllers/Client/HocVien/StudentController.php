<?php

namespace App\Http\Controllers\Client\HocVien;

use App\Contracts\Client\HocVien\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Auth\DeviceSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class StudentController extends Controller
{
    public function __construct(
        protected StudentServiceInterface $studentService
        )
    {
    }

    /** @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View */
    public function index()
    {
        return view('clients.hoc-vien.profile.index');
    }

    public function updateProfile(Request $request)
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        $this->studentService->updateProfile($request, $user);
        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function updateAvatar(Request $request)
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        $upload = $this->studentService->updateAvatar($request, $user);
        $user->refresh()->load('hoSoNguoiDung');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cập nhật ảnh đại diện thành công!',
                'avatarUrl' => $user->getAvatarUrl(),
                'file' => $upload,
            ]);
        }

        return back()->with('success_avatar', 'Cập nhật ảnh đại diện thành công!');
    }

    public function changePassword()
    {
        return view('clients.hoc-vien.change-password');
    }

    public function devices(Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.devices.index', [
            'devices' => $deviceSessionService->activeSessionsForUser($user, $request),
        ]);
    }

    public function sendPasswordSetupLink(Request $request)
    {
        $user = $request->user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        if (!is_string($user->email) || $user->email === '') {
            return back()->withErrors(['password_setup' => 'Tài khoản của bạn chưa có email để nhận liên kết thiết lập mật khẩu.']);
        }
        $status = Password::broker()->sendResetLink(['email' => $user->email]);
        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Đã gửi email thiết lập mật khẩu. Vui lòng kiểm tra hộp thư của bạn.')
            : back()->withErrors(['password_setup' => match ($status) {
                Password::INVALID_USER => 'Không tìm thấy tài khoản phù hợp.',
                Password::RESET_THROTTLED => 'Bạn vừa yêu cầu quá nhanh. Vui lòng đợi ít phút rồi thử lại.',
                default => 'Không thể gửi email lúc này. Vui lòng thử lại sau.',
            }]);
    }

    public function updatePassword(Request $request)
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        try {
            $this->studentService->updatePassword($request, $user);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function revokeDeviceSession(string $sessionId, Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        $deviceSessionService->revokeSessionById($user, $sessionId, $sessionId === (string)$request->session()->getId() ? 'logout_current' : 'manual_revoke', $request);
        if ($sessionId === (string)$request->session()->getId()) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Đã đăng xuất thiết bị hiện tại.');
        }
        $user->rotateRememberToken('device_revoke', (string)$request->session()->getId());
        return back()->with('success', 'Đã đăng xuất thiết bị đã chọn.');
    }

    public function logoutAllDevices(Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        $deviceSessionService->revokeAllSessions($user, $request, 'logout_all_devices');
        $user->rotateRememberToken('logout_all_devices', (string)$request->session()->getId());
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi tất cả thiết bị.');
    }

    public function invoices()
    {
        return redirect()->route('home.student.tuition.debts');
    }

    public function tuitionIndex()
    {
        return redirect()->route('home.student.tuition.debts');
    }

    public function tuitionDebts()
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.tuition.debts', $this->studentService->getTuitionDebtLookup($user));
    }

    public function tuitionReceipts()
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.tuition.receipts', $this->studentService->getReceiptSummary($user));
    }

    public function tuitionPayments()
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.tuition.payments', $this->studentService->getOnlinePayments($user));
    }

    public function invoiceDetail(int $id)
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.invoices.show', $this->studentService->getInvoiceDetail($user, $id));
    }

    public function myClasses()
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.classes.index', $this->studentService->getMyClasses($user));
    }

    public function schedule(Request $request)
    {
        /** @var TaiKhoan $user */
        $user = Auth::user();
        if (!$user instanceof TaiKhoan)
            abort(403);
        return view('clients.hoc-vien.lich-hoc.index', $this->studentService->getSchedule($request, $user));
    }
}
