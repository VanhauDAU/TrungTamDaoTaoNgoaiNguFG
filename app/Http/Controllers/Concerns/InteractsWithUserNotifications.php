<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Interaction\ThongBao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait InteractsWithUserNotifications
{
    protected function notificationBaseQueryFor(Request $request): Builder
    {
        return ThongBaoNguoiDung::query()
            ->with('thongBao.tepDinhs', 'thongBao.nguoiGui.hoSoNguoiDung', 'thongBao.nguoiGui.nhanSu')
            ->where('taiKhoanId', $request->user()->getAuthIdentifier())
            ->whereHas('thongBao', fn ($query) => $query->whereNull('deleted_at'))
            ->latest('updated_at');
    }

    protected function notificationQueryFor(Request $request): Builder
    {
        $query = $this->notificationBaseQueryFor($request);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            if ($keyword !== '') {
                $query->whereHas('thongBao', function (Builder $subQuery) use ($keyword) {
                    $subQuery->where(function (Builder $nested) use ($keyword) {
                        $nested->where('tieuDe', 'like', "%{$keyword}%")
                            ->orWhere('noiDung', 'like', "%{$keyword}%");
                    });
                });
            }
        }

        $readState = $request->query('read');
        if ($readState === 'unread') {
            $query->where('daDoc', false);
        } elseif ($readState === 'read') {
            $query->where('daDoc', true);
        }

        $loaiGui = $request->query('loaiGui');
        if ($loaiGui !== null && $loaiGui !== '' && array_key_exists((int) $loaiGui, ThongBao::loaiLabels())) {
            $query->whereHas('thongBao', fn (Builder $subQuery) => $subQuery->where('loaiGui', (int) $loaiGui));
        }

        $uuTien = $request->query('uuTien');
        if ($uuTien !== null && $uuTien !== '' && array_key_exists((int) $uuTien, ThongBao::uuTienLabels())) {
            $query->whereHas('thongBao', fn (Builder $subQuery) => $subQuery->where('uuTien', (int) $uuTien));
        }

        if ($request->boolean('onlyPinned')) {
            $query->whereHas('thongBao', fn (Builder $subQuery) => $subQuery->where('ghim', true));
        }

        return $query;
    }

    protected function sentNotificationBaseQueryFor(Request $request): Builder
    {
        return ThongBao::query()
            ->with('tepDinhs')
            ->withCount([
                'nguoiNhans',
                'nguoiNhans as da_doc_count' => fn (Builder $query) => $query->where('daDoc', true),
            ])
            ->where('nguoiGuiId', $request->user()->getAuthIdentifier())
            ->whereNull('deleted_at')
            ->latest('created_at');
    }

    protected function sentNotificationQueryFor(Request $request): Builder
    {
        $query = $this->sentNotificationBaseQueryFor($request);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            if ($keyword !== '') {
                $query->where(function (Builder $subQuery) use ($keyword) {
                    $subQuery->where('tieuDe', 'like', "%{$keyword}%")
                        ->orWhere('noiDung', 'like', "%{$keyword}%");
                });
            }
        }

        $loaiGui = $request->query('loaiGui');
        if ($loaiGui !== null && $loaiGui !== '' && array_key_exists((int) $loaiGui, ThongBao::loaiLabels())) {
            $query->where('loaiGui', (int) $loaiGui);
        }

        $uuTien = $request->query('uuTien');
        if ($uuTien !== null && $uuTien !== '' && array_key_exists((int) $uuTien, ThongBao::uuTienLabels())) {
            $query->where('uuTien', (int) $uuTien);
        }

        if ($request->boolean('onlyPinned')) {
            $query->where('ghim', true);
        }

        return $query;
    }
}
