<?php

namespace App\Helpers;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceNumberHelper
{
    /**
     * Generate the next sequential invoice number.
     *
     * MUST be called within a database transaction to guarantee
     * collision safety under concurrent requests.
     *
     * Format: INV-YYYY-000001
     */
    public static function generate(): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}