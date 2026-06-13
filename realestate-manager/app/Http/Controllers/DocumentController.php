<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request, GoogleDriveService $driveService)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'document_type' => 'required|in:agreement,cnic,other',
            'document_file' => 'required|file|max:10240|mimes:pdf,doc,docx,png,jpg,jpeg,txt' // 10MB limit with MIME validation
        ]);

        $client = Client::findOrFail($request->client_id);
        
        if (!$client->google_drive_folder_id) {
            return back()->with('error', 'Google Drive folder for this client is not created yet.');
        }

        $file = $request->file('document_file');
        $originalName = $file->getClientOriginalName();
        $tempPath = $file->getRealPath();

        try {
            $uploaded = $driveService->uploadFile($tempPath, $originalName, $client->google_drive_folder_id);

            if ($uploaded) {
                Document::create([
                    'client_id' => $client->id,
                    'document_type' => $request->document_type,
                    'original_filename' => $originalName,
                    'google_drive_file_id' => $uploaded['id'],
                    'google_drive_file_url' => $uploaded['url'],
                    'google_drive_folder_id' => $client->google_drive_folder_id,
                    'uploaded_by' => auth()->id()
                ]);

                return back()->with('success', 'Document uploaded successfully to Google Drive.');
            }
        } catch (\Exception $e) {
            \Log::error('Uploading document failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload document to Google Drive: ' . $e->getMessage());
        }

        return back()->with('error', 'Failed to upload document.');
    }
}
