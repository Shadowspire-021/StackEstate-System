<?php

namespace App\Observers;

use App\Models\Property;
use App\Services\CacheService;

class PropertyCacheObserver
{
    public function saved(Property $property): void
    {
        $this->invalidate();
    }

    public function deleted(Property $property): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        CacheService::invalidateByPrefix(CacheService::PREFIX_DASHBOARD);
        CacheService::invalidateByPrefix(CacheService::PREFIX_SEARCH);
    }
}