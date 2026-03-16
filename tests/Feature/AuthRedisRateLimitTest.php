<?php

namespace Tests\Feature;

use App\Contracts\Auth\RegisterServiceInterface;
use Mockery;
use Tests\TestCase;

class AuthRedisRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'cache.limiter' => 'array',
            'auth.rate_limiters.login.per_minute' => 1,
            'auth.rate_limiters.login.per_ip_per_minute' => 1,
            'auth.rate_limiters.register.per_minute' => 1,
            'auth.rate_limiters.register.per_ip_per_minute' => 1,
            'auth.rate_limiters.email_check.per_minute' => 1,
            'auth.rate_limiters.email_check.per_ip_per_minute' => 1,
        ]);

        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance('cache.rateLimiter');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_login_is_rate_limited_after_repeated_attempts(): void
    {
        $payload = [
            'taiKhoan' => 'hocvien@example.com',
            'password' => '12345678',
        ];

        $this->from('/login')->post(route('login'), $payload);

        $response = $this->from('/login')->post(route('login'), $payload);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'taiKhoan' => 'Bạn thao tác đăng nhập quá nhanh. Vui lòng chờ một chút rồi thử lại.',
        ]);
    }

    public function test_register_is_rate_limited_after_repeated_attempts(): void
    {
        $payload = [
            'name' => 'Hoc Vien Moi',
            'email' => 'hocvienmoi@example.com',
            'phone' => '0901234567',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $this->from('/register')->post(route('register'), $payload);

        $response = $this->from('/register')->post(route('register'), $payload);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'Bạn gửi đăng ký quá nhanh. Vui lòng chờ một chút rồi thử lại.',
        ]);
    }

    public function test_email_check_is_rate_limited_after_repeated_attempts(): void
    {
        $service = Mockery::mock(RegisterServiceInterface::class);
        $service->shouldReceive('checkEmailAvailability')
            ->once()
            ->with('student@example.com')
            ->andReturn([
                'status' => 'available',
                'message' => 'Email này có thể sử dụng.',
            ]);

        $this->app->instance(RegisterServiceInterface::class, $service);

        $this->getJson(route('register.check-email', ['email' => 'student@example.com']))
            ->assertOk();

        $response = $this->getJson(route('register.check-email', ['email' => 'student@example.com']));

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'throttled',
                'message' => 'Bạn kiểm tra email quá nhanh. Vui lòng chờ một chút rồi thử lại.',
            ]);
    }
}
