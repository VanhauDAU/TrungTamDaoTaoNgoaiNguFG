<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\AboutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::prefix('/')->name('home.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::prefix('lien-he')->name('lienhe.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
    });
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
    });
    Route::prefix('ve-chung-toi')->name('about.')->group(function () {
        Route::get('/', [AboutController::class, 'index'])->name('index');
    });
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
