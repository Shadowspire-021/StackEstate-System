<?php

namespace App\Http\Controllers;

use App\Helpers\InvoiceNumberHelper;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['client', 'installment'])
            ->when($request->filled('client_id'), fn($q, $v) => $q->where('client_id', $v))
            ->when($request->filled('status'), fn($q, $v) => $q->where('status', $v))
            ->when($request->filled('date_from'), fn($q, $v) => $q->whereDate('issued_at', '>=', $v))
            ->when($request->filled('date_to'), fn($q, $v) => $q->whereDate('issued_at', '<=', $v))
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'installment']);
        return view('invoices.show', compact('invoice'));
    }

    public function create()
    {
        $clients = Client::orderBy('full_name')->get(['id', 'full_name', 'cnic']);
        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'installment_id' => 'nullable|exists:installments,id',
            'amount' => 'required|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'issued_at' => 'required|date',
        ]);

        $lateFee = (float) ($validated['late_fee'] ?? 0);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'installment_id' => $validated['installment_id'] ?? null,
            'invoice_number' => InvoiceNumberHelper::generate(),
            'amount' => $validated['amount'],
            'late_fee' => $lateFee,
            'total_amount' => $validated['amount'] + $lateFee,
            'status' => 'pending',
            'issued_at' => $validated['issued_at'],
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function download(Invoice $invoice)
    {
        $pdfService = new InvoicePdfService();
        $pdf = $pdfService->generate($invoice);
        
        return $pdf->download($invoice->invoice_number . '.pdf');
    }
}