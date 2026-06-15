<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'default';
    }

    /**
     * Execute the job.
     */
    public function handle(BackupService $backup): void
    {
        Log::info('BackupJob started', ['pid' => getmypid()]);

        $result = $backup->createBackup();

        if ($result['success']) {
            Log::info('BackupJob completed successfully', [
                'filename' => $result['filename'],
                'size' => $result['size'],
            ]);

            // Prune old backups after successful creation
            $pruned = $backup->pruneOldBackups();
            if ($pruned > 0) {
                Log::info('Old backups pruned', ['count' => $pruned]);
            }
        } else {
            Log::error('BackupJob failed', ['error' => $result['error']]);

            // Throw exception to trigger retry
            throw new \RuntimeException('Backup failed: ' . $result['error']);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('BackupJob failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
