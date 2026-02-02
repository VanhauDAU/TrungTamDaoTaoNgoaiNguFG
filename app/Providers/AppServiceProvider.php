<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Course\KhoaHoc;
use App\Models\Facility\CoSoDaoTao;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        //
        View::composer(
            ['components.client.footer', 'components.client.register-advice'], 
            function ($view) {
                $courses = KhoaHoc::where('trangThai', 1)->get();
                $branches = CoSoDaoTao::where('trangThai',1)->get();
                $view->with('footerCourses', $courses);
                $view->with('danhSachKhoaHoc', $courses); 
                $view->with('danhSachCoSo', $branches);
                }
            );
    }
}
