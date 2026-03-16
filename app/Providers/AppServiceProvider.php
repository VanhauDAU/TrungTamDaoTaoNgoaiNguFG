<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

// ── Auth Contracts ─────────────────────────────────────────────────────────
use App\Contracts\Auth\LoginServiceInterface;
use App\Contracts\Auth\RegisterServiceInterface;
use App\Contracts\Auth\GoogleAuthServiceInterface;

// ── Auth Services ──────────────────────────────────────────────────────────
use App\Services\Auth\LoginService;
use App\Services\Auth\RegisterService;
use App\Services\Auth\GoogleAuthService;

// ── Admin Contracts ────────────────────────────────────────────────────────
use App\Contracts\Admin\KhoaHoc\BuoiHocServiceInterface;
use App\Contracts\Admin\KhoaHoc\CaHocServiceInterface;
use App\Contracts\Admin\KhoaHoc\DanhMucKhoaHocServiceInterface;
use App\Contracts\Admin\KhoaHoc\LopHocServiceInterface;
use App\Contracts\Admin\KhoaHoc\KhoaHocServiceInterface;
use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Contracts\Admin\HocVien\HocVienServiceInterface;
use App\Contracts\Admin\HocVien\DangKyHocServiceInterface;
use App\Contracts\Admin\CoSo\CoSoServiceInterface;
use App\Contracts\Admin\CoSo\PhongHocServiceInterface;
use App\Contracts\Admin\TaiChinh\HoaDonServiceInterface;

// ── Admin Services ─────────────────────────────────────────────────────────
use App\Services\Admin\KhoaHoc\BuoiHocService;
use App\Services\Admin\KhoaHoc\CaHocService;
use App\Services\Admin\KhoaHoc\DanhMucKhoaHocService;
use App\Services\Admin\KhoaHoc\LopHocService;
use App\Services\Admin\KhoaHoc\KhoaHocService;
use App\Services\Admin\NhanVien\NhanSuService;
use App\Services\Admin\HocVien\HocVienService;
use App\Services\Admin\HocVien\DangKyHocService;
use App\Services\Admin\CoSo\CoSoService;
use App\Services\Admin\CoSo\PhongHocService;
use App\Services\Admin\TaiChinh\HoaDonService;

// ── Client Contracts ──────────────────────────────────────────────
use App\Contracts\Client\KhoaHoc\CourseServiceInterface;
use App\Contracts\Client\HocVien\StudentServiceInterface;

// ── Client Services ──────────────────────────────────────────────
use App\Services\Client\KhoaHoc\CourseService;
use App\Services\Client\HocVien\StudentService;

// ── Phase 8 Contracts ─────────────────────────────────────────────
use App\Contracts\Admin\BaiViet\BaiVietServiceInterface;
use App\Contracts\Admin\LienHe\LienHeServiceInterface;
use App\Contracts\Admin\ThongBao\ThongBaoServiceInterface;

