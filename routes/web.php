<?php

use App\Http\Controllers\Client\HomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->name('home.')->group(function(){
    Route::get('/', [HomeController::class, 'index'])->name('home');
});