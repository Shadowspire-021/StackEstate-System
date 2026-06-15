<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

class CreateInvoiceOnPayment
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function handle(PaymentReceived $event): void
    {
        try {
            $payment = $event->payment;

            if (!$payment->installment) {
                Log::debug('CreateInvoiceOnPayment: Payment has no installment link, skipping invoice.', [
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            $invoice = $this->invoiceService->createFromPayment($payment);

            if ($invoice) {
                Log::info('Invoice auto-generated from payment', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            } else {
                Log::warning('CreateInvoiceOnPayment returned null despite installment link', [
                    'payment_id' => $payment->id,
                    'installment_id' => $payment->installment?->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('CreateInvoiceOnPayment failed', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
