<?php

namespace App\Services;

use App\Helpers\InvoiceNumberHelper;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create an invoice from a payment.
     *
     * Duplicate-safe: if an invoice already exists for this payment, it is returned.
     * Uses original_amount (immutable) instead of amount (reduced by payments).
     * Handles race condition via unique constraint on payment_id.
     */
    public function createFromPayment(Payment $payment): ?Invoice
    {
        $installment = $payment->installment;
        if (!$installment) {
            return null;
        }

        return DB::transaction(function () use ($payment, $installment) {
            $existing = Invoice::where('payment_id', $payment->id)->first();
            if ($existing) {
                return $existing;
            }

            $feeBase = $installment->original_amount ?? $installment->amount;
            $lateFee = $installment->late_fee_amount ?? 0;
            $totalAmount = $feeBase + $lateFee;

            try {
                $invoice = Invoice::create([
                    'client_id' => $payment->client_id,
                    'installment_id' => $installment->id,
                    'payment_id' => $payment->id,
                    'invoice_number' => InvoiceNumberHelper::generate(),
                    'amount' => $feeBase,
                    'late_fee' => $lateFee,
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'issued_at' => now(),
                    'paid_at' => now(),
                ]);

                return $invoice;
            } catch (QueryException $e) {
                // Race condition: another transaction created the invoice first
                // Unique constraint on payment_id caught the duplicate
                if ($e->errorInfo[1] ?? null === 1062) { // MySQL duplicate entry error code
                    return Invoice::where('payment_id', $payment->id)->first();
                }
                throw $e;
            }
        });
    }
}