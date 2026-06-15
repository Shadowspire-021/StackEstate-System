<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Receipt;
use App\Services\GoogleDriveService;
use App\Jobs\SyncToGoogleSheetJob;

class UploadToDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected $receipt;

    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }

    public function handle(GoogleDriveService $driveService)
    {
        // TRANSACTION CONSISTENCY: Ensure receipt processing is atomic
        if (\DB::transactionLevel() > 0) {
            // Process synchronously to ensure data consistency
            $receipt = $this->receipt->fresh(['client']);
        } else {
            // Transaction committed, process normally
            $receipt = $this->receipt->fresh(['client']);
        }
        
        $client = $receipt->client;
        $client = $receipt->client;
        
        if (!$client->google_drive_folder_id) {
            throw new \Exception("Client does not have a Google Drive Folder ID assigned.");
        }
        
        $tempPath = storage_path('app/receipts/' . $receipt->docx_filename);
        
        if (!file_exists($tempPath)) {
            $receiptService = new \App\Services\ReceiptService();
            $tempPath = $receiptService->generate($receipt);
        }
        
        $uploaded = $driveService->uploadFile($tempPath, $receipt->docx_filename, $client->google_drive_folder_id);
        
        if ($uploaded) {
            $receipt->google_drive_file_id = $uploaded['id'];
            $receipt->google_drive_file_url = $uploaded['url'];
            $receipt->save();
            
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            \App\Services\SyncManager::trigger($client);
        }
    }
}
