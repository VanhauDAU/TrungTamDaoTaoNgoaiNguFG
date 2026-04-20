<?php

namespace App\Http\Controllers\Staff\ThongBao;

use App\Http\Controllers\Concerns\InteractsWithUserNotifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    use InteractsWithUserNotifications;

    public function index(Request $request)
    {
        return view('internal.notifications.index', [
            'notifications' => $this->notificationQueryFor($request)->paginate(15)->withQueryString(),
            'portalTitle' => 'Nhân viên',
            'indexRoute' => 'staff.notifications.index',
        ]);
    }
}
