<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ClientThongBaoController
 * Xử lý thông báo phía client (học viên / giáo viên / nhân viên)
 * Các endpoint: trang danh sách, SSE stream, API JSON, mark-read
 */
class ClientThongBaoController extends Controller
{
    // ── Trang danh sách thông báo của người dùng ─────────────────────────────
    public function index(Request $request)
    {
        $userId = Auth::id();
        $scope = $request->get('scope', 'all');

        // ── Dữ liệu gộp theo danh mục (cho grid 2×2) ──────────────
        $loaiMap = [
            0 => ['label' => 'Hệ thống',   'icon' => 'fa-cog',                'color' => '#6366f1'],
            1 => ['label' => 'Học tập',     'icon' => 'fa-graduation-cap',     'color' => '#3b82f6'],
            2 => ['label' => 'Tài chính',   'icon' => 'fa-wallet',             'color' => '#10b981'],
            3 => ['label' => 'Sự kiện',     'icon' => 'fa-calendar-alt',       'color' => '#f59e0b'],
            4 => ['label' => 'Khẩn cấp',   'icon' => 'fa-exclamation-triangle','color' => '#ef4444'],
        ];

        // Lấy tất cả thông báo của user (không phân trang), eager load tepDinhs
        $allItems = ThongBaoNguoiDung::with(['thongBao.tepDinhs'])
            ->where('taiKhoanId', $userId)
            ->whereHas('thongBao', function ($q) {
                $q->where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_GUI)
                    ->whereNull('deleted_at');
            })
            ->latest()
            ->get();

        if ($scope === 'unread') {
            $allItems = $allItems->where('daDoc', false)->values();
        } elseif ($scope === 'important') {
            $allItems = $allItems
                ->filter(fn($i) => ($i->thongBao->uuTien ?? 0) >= ThongBao::UU_TIEN_QUAN_TRONG)
                ->values();
        } elseif ($scope === 'system') {
            $allItems = $allItems
                ->filter(fn($i) => ($i->thongBao->loaiGui ?? 0) === ThongBao::LOAI_HE_THONG)
                ->values();
        }

        // Group theo loaiGui. Mỗi category chứa tối đa 5 thông báo mới nhất để hiển thị inline.
        $byCategory = [];
        foreach ($loaiMap as $loaiKey => $meta) {
            $items = $allItems->filter(fn($i) => ($i->thongBao->loaiGui ?? 0) === $loaiKey);
            $byCategory[$loaiKey] = [
                'label'   => $meta['label'],
                'icon'    => $meta['icon'],
                'color'   => $meta['color'],
                'items'   => $items->values(),
                'total'   => $items->count(),
                'unread'  => $items->where('daDoc', false)->count(),
            ];
        }

        $tongChuaDoc = $allItems->where('daDoc', false)->count();

