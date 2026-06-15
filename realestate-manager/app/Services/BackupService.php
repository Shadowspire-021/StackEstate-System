<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupService
{
    /**
     * The disk where backups are stored
     */
    protected string $disk = 'local';

    /**
     * The directory within the disk
     */
    protected string $directory = 'backups';

    /**
     * Maximum number of backups to keep (0 = unlimited)
     */
    protected int $keepDays = 30;

    /**
     * Create a database backup
     *
     * @return array{success: bool, filename?: string, path?: string, size?: int, error?: string}
     */
    public function createBackup(): array
    {
        try {
            $filename = $this->generateFilename();
            $path = $this->getBackupPath($filename);
            $directory = storage_path($this->directory);

            // Ensure backup directory exists
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Get all tables
            $tables = $this->getTables();
            $backupData = [
                'backup_info' => [
                    'created_at' => Carbon::now()->toISOString(),
                    'database' => config('database.connections.mysql.database'),
                    'tables' => count($tables),
                    'laravel_version' => app()->version(),
                ],
                'tables' => [],
            ];

            foreach ($tables as $table) {
                $backupData['tables'][$table] = $this->getTableData($table);
            }

            // Write to file
            $json = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            File::put($path, $json);

            $size = File::size($path);

            Log::info('Database backup created successfully', [
                'filename' => $filename,
                'size' => $size,
                'tables' => count($tables),
            ]);

            // Cloud upload: Google Drive (if OAuth connected)
            $driveFileId = $this->uploadToDrive($path, $filename);

            // Cloud logging: Google Sheets (if OAuth connected)
            $this->logToSheets($filename, $size, $driveFileId);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => $path,
                'size' => $size,
                'drive_file_id' => $driveFileId,
            ];
        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate backup filename with timestamp
     */
    public function generateFilename(): string
    {
        return 'backup_' . Carbon::now()->format('Y-m-d_His') . '.json';
    }

    /**
     * Get the full path for a backup file
     */
    public function getBackupPath(string $filename): string
    {
        return storage_path($this->directory . '/' . $filename);
    }

    /**
     * Get all database tables
     */
    protected function getTables(): array
    {
        $database = config('database.connections.mysql.database');
        $results = DB::select("SHOW TABLES");
        $tables = [];

        foreach ($results as $row) {
            $tables[] = $row->{"Tables_in_{$database}"};
        }

        return $tables;
    }

    /**
     * Get all data from a table
     */
    protected function getTableData(string $table): array
    {
        $data = DB::table($table)->get()->toArray();
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        return [
            'columns' => $columns,
            'rows' => $data,
            'count' => count($data),
        ];
    }

    /**
     * List all backups
     *
     * @return array<array{filename: string, path: string, size: int, created_at: string}>
     */
    public function listBackups(): array
    {
        $directory = storage_path($this->directory);

        if (!File::isDirectory($directory)) {
            return [];
        }

        $files = File::files($directory);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json' && str_starts_with($file->getFilename(), 'backup_')) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->toISOString(),
                ];
            }
        }

        // Sort by creation time descending (newest first)
        usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        return $backups;
    }

    /**
     * Get backup count
     */
    public function getBackupCount(): int
    {
        return count($this->listBackups());
    }

    /**
     * Get total backup size in bytes
     */
    public function getTotalBackupSize(): int
    {
        return array_sum(array_column($this->listBackups(), 'size'));
    }

    /**
     * Verify backup integrity by reading and parsing it
     */
    public function verifyBackup(string $filename): array
    {
        $path = $this->getBackupPath($filename);

        if (!File::exists($path)) {
            return [
                'valid' => false,
                'error' => 'Backup file not found',
            ];
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'error' => 'Invalid JSON: ' . json_last_error_msg(),
            ];
        }

        if (!isset($data['backup_info']) || !isset($data['tables'])) {
            return [
                'valid' => false,
                'error' => 'Missing required backup structure',
            ];
        }

        return [
            'valid' => true,
            'info' => $data['backup_info'],
            'table_count' => count($data['tables']),
        ];
    }

    /**
     * Delete old backups based on retention policy
     */
    public function pruneOldBackups(): int
    {
        if ($this->keepDays <= 0) {
            return 0;
        }

        $backups = $this->listBackups();
        $cutoffDate = Carbon::now()->subDays($this->keepDays);
        $deleted = 0;

        foreach ($backups as $backup) {
            $backupDate = Carbon::parse($backup['created_at']);

            if ($backupDate->lt($cutoffDate)) {
                if (File::delete($backup['path'])) {
                    $deleted++;
                    Log::info('Old backup pruned', ['filename' => $backup['filename']]);
                }
            }
        }

        return $deleted;
    }

    /**
     * Get backup storage configuration
     */
    public function getConfig(): array
    {
        return [
            'disk' => $this->disk,
            'directory' => $this->directory,
            'full_path' => storage_path($this->directory),
            'keep_days' => $this->keepDays,
        ];
    }

    // ---------------------------------------------------------------
    //  Cloud Integration (Google Drive + Sheets via OAuth)
    // ---------------------------------------------------------------

    /**
     * Upload backup file to Google Drive folder (OAuth-based).
     *
     * Falls back silently if OAuth not connected or upload fails.
     *
     * @return string|null Drive file ID on success, null otherwise
     */
    protected function uploadToDrive(string $filePath, string $filename): ?string
    {
        $driveFolderId = Setting::getValue(GoogleOAuthService::DRIVE_FOLDER_KEY, '');
        if (!$driveFolderId) {
            Log::info('Backup: Google Drive not connected. Skipping cloud upload.');
            return null;
        }

        try {
            $driveService = app(GoogleOAuthService::class)->getDriveService();
            if (!$driveService) {
                Log::warning('Backup: Could not initialize Drive service. Skipping upload.');
                return null;
            }

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $filename,
                'parents' => [$driveFolderId],
            ]);

            $content = file_get_contents($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/json';

            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink',
            ]);

            Log::info('Backup: Uploaded to Google Drive', [
                'filename' => $filename,
                'drive_file_id' => $uploadedFile->id,
                'link' => $uploadedFile->webViewLink ?? 'N/A',
            ]);

            return $uploadedFile->id;
        } catch (\Exception $e) {
            Log::error('Backup: Google Drive upload failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Log backup entry to Google Sheets (OAuth-based).
     *
     * Falls back silently if Sheets not connected or logging fails.
     */
    protected function logToSheets(string $filename, int $size, ?string $driveFileId): void
    {
        $spreadsheetId = Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, '');
        if (!$spreadsheetId) {
            Log::info('Backup: Google Sheets not connected. Skipping sheet logging.');
            return;
        }

        try {
            $sheetsService = app(GoogleOAuthService::class)->getSheetsService();
            if (!$sheetsService) {
                Log::warning('Backup: Could not initialize Sheets service. Skipping logging.');
                return;
            }

            $values = [
                now()->toIso8601String(),
                $filename,
                (string) $size,
                $driveFileId ?? 'N/A',
                'success',
            ];

            $body = new \Google\Service\Sheets\ValueRange([
                'values' => [$values],
            ]);

            $sheetsService->spreadsheets_values->append(
                $spreadsheetId,
                'Sheet1!A:E',
                $body,
                [
                    'valueInputOption' => 'USER_ENTERED',
                    'insertDataOption' => 'INSERT_ROWS',
                ]
            );

            Log::info('Backup: Logged to Google Sheets', ['filename' => $filename]);
        } catch (\Exception $e) {
            Log::error('Backup: Google Sheets logging failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
