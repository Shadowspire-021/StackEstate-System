<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function generate(Invoice $invoice): Pdf
    {
        $invoice->load(['client', 'installment']);

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ]);
    }
}