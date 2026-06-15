<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Installment;
use App\Models\Property;
use App\Models\Unit;
use App\Observers\ClientCacheObserver;
use App\Observers\PaymentCacheObserver;
use App\Observers\InstallmentCacheObserver;
use App\Observers\PropertyCacheObserver;
use App\Observers\UnitCacheObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Clear cached permissions on every request to ensure fresh DB state.
        // The PermissionServiceProvider only clears the in-memory collection,
        // NOT the file-based cache. This causes stale permission data to persist
        // across requests, resulting in 403 errors for authorized users.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (config('queue.default') === 'sync') {
            \Log::warning('Queue driver is "sync" — queued jobs block requests. Set QUEUE_CONNECTION=database in .env for async processing.');
        }

        $mailHost = Setting::getValue('mail_host');
        if ($mailHost) {
            config([
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => Setting::getValue('mail_port', '587'),
                'mail.mailers.smtp.username' => Setting::getValue('mail_username'),
                'mail.mailers.smtp.password' => Setting::getValue('mail_password'),
                'mail.mailers.smtp.encryption' => Setting::getValue('mail_encryption', 'tls'),
                'mail.from.address' => Setting::getValue('notification_email_from', config('mail.from.address')),
                'mail.from.name' => Setting::getValue('notification_email_name', config('mail.from.name')),
            ]);
        }

        // Register cache invalidation observers
        Client::observe(ClientCacheObserver::class);
        Payment::observe(PaymentCacheObserver::class);
        Installment::observe(InstallmentCacheObserver::class);
        Property::observe(PropertyCacheObserver::class);
        Unit::observe(UnitCacheObserver::class);
    }
}
