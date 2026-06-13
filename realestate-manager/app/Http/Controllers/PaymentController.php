<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create(Request $request)
    {
        $clientId = $request->get('client_id');
        $client = null;
        if ($clientId) {
            $client = Client::with(['property', 'payments', 'installments' => function($q) {
                $q->where('status', 'pending')->orderBy('installment_number');
            }])->findOrFail($clientId);
        }
        
        $clients = Client::with(['property', 'receipts', 'installments' => function($q) {
            $q->where('status', 'pending')->orderBy('installment_number');
        }])->orderBy('full_name')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'client_id' => $c->client_id ?? '',
                'full_name' => $c->full_name ?? '',
                'cnic' => $c->cnic ?? '',
                'phone' => $c->phone ?? '',
                'plot_number' => $c->property ? $c->property->plot_number : '',
                'block_name' => $c->property ? $c->property->block_name : '',
                'address' => $c->property ? $c->property->address : '',
                'receipt_numbers' => $c->receipts ? $c->receipts->pluck('receipt_number')->implode(', ') : '',
                'pending_installments' => $c->installments->map(function ($inst) {
                    return [
                        'id' => $inst->id,
                        'number' => $inst->installment_number,
                        'amount' => $inst->amount,
                        'due_date' => $inst->due_date ? $inst->due_date->format('Y-m-d') : '',
                    ];
                })
            ];
        });
        
        return view('payments.create', compact('clients', 'client'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_date' => 'required|date',
            'generate_receipt' => 'nullable|boolean',
            'installment_id' => 'nullable|exists:installments,id',
            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:1',
            'payments.*.payment_method' => 'required|string|in:CASH,CHEQUE,BANK_TRANSFER,ONLINE,PO',
            'payments.*.particulars' => 'nullable|string|max:200',
            'payments.*.bank_name' => 'nullable|string|max:100',
            'payments.*.cheque_number' => 'nullable|string|max:50',
            'payments.*.payment_date' => 'required|date',
        ]);

        $client = Client::with('property', 'payments')->findOrFail($request->client_id);
        $property = $client->property;

        if (!$property) {
            return back()->withInput()->with('error', 'The client must have an active property record to log payments.');
        }

        $totalAmount = collect($request->payments)->sum('amount');

        \DB::beginTransaction();
        try {
            $createdPayments = [];
            foreach ($request->payments as $index => $payData) {
                $method = $payData['payment_method'];
                
                // Set default particulars based on method if not filled
                $particulars = $payData['particulars'] ?? '';
                if (empty($particulars)) {
                    if ($method === 'CASH') {
                        $particulars = 'Through Cash';
                    } elseif ($method === 'CHEQUE') {
                        $particulars = 'Paid through Cheque No: ' . ($payData['cheque_number'] ?? '');
                    } elseif ($method === 'PO') {
                        $particulars = 'Paid through Pay Order No: ' . ($payData['cheque_number'] ?? '');
                    } elseif ($method === 'BANK_TRANSFER') {
                        $particulars = 'Online Banking / Bank Transfer';
                    } else {
                        $particulars = 'Payment logged';
                    }
                }

                $paymentNumber = $client->payments()->count() + count($createdPayments) + 1;

                $payment = Payment::create([
                    'client_id' => $client->id,
                    'property_id' => $property->id,
                    'installment_id' => ($index === 0) ? $request->installment_id : null, // Link first payment to installment
                    'payment_number' => $paymentNumber,
                    'amount' => $payData['amount'],
                    'payment_method' => $method,
                    'particulars' => $particulars,
                    'bank_name' => ($method !== 'CASH') ? ($payData['bank_name'] ?? null) : null,
                    'cheque_number' => in_array($method, ['CHEQUE', 'PO', 'BANK_TRANSFER', 'ONLINE']) ? ($payData['cheque_number'] ?? null) : null,
                    'payment_date' => $payData['payment_date'] ?? $request->payment_date,
                    'created_by' => auth()->id()
                ]);

                $createdPayments[] = $payment;
                \App\Services\ActivityLogger::logCreate($payment);
            }

            // Synchronize installments automatically based on total paid amount
            $this->syncInstallments($client->id);

            if ($request->has('generate_receipt') && $request->generate_receipt) {
                $paymentNumberForRef = $client->payments()->count() + count($createdPayments);
                $receiptNumber = 'RCP-' . str_replace('-', '', $client->client_id) . '-' . str_pad($paymentNumberForRef, 3, '0', STR_PAD_LEFT);
                
                $previousPaid = $client->payments()->whereNotIn('id', collect($createdPayments)->pluck('id'))->sum('amount');
                $totalReceived = $previousPaid + $totalAmount;
                $remainingBalance = $property->total_deal_value - $totalReceived;

                $docxFilename = $receiptNumber . '_' . date('Ymd') . '.docx';

                $receipt = Receipt::create([
                    'receipt_number' => $receiptNumber,
                    'client_id' => $client->id,
                    'property_id' => $property->id,
                    'total_amount_this_receipt' => $totalAmount,
                    'total_received_to_date' => $totalReceived,
                    'remaining_balance' => $remainingBalance,
                    'receipt_date' => $request->payment_date,
                    'docx_filename' => $docxFilename,
                    'generated_by' => auth()->id()
                ]);

                foreach ($createdPayments as $payment) {
                    $payment->receipt_id = $receipt->id;
                    $payment->save();
                }

                $receiptService = new \App\Services\ReceiptService();
                $receiptService->generate($receipt);

                \DB::commit();

                if ($client->google_drive_folder_id) {
                    \App\Jobs\UploadToDriveJob::dispatch($receipt);
                } else {
                    \App\Jobs\SyncToGoogleSheetJob::dispatch($client);
                }

                return redirect()->route('clients.show', $client->id)
                    ->with('success', 'Payment logged and Receipt ' . $receiptNumber . ' generated in background.');

            } else {
                \DB::commit();

                \App\Jobs\SyncToGoogleSheetJob::dispatch($client);

                return redirect()->route('clients.show', $client->id)
                    ->with('success', 'Payments logged successfully.');
            }

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Logging payments failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to log payments: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $clientId = $payment->client_id;
        
        \DB::beginTransaction();
        try {
            // We no longer blindly revert a single installment status.
            // We'll fully recalculate the schedule based on total paid after deletion.

            // Also delete associated receipt if it's the only payment on that receipt
            if ($payment->receipt_id) {
                $receipt = \App\Models\Receipt::find($payment->receipt_id);
                if ($receipt && $receipt->payments()->count() <= 1) {
                    $receipt->delete();
                }
            }

            \App\Services\ActivityLogger::logDelete($payment, 'Payment deleted/reversed. Reason: ' . $request->reason);
            $payment->delete();

            \DB::commit();

            // Resync all installments now that the payment is gone
            $this->syncInstallments($clientId);

            return redirect()->route('clients.show', $clientId)->with('success', 'Payment deleted successfully. Reason: ' . $request->reason);
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to recalculate installment status and amounts
     * based on total payments received vs original_amount.
     */
    private function syncInstallments($clientId)
    {
        $client = Client::with(['property', 'payments', 'installments' => function($q) {
            $q->orderBy('installment_number', 'asc');
        }])->find($clientId);

        if (!$client || $client->installments->isEmpty() || !$client->property) return;

        $totalDealValue = $client->property->total_deal_value;
        $totalPaid = $client->payments()->sum('amount');
        $remainingBalance = $totalDealValue - $totalPaid;

        // Total amount that was originally scheduled
        $totalOriginalScheduled = $client->installments->sum(function($inst) {
            return $inst->original_amount ?? $inst->amount;
        });

        // The amount paid *specifically against* the scheduled installments
        $paidAmountToDistribute = $totalOriginalScheduled - $remainingBalance;
        if ($paidAmountToDistribute < 0) {
            $paidAmountToDistribute = 0;
        }

        $budget = $paidAmountToDistribute;

        foreach ($client->installments as $inst) {
            $original = $inst->original_amount ?? $inst->amount;
            
            if ($budget <= 0) {
                // No payment left, fully pending
                $inst->amount = $original;
                $inst->status = 'pending';
            } elseif ($budget >= $original) {
                // Fully covered
                $budget -= $original;
                $inst->amount = 0;
                $inst->status = 'paid';
            } else {
                // Partially covered
                $inst->amount = $original - $budget;
                $budget = 0;
                $inst->status = 'pending';
            }
            $inst->save();
        }
    }
}
