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

        $query = ThongBaoNguoiDung::with('thongBao')
            ->where('taiKhoanId', $userId)
            ->whereHas('thongBao');

        // Filter theo trạng thái đọc
        if ($request->filter === 'unread') $query->where('daDoc', false);
        if ($request->filter === 'read')   $query->where('daDoc', true);

        // Filter theo loại thông báo
        if ($request->filled('loai')) {
            $query->whereHas('thongBao', fn($q) => $q->where('loaiGui', $request->loai));
        }

        $items = $query->latest()->paginate(15);

        $tongChuaDoc = ThongBaoNguoiDung::where('taiKhoanId', $userId)->where('daDoc', false)->count();

        return view('clients.hoc-vien.thong-bao.index', compact('items', 'tongChuaDoc'));
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
            ->whereHas('thongBao')
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
            ->count();

        return response()->json(['count' => $count]);
    }

    // ── API: đánh dấu 1 thông báo đã đọc ─────────────────────────────────────
    public function markRead($id)
    {
        $item = ThongBaoNguoiDung::where('thongBaoId', $id)
            ->where('taiKhoanId', Auth::id())
            ->first();

        if ($item && !$item->daDoc) {
            $item->update(['daDoc' => true, 'ngayDoc' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ── API: đánh dấu tất cả đã đọc ──────────────────────────────────────────
    public function markAllRead()
    {
        ThongBaoNguoiDung::where('taiKhoanId', Auth::id())
            ->where('daDoc', false)
            ->update(['daDoc' => true, 'ngayDoc' => now()]);

        return response()->json(['success' => true, 'message' => 'Đã đọc tất cả thông báo']);
    }
}
