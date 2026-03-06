<?php

namespace App\Http\Controllers\Admin\ThongBao;

use App\Http\Controllers\Controller;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoLichSu;
use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Interaction\ThongBaoTepDinh;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Course\KhoaHoc;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ThongBaoController extends Controller
{
    public function __construct(private ThongBaoService $service) {}

    // ── INDEX ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ThongBao::with('nguoiGui.hoSoNguoiDung', 'nguoiGui.nhanSu')
            ->withCount(['nguoiNhans', 'nguoiNhans as da_doc_count' => fn($q) => $q->where('daDoc', true)]);

        // Tìm kiếm
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('tieuDe', 'like', "%{$q}%")
                    ->orWhere('noiDung', 'like', "%{$q}%");
            });
        }

        // Filter loại
        if ($request->filled('loaiGui')) {
            $query->where('loaiGui', $request->loaiGui);
        }

        // Filter đối tượng
        if ($request->filled('doiTuongGui')) {
            $query->where('doiTuongGui', $request->doiTuongGui);
        }

        // Filter ưu tiên
        if ($request->filled('uuTien')) {
            $query->where('uuTien', $request->uuTien);
        }

        // Filter ghim
        if ($request->filled('ghim')) {
            $query->where('ghim', (bool) $request->ghim);
        }

        // Filter trạng thái gửi
        if ($request->filled('sendTrangThai')) {
            $query->where('sendTrangThai', $request->sendTrangThai);
        }

        // Filter khoảng ngày tạo/gửi
        if ($request->filled('tu_ngay')) {
            $query->whereDate('created_at', '>=', $request->tu_ngay);
        }
        if ($request->filled('den_ngay')) {
            $query->whereDate('created_at', '<=', $request->den_ngay);
        }

        // Sắp xếp: ghim lên trên, rồi mới nhất
        $query->orderByDesc('ghim')->orderByDesc('created_at');

        $thongBaos = $query->paginate(15)->withQueryString();

        // Stats (chỉ đếm bản chưa soft-delete)
        $stats = [
            'tong'        => ThongBao::count(),
            'hom_nay'     => ThongBao::whereDate('created_at', today())->count(),
            'chua_doc'    => ThongBaoNguoiDung::where('daDoc', false)->count(),
            'ghim'        => ThongBao::where('ghim', true)->count(),
            'nhap'        => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_NHAP)->count(),
            'da_len_lich' => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_LEN_LICH)->count(),
            'da_gui'      => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_GUI)->count(),
            'gui_loi'     => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_GUI_LOI)->count(),
            'da_xoa'      => ThongBao::onlyTrashed()->count(),
        ];

        return view('admin.thong-bao.index', compact('thongBaos', 'stats'));
    }

    // ── TRASH ─────────────────────────────────────────────
    public function trash(Request $request)
    {
        $query = ThongBao::onlyTrashed()
            ->with('nguoiGui.hoSoNguoiDung', 'nguoiGui.nhanSu');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('tieuDe', 'like', "%{$q}%")
                    ->orWhere('noiDung', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('deleted_at');
        $thongBaos = $query->paginate(15)->withQueryString();
        $soLuong = ThongBao::onlyTrashed()->count();

        return view('admin.thong-bao.trash', compact('thongBaos', 'soLuong'));
    }

    // ── RESTORE ────────────────────────────────────────────
    public function restore(string $id)
    {
        $thongBao = ThongBao::onlyTrashed()->findOrFail($id);
        $thongBao->restore();
        $this->ghiLichSu($thongBao->thongBaoId, 'restored', 'Khôi phục thông báo từ thùng rác.');

        return redirect()
            ->route('admin.thong-bao.trash')
            ->with('success', 'Đã khôi phục thông báo thành công.');
    }

    // ── FORCE DESTROY ──────────────────────────────────────
    public function forceDestroy(string $id)
    {
        $thongBao = ThongBao::onlyTrashed()->with('tepDinhs')->findOrFail($id);

        // Xóa file vật lý
        foreach ($thongBao->tepDinhs as $tep) {
            Storage::disk('public')->delete($tep->duongDan);
        }

        $thongBao->tepDinhs()->forceDelete();
        $thongBao->nguoiNhans()->delete();
        $thongBao->forceDelete();
        $this->ghiLichSu(null, 'force_deleted', "Xóa vĩnh viễn thông báo: {$thongBao->tieuDe}");

        return redirect()
            ->route('admin.thong-bao.trash')
            ->with('success', 'Đã xóa vĩnh viễn thông báo.');
    }

    // ── BULK RESTORE ───────────────────────────────────────
    public function bulkRestore(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        ThongBao::onlyTrashed()->whereIn('thongBaoId', $ids)->restore();
        $this->ghiLichSu(null, 'bulk_restored', 'Khôi phục nhiều thông báo từ thùng rác.', ['ids' => $ids]);

        return response()->json(['success' => true, 'message' => 'Đã khôi phục ' . count($ids) . ' thông báo.']);
    }

    // ── CREATE ─────────────────────────────────────────────
    public function create()
    {
        $lopHocs   = LopHoc::select('lopHocId', 'tenLopHoc', 'khoaHocId')->orderBy('tenLopHoc')->get();
        $khoaHocs  = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $taiKhoans = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)
            ->orderBy('taiKhoanId')
            ->get();

        return view('admin.thong-bao.create', compact('lopHocs', 'khoaHocs', 'taiKhoans'));
    }

    // ── STORE ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieuDe'        => 'required|string|max:255',
            'noiDung'       => 'required|string',
            'loaiGui'       => 'required|integer|between:0,4',
            'doiTuongGui'   => 'required|integer|between:0,5',
            'doiTuongId'    => 'nullable|integer',
            'uuTien'        => 'required|integer|between:0,2',
            'ghim'          => 'nullable|boolean',
            'hanhDong'      => 'nullable|in:send,draft,schedule',
            'scheduled_at'  => 'nullable|date|after:now',
            'tepDinhs'      => 'nullable|array|max:5',
            'tepDinhs.*'    => 'file|max:10240',
        ]);

        $hanhDong = $validated['hanhDong'] ?? 'send';
        $isDraft    = $hanhDong === 'draft';
        $isSchedule = $hanhDong === 'schedule';

        // Validate: nếu chọn lên lịch thì phải có scheduled_at
        if ($isSchedule && empty($validated['scheduled_at'])) {
            return back()->withErrors(['scheduled_at' => 'Vui lòng chọn thời gian gửi.'])->withInput();
        }

        $sendTrangThai = match(true) {
            $isDraft    => ThongBao::SEND_TRANG_THAI_NHAP,
            $isSchedule => ThongBao::SEND_TRANG_THAI_DA_LEN_LICH,
            default     => ThongBao::SEND_TRANG_THAI_DA_GUI,
        };

        $tb = ThongBao::create([
            'tieuDe'        => $validated['tieuDe'],
            'noiDung'       => $validated['noiDung'],
            'loaiGui'       => $validated['loaiGui'],
            'doiTuongGui'   => $validated['doiTuongGui'],
            'doiTuongId'    => $validated['doiTuongId'] ?? null,
            'uuTien'        => $validated['uuTien'],
            'nguoiGuiId'    => Auth::id(),
            'ngayGui'       => ($isDraft || $isSchedule) ? null : Carbon::now(),
            'trangThai'     => 1,
            'ghim'          => $request->boolean('ghim'),
            'sendTrangThai' => $sendTrangThai,
            'scheduled_at'  => $isSchedule ? Carbon::parse($validated['scheduled_at']) : null,
            'sent_at'       => (!$isDraft && !$isSchedule) ? Carbon::now() : null,
        ]);

        // Lưu file đính kèm
        $this->luuTepDinh($tb->thongBaoId, $request);

        if ($isDraft) {
            $this->ghiLichSu($tb->thongBaoId, 'draft_created', 'Tạo thông báo nháp.');
            return redirect()
                ->route('admin.thong-bao.edit', $tb->thongBaoId)
                ->with('success', 'Đã lưu thông báo ở trạng thái nháp.');
        }

        if ($isSchedule) {
            $this->ghiLichSu($tb->thongBaoId, 'scheduled', 'Đã lên lịch gửi thông báo vào ' . Carbon::parse($validated['scheduled_at'])->format('d/m/Y H:i') . '.');
            return redirect()
                ->route('admin.thong-bao.show', $tb->thongBaoId)
                ->with('success', 'Đã lên lịch gửi thông báo vào ' . Carbon::parse($validated['scheduled_at'])->format('d/m/Y H:i') . '.');
        }

        // Gửi ngay
        $soNguoiNhan = $this->guiThongBaoVaCapNhatTrangThai($tb);

        if ($soNguoiNhan === 0) {
            $this->ghiLichSu($tb->thongBaoId, 'send_failed', 'Gửi thông báo thất bại do không có người nhận phù hợp.');
            return redirect()
                ->route('admin.thong-bao.edit', $tb->thongBaoId)
                ->with('error', 'Không tìm thấy người nhận phù hợp. Thông báo đã được lưu ở trạng thái gửi lỗi.');
        }

        $this->ghiLichSu($tb->thongBaoId, 'sent', "Đã gửi thông báo đến {$soNguoiNhan} người nhận.");
        return redirect()
            ->route('admin.thong-bao.show', $tb->thongBaoId)
            ->with('success', "Đã gửi thông báo thành công đến {$soNguoiNhan} người nhận.");
    }

    /** Lưu các file đính kèm upload vào storage và DB */
    private function luuTepDinh(int $thongBaoId, Request $request): void
    {
        if (!$request->hasFile('tepDinhs')) return;

        foreach ($request->file('tepDinhs') as $file) {
            if (!$file->isValid()) continue;

            $ext        = $file->getClientOriginalExtension();
            $tenFileLuu = Str::uuid() . ($ext ? '.' . $ext : '');
            $duongDan   = $file->storeAs('thongbao/tepdinh', $tenFileLuu, 'public');

            ThongBaoTepDinh::create([
                'thongBaoId' => $thongBaoId,
                'tenFile'    => $file->getClientOriginalName(),
                'tenFileLuu' => $tenFileLuu,
                'duongDan'   => $duongDan,
                'loaiFile'   => $file->getMimeType(),
                'kichThuoc'  => $file->getSize(),
            ]);
        }
    }

    // ── SHOW ───────────────────────────────────────────────
    public function show(string $id)
    {
        $thongBao = ThongBao::with([
            'nguoiGui.hoSoNguoiDung',
            'nguoiGui.nhanSu',
            'nguoiNhans.nguoiDung.hoSoNguoiDung',
            'nguoiNhans.nguoiDung.nhanSu',
            'tepDinhs',
        ])->findOrFail($id);

        $tongNguoiNhan = $thongBao->nguoiNhans->count();
        $daDocs        = $thongBao->nguoiNhans->where('daDoc', true)->count();
        $chuaDocs      = $tongNguoiNhan - $daDocs;
        $tiLe          = $tongNguoiNhan > 0 ? round($daDocs / $tongNguoiNhan * 100, 1) : 0;

        return view('admin.thong-bao.show', compact(
            'thongBao', 'tongNguoiNhan', 'daDocs', 'chuaDocs', 'tiLe'
        ));
    }

    // ── EDIT ───────────────────────────────────────────────
    public function edit(string $id)
    {
        $thongBao  = ThongBao::with('tepDinhs')->findOrFail($id);
        $lopHocs   = LopHoc::select('lopHocId', 'tenLopHoc')->orderBy('tenLopHoc')->get();
        $khoaHocs  = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $taiKhoans = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)->orderBy('taiKhoanId')->get();

        return view('admin.thong-bao.edit', compact('thongBao', 'lopHocs', 'khoaHocs', 'taiKhoans'));
    }

    // ── UPDATE ─────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $thongBao = ThongBao::findOrFail($id);

        $validated = $request->validate([
            'tieuDe'        => 'required|string|max:255',
            'noiDung'       => 'required|string',
            'loaiGui'       => 'required|integer|between:0,4',
            'uuTien'        => 'required|integer|between:0,2',
            'ghim'          => 'nullable|boolean',
            'hanhDong'      => 'nullable|in:save,send,schedule',
            'scheduled_at'  => 'nullable|date|after:now',
            'tepDinhs'      => 'nullable|array|max:5',
            'tepDinhs.*'    => 'file|max:10240',
            'xoa_tep'       => 'nullable|array',
            'xoa_tep.*'     => 'integer',
        ]);

        $thongBao->update([
            'tieuDe'  => $validated['tieuDe'],
            'noiDung' => $validated['noiDung'],
            'loaiGui' => $validated['loaiGui'],
            'uuTien'  => $validated['uuTien'],
            'ghim'    => $request->boolean('ghim'),
        ]);

        // Xóa file được chọn xóa
        if ($request->filled('xoa_tep')) {
            $tepXoas = ThongBaoTepDinh::where('thongBaoId', $thongBao->thongBaoId)
                ->whereIn('tepDinhId', $request->xoa_tep)->get();
            foreach ($tepXoas as $tep) {
                Storage::disk('public')->delete($tep->duongDan);
                $tep->delete();
            }
        }

        // Thêm file mới
        $this->luuTepDinh($thongBao->thongBaoId, $request);

        $hanhDong = $validated['hanhDong'] ?? 'save';

        if ($hanhDong === 'schedule') {
            if (empty($validated['scheduled_at'])) {
                return back()->withErrors(['scheduled_at' => 'Vui lòng chọn thời gian gửi.'])->withInput();
            }
            $thongBao->update([
                'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_LEN_LICH,
                'scheduled_at'  => Carbon::parse($validated['scheduled_at']),
                'sent_at'       => null,
            ]);
            $this->ghiLichSu($thongBao->thongBaoId, 'scheduled', 'Lên lịch gửi thông báo.');
            return redirect()
                ->route('admin.thong-bao.show', $thongBao->thongBaoId)
                ->with('success', 'Đã lên lịch gửi thông báo.');
        }

        if ($hanhDong === 'send') {
            $thongBao->nguoiNhans()->delete();
            $soNguoiNhan = $this->guiThongBaoVaCapNhatTrangThai($thongBao->fresh());
            if ($soNguoiNhan === 0) {
                $this->ghiLichSu($thongBao->thongBaoId, 'send_failed', 'Gửi thông báo thất bại từ màn hình chỉnh sửa do không có người nhận phù hợp.');
                return redirect()
                    ->route('admin.thong-bao.edit', $thongBao->thongBaoId)
                    ->with('error', 'Không tìm thấy người nhận phù hợp. Vui lòng kiểm tra đối tượng gửi.');
            }
            $this->ghiLichSu($thongBao->thongBaoId, 'sent', "Gửi thông báo từ màn hình chỉnh sửa đến {$soNguoiNhan} người nhận.");
            return redirect()
                ->route('admin.thong-bao.show', $thongBao->thongBaoId)
                ->with('success', "Đã gửi thông báo thành công đến {$soNguoiNhan} người nhận.");
        }

        if ((int) $thongBao->sendTrangThai !== ThongBao::SEND_TRANG_THAI_DA_GUI) {
            $thongBao->update(['sendTrangThai' => ThongBao::SEND_TRANG_THAI_NHAP]);
        }

        $this->ghiLichSu($thongBao->thongBaoId, 'updated', 'Cập nhật thông báo.');

        return redirect()
            ->route('admin.thong-bao.show', $thongBao->thongBaoId)
            ->with('success', 'Đã cập nhật thông báo thành công.');
    }

    // ── DESTROY (Soft Delete) ──────────────────────────────
    public function destroy(string $id)
    {
        $thongBao = ThongBao::findOrFail($id);
        $thongBao->delete(); // soft delete
        $this->ghiLichSu($thongBao->thongBaoId, 'deleted', "Chuyển thông báo '{$thongBao->tieuDe}' vào thùng rác.");

        return redirect()
            ->route('admin.thong-bao.index')
            ->with('success', 'Đã chuyển thông báo vào thùng rác.');
    }

    // ── BULK DESTROY (Soft Delete) ─────────────────────────
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        ThongBao::whereIn('thongBaoId', $ids)->delete(); // soft delete
        $this->ghiLichSu(null, 'bulk_deleted', 'Chuyển nhiều thông báo vào thùng rác.', ['ids' => $ids]);

        return response()->json(['success' => true, 'message' => 'Đã chuyển ' . count($ids) . ' thông báo vào thùng rác.']);
    }

    // ── TOGGLE PIN ─────────────────────────────────────────
    public function togglePin(string $id)
    {
        $thongBao = ThongBao::findOrFail($id);
        $thongBao->update(['ghim' => !$thongBao->ghim]);
        $this->ghiLichSu($thongBao->thongBaoId, $thongBao->ghim ? 'pinned' : 'unpinned', $thongBao->ghim ? 'Đã ghim thông báo.' : 'Đã bỏ ghim thông báo.');

        return response()->json([
            'success' => true,
            'ghim'    => $thongBao->ghim,
            'message' => $thongBao->ghim ? 'Đã ghim thông báo.' : 'Đã bỏ ghim thông báo.',
        ]);
    }

    public function duplicate(string $id)
    {
        $source = ThongBao::with('tepDinhs')->findOrFail($id);
        $clone = ThongBao::create([
            'tieuDe'         => '[Bản sao] ' . $source->tieuDe,
            'noiDung'        => $source->noiDung,
            'nguoiGuiId'     => Auth::id(),
            'loaiThongBao'   => $source->loaiThongBao,
            'doiTuongGui'    => $source->doiTuongGui,
            'doiTuongId'     => $source->doiTuongId,
            'ngayGui'        => null,
            'trangThai'      => 1,
            'loaiGui'        => $source->loaiGui,
            'uuTien'         => $source->uuTien,
            'ghim'           => false,
            'sendTrangThai'  => ThongBao::SEND_TRANG_THAI_NHAP,
            'scheduled_at'   => null,
            'sent_at'        => null,
            'failed_at'      => null,
            'failure_reason' => null,
        ]);

        foreach ($source->tepDinhs as $tep) {
            ThongBaoTepDinh::create([
                'thongBaoId' => $clone->thongBaoId,
                'tenFile'    => $tep->tenFile,
                'tenFileLuu' => $tep->tenFileLuu,
                'duongDan'   => $tep->duongDan,
                'loaiFile'   => $tep->loaiFile,
                'kichThuoc'  => $tep->kichThuoc,
            ]);
        }

        $this->ghiLichSu($clone->thongBaoId, 'duplicated', "Nhân bản từ thông báo #{$source->thongBaoId}");
        return redirect()
            ->route('admin.thong-bao.edit', $clone->thongBaoId)
            ->with('success', 'Đã nhân bản thông báo sang bản nháp mới.');
    }

    public function sendTest(string $id)
    {
        $thongBao = ThongBao::findOrFail($id);
        $userId = Auth::id();

        ThongBaoNguoiDung::updateOrCreate(
            ['thongBaoId' => $thongBao->thongBaoId, 'taiKhoanId' => $userId],
            ['daDoc' => false, 'ngayDoc' => null]
        );

        $this->ghiLichSu($thongBao->thongBaoId, 'test_sent', 'Gửi thử thông báo đến chính người thao tác.');
        return redirect()
            ->route('admin.thong-bao.index')
            ->with('success', 'Đã gửi thử thông báo cho tài khoản của bạn.');
    }

    // ── AJAX: Preview người nhận ────────────────────────────
    public function getRecipients(Request $request)
    {
        $doiTuongGui = (int) $request->get('doiTuongGui', 0);
        $doiTuongId  = $request->filled('doiTuongId') ? (int) $request->doiTuongId : null;

        $nguoiNhans = $this->service->previewNguoiNhan($doiTuongGui, $doiTuongId, Auth::id());

        return response()->json([
            'success'     => true,
            'soNguoiNhan' => $nguoiNhans->count(),
            'nguoiNhans'  => $nguoiNhans->take(20)->values(),
        ]);
    }

    // ── AJAX: Unread count (Bell badge) ────────────────────
    public function getUnreadCount()
    {
        $count = $this->service->getUnreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }

    // ── AJAX: Dropdown list ────────────────────────────────
    public function getDropdown()
    {
        $notifications = $this->service->getRecentNotifications(Auth::id(), 8);
        $unreadCount   = $this->service->getUnreadCount(Auth::id());
        return response()->json([
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    // ── AJAX: Mark one as read ─────────────────────────────
    public function markAsRead(string $id)
    {
        $this->service->markAsRead((int) $id, Auth::id());
        return response()->json(['success' => true]);
    }

    // ── AJAX: Mark all read ────────────────────────────────
    public function markAllRead()
    {
        $count = $this->service->markAllRead(Auth::id());
        return response()->json(['success' => true, 'updated' => $count]);
    }

    private function guiThongBaoVaCapNhatTrangThai(ThongBao $tb): int
    {
        $soNguoiNhan = $this->service->guiThongBao($tb);
        if ($soNguoiNhan > 0) {
            $tb->update([
                'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DA_GUI,
                'failed_at'     => null,
                'failure_reason' => null,
                'ngayGui'       => Carbon::now(),
                'sent_at'       => Carbon::now(),
                'scheduled_at'  => null,
            ]);
            return $soNguoiNhan;
        }

        $tb->update([
            'sendTrangThai'  => ThongBao::SEND_TRANG_THAI_GUI_LOI,
            'failed_at'      => Carbon::now(),
            'failure_reason' => 'Không có người nhận phù hợp.',
        ]);
        return 0;
    }

    private function ghiLichSu(?int $thongBaoId, string $hanhDong, string $moTa, array $payload = []): void
    {
        ThongBaoLichSu::create([
            'thongBaoId' => $thongBaoId,
            'taiKhoanId' => Auth::id(),
            'hanhDong'   => $hanhDong,
            'moTa'       => $moTa,
            'payload'    => $payload ?: null,
            'created_at' => Carbon::now(),
        ]);
    }
}
