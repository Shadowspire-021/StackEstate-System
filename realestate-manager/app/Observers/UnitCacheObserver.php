<?php

namespace App\Observers;

use App\Models\Unit;
use App\Services\CacheService;

class UnitCacheObserver
{
    public function saved(Unit $unit): void
    {
        $this->invalidate();
    }

    public function deleted(Unit $unit): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        CacheService::invalidateByPrefix(CacheService::PREFIX_UNIT_STATS);
        CacheService::invalidateByPrefix(CacheService::PREFIX_SEARCH);
    }
}