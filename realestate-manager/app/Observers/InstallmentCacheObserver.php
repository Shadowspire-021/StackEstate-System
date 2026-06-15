<?php

namespace App\Observers;

use App\Models\Installment;
use App\Services\CacheService;

class InstallmentCacheObserver
{
    public function saved(Installment $installment): void
    {
        $this->invalidate();
    }

    public function deleted(Installment $installment): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        CacheService::invalidateByPrefix(CacheService::PREFIX_DASHBOARD);
        CacheService::invalidateByPrefix(CacheService::PREFIX_SEARCH);
    }
}