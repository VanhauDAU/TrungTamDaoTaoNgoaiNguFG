<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class QueuedResetPasswordNotification extends ResetPassword implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(string $token)
    {
        parent::__construct($token);
    }
}
