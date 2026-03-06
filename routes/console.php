<?php

use App\Console\Commands\GuiThongBaoLenLich;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động gửi thông báo đã lên lịch, chạy mỗi phút
Schedule::command(GuiThongBaoLenLich::class)->everyMinute()->withoutOverlapping();
