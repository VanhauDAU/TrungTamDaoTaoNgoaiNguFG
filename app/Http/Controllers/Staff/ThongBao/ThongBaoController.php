<?php

namespace App\Http\Controllers\Staff\ThongBao;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use App\Models\Interaction\ThongBao;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    use InteractsWithUserNotifications;

    public function index(Request $request)
    {
        $box = $request->query('box', 'inbox') === 'sent' ? 'sent' : 'inbox';
        $selectedNotificationId = (int) $request->query('thong_bao', 0);
        $baseInboxQuery = $this->notificationBaseQueryFor($request);
        $baseSentQuery = $this->sentNotificationBaseQueryFor($request);
        $listQuery = $box === 'sent'
            ? $this->sentNotificationQueryFor($request)
            : $this->notificationQueryFor($request);

        return view('internal.notifications.index', [
            'notifications' => $listQuery->paginate(12)->withQueryString(),
            'portalTitle' => 'Nhân viên',
            'indexRoute' => 'staff.notifications.index',
            'createRoute' => 'staff.notifications.create',
            'markReadRoute' => 'staff.api.notifications.mark-read',
            'markUnreadRoute' => 'staff.api.notifications.mark-unread',
            'currentBox' => $box,
            'selectedNotificationId' => $selectedNotificationId,
            'stats' => [
                'tong_hop_thu_den' => (clone $baseInboxQuery)->count(),
                'chua_doc' => (clone $baseInboxQuery)->where('daDoc', false)->count(),
                'quan_trong' => (clone $baseInboxQuery)->whereHas('thongBao', fn ($query) => $query->where('uuTien', '>=', ThongBao::UU_TIEN_QUAN_TRONG))->count(),
                'tai_chinh' => (clone $baseInboxQuery)->whereHas('thongBao', fn ($query) => $query->where('loaiGui', ThongBao::LOAI_TAI_CHINH))->count(),
                'da_gui' => (clone $baseSentQuery)->count(),
            ],
        ]);
    }
}