// ── Phase 8 Services ─────────────────────────────────────────────
use App\Services\Admin\BaiViet\BaiVietService;
use App\Services\Admin\LienHe\LienHeService;
use App\Services\Admin\ThongBao\ThongBaoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * Đăng ký Interface → Implementation binding.
     */
    public function register(): void
    {
        // ── Phase 1: Auth ──────────────────────────────────────────────────
        $this->app->bind(LoginServiceInterface::class , LoginService::class);
        $this->app->bind(RegisterServiceInterface::class , RegisterService::class);
        $this->app->bind(GoogleAuthServiceInterface::class , GoogleAuthService::class);

        // ── Phase 2: Admin/KhoaHoc ─────────────────────────────────────────
        $this->app->bind(DanhMucKhoaHocServiceInterface::class , DanhMucKhoaHocService::class);
        $this->app->bind(CaHocServiceInterface::class , CaHocService::class);
        $this->app->bind(BuoiHocServiceInterface::class , BuoiHocService::class);
        $this->app->bind(LopHocServiceInterface::class , LopHocService::class);
        $this->app->bind(KhoaHocServiceInterface::class , KhoaHocService::class);

        // ── Phase 3: Admin/User ─────────────────────────────────────────
        $this->app->bind(NhanSuServiceInterface::class , NhanSuService::class);
        $this->app->bind(HocVienServiceInterface::class , HocVienService::class);
        $this->app->bind(DangKyHocServiceInterface::class , DangKyHocService::class);

        // ── Phase 4: Admin/CoSo, TaiChinh ──────────────────────────────────
        $this->app->bind(CoSoServiceInterface::class , CoSoService::class);
        $this->app->bind(PhongHocServiceInterface::class , PhongHocService::class);
        $this->app->bind(HoaDonServiceInterface::class , HoaDonService::class);

        // ── Phase 5: Client ──────────────────────────────────────────────
        $this->app->bind(CourseServiceInterface::class , CourseService::class);
        $this->app->bind(StudentServiceInterface::class , StudentService::class);

        // ── Phase 8: BaiViet, LienHe, ThongBao ───────────────────────────────
        $this->app->bind(BaiVietServiceInterface::class , BaiVietService::class);
        $this->app->bind(LienHeServiceInterface::class , LienHeService::class);
        $this->app->bind(ThongBaoServiceInterface::class , ThongBaoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $this->registerAuthRateLimiters();

        View::composer(
        ['components.client.footer', 'components.client.register-advice'],
            function ($view) {
            $courses = KhoaHoc::where('trangThai', 1)->get();
            $branches = CoSoDaoTao::where('trangThai', 1)->get();
            $view->with('footerCourses', $courses);
            $view->with('danhSachKhoaHoc', $courses);
            $view->with('danhSachCoSo', $branches);
        }
        );
    }

    private function registerAuthRateLimiters(): void
    {
        RateLimiter::for('auth-login', function (Request $request) {
            $portal = match ($request->route()?->getName()) {
                'teacher.login.submit' => 'teacher',
                'staff.login.submit', 'admin.login.submit' => 'staff',
                default => 'student',
            };

            $loginInput = mb_strtolower(trim((string) $request->input('taiKhoan', '')));
            $ip = (string) $request->ip();
            $limits = config('auth.rate_limiters.login');

            return [
                Limit::perMinute((int) ($limits['per_minute'] ?? 12))
                    ->by("auth-login:{$portal}:{$ip}:{$loginInput}")
                    ->response(fn (Request $request, array $headers) => $this->buildThrottleResponse(
                        $request,
                        'taiKhoan',
                        'Bạn thao tác đăng nhập quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                        $headers
                    )),
                Limit::perMinute((int) ($limits['per_ip_per_minute'] ?? 30))
                    ->by("auth-login-ip:{$portal}:{$ip}")
                    ->response(fn (Request $request, array $headers) => $this->buildThrottleResponse(
                        $request,
                        'taiKhoan',
                        'Bạn thao tác đăng nhập quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                        $headers
                    )),
            ];
        });

        RateLimiter::for('auth-register', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $ip = (string) $request->ip();
            $limits = config('auth.rate_limiters.register');

            return [
                Limit::perMinute((int) ($limits['per_minute'] ?? 6))
                    ->by("auth-register:{$ip}:{$email}")
                    ->response(fn (Request $request, array $headers) => $this->buildThrottleResponse(
                        $request,
                        'email',
                        'Bạn gửi đăng ký quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                        $headers
                    )),
                Limit::perMinute((int) ($limits['per_ip_per_minute'] ?? 12))
                    ->by("auth-register-ip:{$ip}")
                    ->response(fn (Request $request, array $headers) => $this->buildThrottleResponse(
                        $request,
                        'email',
                        'Bạn gửi đăng ký quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                        $headers
                    )),
            ];
        });

        RateLimiter::for('auth-email-check', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->query('email', '')));
            $ip = (string) $request->ip();
            $limits = config('auth.rate_limiters.email_check');

            return [
                Limit::perMinute((int) ($limits['per_minute'] ?? 30))
                    ->by("auth-email-check:{$ip}:{$email}")
                    ->response(fn (Request $request, array $headers) => response()->json([
                        'status' => 'throttled',
                        'message' => 'Bạn kiểm tra email quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                    ], 429, $headers)),
                Limit::perMinute((int) ($limits['per_ip_per_minute'] ?? 120))
                    ->by("auth-email-check-ip:{$ip}")
                    ->response(fn (Request $request, array $headers) => response()->json([
                        'status' => 'throttled',
                        'message' => 'Bạn kiểm tra email quá nhanh. Vui lòng chờ một chút rồi thử lại.',
                    ], 429, $headers)),
            ];
        });
    }

    private function buildThrottleResponse(Request $request, string $field, string $message, array $headers = [])
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 429, $headers);
        }

        return redirect()
            ->back()
            ->withHeaders($headers)
            ->withInput($request->except(['password', 'password_confirmation', 'recaptcha_token']))
            ->withErrors([$field => [$message]]);
    }
}
