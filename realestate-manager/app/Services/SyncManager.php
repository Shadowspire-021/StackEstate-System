<?php

namespace App\Services;

use App\Jobs\SyncToGoogleSheetJob;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;

class SyncManager
{
    protected const DEBOUNCE_SECONDS = 60;

    public static function trigger(Client $client): void
    {
        $lockKey = 'gs_sync_lock_' . $client->id;

        if (Cache::has($lockKey)) {
            return;
        }

        Cache::put($lockKey, true, now()->addSeconds(self::DEBOUNCE_SECONDS));

        SyncToGoogleSheetJob::dispatch($client)
            ->delay(now()->addSeconds(5));
    }
}
