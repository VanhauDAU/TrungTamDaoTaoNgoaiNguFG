<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class QueuedVerifyEmailNotification extends VerifyEmail implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;
}
