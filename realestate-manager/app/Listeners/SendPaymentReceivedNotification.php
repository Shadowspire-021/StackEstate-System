<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentReceivedNotification implements ShouldQueue
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function handle(PaymentReceived $event): void
    {
        try {
            $payment = $event->payment;

            // Generate invoice for this payment
            if ($payment->installment && $payment->client) {
                try {
                    $this->invoiceService->createFromPayment($payment);
                    Log::info('Invoice generated from payment', [
                        'payment_id' => $payment->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate invoice from payment', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Notify all admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'admin']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new PaymentReceivedNotification($payment));
            }

            Log::info('PaymentReceived notification sent', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'admins_notified' => $admins->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send PaymentReceived notification', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