        return view('clients.hoc-vien.thong-bao.index', compact('byCategory', 'tongChuaDoc', 'scope'));
    }

    // ── SSE (Server-Sent Events) stream ──────────────────────────────────────
    public function stream(Request $request): StreamedResponse
    {
        // Lấy userId trước khi đóng session
        $userId = Auth::id();

        // ⚡ QUAN TRỌNG: Giải phóng session lock TRƯỚC KHI stream
        // PHP file session bị lock trong suốt request — nếu không close,
        // mọi request khác của cùng user sẽ bị block cho đến khi stream kết thúc
        session()->save();

        return response()->stream(function () use ($userId) {
            // ⚡ Bỏ giới hạn thời gian thực thi (default 30s sẽ kill stream)
            set_time_limit(0);
            // Tiếp tục chạy dù client đóng kết nối (để cleanup đúng cách)
            ignore_user_abort(true);

            // Đảm bảo session đã đóng (gọi lại để chắc chắn)
            if (session()->isStarted()) {
                session()->save();
            }
            // Đóng native PHP session nếu còn mở
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Tắt output buffering để flush ngay lập tức
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $lastCheck  = now();
            $retryCount = 0;
            $maxRetries = 40; // 40 x 15s = 10 phút tối đa

            // Gửi sự kiện kết nối thành công
            echo "event: connected\n";
            echo "data: " . json_encode(['status' => 'ok']) . "\n\n";
            flush();

            while ($retryCount < $maxRetries) {
                if (connection_aborted()) break;

                sleep(15); // poll mỗi 15 giây (đủ real-time, ít tải hơn 5s)

                // Chỉ query DB khi cần thiết
                $newItems = ThongBaoNguoiDung::with('thongBao')
                    ->where('taiKhoanId', $userId)
                    ->where('daDoc', false)
                    ->whereHas('thongBao', fn($q) => $q
                        ->where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_GUI)
                        ->whereNull('deleted_at'))
                    ->where('created_at', '>', $lastCheck)
                    ->get();

                if ($newItems->count() > 0) {
                    $payload = $newItems->map(function ($item) {
                        $tb = $item->thongBao;
                        if (!$tb) return null;
                        return [
                            'id'      => $tb->thongBaoId,
                            'tieuDe'  => $tb->tieuDe,
                            'tomTat'  => strip_tags(mb_substr($tb->noiDung ?? '', 0, 80)) . '…',
                            'loaiGui' => $tb->loaiGui ?? 0,
                            'uuTien'  => $tb->uuTien ?? 0,
                            'ngayGui' => ($tb->ngayGui ?? $tb->created_at)?->toIso8601String(),
                        ];
                    })->filter()->values();

                    echo "event: new_notification\n";
                    echo "data: " . json_encode([
                        'notifications' => $payload,
                        'count'         => $payload->count(),
                    ]) . "\n\n";
                    flush();
                }

                // Heartbeat nhẹ để giữ kết nối
                echo ": heartbeat\n\n"; // dùng comment SSE (: ...) thay vì event để nhẹ hơn
                flush();

                $lastCheck = now();
                $retryCount++;
            }

            // Thông báo client tự reconnect
            echo "event: close\n";
            echo "data: {}\n\n";
            flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    // ── API: danh sách gần đây cho dropdown header ────────────────────────────
    public function getDropdown(Request $request)
    {
        $userId = Auth::id();

        $items = ThongBaoNguoiDung::with('thongBao')
            ->where('taiKhoanId', $userId)
            ->whereHas('thongBao', fn($q) => $q
                ->where('sendTrangThai', ThongBao::SEND_TRANG_THAI_DA_GUI)
                ->whereNull('deleted_at'))
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($item) {
                $tb = $item->thongBao;
                return [
                    'id'          => $tb->thongBaoId,
                    'pivotId'     => $item->thongBaoNguoiDungId,
                    'tieuDe'      => $tb->tieuDe,
                    'tomTat'      => strip_tags(mb_substr($tb->noiDung, 0, 70)) . '…',
                    'loaiGui'     => $tb->loaiGui ?? 0,
                    'uuTien'      => $tb->uuTien ?? 0,
                    'daDoc'       => (bool)$item->daDoc,
                    'ghim'        => (bool)$tb->ghim,
                    'ngayGui'     => $tb->ngayGui?->toIso8601String()
                                    ?? $tb->created_at?->toIso8601String(),
                ];
            });

        $unreadCount = ThongBaoNguoiDung::where('taiKhoanId', $userId)
            ->where('daDoc', false)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->count();

        return response()->json([
            'notifications' => $items,
            'unreadCount'   => $unreadCount,
        ]);
    }

    // ── API: số thông báo chưa đọc ───────────────────────────────────────────
    public function getUnreadCount()
    {
        $count = ThongBaoNguoiDung::where('taiKhoanId', Auth::id())
            ->where('daDoc', false)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->count();

        return response()->json(['count' => $count]);
    }

    // ── API: đánh dấu 1 thông báo đã đọc ─────────────────────────────────────
    public function markRead($id)
    {
        $item = ThongBaoNguoiDung::where('thongBaoId', $id)
            ->where('taiKhoanId', Auth::id())
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if ($item && !$item->daDoc) {
            $item->update(['daDoc' => true, 'ngayDoc' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ── API: đánh dấu 1 thông báo chưa đọc ──────────────────────────────────
    public function markUnread($id)
    {
        $item = ThongBaoNguoiDung::where('thongBaoId', $id)
            ->where('taiKhoanId', Auth::id())
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if ($item && $item->daDoc) {
            $item->update(['daDoc' => false, 'ngayDoc' => null]);
        }

        return response()->json(['success' => true]);
    }

    // ── API: đánh dấu tất cả đã đọc ──────────────────────────────────────────
    public function markAllRead()
    {
        ThongBaoNguoiDung::where('taiKhoanId', Auth::id())
            ->where('daDoc', false)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->update(['daDoc' => true, 'ngayDoc' => now()]);

        return response()->json(['success' => true, 'message' => 'Đã đọc tất cả thông báo']);
    }
}
