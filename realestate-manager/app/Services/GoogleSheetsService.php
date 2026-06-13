<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->client = new Client();
        $credentialsPath = config('google.credentials');
        if (file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        }
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
        $this->spreadsheetId = config('google.sheet_id');
    }

    public function appendRow($values)
    {
        $credentialsPath = config('google.credentials');
        if (!file_exists($credentialsPath) || !$this->spreadsheetId || $this->spreadsheetId === 'paste_from_step_D4') {
            \Log::warning('Google Sheets Service: Credentials or spreadsheet ID missing. Bypassing sheet sync.');
            return null;
        }

        // Initialize header row if sheet is empty
        $this->ensureHeaders();

        $body = new ValueRange([
            'values' => [$values]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS'
        ];

        $result = $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            'Sheet1!A:N',
            $body,
            $params
        );

        $updatedRange = $result->getUpdates()->getUpdatedRange(); // "Sheet1!A10:N10"
        preg_match('/[A-Za-z]+(\d+):/', $updatedRange, $matches);
        return isset($matches[1]) ? intval($matches[1]) : null;
    }

    public function updateRow($rowNumber, $values)
    {
        $credentialsPath = config('google.credentials');
        if (!file_exists($credentialsPath) || !$this->spreadsheetId || $this->spreadsheetId === 'paste_from_step_D4' || !$rowNumber) {
            \Log::warning('Google Sheets Service: Credentials or spreadsheet ID missing. Bypassing sheet sync.');
            return null;
        }

        $body = new ValueRange([
            'values' => [$values]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $range = "Sheet1!A{$rowNumber}:N{$rowNumber}";

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    protected function ensureHeaders()
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'Sheet1!A1:N1');
            $values = $response->getValues();

            if (empty($values)) {
                $headers = [
                    'Client ID',
                    'Client Full Name',
                    'CNIC',
                    'Phone',
                    'Property (Plot No + Block + Location)',
                    'Total Deal Value',
                    'Total Received So Far',
                    'Remaining Balance',
                    'Last Payment Date',
                    'Last Payment Amount',
                    'Payment Method',
                    'Receipt Number',
                    'Drive Folder Link',
                    'Status'
                ];

                $body = new ValueRange([
                    'values' => [$headers]
                ]);

                $params = ['valueInputOption' => 'USER_ENTERED'];

                $this->service->spreadsheets_values->update(
                    $this->spreadsheetId,
                    'Sheet1!A1:N1',
                    $body,
                    $params
                );
            }
        } catch (\Exception $e) {
            \Log::error('Google Sheet ensureHeaders failed: ' . $e->getMessage());
        }
    }
}
