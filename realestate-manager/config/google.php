<?php

return [
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/google/google-service-account.json')),
    'root_folder_id' => env('GOOGLE_DRIVE_ROOT_FOLDER_ID'),
    'sheet_id' => env('GOOGLE_SHEET_ID'),
];
