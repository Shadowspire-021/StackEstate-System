<?php

namespace App\Observers;

use App\Models\Client;
use App\Services\CacheService;

class ClientCacheObserver
{
    public function saved(Client $client): void
    {
        $this->invalidate();
    }

    public function deleted(Client $client): void
    {
        $this->invalidate();
    }

    public function restored(Client $client): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        CacheService::invalidateByPrefix(CacheService::PREFIX_DASHBOARD);
        CacheService::invalidateByPrefix(CacheService::PREFIX_SEARCH);
    }
}