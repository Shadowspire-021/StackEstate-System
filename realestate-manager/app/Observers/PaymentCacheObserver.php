<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\CacheService;

class PaymentCacheObserver
{
    public function created(Payment $payment): void
    {
        $this->invalidate();
    }

    public function deleted(Payment $payment): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        CacheService::invalidateByPrefix(CacheService::PREFIX_DASHBOARD);
        CacheService::invalidateByPrefix(CacheService::PREFIX_SEARCH);
    }
}