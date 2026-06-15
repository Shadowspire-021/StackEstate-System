<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Str;

class GoogleDriveService
{
    protected $client;
    protected $service;
    protected ?GoogleOAuthService $oauthService = null;

    /**
     * Subfolder name constants for client document hierarchy.
     */
    const SUBFOLDER_AGREEMENTS = 'Agreements';
    const SUBFOLDER_RECEIPTS = 'Receipts';
    const SUBFOLDER_KYC = 'KYC';
    const SUBFOLDER_CORRESPONDENCE = 'Correspondence';

    /**
     * Maps document_type values to their corresponding subfolder names.
     */
    const DOCUMENT_TYPE_FOLDER_MAP = [
        'agreement' => self::SUBFOLDER_AGREEMENTS,
        'cnic'      => self::SUBFOLDER_KYC,
        'other'     => self::SUBFOLDER_CORRESPONDENCE,
    ];

    public function __construct(?GoogleOAuthService $oauthService = null)
    {
        $this->oauthService = $oauthService ?? app(GoogleOAuthService::class);
        $this->client = new Client();

        // Primary: OAuth token
        if ($this->oauthService->applyAccessToken($this->client)) {
            $this->client->addScope(Drive::DRIVE);
            $this->service = new Drive($this->client);
            return;
        }

        // Fallback: service account credentials JSON
        $credentialsPath = $this->resolveCredentialsPath();
        if ($credentialsPath && file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        }
        $this->client->addScope(Drive::DRIVE);
        $this->service = new Drive($this->client);
    }

    /**
     * Check if the Drive service is available via either OAuth or credentials file.
     */
    protected function isDriveAvailable(): bool
    {
        if ($this->oauthService->hasDriveToken()) {
            return true;
        }
        $credentialsPath = $this->resolveCredentialsPath();
        return $credentialsPath !== null && file_exists($credentialsPath);
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
            \Log::warning('Google Drive Service: Absolute credentials path rejected', ['path' => $path]);
            return null;
        }

        // Reject path traversal attempts
        if (str_contains($path, '..')) {
            \Log::warning('Google Drive Service: Path traversal attempt rejected', ['path' => $path]);
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
            \Log::warning('Google Drive Service: Could not resolve credentials path', ['path' => $path]);
            return null;
        }

