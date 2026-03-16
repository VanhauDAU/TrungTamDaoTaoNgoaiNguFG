<?php

namespace Tests\Unit;

use App\Observers\PublicContentCacheObserver;
use App\Services\Client\PublicContentCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PublicContentCacheServiceTest extends TestCase
{
    private PublicContentCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'cache.public_lists.store' => 'array',
            'cache.public_lists.ttl' => 60,
            'cache.public_lists.version_key' => 'tests:public-content:version',
        ]);

        Cache::store('array')->flush();

        $this->service = app(PublicContentCacheService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_reuses_cached_payload_for_the_same_context(): void
    {
        $calls = 0;

        $first = $this->service->remember('courses.index', ['page' => 1, 'sort' => 'newest'], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $second = $this->service->remember('courses.index', ['page' => 1, 'sort' => 'newest'], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $this->assertSame(['calls' => 1], $first);
        $this->assertSame(['calls' => 1], $second);
        $this->assertSame(1, $calls);
    }

    public function test_it_normalizes_context_keys_before_generating_cache_keys(): void
    {
        $calls = 0;

        $first = $this->service->remember('blog.index', ['sort' => 'latest', 'page' => 2], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $second = $this->service->remember('blog.index', ['page' => 2, 'sort' => 'latest'], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }

    public function test_bust_forces_the_next_read_to_rebuild_the_payload(): void
    {
        $calls = 0;

        $this->service->remember('shared.footer', [], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $this->service->bust();

        $result = $this->service->remember('shared.footer', [], function () use (&$calls) {
            $calls++;

            return ['calls' => $calls];
        });

        $this->assertSame(['calls' => 2], $result);
        $this->assertSame(2, $calls);
    }

    public function test_public_cache_observer_ignores_view_counter_updates(): void
    {
        $cacheService = Mockery::mock(PublicContentCacheService::class);
        $cacheService->shouldNotReceive('bust');
        app()->instance(PublicContentCacheService::class, $cacheService);

        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getChanges')->andReturn([
            'luotXem' => 99,
            'updated_at' => now(),
        ]);

        (new PublicContentCacheObserver())->saved($model);
        $this->assertTrue(true);
    }

    public function test_public_cache_observer_busts_when_public_content_changes(): void
    {
        $cacheService = Mockery::mock(PublicContentCacheService::class);
        $cacheService->shouldReceive('bust')->once();
        app()->instance(PublicContentCacheService::class, $cacheService);

        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getChanges')->andReturn([
            'tenKhoaHoc' => 'IELTS Master',
            'updated_at' => now(),
        ]);

        (new PublicContentCacheObserver())->saved($model);
        $this->assertTrue(true);
    }
}
