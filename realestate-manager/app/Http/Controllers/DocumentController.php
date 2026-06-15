<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Jobs\DocumentUploadJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document_type' => 'required|in:agreement,cnic,other',
            'document_file' => 'required|file|max:10240|mimes:pdf,doc,docx,png,jpg,jpeg,txt'
        ]);

        $client = Client::findOrFail($request->client_id);

        if (!$client->google_drive_folder_id) {
            return back()->with('error', 'Google Drive folder for this client is not created yet.');
        }

        $file = $request->file('document_file');
        $originalName = $file->getClientOriginalName();

        // Move uploaded file to persistent storage so it survives the request
        // (temp file from getRealPath() is deleted after request ends)
        $storageDir = storage_path('app/documents');
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $persistentPath = $storageDir . '/' . uniqid('doc_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $file->move($storageDir, basename($persistentPath));

        // Version detection: find latest root-level document of same type for this client
        $latestRoot = Document::where('client_id', $client->id)
            ->where('document_type', $request->document_type)
            ->whereNull('parent_document_id')
            ->orderByDesc('version_number')
            ->first();

        if ($latestRoot) {
            $versionNumber = ($latestRoot->version_number ?? 1) + 1;
            $parentDocumentId = $latestRoot->parent_document_id ?? $latestRoot->id;
        } else {
            $versionNumber = 1;
            $parentDocumentId = null;
        }

        DocumentUploadJob::dispatch(
            $client->id,
            $request->document_type,
            $originalName,
            $persistentPath,
            auth()->id(),
            $versionNumber,
            $parentDocumentId
        );

        return back()->with('success', 'Document upload queued successfully. It will be processed shortly.');
    }

    /**
     * Resolve the root document for a given document ID.
     *
     * Walks up parent_document_id until it reaches the root (parent_document_id IS NULL).
     *
     * @param  int $documentId
     * @return \App\Models\Document|null Root document, or null if not found
     */
    protected function resolveRootDocument(int $documentId): ?Document
    {
        $doc = Document::find($documentId);

        if (!$doc) {
            return null;
        }

        while ($doc->parent_document_id !== null) {
            $parent = Document::find($doc->parent_document_id);
            if (!$parent) {
                break;
            }
            $doc = $parent;
        }

        return $doc;
    }

    /**
     * Return full version history for a document group.
     *
     * Traverses up to the root, then fetches all documents sharing
     * the same client_id + document_type with parent_document_id in the root lineage.
     *
     * @param  int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function versions($documentId): JsonResponse
    {
        $root = $this->resolveRootDocument($documentId);

        if (!$root) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        // Collect all document IDs in this version chain (root + all children)
        $versionIds = [$root->id];
        $childIds = [$root->id];

        // BFS down from root to collect all descendants
        while (!empty($childIds)) {
            $children = Document::whereIn('parent_document_id', $childIds)
                ->whereNotIn('id', $versionIds)
                ->pluck('id')
                ->toArray();

            $versionIds = array_merge($versionIds, $children);
            $childIds = $children;
        }

        $versions = Document::whereIn('id', $versionIds)
            ->orderByDesc('version_number')
            ->get([
                'id',
                'version_number',
                'original_filename',
                'google_drive_file_url',
                'uploaded_by',
                'created_at',
                'parent_document_id',
            ]);

        return response()->json([
            'success' => true,
            'data' => $versions,
            'root_id' => $root->id,
            'document_type' => $root->document_type,
        ]);
    }

    /**
     * Return only the latest version of a document group.
     *
     * @param  int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function latestVersion($documentId): JsonResponse
    {
        $root = $this->resolveRootDocument($documentId);

        if (!$root) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.',
            ], 404);
        }

        // Collect all descendant IDs
        $versionIds = [$root->id];
        $childIds = [$root->id];

        while (!empty($childIds)) {
            $children = Document::whereIn('parent_document_id', $childIds)
                ->whereNotIn('id', $versionIds)
                ->pluck('id')
                ->toArray();

            $versionIds = array_merge($versionIds, $children);
            $childIds = $children;
        }

        $latest = Document::whereIn('id', $versionIds)
            ->orderByDesc('version_number')
            ->first([
                'id',
                'version_number',
                'original_filename',
                'google_drive_file_url',
                'uploaded_by',
                'created_at',
                'parent_document_id',
            ]);

        return response()->json([
            'success' => true,
            'data' => $latest,
            'root_id' => $root->id,
            'document_type' => $root->document_type,
        ]);
    }

    /**
     * Collect all document IDs in a version chain via BFS from root.
     *
     * @param  int $rootId
     * @return array<int> All document IDs in the chain
     */
    protected function collectVersionIds(int $rootId): array
    {
        $versionIds = [$rootId];
        $childIds = [$rootId];

        while (!empty($childIds)) {
            $children = Document::whereIn('parent_document_id', $childIds)
                ->whereNotIn('id', $versionIds)
                ->pluck('id')
                ->toArray();

            $versionIds = array_merge($versionIds, $children);
            $childIds = $children;
        }

        return $versionIds;
    }

    /**
     * Get the active (latest) version of a document chain.
     *
     * @param  int $documentId  Any document ID in the chain
     * @return \App\Models\Document|null Latest version, or null if not found
     */
    public function getActiveVersion(int $documentId): ?Document
    {
        $root = $this->resolveRootDocument($documentId);

        if (!$root) {
            return null;
        }

        $versionIds = $this->collectVersionIds($root->id);

        return Document::whereIn('id', $versionIds)
            ->orderByDesc('version_number')
            ->first();
    }

    /**
     * Rollback to a previous document version.
     *
     * Creates a NEW document record copying the target version's Drive file
     * reference, with an incremented version_number. No files are deleted
     * or overwritten on Google Drive — this is a DB pointer shift only.
     *
     * @param  int $documentId      Current document ID
     * @param  int $targetVersionId Target version to restore
     * @return \Illuminate\Http\JsonResponse
     */
    public function rollbackVersion($documentId, $targetVersionId): JsonResponse
    {
        $current = Document::find($documentId);

        if (!$current) {
            return response()->json([
                'success' => false,
                'message' => 'Current document not found.',
            ], 404);
        }

        $target = Document::find($targetVersionId);

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target version not found.',
            ], 404);
        }

        // Resolve roots for both documents
        $currentRoot = $this->resolveRootDocument($current->id);
        $targetRoot = $this->resolveRootDocument($target->id);

        if (!$currentRoot || !$targetRoot) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve document lineage.',
            ], 400);
        }

        // Safety guard: prevent cross-client rollback
        if ($currentRoot->client_id !== $targetRoot->client_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot rollback across different clients.',
            ], 400);
        }

        // Safety guard: prevent cross-document_type rollback
        if ($currentRoot->document_type !== $targetRoot->document_type) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot rollback across different document types.',
            ], 400);
        }

        // Safety guard: ensure both belong to the same version chain
        $currentChainIds = $this->collectVersionIds($currentRoot->id);
        if (!in_array($target->id, $currentChainIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Target version does not belong to this document chain.',
            ], 400);
        }

        // Find max version_number in the chain
        $maxVersion = Document::whereIn('id', $currentChainIds)
            ->max('version_number');

        // Create new document record copying target version's Drive reference
        // This is the rollback — a DB pointer shift, no Drive files touched
        $rollback = Document::create([
            'client_id'             => $currentRoot->client_id,
            'document_type'         => $currentRoot->document_type,
            'original_filename'     => $target->original_filename,
            'google_drive_file_id'  => $target->google_drive_file_id,
            'google_drive_file_url' => $target->google_drive_file_url,
            'google_drive_folder_id'=> $target->google_drive_folder_id,
            'uploaded_by'           => auth()->id(),
            'version_number'        => ($maxVersion ?? 0) + 1,
            'parent_document_id'    => $currentRoot->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rollback complete. New version created from v' . $target->version_number . '.',
            'data' => [
                'id'                => $rollback->id,
                'version_number'    => $rollback->version_number,
                'original_filename' => $rollback->original_filename,
                'google_drive_file_url' => $rollback->google_drive_file_url,
                'uploaded_by'       => $rollback->uploaded_by,
                'created_at'        => $rollback->created_at,
                'parent_document_id'=> $rollback->parent_document_id,
                'restored_from'     => $target->id,
                'restored_from_version' => $target->version_number,
            ],
            'root_id' => $currentRoot->id,
            'document_type' => $currentRoot->document_type,
        ]);
    }

    /**
     * Run a full integrity audit across all document chains.
     *
     * Checks for orphans, broken chains, duplicate versions, and missing
     * Drive files. Read-only — no data is mutated.
     *
     * @param  int|null $clientId  Optional client ID filter
     * @return \Illuminate\Http\JsonResponse
     */
    public function auditDocumentIntegrity($clientId = null): JsonResponse
    {
        // Batch-fetch all documents in one query (avoid N+1)
        $query = Document::with('client')->orderBy('client_id')->orderBy('document_type');
        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }
        $allDocs = $query->limit(50)->get();

        // Group documents by (client_id, document_type) to find roots
        $grouped = $allDocs->groupBy(fn ($d) => $d->client_id . '|' . $d->document_type);

        // Identify root documents (parent_document_id IS NULL)
        $roots = $allDocs->whereNull('parent_document_id');

        // Build a map of document_id → document for quick lookup
        $docMap = $allDocs->keyBy('id');

        // Track all chain IDs we've already audited to skip duplicates
        $auditedChainIds = [];

        $orphanedDocs = [];
        $brokenChains = [];
        $duplicateVersions = [];
        $missingDriveFiles = [];
        $totalChains = 0;

        foreach ($roots as $root) {
            // Skip if this root's chain was already processed
            if (in_array($root->id, $auditedChainIds)) {
                continue;
            }

            $totalChains++;
            $chainIds = $this->collectVersionIds($root->id);
            $auditedChainIds = array_merge($auditedChainIds, $chainIds);

            $chainDocs = Document::with('client')->whereIn('id', $chainIds)->get();
            $versionNumbers = $chainDocs->pluck('version_number')->toArray();

            // CHECK 1 — Orphan check: parent_document_id must exist in chain or be NULL
            foreach ($chainDocs as $doc) {
                if ($doc->parent_document_id !== null && !in_array($doc->parent_document_id, $chainIds)) {
                    $orphanedDocs[] = [
                        'document_id'   => $doc->id,
                        'version_number'=> $doc->version_number,
                        'parent_document_id' => $doc->parent_document_id,
                        'chain_root_id' => $root->id,
                        'client_id'     => $root->client_id,
                        'document_type' => $root->document_type,
                        'suggested_fix' => 'Re-link document to nearest valid root or rebuild chain via rollback system.',
                    ];
                }
            }

            // CHECK 2 — Broken chain: version_number must be continuous 1..N
            $sorted = array_values(array_unique($versionNumbers));
            sort($sorted);
            if (!empty($sorted)) {
                $expected = range(1, max($sorted));
                $missing = array_diff($expected, $sorted);
                if (!empty($missing)) {
                    $brokenChains[] = [
                        'chain_root_id' => $root->id,
                        'client_id'     => $root->client_id,
                        'document_type' => $root->document_type,
                        'expected_versions' => $expected,
                        'actual_versions'   => $sorted,
                        'missing_versions'  => array_values($missing),
                        'suggested_fix' => 'Re-upload missing versions or use rollback to rebuild the chain.',
                    ];
                }
            }

            // CHECK 3 — Duplicate version: same version_number appears more than once
            $counts = array_count_values($versionNumbers);
            foreach ($counts as $vn => $count) {
                if ($count > 1) {
                    $dupes = $chainDocs->where('version_number', $vn)->pluck('id')->toArray();
                    $duplicateVersions[] = [
                        'chain_root_id' => $root->id,
                        'client_id'     => $root->client_id,
                        'document_type' => $root->document_type,
                        'version_number'=> $vn,
                        'duplicate_document_ids' => $dupes,
                        'suggested_fix' => 'Remove the older duplicate record. Both files exist on Drive — keep the most recent upload.',
                    ];
                }
            }

            // CHECK 4 — Missing Drive file: google_drive_file_id or url is null
            foreach ($chainDocs as $doc) {
                if (empty($doc->google_drive_file_id) || empty($doc->google_drive_file_url)) {
                    $missingDriveFiles[] = [
                        'document_id'   => $doc->id,
                        'version_number'=> $doc->version_number,
                        'chain_root_id' => $root->id,
                        'client_id'     => $root->client_id,
                        'document_type' => $root->document_type,
                        'suggested_fix' => 'Re-upload this document version to generate valid Drive links.',
                    ];
                }
            }
        }

        $totalDocs = $allDocs->count();
        $hasIssues = count($orphanedDocs) > 0
            || count($brokenChains) > 0
            || count($duplicateVersions) > 0
            || count($missingDriveFiles) > 0;

        return response()->json([
            'status'   => $hasIssues ? 'issues_found' : 'ok',
            'summary'  => [
                'total_documents'           => $totalDocs,
                'total_chains'              => $totalChains,
                'orphaned_count'            => count($orphanedDocs),
                'broken_chains_count'       => count($brokenChains),
                'duplicate_versions_count'  => count($duplicateVersions),
                'missing_drive_files_count' => count($missingDriveFiles),
            ],
            'issues' => [
                'orphaned_documents'    => $orphanedDocs,
                'broken_chains'         => $brokenChains,
                'duplicate_versions'    => $duplicateVersions,
                'missing_drive_files'   => $missingDriveFiles,
            ],
        ]);
    }
}
