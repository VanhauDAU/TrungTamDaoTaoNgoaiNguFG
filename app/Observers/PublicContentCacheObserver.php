<?php

namespace App\Observers;

use App\Services\Client\PublicContentCacheService;
use Illuminate\Database\Eloquent\Model;

class PublicContentCacheObserver
{
    public function saved(Model $model): void
    {
        $changedFields = array_diff(array_keys($model->getChanges()), ['updated_at', 'luotXem']);

        if ($changedFields === []) {
            return;
        }

        $this->bust();
    }

    public function deleted(Model $model): void
    {
        $this->bust();
    }

    public function restored(Model $model): void
    {
        $this->bust();
    }

    public function forceDeleted(Model $model): void
    {
        $this->bust();
    }

    private function bust(): void
    {
        app(PublicContentCacheService::class)->bust();
    }
}
