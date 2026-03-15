<?php

namespace App\Services\Client\HocVien;

use App\Contracts\Client\HocVien\StudentServiceInterface;

use App\Models\Auth\TaiKhoan;
use App\Models\Education\BuoiHoc;
use App\Models\Education\CaHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\LopHoc;
use App\Models\Finance\HoaDon;
use App\Models\Finance\PhieuThu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService implements StudentServiceInterface
{
    public function updateProfile(Request $request, TaiKhoan $user): void
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
            'nguoiGiamHo.max' => 'Tên người giám hộ không quá 100 ký tự.',
        ]);

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
    }

    public function updateAvatar(Request $request, TaiKhoan $user): void
    {
        $request->validate([
            'anhDaiDien' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'anhDaiDien.required' => 'Vui lòng chọn ảnh.',
            'anhDaiDien.image' => 'File phải là ảnh.',
            'anhDaiDien.mimes' => 'Chỉ chấp nhận JPG, PNG, GIF hoặc WebP.',
            'anhDaiDien.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        $hoSo = $user->hoSoNguoiDung;
        if ($hoSo && $hoSo->anhDaiDien && Storage::disk('public')->exists($hoSo->anhDaiDien)) {
            Storage::disk('public')->delete($hoSo->anhDaiDien);
        }

        $path = $request->file('anhDaiDien')->store('anh-dai-dien', 'public');

        $user->hoSoNguoiDung()->updateOrCreate(
        ['taiKhoanId' => $user->taiKhoanId],
        ['anhDaiDien' => $path]
        );
    }

    public function updatePassword(Request $request, TaiKhoan $user): void
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

        if (!Hash::check($request->current_password, $user->matKhau)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        $user->update(['matKhau' => Hash::make($request->new_password)]);
        $user->rotateRememberToken('password_changed', (string)$request->session()->getId());
    }

    public function getInvoices(TaiKhoan $user): array
    {
        return $this->getTuitionDebtLookup($user);
    }

    public function getTuitionDebtLookup(TaiKhoan $user): array
    {
        $baseQuery = $this->invoiceQueryForUser($user);
        $summary = $this->buildInvoiceSummary((clone $baseQuery)->get());

        return [
            'debts' => $baseQuery->orderByDesc('ngayLap')->paginate(10),
            'summary' => $summary,
        ];
    }

    public function getReceiptSummary(TaiKhoan $user): array
    {
        $receiptQuery = PhieuThu::where('taiKhoanId', $user->taiKhoanId)
            ->where('trangThai', PhieuThu::TRANG_THAI_HOP_LE)
            ->with([
                'hoaDon.dangKyLopHoc.lopHoc.khoaHoc',
                'hoaDon.dangKyLopHocPhuPhi',
                'hoaDon.coSo',
                'nguoiDuyet.hoSoNguoiDung',
            ]);

        $receipts = $receiptQuery->orderByDesc('ngayThu')->paginate(12);
        $receiptCollection = (clone $receiptQuery)->get();

        return [
            'receipts' => $receipts,
            'summary' => [
                'count' => $receiptCollection->count(),
                'totalCollected' => (float) $receiptCollection->sum(fn(PhieuThu $receipt) => (float) $receipt->soTien),
                'bankTransferCount' => $receiptCollection->where('phuongThucThanhToan', 2)->count(),
                'onlineCount' => $receiptCollection->where('phuongThucThanhToan', 3)->count(),
            ],
        ];
    }

    public function getOnlinePayments(TaiKhoan $user): array
    {
        $paymentQuery = $this->invoiceQueryForUser($user)
            ->whereIn('trangThai', [HoaDon::TRANG_THAI_CHUA_TT, HoaDon::TRANG_THAI_MOT_PHAN]);

        $pendingInvoices = $paymentQuery->orderByRaw('CASE WHEN ngayHetHan IS NULL THEN 1 ELSE 0 END')
            ->orderBy('ngayHetHan')
            ->orderByDesc('ngayLap')
            ->paginate(10);

        $pendingCollection = (clone $paymentQuery)->get();

        return [
            'payments' => $pendingInvoices,
            'summary' => [
                'count' => $pendingCollection->count(),
                'outstandingTotal' => (float) $pendingCollection->sum(function (HoaDon $invoice) {
                    return $this->calculateOutstanding($invoice);
                }),
                'overdueCount' => $pendingCollection->filter(fn(HoaDon $invoice) => $invoice->isQuaHan)->count(),
                'dueSoonCount' => $pendingCollection->filter(fn(HoaDon $invoice) => $invoice->isSapHetHan)->count(),
            ],
        ];
    }

    public function getInvoiceDetail(TaiKhoan $user, int $id): array
    {
        return [
            'invoice' => $this->invoiceQueryForUser($user)
                ->where('hoaDonId', $id)
                ->with(['coSo.tinhThanh', 'phieuThus.nguoiDuyet.hoSoNguoiDung'])
                ->firstOrFail(),
        ];
    }

    public function getMyClasses(TaiKhoan $user): array
    {
        return [
            'classes' => DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->visibleToStudent()
            ->with(['lopHoc.khoaHoc', 'lopHoc.coSo', 'lopHoc.taiKhoan.hoSoNguoiDung', 'lopHoc.buoiHocs.caHoc'])
            ->orderBy('ngayDangKy', 'desc')->get(),
        ];
    }

    public function getSchedule(Request $request, TaiKhoan $user): array
    {
        $baseDate = $request->get('tuan') ?Carbon::parse($request->get('tuan')) : Carbon::now();
        $startOfWeek = $baseDate->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $baseDate->copy()->endOfWeek(Carbon::SUNDAY);

        $lopHocIds = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->eligibleForSchedule()
            ->whereHas('lopHoc', fn($q) => $q->where('trangThai', LopHoc::TRANG_THAI_DANG_HOC))
            ->pluck('lopHocId');

        $buoiHocs = BuoiHoc::whereIn('lopHocId', $lopHocIds)
            ->whereBetween('ngayHoc', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->with(['caHoc', 'phongHoc', 'lopHoc.khoaHoc', 'lopHoc.taiKhoan.hoSoNguoiDung', 'lopHoc.coSo'])
            ->orderBy('ngayHoc')->orderBy('caHocId')->get();

        $caHocs = CaHoc::where('trangThai', 1)->orderBy('gioBatDau')->get();
        $schedule = [];
        foreach ($buoiHocs as $buoi) {
            $ngay = Carbon::parse($buoi->ngayHoc);
            $thu = $ngay->dayOfWeek === 0 ? 8 : $ngay->dayOfWeek + 1;
            $schedule[$thu][$buoi->caHocId][] = $buoi;
        }

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $thu = $i === 6 ? 8 : $i + 2;
            $weekDays[] = ['date' => $day, 'thu' => $thu, 'label' => $i === 6 ? 'Chủ nhật' : 'Thứ ' . ($i + 2)];
        }

        return compact('schedule', 'caHocs', 'weekDays', 'startOfWeek', 'endOfWeek', 'baseDate');
    }

    private function invoiceQueryForUser(TaiKhoan $user): Builder
    {
        return HoaDon::where('taiKhoanId', $user->taiKhoanId)
            ->where(function (Builder $query) {
                $query->whereNull('dangKyLopHocId')
                    ->orWhereHas('dangKyLopHoc', function (Builder $registrationQuery) {
                        $registrationQuery->where('trangThai', '!=', DangKyLopHoc::TRANG_THAI_HUY);
                    })
                    ->orWhere('daTra', '>', 0);
            })
            ->with(['dangKyLopHoc.lopHoc.khoaHoc', 'dangKyLopHocPhuPhi', 'coSo']);
    }

    private function buildInvoiceSummary(Collection $invoices): array
    {
        return [
            'count' => $invoices->count(),
            'outstandingCount' => $invoices->where('trangThai', '!=', HoaDon::TRANG_THAI_DA_TT)->count(),
            'outstandingTotal' => (float) $invoices->sum(function (HoaDon $invoice) {
                return $this->calculateOutstanding($invoice);
            }),
            'paidTotal' => (float) $invoices->sum(fn(HoaDon $invoice) => (float) $invoice->daTra),
        ];
    }

    private function calculateOutstanding(HoaDon $invoice): float
    {
        $net = (float) $invoice->tongTien - (float) ($invoice->giamGia ?? 0);

        return max(0, $net - (float) $invoice->daTra);
    }
}
