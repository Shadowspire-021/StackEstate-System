<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; }
        .header h1 { font-size: 24px; color: #4f46e5; margin: 0; font-weight: bold; }
        .header p { color: #666; margin: 5px 0; }
        .invoice-title { font-size: 18px; font-weight: bold; margin: 20px 0; text-align: center; }
        .section { margin-bottom: 15px; }
        .section-label { font-size: 10px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .section-value { font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { text-align: left; border-bottom: 2px solid #ddd; padding: 8px 4px; font-size: 11px; color: #666; text-transform: uppercase; }
        td { padding: 8px 4px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .total-row td { border-top: 2px solid #333; font-size: 15px; padding-top: 10px; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-paid { background: #d1fae5; color: #059669; }
        .grid { display: flex; justify-content: space-between; }
        .grid-item { flex: 1; }
        .grid-item:last-child { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>StackEstate</h1>
        <p>Real Estate Management System</p>
    </div>

    <div class="invoice-title">INVOICE</div>

    <div class="grid">
        <div class="grid-item">
            <div class="section">
                <div class="section-label">Invoice Number</div>
                <div class="section-value">{{ $invoice->invoice_number }}</div>
            </div>
            <div class="section">
                <div class="section-label">Issued Date</div>
                <div class="section-value">{{ $invoice->issued_at?->format('M d, Y') ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="grid-item">
            <div class="section">
                <div class="section-label">Client</div>
                <div class="section-value">{{ $invoice->client?->full_name ?? 'N/A' }}</div>
                @if($invoice->client)
                    <div style="font-size: 11px; color: #666;">{{ $invoice->client->cnic }}</div>
                @endif
            </div>
            <div class="section">
                <div class="section-label">Installment</div>
                <div class="section-value">{{ $invoice->installment ? '#' . $invoice->installment->installment_number : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Installment Amount</td>
                <td class="text-right">Rs. {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->late_fee > 0)
                <tr>
                    <td>Late Fee</td>
                    <td class="text-right" style="color: #e11d48;">Rs. {{ number_format($invoice->late_fee, 2) }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td class="text-bold">Total</td>
                <td class="text-right text-bold">Rs. {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align: center;">
        <span class="badge badge-paid">{{ strtoupper($invoice->status) }}</span>
        @if($invoice->paid_at)
            <span style="font-size: 11px; color: #666; margin-left: 8px;">Paid on {{ $invoice->paid_at->format('M d, Y') }}</span>
        @endif
    </p>

    <div class="footer">
        <p>This is a computer-generated invoice.</p>
    </div>
</body>
</html>