<?php

namespace App\Listeners;

use App\Events\InstallmentUpcomingDue;
use App\Models\User;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentReminderNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(InstallmentUpcomingDue $event): void
    {
        try {
            $installment = $event->installment;
            $daysUntilDue = (int) now()->diffInDays($installment->due_date, false);

            // Notify all admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'admin']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new PaymentReminderNotification($installment, $daysUntilDue));
            }

            Log::info('PaymentReminder notification sent', [
                'installment_id' => $installment->id,
                'amount' => $installment->amount,
                'due_date' => $installment->due_date->toDateString(),
                'days_until_due' => $daysUntilDue,
                'admins_notified' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send PaymentReminder notification', [
                'installment_id' => $event->installment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
