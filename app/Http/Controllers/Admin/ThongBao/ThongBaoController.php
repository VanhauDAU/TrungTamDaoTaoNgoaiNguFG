<?php

namespace App\Http\Controllers\Admin\ThongBao;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessThongBaoDelivery;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoLichSu;
use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Interaction\ThongBaoTepDinh;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\CoSoDaoTao;
use App\Services\Admin\ThongBao\ThongBaoService;
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
            ->withCount(['nguoiNhans', 'nguoiNhans as da_doc_count' => fn($q) => $q->where('daDoc', true)])
            ->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('tieuDe', 'like', "%{$q}%")
                        ->orWhere('noiDung', 'like', "%{$q}%");
                });
            }
        }

        // Filter loại
        $loaiGui = $request->query('loaiGui');
        if ($loaiGui !== null && $loaiGui !== '' && array_key_exists((int) $loaiGui, ThongBao::loaiLabels())) {
            $query->where('loaiGui', (int) $loaiGui);
        }

        // Filter đối tượng
        $doiTuongGui = $request->query('doiTuongGui');
        if (
            $doiTuongGui !== null &&
            $doiTuongGui !== '' &&
            array_key_exists((int) $doiTuongGui, ThongBao::doiTuongLabels())
        ) {
            $query->where('doiTuongGui', (int) $doiTuongGui);
        }

        // Filter ưu tiên
        $uuTien = $request->query('uuTien');
        if ($uuTien !== null && $uuTien !== '' && array_key_exists((int) $uuTien, ThongBao::uuTienLabels())) {
            $query->where('uuTien', (int) $uuTien);
        }

        // Filter ghim
        if ($request->filled('ghim')) {
            $query->where('ghim', (bool)$request->ghim);
        }

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
            'tong'     => ThongBao::count(),
            'hom_nay'  => ThongBao::whereDate('created_at', today())->count(),
            'chua_doc' => ThongBaoNguoiDung::where('daDoc', false)->count(),
            'ghim'     => ThongBao::where('ghim', true)->count(),
            'nhap'     => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_NHAP)->count(),
            'dang_xu_ly' => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DANG_XU_LY)->count(),
            'da_gui'   => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_GUI)->count(),
            'gui_loi'  => ThongBao::where('sendTrangThai', ThongBao::SEND_TRANG_THAI_GUI_LOI)->count(),
        ];
        $trashCount = ThongBao::onlyTrashed()->count();

        return view('admin.thong-bao.index', compact('thongBaos', 'stats', 'trashCount'));
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
        $lopHocs    = LopHoc::select('lopHocId', 'tenLopHoc', 'khoaHocId')->orderBy('tenLopHoc')->get();
        $khoaHocs   = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $coSos      = CoSoDaoTao::select('coSoId', 'tenCoSo')->orderBy('tenCoSo')->get();
        $taiKhoans  = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)
            ->orderBy('taiKhoanId')
            ->get();

        return view('admin.thong-bao.create', compact('lopHocs', 'khoaHocs', 'coSos', 'taiKhoans'));
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
            'hanhDong'      => 'nullable|in:send,draft',
            'tepDinhs'      => 'nullable|array|max:5',
            'tepDinhs.*'    => 'file|max:10240',
        ]);

        if ((int) $validated['doiTuongGui'] === ThongBao::DOI_TUONG_THEO_CO_SO) {
            $coSoId = $validated['doiTuongId'] ?? null;
            if (!$coSoId || !CoSoDaoTao::where('coSoId', $coSoId)->exists()) {
                return back()->withInput()->withErrors([
                    'doiTuongId' => 'Vui lòng chọn cơ sở hợp lệ để gửi thông báo.',
                ]);
            }
        }

        $hanhDong = $validated['hanhDong'] ?? 'send';
        $isDraft = $hanhDong === 'draft';

        $tb = ThongBao::create([
            'tieuDe'      => $validated['tieuDe'],
            'noiDung'     => $validated['noiDung'],
            'loaiGui'     => $validated['loaiGui'],
            'doiTuongGui' => $validated['doiTuongGui'],
            'doiTuongId'  => $validated['doiTuongId'] ?? null,
            'uuTien'      => $validated['uuTien'],
            'nguoiGuiId'  => Auth::id(),
            'ngayGui'     => null,
            'trangThai'   => 1,
            'ghim'        => $request->boolean('ghim'),
            'sendTrangThai' => $isDraft ? ThongBao::SEND_TRANG_THAI_NHAP : ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
            'sent_at'     => null,
        ]);

        // Lưu file đính kèm
        $this->luuTepDinh($tb->thongBaoId, $request);

        if ($isDraft) {
            $this->ghiLichSu($tb->thongBaoId, 'draft_created', 'Tạo thông báo nháp.');
            return redirect()
                ->route('admin.thong-bao.edit', $tb->thongBaoId)
                ->with('success', 'Đã lưu bản nháp. Bạn có thể chỉnh sửa thêm và bấm "Gửi thông báo ngay" để phát hành.');
        }

        $this->dispatchQueuedDelivery($tb->fresh(), 'admin_create_send');
        return redirect()
            ->route('admin.thong-bao.show', $tb->thongBaoId)
            ->with('success', 'Đã đưa thông báo vào hàng chờ gửi. Worker queue sẽ xử lý việc phát hành.');
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
        $thongBao = ThongBao::whereNull('deleted_at')->with([
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
        $thongBao   = ThongBao::with('tepDinhs')->findOrFail($id);
        $lopHocs    = LopHoc::select('lopHocId', 'tenLopHoc')->orderBy('tenLopHoc')->get();
        $khoaHocs   = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $taiKhoans  = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)->orderBy('taiKhoanId')->get();

        return view('admin.thong-bao.edit', compact('thongBao', 'lopHocs', 'khoaHocs', 'taiKhoans'));
    }

    // ── UPDATE ─────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $thongBao = ThongBao::whereNull('deleted_at')->findOrFail($id);

        $validated = $request->validate([
            'tieuDe'        => 'required|string|max:255',
            'noiDung'       => 'required|string',
            'loaiGui'       => 'required|integer|between:0,4',
            'uuTien'        => 'required|integer|between:0,2',
            'ghim'          => 'nullable|boolean',
            'hanhDong'      => 'nullable|in:save,send',
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
        if ($hanhDong === 'send') {
            $thongBao->nguoiNhans()->delete();
            $thongBao->update([
                'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
                'failed_at' => null,
                'failure_reason' => null,
                'ngayGui' => null,
                'sent_at' => null,
                'scheduled_at' => null,
            ]);
            $this->dispatchQueuedDelivery($thongBao->fresh(), 'admin_edit_send');
            return redirect()
                ->route('admin.thong-bao.show', $thongBao->thongBaoId)
                ->with('success', 'Đã đưa thông báo vào hàng chờ gửi. Worker queue sẽ xử lý việc phát hành.');
        }

        if (!in_array((int) $thongBao->sendTrangThai, [
            ThongBao::SEND_TRANG_THAI_DA_GUI,
            ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
        ], true)) {
            $thongBao->update(['sendTrangThai' => ThongBao::SEND_TRANG_THAI_NHAP]);
        }

        $this->ghiLichSu($thongBao->thongBaoId, 'updated', 'Cập nhật thông báo.');

        if ((int) $thongBao->fresh()->sendTrangThai === ThongBao::SEND_TRANG_THAI_NHAP) {
            return redirect()
                ->route('admin.thong-bao.edit', $thongBao->thongBaoId)
                ->with('success', 'Đã lưu nháp. Khi sẵn sàng, bấm "Gửi thông báo ngay" để gửi cho người nhận.');
        }

        if ((int) $thongBao->fresh()->sendTrangThai === ThongBao::SEND_TRANG_THAI_DA_LEN_LICH) {
            return redirect()
                ->route('admin.thong-bao.edit', $thongBao->thongBaoId)
                ->with('success', 'Đã lưu thay đổi. Thông báo vẫn đang ở trạng thái lên lịch gửi.');
        }

        return redirect()
            ->route('admin.thong-bao.show', $thongBao->thongBaoId)
            ->with('success', 'Đã cập nhật thông báo thành công.');
    }

    // ── DESTROY (Soft Delete) ──────────────────────────────
    public function destroy(string $id)
    {
        $thongBao = ThongBao::with('tepDinhs')->findOrFail($id);
        $deletedTitle = $thongBao->tieuDe;

        // Xóa file vật lý
        foreach ($thongBao->tepDinhs as $tep) {
            Storage::disk('public')->delete($tep->duongDan);
        }

        // Xóa records liên quan
        $thongBao->tepDinhs()->delete();
        $thongBao->nguoiNhans()->delete();
        $thongBao->delete();
        $this->ghiLichSu(null, 'deleted', "Đã xóa thông báo: {$deletedTitle}", ['thongBaoId' => (int)$id]);

        return redirect()
            ->route('admin.thong-bao.index')
            ->with('success', 'Đã chuyển thông báo vào thùng rác.');
    }

    // ── BULK DESTROY (Soft Delete) ─────────────────────────
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        // Xóa file vật lý
        $tepDinhs = ThongBaoTepDinh::whereIn('thongBaoId', $ids)->get();
        foreach ($tepDinhs as $tep) {
            Storage::disk('public')->delete($tep->duongDan);
        }

        ThongBaoTepDinh::whereIn('thongBaoId', $ids)->delete();
        ThongBaoNguoiDung::whereIn('thongBaoId', $ids)->delete();
        ThongBao::whereIn('thongBaoId', $ids)->delete();
        $this->ghiLichSu(null, 'bulk_deleted', 'Đã xóa nhiều thông báo.', ['ids' => $ids]);

        return response()->json(['success' => true, 'message' => 'Đã xóa ' . count($ids) . ' thông báo.']);
    }

    // ── TOGGLE PIN ─────────────────────────────────────────
    public function togglePin(string $id)
    {
        $thongBao = ThongBao::whereNull('deleted_at')->findOrFail($id);
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
        $source = ThongBao::whereNull('deleted_at')->with('tepDinhs')->findOrFail($id);
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
        $thongBao = ThongBao::whereNull('deleted_at')->findOrFail($id);
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

    private function dispatchQueuedDelivery(ThongBao $tb, string $source): void
    {
        $tb->update([
            'sendTrangThai' => ThongBao::SEND_TRANG_THAI_DANG_XU_LY,
            'failed_at' => null,
            'failure_reason' => null,
        ]);

        ProcessThongBaoDelivery::dispatch($tb->thongBaoId, Auth::id(), $source)->afterCommit();

        $this->ghiLichSu(
            $tb->thongBaoId,
            'queued',
            'Đã đưa thông báo vào hàng chờ gửi.',
            ['source' => $source]
        );
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

    private function parseScheduledAt(?string $scheduledAtInput): ?Carbon
    {
        if (!$scheduledAtInput) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d\TH:i', $scheduledAtInput, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
