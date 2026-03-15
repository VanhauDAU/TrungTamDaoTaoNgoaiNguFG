<?php

use App\Console\Commands\GuiThongBaoLenLich;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoice:check-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/invoice-check-overdue.log'));

Schedule::command('registration:expire-holds')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/registration-expire-holds.log'));
