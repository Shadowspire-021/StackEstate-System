<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function download(Receipt $receipt)
    {
        $tempPath = storage_path('app/receipts/' . $receipt->docx_filename);

        if (!file_exists($tempPath)) {
            $receiptService = new \App\Services\ReceiptService();
            try {
                $tempPath = $receiptService->generate($receipt);
            } catch (\Exception $e) {
                \Log::error('Regenerating receipt failed: ' . $e->getMessage());
                return back()->with('error', 'Failed to generate receipt: ' . $e->getMessage());
            }
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
