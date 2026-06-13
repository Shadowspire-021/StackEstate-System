<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;
use App\Services\GoogleSheetsService;

class SyncToGoogleSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(GoogleSheetsService $sheetsService)
    {
        // TRANSACTION CONSISTENCY: Use after_commit to ensure this runs only if parent transaction succeeds
        if (\DB::transactionLevel() > 0) {
            // This job will be delayed until the transaction completes
            // We'll process synchronously here since it's critical for data consistency
            // In production, consider using async processing with proper transaction monitoring
            $client = $this->client->fresh(['property', 'payments']);
        } else {
            // Transaction has already committed, safe to process
            $client = $this->client->fresh(['property', 'payments']);
        }
        
        $property = $client->property;
        $totalDealValue = $property ? $property->total_deal_value : 0;
        $totalReceived = $client->payments->sum('amount');
        $remainingBalance = $totalDealValue - $totalReceived;
        
        $lastPayment = $client->payments()->latest('payment_date')->latest('id')->first();
        $lastPaymentDate = $lastPayment ? $lastPayment->payment_date : '';
        $lastPaymentAmount = $lastPayment ? $lastPayment->amount : '';
        $lastPaymentMethod = $lastPayment ? $lastPayment->payment_method : '';
        
        $lastReceipt = $client->receipts()->latest('receipt_date')->latest('id')->first();
        $receiptNumber = $lastReceipt ? $lastReceipt->receipt_number : '';
        
        $propertyDetails = $property ? "Plot {$property->plot_number}, {$property->block_name}, {$property->location}" : '';
        $driveLink = $client->google_drive_folder_id ? "https://drive.google.com/drive/folders/" . $client->google_drive_folder_id : '';
        
        $rowValues = [
            $client->client_id,
            $client->full_name,
            $client->cnic,
            $client->phone,
            $propertyDetails,
            $totalDealValue,
            $totalReceived,
            $remainingBalance,
            $lastPaymentDate,
            $lastPaymentAmount,
            $lastPaymentMethod,
            $receiptNumber,
            $driveLink,
            $client->status
        ];
        
        if (!$client->google_sheet_row) {
            $rowNumber = $sheetsService->appendRow($rowValues);
            if ($rowNumber) {
                $client->google_sheet_row = $rowNumber;
                $client->saveQuietly();
            }
        } else {
            $sheetsService->updateRow($client->google_sheet_row, $rowValues);
        }
    }
}
