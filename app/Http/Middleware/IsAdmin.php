<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Chỉ cho phép nhân sự (giáo viên, nhân viên, admin) truy cập khu vực /admin.
     * role: 0 = học viên, 1 = giáo viên, 2 = nhân viên, 3 = admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isStaff()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
