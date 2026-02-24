<?php

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\AboutController;
use App\Http\Controllers\Client\CourseController;
use App\Http\Controllers\Client\StudentController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\NhomQuyenController;
use App\Http\Controllers\Admin\HocVien\HocVienController as AdminHocVienController;
use App\Http\Controllers\Admin\GiaoVien\GiaoVienController as AdminGiaoVienController;
use App\Http\Controllers\Admin\Auth\TaiKhoanController;
use App\Http\Controllers\Admin\Facility\CoSoController;
use App\Http\Controllers\Admin\Facility\PhongHocController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ─── API ROUTES (Public) ────────────────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    // Lấy danh sách phường/xã theo tỉnh (từ provinces.open-api.vn proxy)
    Route::get('/phuong-xa/{maTinh}', [CoSoController::class, 'getPhuongXa'])->name('phuongxa');
    // Danh sách cơ sở có filter (dùng cho client contact page)
    Route::get('/co-so', [CoSoController::class, 'apiList'])->name('coso');
});

// ─── CLIENT ROUTES ──────────────────────────────────────────────────────────
Route::prefix('/')->name('home.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::prefix('lien-he')->name('contact.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/tu-van', [ContactController::class, 'storeConsultation'])->name('consultation.store');
    });
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    });
    Route::prefix('ve-chung-toi')->name('about.')->group(function () {
        Route::get('/', [AboutController::class, 'index'])->name('index');
    });
    Route::prefix('khoa-hoc')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{slug}', [CourseController::class, 'show'])->name('show');
    });
    Route::prefix('lop-hoc')->name('classes.')->group(function () {
        Route::get('/{slug}/{slugLopHoc}', [CourseController::class, 'showClass'])->name('show');
        Route::get('/{slug}/{slugLopHoc}/dang-ky', [CourseController::class, 'confirmRegistration'])->name('confirm');
        Route::post('/{slug}/{slugLopHoc}/xac-nhan-dang-ky', [CourseController::class, 'processRegistration'])->name('process');
    });
    Route::prefix('hoc-vien')->name('student.')->middleware('auth')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/doi-mat-khau', [StudentController::class, 'changePassword'])->name('change-password');
        Route::post('/doi-mat-khau', [StudentController::class, 'updatePassword'])->name('update-password');
        Route::get('/hoa-don', [StudentController::class, 'invoices'])->name('invoices');
        Route::get('/hoa-don/{id}', [StudentController::class, 'invoiceDetail'])->name('invoices.show');
        Route::get('/lop-hoc', [StudentController::class, 'myClasses'])->name('classes');
    });
});

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/dashboard', [AdminHomeController::class, 'index'])->name('dashboard');

    // ── Phân quyền (chỉ Admin role=3 mới vào được) ──────────────────────────
    Route::prefix('phan-quyen')->name('phan-quyen.')->group(function () {
        Route::get('/',           [NhomQuyenController::class, 'index'])->name('index');
        Route::get('/tao-moi',    [NhomQuyenController::class, 'create'])->name('create');
        Route::post('/',          [NhomQuyenController::class, 'store'])->name('store');
        Route::get('/{id}/sua',   [NhomQuyenController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [NhomQuyenController::class, 'update'])->name('update');
        Route::delete('/{id}',    [NhomQuyenController::class, 'destroy'])->name('destroy');
    });

    // ── Danh sách Tài khoản Hệ thống ──────────────────────────────────────────
    Route::prefix('tai-khoan')->name('tai-khoan.')->group(function () {
        Route::get('/', [TaiKhoanController::class, 'index'])->name('index');
        Route::post('/{id}/nhom-quyen', [TaiKhoanController::class, 'updateNhomQuyen'])->name('update-nhom-quyen');
        Route::post('/{id}/toggle-status', [TaiKhoanController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/reset-password', [TaiKhoanController::class, 'resetPassword'])->name('reset-password');
    });

    // ── Học viên ─────────────────────────────────────────────────────────────
    Route::prefix('hoc-vien')->name('hoc-vien.')->group(function () {
        Route::get('/',                    [AdminHocVienController::class, 'index'])->name('index');
        Route::get('/tao-moi',             [AdminHocVienController::class, 'create'])->name('create');
        Route::post('/',                   [AdminHocVienController::class, 'store'])->name('store');
        Route::get('/thung-rac',           [AdminHocVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc',    [AdminHocVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}/sua',      [AdminHocVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}',          [AdminHocVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}',       [AdminHocVienController::class, 'destroy'])->name('destroy');
    });

    // ── Giáo viên ────────────────────────────────────────────────────────────
    Route::prefix('giao-vien')->name('giao-vien.')->group(function () {
        Route::get('/',                    [AdminGiaoVienController::class, 'index'])->name('index');
        Route::get('/tao-moi',             [AdminGiaoVienController::class, 'create'])->name('create');
        Route::post('/',                   [AdminGiaoVienController::class, 'store'])->name('store');
        Route::get('/thung-rac',           [AdminGiaoVienController::class, 'trash'])->name('trash');
        Route::patch('/{id}/khoi-phuc',    [AdminGiaoVienController::class, 'restore'])->name('restore');
        Route::get('/{taiKhoan}/sua',      [AdminGiaoVienController::class, 'edit'])->name('edit');
        Route::put('/{taiKhoan}',          [AdminGiaoVienController::class, 'update'])->name('update');
        Route::delete('/{taiKhoan}',       [AdminGiaoVienController::class, 'destroy'])->name('destroy');
    });

    // ── Cơ sở Đào tạo ────────────────────────────────────────────────────────
    Route::prefix('co-so')->name('co-so.')->group(function () {
        Route::get('/',           [CoSoController::class, 'index'])->name('index');
        Route::get('/tao-moi',    [CoSoController::class, 'create'])->name('create');
        Route::post('/',          [CoSoController::class, 'store'])->name('store');
        Route::get('/{id}',       [CoSoController::class, 'show'])->name('show');
        Route::get('/{id}/sua',   [CoSoController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [CoSoController::class, 'update'])->name('update');
        Route::delete('/{id}',    [CoSoController::class, 'destroy'])->name('destroy');
    });

    // ── Phòng Học ─────────────────────────────────────────────────────────────
    Route::prefix('phong-hoc')->name('phong-hoc.')->group(function () {
        Route::post('/',       [PhongHocController::class, 'store'])->name('store');
        Route::put('/{id}',    [PhongHocController::class, 'update'])->name('update');
        Route::delete('/{id}', [PhongHocController::class, 'destroy'])->name('destroy');
    });
});

// ─── AUTH ROUTES ─────────────────────────────────────────────────────────────
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
