<?php

namespace App\Http\Controllers\Admin\ThongBao;

use App\Http\Controllers\Controller;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Course\KhoaHoc;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $query->where('ghim', (bool)$request->ghim);
        }

        // Sắp xếp: ghim lên trên, rồi mới nhất
        $query->orderByDesc('ghim')->orderByDesc('created_at');

        $thongBaos = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'tong'     => ThongBao::count(),
            'hom_nay'  => ThongBao::whereDate('created_at', today())->count(),
            'chua_doc' => ThongBaoNguoiDung::where('daDoc', false)->count(),
            'ghim'     => ThongBao::where('ghim', true)->count(),
        ];

        return view('admin.thong-bao.index', compact('thongBaos', 'stats'));
    }

    // ── CREATE ─────────────────────────────────────────────
    public function create()
    {
        $lopHocs    = LopHoc::select('lopHocId', 'tenLopHoc', 'khoaHocId')->orderBy('tenLopHoc')->get();
        $khoaHocs   = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $taiKhoans  = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)
            ->orderBy('taiKhoanId')
            ->get();

        return view('admin.thong-bao.create', compact('lopHocs', 'khoaHocs', 'taiKhoans'));
    }

    // ── STORE ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tieuDe'      => 'required|string|max:255',
            'noiDung'     => 'required|string',
            'loaiGui'     => 'required|integer|between:0,4',
            'doiTuongGui' => 'required|integer|between:0,4',
            'doiTuongId'  => 'nullable|integer',
            'uuTien'      => 'required|integer|between:0,2',
            'ghim'        => 'nullable|boolean',
            'hinhAnh'     => 'nullable|string|max:500',
        ]);

        $tb = ThongBao::create([
            ...$validated,
            'nguoiGuiId' => Auth::id(),
            'ngayGui'    => Carbon::now(),
            'trangThai'  => 1,
            'ghim'       => $request->boolean('ghim'),
        ]);

        // Gửi đến người nhận
        $soNguoiNhan = $this->service->guiThongBao($tb);

        return redirect()
            ->route('admin.thong-bao.show', $tb->thongBaoId)
            ->with('success', "Đã gửi thông báo thành công đến {$soNguoiNhan} người nhận.");
    }

    // ── SHOW ───────────────────────────────────────────────
    public function show(string $id)
    {
        $thongBao = ThongBao::with([
            'nguoiGui.hoSoNguoiDung',
            'nguoiGui.nhanSu',
            'nguoiNhans.nguoiDung.hoSoNguoiDung',
            'nguoiNhans.nguoiDung.nhanSu',
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
        $thongBao   = ThongBao::findOrFail($id);
        $lopHocs    = LopHoc::select('lopHocId', 'tenLopHoc')->orderBy('tenLopHoc')->get();
        $khoaHocs   = KhoaHoc::select('khoaHocId', 'tenKhoaHoc')->orderBy('tenKhoaHoc')->get();
        $taiKhoans  = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
            ->where('trangThai', 1)->orderBy('taiKhoanId')->get();

        return view('admin.thong-bao.edit', compact('thongBao', 'lopHocs', 'khoaHocs', 'taiKhoans'));
    }

    // ── UPDATE ─────────────────────────────────────────────
    public function update(Request $request, string $id)
    {
        $thongBao = ThongBao::findOrFail($id);

        $validated = $request->validate([
            'tieuDe'  => 'required|string|max:255',
            'noiDung' => 'required|string',
            'loaiGui' => 'required|integer|between:0,4',
            'uuTien'  => 'required|integer|between:0,2',
            'ghim'    => 'nullable|boolean',
        ]);

        $thongBao->update([
            ...$validated,
            'ghim' => $request->boolean('ghim'),
        ]);

        return redirect()
            ->route('admin.thong-bao.show', $thongBao->thongBaoId)
            ->with('success', 'Đã cập nhật thông báo thành công.');
    }

    // ── DESTROY ────────────────────────────────────────────
    public function destroy(string $id)
    {
        $thongBao = ThongBao::findOrFail($id);
        // Xóa pivot records trước
        $thongBao->nguoiNhans()->delete();
        $thongBao->delete();

        return redirect()
            ->route('admin.thong-bao.index')
            ->with('success', 'Đã xóa thông báo thành công.');
    }

    // ── BULK DESTROY ───────────────────────────────────────
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        ThongBaoNguoiDung::whereIn('thongBaoId', $ids)->delete();
        ThongBao::whereIn('thongBaoId', $ids)->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ' . count($ids) . ' thông báo.']);
    }

    // ── TOGGLE PIN ─────────────────────────────────────────
    public function togglePin(string $id)
    {
        $thongBao = ThongBao::findOrFail($id);
        $thongBao->update(['ghim' => !$thongBao->ghim]);

        return response()->json([
            'success' => true,
            'ghim'    => $thongBao->ghim,
            'message' => $thongBao->ghim ? 'Đã ghim thông báo.' : 'Đã bỏ ghim thông báo.',
        ]);
    }

    // ── AJAX: Preview người nhận ────────────────────────────
    public function getRecipients(Request $request)
    {
        $doiTuongGui = (int) $request->get('doiTuongGui', 0);
        $doiTuongId  = $request->filled('doiTuongId') ? (int)$request->doiTuongId : null;

        $nguoiNhans = $this->service->previewNguoiNhan($doiTuongGui, $doiTuongId, Auth::id());

        return response()->json([
            'success'    => true,
            'soNguoiNhan' => $nguoiNhans->count(),
            'nguoiNhans' => $nguoiNhans->take(20)->values(), // preview 20 đầu
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
        $this->service->markAsRead((int)$id, Auth::id());
        return response()->json(['success' => true]);
    }

    // ── AJAX: Mark all read ────────────────────────────────
    public function markAllRead()
    {
        $count = $this->service->markAllRead(Auth::id());
        return response()->json(['success' => true, 'updated' => $count]);
    }
}
