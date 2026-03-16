<?php

namespace Tests\Feature;

use App\Contracts\Auth\RegisterServiceInterface;
use Mockery;
use Tests\TestCase;

class RegisterEmailCheckTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'cache.limiter' => 'array',
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

    public function test_check_email_endpoint_returns_service_payload(): void
    {
        $service = Mockery::mock(RegisterServiceInterface::class);
        $service->shouldReceive('checkEmailAvailability')
            ->once()
            ->with('student@example.com')
            ->andReturn([
                'status' => 'taken',
                'message' => 'Email này đã được sử dụng.',
            ]);

        $this->app->instance(RegisterServiceInterface::class, $service);

        $response = $this->getJson(route('register.check-email', ['email' => 'student@example.com']));

        $response->assertOk()
            ->assertJson([
                'status' => 'taken',
                'message' => 'Email này đã được sử dụng.',
            ]);
    }
}
