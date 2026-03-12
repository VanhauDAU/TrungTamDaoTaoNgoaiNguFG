<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Kiểm tra quyền truy cập theo tính năng và action.
     *
     * Dùng: ->middleware('permission:khoa_hoc,xem')
     *        ->middleware('permission:tai_chinh,them')
     *
     * @param string $feature  khoa_hoc | lop_hoc | hoc_vien | ...
     * @param string $action   xem | them | sua | xoa  (mặc định: xem)
     */
    public function handle(Request $request, Closure $next, string $feature, string $action = 'xem'): Response
    {
        if (!auth()->check()) {
            return redirect()->route('staff.login');
        }

        $user = auth()->user();

        // Admin (role=3) bypass hoàn toàn
        if ($user->isAdmin()) {
            return $next($request);
        }

        // User không phải staff → không được vào admin
        if (!$user->isStaff()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Kiểm tra quyền theo nhóm
        if (!$user->canDo($feature, $action)) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
