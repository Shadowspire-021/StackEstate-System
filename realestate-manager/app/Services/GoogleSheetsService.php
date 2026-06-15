<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Str;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;
    protected ?GoogleOAuthService $oauthService = null;

    public function __construct(?GoogleOAuthService $oauthService = null)
    {
        $this->oauthService = $oauthService ?? app(GoogleOAuthService::class);
        $this->client = new Client();

        // Primary: OAuth token
        if ($this->oauthService->applyAccessToken($this->client)) {
            $this->client->addScope(Sheets::SPREADSHEETS);
            $this->service = new Sheets($this->client);
            $this->spreadsheetId = \App\Models\Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, '');
            return;
        }

        // Fallback: service account credentials JSON
        $credentialsPath = $this->resolveCredentialsPath();
        if ($credentialsPath && file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        }
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
        $this->spreadsheetId = config('google.sheet_id');
    }

    /**
     * Check if Sheets service is available via either OAuth or credentials file.
     */
    protected function isSheetsAvailable(): bool
    {
        $oauthId = \App\Models\Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, '');
        if ($this->oauthService->hasSheetsToken() && $oauthId) {
            return true;
        }
        $credentialsPath = $this->resolveCredentialsPath();
        $sheetId = config('google.sheet_id');
        return $credentialsPath !== null && file_exists($credentialsPath) && $sheetId && $sheetId !== 'paste_from_step_D4';
    }

    /**
     * Resolve and validate the Google credentials path.
     *
     * Only allows paths within the approved storage directory to prevent
     * path traversal attacks. Returns null if path is invalid or not configured.
     */
    protected function resolveCredentialsPath(): ?string
    {
        $configuredPath = config('google.credentials');

        if (empty($configuredPath)) {
            return null;
        }

        return $this->validateCredentialsPath($configuredPath);
    }

    /**
     * Validate that a credentials path is safe and within approved directory.
     *
     * @param  string  $path  The credentials file path to validate
     * @return string|null  Normalized path if valid, null otherwise
     */
    protected function validateCredentialsPath(string $path): ?string
    {
        // Reject absolute paths that escape the project
        if (Str::startsWith($path, '/') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $path)) {
            \Log::warning('Google Sheets Service: Absolute credentials path rejected', ['path' => $path]);
            return null;
        }

        // Reject path traversal attempts
        if (str_contains($path, '..')) {
            \Log::warning('Google Sheets Service: Path traversal attempt rejected', ['path' => $path]);
            return null;
        }

        // Normalize path
        $normalizedPath = $this->normalizePath($path);

        // Define approved base directory
        $approvedBase = storage_path('app/google');

        // Ensure the resolved path is within the approved directory
        $realApprovedBase = realpath($approvedBase);
        $realPath = realpath($normalizedPath);

        // If the file doesn't exist yet, check the directory
        if ($realPath === false) {
            $realPath = realpath(dirname($normalizedPath));
        }

        if ($realPath === false || $realApprovedBase === false) {
            \Log::warning('Google Sheets Service: Could not resolve credentials path', ['path' => $path]);
            return null;
        }

        // Ensure path is within approved directory
        if (!Str::startsWith($realPath, $realApprovedBase)) {
            \Log::warning('Google Sheets Service: Credentials path outside approved directory', [
                'path' => $path,
                'resolved' => $realPath,
                'approved' => $realApprovedBase,
            ]);
            return null;
        }

        return $normalizedPath;
    }

    /**
     * Normalize a file path for the current OS.
     */
    protected function normalizePath(string $path): string
    {
        // Convert to absolute path if relative
        if (!Str::startsWith($path, '/') && !preg_match('/^[a-zA-Z]:[\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return str_replace('\\', '/', $path);
    }

    public function appendRow($values)
    {
        if (!$this->isSheetsAvailable()) {
            \Log::warning('Google Sheets Service: OAuth/credentials or spreadsheet ID missing. Bypassing sheet sync.');
            return null;
        }

        // Initialize header row if sheet is empty
        $this->ensureHeaders();

        $body = new ValueRange([
            'values' => [$values]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS'
        ];

        $result = $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            'Sheet1!A:N',
            $body,
            $params
        );

        $updatedRange = $result->getUpdates()->getUpdatedRange(); // "Sheet1!A10:N10"
        preg_match('/[A-Za-z]+(\d+):/', $updatedRange, $matches);
        return isset($matches[1]) ? intval($matches[1]) : null;
    }

    public function updateRow($rowNumber, $values)
    {
        if (!$this->isSheetsAvailable() || !$rowNumber) {
            \Log::warning('Google Sheets Service: OAuth/credentials or spreadsheet ID missing. Bypassing sheet sync.');
            return null;
        }

        $body = new ValueRange([
            'values' => [$values]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $range = "Sheet1!A{$rowNumber}:N{$rowNumber}";

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    protected function ensureHeaders()
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'Sheet1!A1:N1');
            $values = $response->getValues();

            if (empty($values)) {
                $headers = [
                    'Client ID',
                    'Client Full Name',
                    'CNIC',
                    'Phone',
                    'Property (Plot No + Block + Location)',
                    'Total Deal Value',
                    'Total Received So Far',
                    'Remaining Balance',
                    'Last Payment Date',
                    'Last Payment Amount',
                    'Payment Method',
                    'Receipt Number',
                    'Drive Folder Link',
                    'Status'
                ];

                $body = new ValueRange([
                    'values' => [$headers]
                ]);

                $params = ['valueInputOption' => 'USER_ENTERED'];

                $this->service->spreadsheets_values->update(
                    $this->spreadsheetId,
                    'Sheet1!A1:N1',
                    $body,
                    $params
                );
            }
        } catch (\Exception $e) {
            \Log::error('Google Sheet ensureHeaders failed: ' . $e->getMessage());
        }
    }
}
