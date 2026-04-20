<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Interaction\ThongBaoNguoiDung;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait InteractsWithUserNotifications
{
    protected function notificationQueryFor(Request $request): Builder
    {
        return ThongBaoNguoiDung::query()
            ->with('thongBao.tepDinhs')
            ->where('taiKhoanId', $request->user()->getAuthIdentifier())
            ->whereHas('thongBao', fn ($query) => $query->whereNull('deleted_at'))
            ->latest('updated_at');
    }
}
