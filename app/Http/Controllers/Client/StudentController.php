<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Finance\HoaDon;
use App\Services\Auth\DeviceSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /** Trang thông tin cá nhân */
    public function index()
    {
        return view('clients.hoc-vien.profile.index');
    }

    /** Cập nhật thông tin cá nhân */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:15',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20',
            'nguoiGiamHo' => 'nullable|string|max:100',
            'sdtGuardian' => 'nullable|string|max:20',
            'moiQuanHe' => 'nullable|string|max:50',
            'trinhDoHienTai' => 'nullable|string|max:30',
            'ngonNguMucTieu' => 'nullable|string|max:50',
            'nguonBietDen' => 'nullable|string|max:50',
            'ghiChu' => 'nullable|string',
        ], [
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'hoTen.max' => 'Họ và tên không được quá 100 ký tự.',
            'soDienThoai.max' => 'Số điện thoại không được quá 15 ký tự.',
            'ngaySinh.date' => 'Ngày sinh không hợp lệ.',
            'gioiTinh.in' => 'Giới tính không hợp lệ.',
            'diaChi.max' => 'Địa chỉ không được quá 255 ký tự.',
            'cccd.max' => 'Số CCCD/CMND không được quá 20 ký tự.',
            'nguoiGiamHo.max' => 'Tên người giám hộ không quá 100 ký tự.',
        ]);

        $user = auth()->user();

        $user->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $user->taiKhoanId],
            [
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh !== '' ? $request->gioiTinh : null,
                'diaChi' => $request->diaChi,
                'cccd' => $request->cccd,
                'nguoiGiamHo' => $request->nguoiGiamHo,
                'sdtGuardian' => $request->sdtGuardian,
                'moiQuanHe' => $request->moiQuanHe,
                'trinhDoHienTai' => $request->trinhDoHienTai,
                'ngonNguMucTieu' => $request->ngonNguMucTieu,
                'nguonBietDen' => $request->nguonBietDen,
                'ghiChu' => $request->ghiChu,
            ]
        );

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    /** Cập nhật ảnh đại diện */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'anhDaiDien' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'anhDaiDien.required' => 'Vui lòng chọn ảnh.',
            'anhDaiDien.image' => 'File phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.',
            'anhDaiDien.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        $user = auth()->user();
        $hoSo = $user->hoSoNguoiDung;

        // Xóa ảnh cũ (nếu có)
        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        // Lưu ảnh mới (giống logic khóa học: DB lưu 'avatars/RandomName.jpg')
        $path = $request->file('anhDaiDien')->store('anh-dai-dien', 'public');

        $user->hoSoNguoiDung()->updateOrCreate(
            ['taiKhoanId' => $user->taiKhoanId],
            ['anhDaiDien' => $path]
        );

        return back()->with('success_avatar', 'Cập nhật ảnh đại diện thành công!');
    }

    public function changePassword()
    {
        return view('clients.hoc-vien.change-password');
    }

    public function devices(Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            abort(403);
        }

        $devices = $deviceSessionService->activeSessionsForUser($user, $request);

        return view('clients.hoc-vien.devices.index', compact('devices'));
    }

    public function sendPasswordSetupLink(Request $request)
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            abort(403);
        }

        if (!is_string($user->email) || $user->email === '') {
            return back()->withErrors([
                'password_setup' => 'Tài khoản của bạn chưa có email để nhận liên kết thiết lập mật khẩu.',
            ]);
        }

        $status = Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors([
                'password_setup' => $this->passwordSetupStatusMessage($status),
            ]);
        }

        return back()->with('success', 'Đã gửi email thiết lập mật khẩu. Vui lòng kiểm tra hộp thư của bạn.');
    }

    protected function passwordSetupStatusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Không tìm thấy tài khoản phù hợp để gửi email thiết lập mật khẩu.',
            Password::RESET_THROTTLED => 'Bạn vừa yêu cầu quá nhanh. Vui lòng đợi ít phút rồi thử lại.',
            default => 'Không thể gửi email thiết lập mật khẩu lúc này. Vui lòng thử lại sau.',
        };
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        $user = auth()->user();

        if (!$user instanceof TaiKhoan) {
            abort(403);
        }

        if (!Hash::check($request->current_password, $user->matKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        $user->update([
            'matKhau' => Hash::make($request->new_password)
        ]);
        $user->rotateRememberToken('password_changed', (string) $request->session()->getId());

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function revokeDeviceSession(string $sessionId, Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            abort(403);
        }

        $deviceSessionService->revokeSessionById(
            $user,
            $sessionId,
            $sessionId === (string) $request->session()->getId() ? 'logout_current' : 'manual_revoke',
            $request
        );

        if ($sessionId === (string) $request->session()->getId()) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'Đã đăng xuất thiết bị hiện tại.');
        }

        $user->rotateRememberToken('device_revoke', (string) $request->session()->getId());

        return back()->with('success', 'Đã đăng xuất thiết bị đã chọn. Các cookie ghi nhớ đăng nhập cũ cũng đã bị vô hiệu.');
    }

    public function logoutAllDevices(Request $request, DeviceSessionService $deviceSessionService)
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            abort(403);
        }

        $deviceSessionService->revokeAllSessions($user, $request, 'logout_all_devices');
        $user->rotateRememberToken('logout_all_devices', (string) $request->session()->getId());

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Đã đăng xuất khỏi tất cả thiết bị.');
    }

    public function invoices()
    {
        $invoices = HoaDon::where('taiKhoanId', auth()->user()->taiKhoanId)
            ->with(['dangKyLopHoc.lopHoc.khoaHoc', 'coSo'])
            ->orderBy('ngayLap', 'desc')
            ->paginate(10);

        return view('clients.hoc-vien.invoices.index', compact('invoices'));
    }

    public function invoiceDetail($id)
    {
        $invoice = HoaDon::where('hoaDonId', $id)
            ->where('taiKhoanId', auth()->user()->taiKhoanId)
            ->with([
                'dangKyLopHoc.lopHoc.khoaHoc',
                'coSo.tinhThanh',
                'phieuThus'
            ])
            ->firstOrFail();

        return view('clients.hoc-vien.invoices.show', compact('invoice'));
    }

    public function myClasses()
    {
        $classes = DangKyLopHoc::where('taiKhoanId', auth()->user()->taiKhoanId)
            ->visibleToStudent()
            ->with([
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lopHoc.buoiHocs.caHoc'
            ])
            ->orderBy('ngayDangKy', 'desc')
            ->get();

        return view('clients.hoc-vien.classes.index', compact('classes'));
    }

    /** Trang lịch học theo tuần */
    public function schedule(Request $request)
    {
        // Xác định tuần hiện tại hoặc tuần do user chọn
        $baseDate = $request->get('tuan')
            ? \Carbon\Carbon::parse($request->get('tuan'))
            : \Carbon\Carbon::now();

        $startOfWeek = $baseDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek   = $baseDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $userId = auth()->user()->taiKhoanId;

        $lopHocIds = DangKyLopHoc::where('taiKhoanId', $userId)
            ->eligibleForSchedule()
            ->whereHas('lopHoc', fn($query) => $query->where('trangThai', LopHoc::TRANG_THAI_DANG_HOC))
            ->pluck('lopHocId');

        // Lấy buổi học trong tuần được chọn
        $buoiHocs = \App\Models\Education\BuoiHoc::whereIn('lopHocId', $lopHocIds)
            ->whereBetween('ngayHoc', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->with([
                'caHoc',
                'phongHoc',
                'lopHoc.khoaHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lopHoc.coSo',
            ])
            ->orderBy('ngayHoc')
            ->orderBy('caHocId')
            ->get();

        // Lấy tất cả ca học đang hoạt động (trangThai = 1)
        $caHocs = \App\Models\Education\CaHoc::where('trangThai', 1)
            ->orderBy('gioBatDau')
            ->get();

        // Nhóm buổi học theo: thu (1=Thứ2,...,7=Chủ nhật) -> caHocId -> [buoiHoc,...]
        // Carbon dayOfWeek: 0=Sunday, 1=Monday,...,6=Saturday
        $schedule = [];
        foreach ($buoiHocs as $buoi) {
            $ngay    = \Carbon\Carbon::parse($buoi->ngayHoc);
            // Chuyển sang số thứ: Thứ2=2, Thứ3=3,..., CN=8
            $thu = $ngay->dayOfWeek === 0 ? 8 : $ngay->dayOfWeek + 1;
            $caId = $buoi->caHocId;
            if (!isset($schedule[$thu])) {
                $schedule[$thu] = [];
            }
            if (!isset($schedule[$thu][$caId])) {
                $schedule[$thu][$caId] = [];
            }
            $schedule[$thu][$caId][] = $buoi;
        }

        // Danh sách 7 ngày trong tuần (Thứ 2 → Chủ nhật)
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            // thu: Thứ2=2,...,CN=8
            $thu = $i === 6 ? 8 : $i + 2;
            $weekDays[] = [
                'date' => $day,
                'thu'  => $thu,
                'label' => $i === 6 ? 'Chủ nhật' : 'Thứ ' . ($i + 2),
            ];
        }

        return view('clients.hoc-vien.lich-hoc.index', compact(
            'schedule',
            'caHocs',
            'weekDays',
            'startOfWeek',
            'endOfWeek',
            'baseDate'
        ));
    }
}
