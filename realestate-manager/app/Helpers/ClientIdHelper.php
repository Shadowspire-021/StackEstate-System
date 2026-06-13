<?php

namespace App\Helpers;

use App\Models\Client;

class ClientIdHelper
{
    public static function generate()
    {
        $year = date('Y');
        // ATOMIC FIX: Use a transaction to prevent race conditions
        \DB::beginTransaction();
        try {
            // Lock the table for writing to ensure atomic operation
            $last = Client::whereYear('created_at', $year)
                ->lockForUpdate()
                ->max('id') ?? 0;
            
            $nextId = $last + 1;
            \DB::commit();
            
            return 'CL-' . $year . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Client ID generation failed: ' . $e->getMessage());
            // Fallback: generate a unique ID with timestamp
            return 'CL-' . $year . '-' . str_pad(time(), 4, '0', STR_PAD_LEFT);
        }
    }
}
