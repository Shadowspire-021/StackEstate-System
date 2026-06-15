<?php

namespace App\Listeners;

use App\Events\InstallmentOverdue;
use App\Models\User;
use App\Notifications\InstallmentOverdueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendInstallmentOverdueNotification implements ShouldQueue
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
    public function handle(InstallmentOverdue $event): void
    {
        try {
            $installment = $event->installment;

            // Notify all admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'admin']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new InstallmentOverdueNotification($installment));
            }

            Log::info('InstallmentOverdue notification sent', [
                'installment_id' => $installment->id,
                'amount' => $installment->amount,
                'admins_notified' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send InstallmentOverdue notification', [
                'installment_id' => $event->installment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