        // Ensure path is within approved directory
        if (!Str::startsWith($realPath, $realApprovedBase)) {
            \Log::warning('Google Drive Service: Credentials path outside approved directory', [
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

    /**
     * Create a folder on Google Drive under an optional parent.
     * Original method — unchanged for backward compatibility.
     */
    public function createFolder($folderName, $parentFolderId = null)
    {
        if (!$this->isDriveAvailable()) {
            \Log::warning('Google Drive Service: No OAuth token or credentials file. Running in local dry-run mode.');
            return 'mock-drive-folder-' . uniqid();
        }

        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        if ($parentFolderId) {
            $fileMetadata->setParents([$parentFolderId]);
        } else {
            $rootId = config('google.root_folder_id');
            if ($rootId && $rootId !== 'paste_from_step_E4') {
                $fileMetadata->setParents([$rootId]);
            }
        }

        $folder = $this->service->files->create($fileMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }

    /**
     * Upload a file to Google Drive under the specified parent folder.
     * Original method — unchanged for backward compatibility.
     */
    public function uploadFile($filePath, $fileName, $parentFolderId)
    {
        if (!$this->isDriveAvailable()) {
            \Log::warning('Google Drive Service: No OAuth token or credentials file. Running in local dry-run mode.');
            return [
                'id' => 'mock-drive-file-' . uniqid(),
                'url' => '#',
                'download_url' => '#'
            ];
        }

        // Drive-level duplicate prevention: if a file with the same name
        // already exists in this folder, append a timestamp suffix to
        // ensure we never silently overwrite.
        $existingFileId = $this->findExistingFileByName($parentFolderId, $fileName);
        if ($existingFileId) {
            $pathInfo = pathinfo($fileName);
            $name = $pathInfo['filename'] ?? $fileName;
            $ext = $pathInfo['extension'] ?? '';
            $timestamp = date('Ymd_His');
            $fileName = $ext !== ''
                ? "{$name}_{$timestamp}.{$ext}"
                : "{$name}_{$timestamp}";
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$parentFolderId]
        ]);

        $content = file_get_contents($filePath);
        
        $mimeType = 'application/octet-stream';
        if (file_exists($filePath)) {
            $detected = mime_content_type($filePath);
            if ($detected) {
                $mimeType = $detected;
            }
        }

        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, webContentLink'
        ]);

        try {
            $user = auth()->user();
            if ($user) {
                $driveRole = $this->resolveDriveRole($user);
                if ($driveRole) {
                    $permission = new \Google\Service\Drive\Permission([
                        'type'       => 'user',
                        'role'       => $driveRole,
                        'emailAddress' => $user->email,
                    ]);
                    $this->service->permissions->create($file->id, $permission);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Google Drive permission update failed: ' . $e->getMessage());
        }

        return [
            'id' => $file->id,
            'url' => $file->webViewLink,
            'download_url' => $file->webContentLink
        ];
    }

    /**
     * Search for an existing file by exact name within a parent folder on Google Drive.
     *
     * @param  string      $parentFolderId  Parent folder ID to search within
     * @param  string      $fileName        Exact file name to match
     * @return string|null File ID if found, null otherwise
     */
    public function findExistingFileByName(string $parentFolderId, string $fileName): ?string
    {
        if (!$this->isDriveAvailable()) {
            return null;
        }

        try {
            $escapedName = str_replace(["\\", "'"], ["\\\\", "\\'"], $fileName);
            $escapedParent = str_replace(["\\", "'"], ["\\\\", "\\'"], $parentFolderId);
            $query = sprintf(
                "name = '%s' and '%s' in parents and trashed = false and mimeType != 'application/vnd.google-apps.folder'",
                $escapedName,
                $escapedParent
            );

            $response = $this->service->files->listFiles([
                'q'      => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive',
            ]);

            if (count($response->getFiles()) > 0) {
                return $response->getFiles()[0]->id;
            }
        } catch (\Exception $e) {
            \Log::error('Google Drive file search failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Resolve the Google Drive permission role for the given user.
     *
     * Role mapping:
     *   super_admin / admin  → writer  (full access)
     *   staff / accountant   → reader  (view only)
     *   user / no role       → null    (no permission granted)
     *
     * @param  \App\Models\User $user
     * @return string|null  'writer', 'reader', or null
     */
    protected function resolveDriveRole($user): ?string
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return 'writer';
        }

        if ($user->hasRole('staff') || $user->hasRole('accountant')) {
            return 'reader';
        }

        return null;
    }

    // ──────────────────────────────────────────────────────────────
    //  FOLDER HIERARCHY SYSTEM (Phase 3 Step 1)
    // ──────────────────────────────────────────────────────────────

    /**
     * Create the standard 4-subfolder structure under a client's root Drive folder.
     *
     * Subfolders created:
     *   /Agreements   — contracts, sale agreements
     *   /Receipts     — payment receipts
     *   /KYC          — CNIC, identity documents
     *   /Correspondence — general documents
     *
     * Idempotent: if a subfolder already exists by name, its ID is returned
     * instead of creating a duplicate.
     *
     * @param  string  $clientRootFolderId  The client's root google_drive_folder_id
     * @return array   ['agreements' => 'id', 'receipts' => 'id', 'kyc' => 'id', 'correspondence' => 'id']
     */
    public function createClientFolderStructure(string $clientRootFolderId): array
    {
        $subfolders = [];

        $subfolderMap = [
            'agreements'    => self::SUBFOLDER_AGREEMENTS,
            'receipts'      => self::SUBFOLDER_RECEIPTS,
            'kyc'           => self::SUBFOLDER_KYC,
            'correspondence' => self::SUBFOLDER_CORRESPONDENCE,
        ];

        foreach ($subfolderMap as $key => $name) {
            $subfolders[$key] = $this->findOrCreateSubfolder($clientRootFolderId, $name);
        }

        return $subfolders;
    }

    /**
     * Resolve the Google Drive subfolder ID for a given document type.
     *
     * @param  string      $clientRootFolderId  The client's root google_drive_folder_id
     * @param  string      $documentType        Document type: agreement|cnic|other|receipt
     * @return string|null Subfolder ID, or the root folder ID if type is unmapped
     */
    public function getClientSubfolderId(string $clientRootFolderId, string $documentType): ?string
    {
        $subfolderName = self::DOCUMENT_TYPE_FOLDER_MAP[$documentType] ?? null;

        if ($subfolderName === null) {
            // receipt or unmapped type → fall back to root folder
            return $clientRootFolderId;
        }

        return $this->findOrCreateSubfolder($clientRootFolderId, $subfolderName);
    }

    /**
     * Find an existing subfolder by name under a parent, or create it.
     *
     * Uses the Google Drive API files.list with exact name + mimeType filter
     * to avoid creating duplicates on repeated calls.
     *
     * @param  string $parentFolderId  Parent folder ID to search within
     * @param  string $subfolderName   Name of the subfolder to find or create
     * @return string The subfolder ID
     */
    public function findOrCreateSubfolder(string $parentFolderId, string $subfolderName): string
    {
        if (!$this->isDriveAvailable()) {
            \Log::warning('Google Drive Service: No OAuth token or credentials file. Running in local dry-run mode.');
            return 'mock-subfolder-' . strtolower(str_replace(' ', '_', $subfolderName)) . '-' . uniqid();
        }

        // Search for existing subfolder by exact name under this parent
        $existingId = $this->findFolderByName($parentFolderId, $subfolderName);
        if ($existingId) {
            return $existingId;
        }

        // Not found — create it
        return $this->createFolder($subfolderName, $parentFolderId);
    }

    /**
     * Search for a folder by exact name within a parent folder on Google Drive.
     *
     * @param  string      $parentFolderId  Parent folder ID to search within
     * @param  string      $folderName      Exact folder name to match
     * @return string|null Folder ID if found, null otherwise
     */
    public function findFolderByName(string $parentFolderId, string $folderName): ?string
    {
        if (!$this->isDriveAvailable()) {
            return null;
        }

        try {
            $escapedName = str_replace(["\\", "'"], ["\\\\", "\\'"], $folderName);
            $escapedParent = str_replace(["\\", "'"], ["\\\\", "\\'"], $parentFolderId);
            $query = sprintf(
                "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
                $escapedName,
                $escapedParent
            );

            $response = $this->service->files->listFiles([
                'q'      => $query,
                'fields' => 'files(id, name)',
                'spaces' => 'drive',
            ]);

            if (count($response->getFiles()) > 0) {
                return $response->getFiles()[0]->id;
            }
        } catch (\Exception $e) {
            \Log::error('Google Drive folder search failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get the standard subfolder name for a document type.
     *
     * @param  string $documentType  agreement|cnic|other|receipt
     * @return string|null Subfolder name, or null for unmapped types
     */
    public static function getSubfolderNameForDocumentType(string $documentType): ?string
    {
        if ($documentType === 'receipt') {
            return self::SUBFOLDER_RECEIPTS;
        }

        return self::DOCUMENT_TYPE_FOLDER_MAP[$documentType] ?? null;
    }
}

