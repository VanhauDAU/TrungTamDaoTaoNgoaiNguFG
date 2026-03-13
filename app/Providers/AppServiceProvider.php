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
use App\Contracts\Admin\BuoiHocServiceInterface;
use App\Contracts\Admin\CaHocServiceInterface;
use App\Contracts\Admin\DanhMucKhoaHocServiceInterface;
use App\Contracts\Admin\LopHocServiceInterface;
use App\Contracts\Admin\KhoaHocServiceInterface;
use App\Contracts\Admin\HocPhiServiceInterface;
use App\Contracts\Admin\NhanSuServiceInterface;
use App\Contracts\Admin\HocVienServiceInterface;
use App\Contracts\Admin\CoSoServiceInterface;
use App\Contracts\Admin\PhongHocServiceInterface;
use App\Contracts\Admin\HoaDonServiceInterface;

// ── Admin Services ─────────────────────────────────────────────────────────
use App\Services\Admin\BuoiHocService;
use App\Services\Admin\CaHocService;
use App\Services\Admin\DanhMucKhoaHocService;
use App\Services\Admin\LopHocService;
use App\Services\Admin\KhoaHocService;
use App\Services\Admin\HocPhiService;
use App\Services\Admin\NhanSuService;
use App\Services\Admin\HocVienService;
use App\Services\Admin\CoSoService;
use App\Services\Admin\PhongHocService;
use App\Services\Admin\HoaDonService;

// ── Client Contracts ──────────────────────────────────────────────
 use App\Contracts\Client\CourseServiceInterface;
use App\Contracts\Client\StudentServiceInterface;

// ── Client Services ──────────────────────────────────────────────
use App\Services\Client\CourseService;
use App\Services\Client\StudentService;

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
        $this->app->bind(DanhMucKhoaHocServiceInterface::class, DanhMucKhoaHocService::class);
        $this->app->bind(CaHocServiceInterface::class, CaHocService::class);
        $this->app->bind(BuoiHocServiceInterface::class, BuoiHocService::class);
        $this->app->bind(HocPhiServiceInterface::class, HocPhiService::class);
        $this->app->bind(LopHocServiceInterface::class, LopHocService::class);
        $this->app->bind(KhoaHocServiceInterface::class, KhoaHocService::class);

        // ── Phase 3: Admin/User ─────────────────────────────────────────
        $this->app->bind(NhanSuServiceInterface::class, NhanSuService::class);
        $this->app->bind(HocVienServiceInterface::class, HocVienService::class);

        // ── Phase 4: Admin/CoSo, TaiChinh ──────────────────────────────────
        $this->app->bind(CoSoServiceInterface::class, CoSoService::class);
        $this->app->bind(PhongHocServiceInterface::class, PhongHocService::class);
        $this->app->bind(HoaDonServiceInterface::class, HoaDonService::class);

        // ── Phase 5: Client ──────────────────────────────────────────────
        $this->app->bind(CourseServiceInterface::class, CourseService::class);
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
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
