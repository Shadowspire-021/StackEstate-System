<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Document;
use App\Services\GoogleDriveService;

class DocumentUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 30;

    protected int $clientId;
    protected string $documentType;
    protected string $originalFilename;
    protected string $tempFilePath;
    protected ?int $uploadedBy;
    protected int $versionNumber;
    protected ?int $parentDocumentId;

    /**
     * @param  int         $clientId          Client ID
     * @param  string      $documentType      agreement|cnic|other
     * @param  string      $originalFilename  Original file name from user
     * @param  string      $tempFilePath      Path to the temp file in storage
     * @param  int|null    $uploadedBy        User ID who initiated the upload
     * @param  int         $versionNumber     Version number (default 1)
     * @param  int|null    $parentDocumentId  Parent document ID for version chain (null for first version)
     */
    public function __construct(
        int $clientId,
        string $documentType,
        string $originalFilename,
        string $tempFilePath,
        ?int $uploadedBy,
        int $versionNumber = 1,
        ?int $parentDocumentId = null
    ) {
        $this->clientId = $clientId;
        $this->documentType = $documentType;
        $this->originalFilename = $originalFilename;
        $this->tempFilePath = $tempFilePath;
        $this->uploadedBy = $uploadedBy;
        $this->versionNumber = $versionNumber;
        $this->parentDocumentId = $parentDocumentId;
    }

    /**
     * Upload the document to Google Drive and persist metadata.
     */
    public function handle(GoogleDriveService $driveService): void
    {
        if (!file_exists($this->tempFilePath)) {
            throw new \RuntimeException(
                "Document upload failed: temp file not found at {$this->tempFilePath}"
            );
        }

        $client = \App\Models\Client::withTrashed()->find($this->clientId);

        if (!$client) {
            throw new \RuntimeException(
                "Document upload failed: client #{$this->clientId} not found."
            );
        }

        if (!$client->google_drive_folder_id) {
            throw new \RuntimeException(
                "Document upload failed: client #{$this->clientId} has no Google Drive folder."
            );
        }

        $targetFolderId = $driveService->getClientSubfolderId(
            $client->google_drive_folder_id,
            $this->documentType
        );

        $driveFileName = $this->getVersionedFilename(
            $this->originalFilename,
            $this->versionNumber
        );

        $uploaded = $driveService->uploadFile(
            $this->tempFilePath,
            $driveFileName,
            $targetFolderId
        );

        if (!$uploaded || empty($uploaded['id']) || empty($uploaded['url'])) {
            throw new \RuntimeException(
                "Document upload failed: invalid Drive response for client #{$this->clientId}."
            );
        }

        $fileId = $uploaded['id'];

        Document::create([
            'client_id'             => $this->clientId,
            'document_type'         => $this->documentType,
            'original_filename'     => $this->originalFilename,
            'google_drive_file_id'  => $fileId,
            'google_drive_file_url' => $uploaded['url'],
            'google_drive_folder_id'=> $targetFolderId,
            'uploaded_by'           => $this->uploadedBy,
            'version_number'        => $this->versionNumber,
            'parent_document_id'    => $this->parentDocumentId,
        ]);

        \Log::info('Document uploaded', [
            'document_type'   => $this->documentType,
            'version'         => $this->versionNumber,
            'drive_file_id'   => $fileId,
            'client_id'       => $this->clientId,
        ]);

        // Clean up temp file after successful upload
        @unlink($this->tempFilePath);
    }

    /**
     * Generate a versioned filename for Google Drive storage.
     *
     * v1 → original name unchanged (e.g., "contract.pdf")
     * v2+ → version suffix appended (e.g., "contract_v2.pdf", "contract_v3.pdf")
     *
     * @param  string $originalFilename
     * @param  int    $versionNumber
     * @return string Versioned filename for Drive
     */
    protected function getVersionedFilename(string $originalFilename, int $versionNumber): string
    {
        if ($versionNumber <= 1) {
            return $originalFilename;
        }

        $pathInfo = pathinfo($originalFilename);
        $name = $pathInfo['filename'] ?? $originalFilename;
        $extension = $pathInfo['extension'] ?? '';

        if ($extension !== '') {
            return "{$name}_v{$versionNumber}.{$extension}";
        }

        return "{$name}_v{$versionNumber}";
    }

    /**
     * Handle job failure — log error with context.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('DocumentUploadJob failed', [
            'client_id'         => $this->clientId,
            'document_type'     => $this->documentType,
            'original_filename' => $this->originalFilename,
            'uploaded_by'       => $this->uploadedBy,
            'version_number'    => $this->versionNumber,
            'parent_document_id'=> $this->parentDocumentId,
            'error'             => $exception->getMessage(),
        ]);

        // Clean up temp file on failure
        if (!empty($this->tempFilePath) && file_exists($this->tempFilePath)) {
            @unlink($this->tempFilePath);
        }
    }
}
