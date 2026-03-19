<?php

namespace App\Http\Middleware;

use App\Models\Auth\TaiKhoan;
use App\Services\Auth\DeviceSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAuthenticatedDeviceSession
{
    public function __construct(
        private readonly DeviceSessionService $deviceSessionService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if ($user instanceof TaiKhoan) {
            $this->deviceSessionService->syncCurrentSession($request, $user);
        }
        return $response;
    }
}
