<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Course\KhoaHoc;
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
                // Lấy 6 khóa học đang hoạt động
                $courses = KhoaHoc::where('trangThai', 1)->get();
                
                // Truyền biến vào view. 
                // Lưu ý: Tên biến nên đồng nhất để dễ quản lý
                $view->with('footerCourses', $courses);
                $view->with('danhSachKhoaHoc', $courses); 
                }
            );
    }
}
