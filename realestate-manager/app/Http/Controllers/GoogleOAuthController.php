<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\GoogleOAuthService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleOAuthController extends Controller
{
    public function __construct(
        private GoogleOAuthService $oauthService
    ) {}

    public function redirectToGoogle(Request $request)
    {
        $service = $request->query('service', 'drive');

        if (!in_array($service, ['drive', 'sheets'], true)) {
            $service = 'drive';
        }

        $scope = implode(' ', [
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/spreadsheets',
        ]);

        $state = http_build_query([
            'service' => $service,
            'return_url' => $request->query('return_url', route('settings.index')),
        ]);

        $authUrl = $this->oauthService->getAuthUrl($scope, $state);

        return redirect()->away($authUrl);
    }

    public function handleCallback(Request $request)
    {
        $error = $request->query('error');
        if ($error) {
            return redirect()->route('settings.index')
                ->with('error', 'Google OAuth was denied: ' . $error);
        }

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('settings.index')
                ->with('error', 'Google OAuth failed: No authorization code received.');
        }

        $state = $request->query('state', '');
        parse_str($state, $stateParams);
        $service = $stateParams['service'] ?? 'drive';
        $returnUrl = $stateParams['return_url'] ?? route('settings.index');

        $result = $this->oauthService->handleCallback($code);

        if (!$result['success']) {
            return redirect()->to($returnUrl)
                ->with('error', 'Google authentication failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        try {
            $this->provisionDriveFolder();
        } catch (\Exception $e) {
            Log::error('Google OAuth: Failed to provision Drive folder', ['error' => $e->getMessage()]);
        }

        try {
            $this->provisionSpreadsheet();
        } catch (\Exception $e) {
            Log::error('Google OAuth: Failed to provision spreadsheet', ['error' => $e->getMessage()]);
        }

        $message = $service === 'sheets'
            ? 'Google Sheets connected successfully!'
            : 'Google Drive connected successfully!';

        return redirect()->to($returnUrl)->with('success', $message);
    }

    public function disconnect()
    {
        $this->oauthService->revokeToken();

        return redirect()->route('settings.index')
            ->with('success', 'Google account disconnected successfully.');
    }

    public function status(): \Illuminate\Http\JsonResponse
    {
        $token = $this->oauthService->getStoredToken();

        return response()->json([
            'connected' => $token !== null,
            'drive_folder_id' => Setting::getValue(GoogleOAuthService::DRIVE_FOLDER_KEY, ''),
            'spreadsheet_id' => Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, ''),
        ]);
    }

    private function provisionDriveFolder(): void
    {
        $existingId = Setting::getValue(GoogleOAuthService::DRIVE_FOLDER_KEY, '');
        if ($existingId) {
            return;
        }

        $driveService = $this->oauthService->getDriveService();
        if (!$driveService) {
            return;
        }

        $companyName = Setting::getValue('company_name', 'StackEstate');
        $folderName = $companyName . ' Backups';

        $query = "mimeType = 'application/vnd.google-apps.folder' and name = '" . addslashes($folderName) . "' and trashed = false";
        $response = $driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'spaces' => 'drive',
        ]);

        $files = $response->getFiles();
        if (count($files) > 0) {
            $folderId = $files[0]->getId();
        } else {
            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);
            $folder = $driveService->files->create($folderMetadata, ['fields' => 'id']);
            $folderId = $folder->id;
        }

        Setting::setValue(GoogleOAuthService::DRIVE_FOLDER_KEY, $folderId, 'google');
        Log::info('Google OAuth: Drive folder provisioned', ['folder_id' => $folderId, 'name' => $folderName]);
    }

    private function provisionSpreadsheet(): void
    {
        $existingId = Setting::getValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, '');
        if ($existingId) {
            return;
        }

        $sheetsService = $this->oauthService->getSheetsService();
        if (!$sheetsService) {
            return;
        }

        $companyName = Setting::getValue('company_name', 'StackEstate');
        $spreadsheetName = $companyName . ' Backup Log';

        $spreadsheet = new Spreadsheet([
            'properties' => [
                'title' => $spreadsheetName,
            ],
        ]);

        $created = $sheetsService->spreadsheets->create($spreadsheet, ['fields' => 'spreadsheetId']);
        $spreadsheetId = $created->spreadsheetId;

        $headerRow = new Sheets\ValueRange([
            'values' => [[
                'Timestamp',
                'Backup Filename',
                'File Size (bytes)',
                'Drive File ID',
                'Status',
            ]],
        ]);

        $sheetsService->spreadsheets_values->update(
            $spreadsheetId,
            'Sheet1!A1:E1',
            $headerRow,
            ['valueInputOption' => 'USER_ENTERED']
        );

        Setting::setValue(GoogleOAuthService::SHEETS_SPREADSHEET_KEY, $spreadsheetId, 'google');
        Log::info('Google OAuth: Spreadsheet provisioned', ['spreadsheet_id' => $spreadsheetId, 'name' => $spreadsheetName]);
    }
}