<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'document_type', 'original_filename', 'google_drive_file_id',
        'google_drive_file_url', 'google_drive_folder_id', 'uploaded_by',
        'version_number', 'parent_document_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    // ── Version Helpers ────────────────────────────────────────

    /**
     * Determine whether this document is the latest version.
     *
     * A document is the latest if no child versions exist.
     */
    public function isLatestVersion(): bool
    {
        return $this->versions()->count() === 0;
    }

    /**
     * Get the latest version in this document's version chain.
     *
     * Walks down the version tree from this document (or its root ancestor)
     * to find the leaf — the document with no newer versions.
     */
    public function getLatestVersion(): self
    {
        $current = $this;

        while (true) {
            $newer = $current->versions()->first();
            if (!$newer) {
                return $current;
            }
            $current = $newer;
        }
    }

    /**
     * Create the next version of this document.
     *
     * This is a pure data method — it returns a new unsaved Document instance
     * with incremented version_number and parent_document_id set to this
     * document's ID (or its root ancestor's ID).
     *
     * The caller is responsible for saving and uploading the new file.
     */
    public function incrementVersion(): self
    {
        $rootId = $this->parent_document_id ?? $this->id;

        return new self([
            'client_id'            => $this->client_id,
            'document_type'        => $this->document_type,
            'original_filename'    => $this->original_filename,
            'google_drive_folder_id' => $this->google_drive_folder_id,
            'uploaded_by'          => $this->uploaded_by,
            'version_number'       => ($this->version_number ?? 1) + 1,
            'parent_document_id'   => $rootId,
        ]);
    }
}
