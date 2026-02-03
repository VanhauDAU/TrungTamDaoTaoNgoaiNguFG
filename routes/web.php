<?php

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\AboutController;
use App\Http\Controllers\Client\CourseController;
use App\Http\Controllers\Client\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::prefix('/')->name('home.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::prefix('lien-he')->name('contact.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
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
        Route::get('/{slug}/lop-hoc/{slugLopHoc}', [CourseController::class, 'showClass'])->name('show');
        Route::get('/{slug}/lop-hoc/{slugLopHoc}/dang-ky', [CourseController::class, 'confirmRegistration'])->name('confirm');
        Route::post('/{slug}/lop-hoc/{slugLopHoc}/xac-nhan-dang-ky', [CourseController::class, 'processRegistration'])->name('process');
    });
    Route::prefix('hoc-vien')->name('student.')->middleware('auth')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/doi-mat-khau', [StudentController::class, 'changePassword'])->name('change-password');
        Route::post('/doi-mat-khau', [StudentController::class, 'updatePassword'])->name('update-password');
    });
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
