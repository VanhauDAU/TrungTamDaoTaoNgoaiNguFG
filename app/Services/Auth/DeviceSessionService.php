<?php

namespace App\Services\Auth;

use App\Models\Auth\NhatKyBaoMat;
use App\Models\Auth\PhienDangNhap;
use App\Models\Auth\TaiKhoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeviceSessionService
{
    public function syncCurrentSession(Request $request, TaiKhoan $user): ?PhienDangNhap
    {
        if (!$request->hasSession()) {
            return null;
        }

        $sessionId = (string) $request->session()->getId();

        if ($sessionId === '') {
            return null;
        }

        $metadata = $this->buildSessionMetadata($request, $user);
        $device = PhienDangNhap::firstOrNew(['sessionId' => $sessionId]);
        $isNew = !$device->exists;

        $device->fill([
            'taiKhoanId' => $user->taiKhoanId,
            'portal' => $metadata['portal'],
            'loginMethod' => $metadata['login_method'],
            'remembered' => $metadata['remembered'],
            'ipAddress' => $metadata['ip_address'],
            'userAgent' => $metadata['user_agent'],
            'deviceName' => $metadata['device_name'],
            'platform' => $metadata['platform'],
            'browser' => $metadata['browser'],
            'lastSeenAt' => now(),
            'revokedAt' => null,
            'revokeReason' => null,
        ]);
        $device->save();

        if ($isNew) {
            $this->logEvent(
                user: $user,
                sessionId: $sessionId,
                event: $metadata['via_remember'] ? 'remembered_session_restored' : 'session_registered',
                description: $metadata['via_remember']
                    ? 'Khôi phục phiên đăng nhập từ cookie ghi nhớ đăng nhập.'
                    : 'Tạo phiên đăng nhập mới.',
                request: $request,
                device: $device,
                data: [
                    'portal' => $metadata['portal'],
                    'login_method' => $metadata['login_method'],
                    'remembered' => $metadata['remembered'],
                ],
            );
        }

        return $device;
    }

    public function activeSessionsForUser(TaiKhoan $user, Request $request): Collection
    {
        $currentSessionId = $request->hasSession() ? (string) $request->session()->getId() : '';
        $cutoff = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;
        $sessionRows = DB::table($this->sessionTable())
            ->where('user_id', $user->getAuthIdentifier())
            ->where('last_activity', '>=', $cutoff)
            ->orderByDesc('last_activity')
            ->get()
            ->keyBy('id');

        if ($currentSessionId !== '' && !$sessionRows->has($currentSessionId)) {
            $sessionRows->put($currentSessionId, (object) [
                'id' => $currentSessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity' => now()->timestamp,
            ]);
        }

        $trackedDevices = PhienDangNhap::query()
            ->where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('sessionId', $sessionRows->keys())
            ->get()
            ->keyBy('sessionId');

        return $sessionRows->map(function (object $session) use ($trackedDevices, $currentSessionId, $request, $user) {
            $tracked = $trackedDevices->get((string) $session->id);
            $userAgent = is_string($session->user_agent ?? null) && $session->user_agent !== ''
                ? $session->user_agent
                : $tracked?->userAgent;
            $detected = $this->parseUserAgent($userAgent);
            $loginMethod = $tracked?->loginMethod ?? $this->fallbackLoginMethod($request, $user);
            $portal = $tracked?->portal ?? $this->detectPortal($request, $user);

            return [
                'sessionId' => (string) $session->id,
                'phienDangNhapId' => $tracked?->phienDangNhapId,
                'deviceName' => $tracked?->deviceName ?? $this->buildDeviceName($detected['platform'], $detected['browser']),
                'platform' => $tracked?->platform ?? $detected['platform'],
                'browser' => $tracked?->browser ?? $detected['browser'],
                'ipAddress' => $tracked?->ipAddress ?? $session->ip_address,
                'userAgent' => $userAgent,
                'remembered' => (bool) ($tracked?->remembered ?? false),
                'loginMethod' => $loginMethod,
                'portal' => $portal,
                'loggedInAt' => $tracked?->created_at ?? Carbon::createFromTimestamp((int) $session->last_activity),
                'lastSeenAt' => $tracked?->lastSeenAt ?? Carbon::createFromTimestamp((int) $session->last_activity),
                'isCurrent' => (string) $session->id === $currentSessionId,
            ];
        })->values();
    }

    public function revokeSessionById(TaiKhoan $user, string $sessionId, string $reason, ?Request $request = null): void
    {
        if ($sessionId === '') {
            return;
        }

        $device = PhienDangNhap::query()
            ->where('taiKhoanId', $user->taiKhoanId)
            ->where('sessionId', $sessionId)
            ->first();

        DB::table($this->sessionTable())
            ->where('id', $sessionId)
            ->delete();

        if ($device instanceof PhienDangNhap && !$device->isRevoked()) {
            $device->forceFill([
                'revokedAt' => now(),
                'revokeReason' => $reason,
            ])->saveQuietly();
        }

        $this->logEvent(
            user: $user,
            sessionId: $sessionId,
            event: 'session_revoked',
            description: $this->revokeDescription($reason),
            request: $request,
            device: $device,
            data: ['reason' => $reason],
        );
    }

    public function revokeAllSessions(TaiKhoan $user, Request $request, string $reason): int
    {
        $sessionIds = DB::table($this->sessionTable())
            ->where('user_id', $user->getAuthIdentifier())
            ->pluck('id')
            ->filter(fn($id) => is_string($id) && $id !== '')
            ->values();

        $currentSessionId = $request->hasSession() ? (string) $request->session()->getId() : '';

        if ($currentSessionId !== '' && !$sessionIds->contains($currentSessionId)) {
            $sessionIds->push($currentSessionId);
        }

        $sessionIds = $sessionIds->unique()->values();

        if ($sessionIds->isEmpty()) {
            return 0;
        }

        DB::table($this->sessionTable())
            ->whereIn('id', $sessionIds)
            ->delete();

        $devices = PhienDangNhap::query()
            ->where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('sessionId', $sessionIds)
            ->get();

        foreach ($devices as $device) {
            if (!$device->isRevoked()) {
                $device->forceFill([
                    'revokedAt' => now(),
                    'revokeReason' => $reason,
                ])->saveQuietly();
            }
        }

        $this->logEvent(
            user: $user,
            sessionId: $currentSessionId !== '' ? $currentSessionId : null,
            event: 'logout_all_devices',
            description: 'Đăng xuất khỏi tất cả thiết bị.',
            request: $request,
            data: [
                'reason' => $reason,
                'affected_sessions' => $sessionIds->count(),
            ],
        );

        return $sessionIds->count();
    }

    private function buildSessionMetadata(Request $request, TaiKhoan $user): array
    {
        $userAgent = $request->userAgent();
        $parsed = $this->parseUserAgent($userAgent);
        $viaRemember = Auth::guard()->viaRemember();
        $remembered = $viaRemember || (bool) $request->session()->get('auth_remembered', false);

        return [
            'portal' => (string) $request->session()->get('auth_portal', $this->detectPortal($request, $user)),
            'login_method' => (string) $request->session()->get('auth_login_method', $this->fallbackLoginMethod($request, $user)),
            'remembered' => $remembered,
            'via_remember' => $viaRemember,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device_name' => $this->buildDeviceName($parsed['platform'], $parsed['browser']),
            'platform' => $parsed['platform'],
            'browser' => $parsed['browser'],
        ];
    }

    private function fallbackLoginMethod(Request $request, TaiKhoan $user): string
    {
        $storedMethod = $request->session()->get('auth_login_method');

        if (is_string($storedMethod) && $storedMethod !== '') {
            return $storedMethod;
        }

        if ($user->auth_provider === 'google' && Auth::guard()->viaRemember()) {
            return 'google';
        }

        return $user->auth_provider === 'google' ? 'google' : 'password';
    }

    private function detectPortal(Request $request, TaiKhoan $user): string
    {
        if ($request->routeIs('admin.*') || Str::startsWith($request->path(), 'admin')) {
            return 'admin';
        }

        return $user->isStaff() ? 'admin' : 'student';
    }

    private function parseUserAgent(?string $userAgent): array
    {
        $agent = Str::lower((string) $userAgent);

        $platform = match (true) {
            Str::contains($agent, ['iphone']) => 'iPhone',
            Str::contains($agent, ['ipad']) => 'iPad',
            Str::contains($agent, ['android']) => 'Android',
            Str::contains($agent, ['mac os x', 'macintosh']) => 'macOS',
            Str::contains($agent, ['windows']) => 'Windows',
            Str::contains($agent, ['linux']) => 'Linux',
            default => 'Thiết bị khác',
        };

        $browser = match (true) {
            Str::contains($agent, ['edg/']) => 'Microsoft Edge',
            Str::contains($agent, ['opr/', 'opera']) => 'Opera',
            Str::contains($agent, ['firefox/']) => 'Firefox',
            Str::contains($agent, ['chrome/']) && !Str::contains($agent, ['edg/', 'opr/']) => 'Chrome',
            Str::contains($agent, ['safari/']) && !Str::contains($agent, ['chrome/']) => 'Safari',
            default => 'Trình duyệt khác',
        };

        return [
            'platform' => $platform,
            'browser' => $browser,
        ];
    }

    private function buildDeviceName(string $platform, string $browser): string
    {
        return $platform . ' - ' . $browser;
    }

    private function revokeDescription(string $reason): string
    {
        return match ($reason) {
            'logout_current' => 'Đăng xuất thiết bị hiện tại.',
            'manual_revoke' => 'Thu hồi một thiết bị đã đăng nhập.',
            'logout_all_devices' => 'Đăng xuất khỏi tất cả thiết bị.',
            'password_changed' => 'Thu hồi phiên do người dùng đổi mật khẩu.',
            'password_reset' => 'Thu hồi phiên do đặt lại mật khẩu.',
            'admin_password_reset' => 'Thu hồi phiên do quản trị viên đặt lại mật khẩu.',
            default => 'Thu hồi phiên đăng nhập.',
        };
    }

    private function logEvent(
        TaiKhoan $user,
        ?string $sessionId,
        string $event,
        string $description,
        ?Request $request = null,
        ?PhienDangNhap $device = null,
        array $data = []
    ): void {
        NhatKyBaoMat::create([
            'taiKhoanId' => $user->taiKhoanId,
            'phienDangNhapId' => $device?->phienDangNhapId,
            'sessionId' => $sessionId,
            'suKien' => $event,
            'moTa' => $description,
            'ipAddress' => $request?->ip() ?? $device?->ipAddress,
            'userAgent' => $request?->userAgent() ?? $device?->userAgent,
            'duLieu' => $data !== [] ? $data : null,
            'thoiGian' => now(),
        ]);
    }

    private function sessionTable(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
