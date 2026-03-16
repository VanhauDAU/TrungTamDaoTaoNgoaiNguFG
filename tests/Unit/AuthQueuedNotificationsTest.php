<?php

namespace Tests\Unit;

use App\Models\Auth\TaiKhoan;
use App\Notifications\Auth\QueuedResetPasswordNotification;
use App\Notifications\Auth\QueuedVerifyEmailNotification;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthQueuedNotificationsTest extends TestCase
{
    public function test_email_verification_notification_is_queued_after_commit(): void
    {
        Queue::fake();

        $user = new TaiKhoan([
            'email' => 'hocvien@example.com',
            'taiKhoan' => 'HV000001',
            'role' => TaiKhoan::ROLE_HOC_VIEN,
        ]);
        $user->setAttribute('taiKhoanId', 1);

        $user->sendEmailVerificationNotification();

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($user) {
            return $job->notification instanceof QueuedVerifyEmailNotification
                && $job->afterCommit === true
                && (int) optional($job->notifiables->first())->taiKhoanId === (int) $user->taiKhoanId;
        });
    }

    public function test_password_reset_notification_is_queued_after_commit(): void
    {
        Queue::fake();

        $user = new TaiKhoan([
            'email' => 'hocvien@example.com',
            'taiKhoan' => 'HV000001',
            'role' => TaiKhoan::ROLE_HOC_VIEN,
        ]);
        $user->setAttribute('taiKhoanId', 1);

        $user->sendPasswordResetNotification('queued-reset-token');

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($user) {
            return $job->notification instanceof QueuedResetPasswordNotification
                && $job->notification->token === 'queued-reset-token'
                && $job->afterCommit === true
                && (int) optional($job->notifiables->first())->taiKhoanId === (int) $user->taiKhoanId;
        });
    }
}
