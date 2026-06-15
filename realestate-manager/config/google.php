<?php

return [
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/google/google-service-account.json')),

    'root_folder_id' => env('GOOGLE_DRIVE_ROOT_FOLDER_ID'),
    'sheet_id'       => env('GOOGLE_SHEET_ID'),

    /*
     * OAuth 2.0 client credentials for Google API access.
     * Used by the "Connect with Google" flow in Settings.
     * Generate at https://console.cloud.google.com/apis/credentials
     */
    'oauth' => [
        'client_id'     => env('GOOGLE_OAUTH_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
        'redirect_uri'  => env('GOOGLE_OAUTH_REDIRECT_URI'),
    ],
];
