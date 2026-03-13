<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Pagination\Paginator;

// ── Auth Contracts ─────────────────────────────────────────────────────────
use App\Contracts\Auth\LoginServiceInterface;
use App\Contracts\Auth\RegisterServiceInterface;
use App\Contracts\Auth\GoogleAuthServiceInterface;

// ── Auth Services ──────────────────────────────────────────────────────────
use App\Services\Auth\LoginService;
use App\Services\Auth\RegisterService;
use App\Services\Auth\GoogleAuthService;

// ── Admin Contracts ────────────────────────────────────────────────────────
use App\Contracts\Admin\LopHocServiceInterface;
use App\Contracts\Admin\KhoaHocServiceInterface;
use App\Contracts\Admin\NhanSuServiceInterface;
use App\Contracts\Admin\HocVienServiceInterface;

// ── Admin Services ─────────────────────────────────────────────────────────
use App\Services\Admin\LopHocService;
use App\Services\Admin\KhoaHocService;
use App\Services\Admin\NhanSuService;
use App\Services\Admin\HocVienService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * Đăng ký Interface → Implementation binding.
     */
    public function register(): void
    {
        // ── Phase 1: Auth ──────────────────────────────────────────────────
        $this->app->bind(LoginServiceInterface::class, LoginService::class);
        $this->app->bind(RegisterServiceInterface::class, RegisterService::class);
        $this->app->bind(GoogleAuthServiceInterface::class, GoogleAuthService::class);

        // ── Phase 2: Admin/KhoaHoc ─────────────────────────────────────────
        $this->app->bind(LopHocServiceInterface::class, LopHocService::class);
        $this->app->bind(KhoaHocServiceInterface::class, KhoaHocService::class);

        // ── Phase 3: Admin/User ─────────────────────────────────────────
        $this->app->bind(NhanSuServiceInterface::class, NhanSuService::class);
        $this->app->bind(HocVienServiceInterface::class, HocVienService::class);───

        // ── Phase 4: Admin/CoSo, TaiChinh, BaiViet (sẽ bổ sung) ──────────

        // ── Phase 5: Client (sẽ bổ sung) ──────────────────────────────────
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer(
            ['components.client.footer', 'components.client.register-advice'],
            function ($view) {
                $courses  = KhoaHoc::where('trangThai', 1)->get();
                $branches = CoSoDaoTao::where('trangThai', 1)->get();
                $view->with('footerCourses', $courses);
                $view->with('danhSachKhoaHoc', $courses);
                $view->with('danhSachCoSo', $branches);
            }
        );
    }
}
