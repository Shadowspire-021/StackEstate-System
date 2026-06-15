<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use App\Models\User;
use App\Notifications\ClientCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendClientCreatedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClientCreated $event): void
    {
        try {
            $client = $event->client;

            // Notify all admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'admin']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new ClientCreatedNotification($client));
            }

            Log::info('ClientCreated notification sent', [
                'client_id' => $client->id,
                'admins_notified' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ClientCreated notification', [
                'client_id' => $event->client->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
