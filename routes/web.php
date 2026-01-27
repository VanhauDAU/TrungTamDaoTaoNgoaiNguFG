<?php

use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
Route::prefix('/')->name('home.')->group(function(){
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::prefix('lienhe')->name('lienhe.')->group(function () {
        Route::get('/', [ContactController::class,'index'])->name('index');
    });
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
