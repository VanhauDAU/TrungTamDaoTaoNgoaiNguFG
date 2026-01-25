<?php

use App\Http\Controllers\Client\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
Route::prefix('/')->name('home.')->group(function(){
    Route::get('/', [HomeController::class, 'index'])->name('home');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
