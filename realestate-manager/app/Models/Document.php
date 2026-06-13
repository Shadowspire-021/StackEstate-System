<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'document_type', 'original_filename', 'google_drive_file_id',
        'google_drive_file_url', 'google_drive_folder_id', 'uploaded_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
