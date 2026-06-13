<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $credentialsPath = config('google.credentials');
        if (file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        }
        $this->client->addScope(Drive::DRIVE);
        $this->service = new Drive($this->client);
    }

    public function createFolder($folderName, $parentFolderId = null)
    {
        $credentialsPath = config('google.credentials');
        if (!file_exists($credentialsPath)) {
            \Log::warning('Google Drive Service: Credentials file not found. Running in local dry-run mode.');
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

    public function uploadFile($filePath, $fileName, $parentFolderId)
    {
        $credentialsPath = config('google.credentials');
        if (!file_exists($credentialsPath)) {
            \Log::warning('Google Drive Service: Credentials file not found. Running in local dry-run mode.');
            return [
                'id' => 'mock-drive-file-' . uniqid(),
                'url' => '#',
                'download_url' => '#'
            ];
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$parentFolderId]
        ]);

        $content = file_get_contents($filePath);
        
        // Safety check for mimeType
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

        // SECURITY FIX: Replace public access with secure staff-only access
        // IMPLEMENTATION NOTE: Use Google Groups for bulk permissions in production
        try {
            // Create a shareable link for staff access only
            // In production, replace 'staff@realestate.com' with your actual staff email or Google Group
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'user',
                'role' => 'reader',
                'emailAddress' => 'staff@realestate.com' // REPLACE: Set to your actual staff email or create a shared Google Group
            ]);
            $this->service->permissions->create($file->id, $permission);
        } catch (\Exception $e) {
            \Log::error('Google Drive permission update failed: ' . $e->getMessage());
        }

        return [
            'id' => $file->id,
            'url' => $file->webViewLink,
            'download_url' => $file->webContentLink
        ];
    }
}

