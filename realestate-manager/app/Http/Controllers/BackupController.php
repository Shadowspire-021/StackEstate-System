<?php

namespace App\Http\Controllers;

use App\Jobs\BackupJob;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Show list of backups
     */
    public function index(BackupService $backup)
    {
        $backups = $backup->listBackups();
        $config = $backup->getConfig();
        $totalCount = count($backups);
        $totalSize = $backup->getTotalBackupSize();

        return view('backups.index', compact('backups', 'config', 'totalCount', 'totalSize'));
    }

    /**
     * Create a new backup manually
     */
    public function store(BackupService $backup)
    {
        $result = $backup->createBackup();

        if ($result['success']) {
            return back()->with('success', "Backup created: {$result['filename']} (" . number_format($result['size'] / 1024, 1) . " KB)");
        }

        Log::error('Backup failed: ' . $result['error']);
        return back()->withErrors(['error' => 'Backup failed. Please try again.']);
    }

    /**
     * Create backup via queue (non-blocking)
     */
    public function storeQueued()
    {
        BackupJob::dispatch();

        return back()->with('success', 'Backup job queued. It will be processed shortly.');
    }

    /**
     * Verify a backup file integrity
     */
    public function verify(BackupService $backup, string $filename)
    {
        $result = $backup->verifyBackup($filename);

        if ($result['valid']) {
            return back()->with('success', "Backup verified: {$result['table_count']} tables, created {$result['info']['created_at']}");
        }

        Log::error('Backup verification failed: ' . $result['error']);
        return back()->withErrors(['error' => 'Backup verification failed. Please try again.']);
    }

    /**
     * Delete a backup file
     */
    public function destroy(BackupService $backup, string $filename)
    {
        $path = $backup->getBackupPath($filename);

        if (file_exists($path)) {
            unlink($path);
            Log::info('Backup deleted', ['filename' => $filename]);
            return back()->with('success', "Backup deleted: {$filename}");
        }

        return back()->withErrors(['error' => 'Backup file not found']);
    }
}
