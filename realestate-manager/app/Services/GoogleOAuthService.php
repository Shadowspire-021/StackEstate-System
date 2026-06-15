<?php

namespace App\Services;

use App\Models\Setting;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GoogleOAuthService
{
    protected ?Client $client = null;

    public const TOKEN_KEY = 'google_oauth_token';
    public const DRIVE_FOLDER_KEY = 'google_drive_folder_id';
    public const SHEETS_SPREADSHEET_KEY = 'google_sheets_spreadsheet_id';

    public function getClient(): Client
    {
        if ($this->client === null) {
            $clientId     = config('google.oauth.client_id');
            $clientSecret = config('google.oauth.client_secret');
            $redirectUri  = config('google.oauth.redirect_uri');

            if (!$clientId || !$clientSecret) {
                throw new \RuntimeException(
                    'Google OAuth credentials missing. Ensure GOOGLE_OAUTH_CLIENT_ID and GOOGLE_OAUTH_CLIENT_SECRET are set in your .env file (or config:cache is up-to-date).'
                );
            }

            $this->client = new Client();
            $this->client->setClientId($clientId);
            $this->client->setClientSecret($clientSecret);
            $this->client->setRedirectUri($redirectUri);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setIncludeGrantedScopes(true);
        }
        return $this->client;
    }

    public function getAuthUrl(string $scope, string $state = ''): string
    {
        $client = $this->getClient();
        $client->setScopes($scope);
        if ($state) {
            $client->setState($state);
        }
        return $client->createAuthUrl();
    }

    public function handleCallback(string $authorizationCode): array
    {
        $client = $this->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($authorizationCode);

        if (isset($token['error'])) {
            Log::error('Google OAuth: Failed to fetch token', [
                'error' => $token['error_description'] ?? $token['error'],
            ]);
            return ['success' => false, 'error' => $token['error_description'] ?? $token['error']];
        }

        $this->storeToken($token);

        return ['success' => true, 'token' => $token];
    }

    public function storeToken(array $token): void
    {
        $encrypted = Crypt::encryptString(json_encode($token));
        Setting::setValue(self::TOKEN_KEY, $encrypted, 'google');
    }

    public function getStoredToken(): ?array
    {
        $encrypted = Setting::getValue(self::TOKEN_KEY);
        if (!$encrypted) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($encrypted);
            $token = json_decode($decrypted, true);
            if (!is_array($token) || !isset($token['access_token'])) {
                return null;
            }
            return $token;
        } catch (\Exception $e) {
            Log::error('Google OAuth: Failed to decrypt token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getValidAccessToken(): ?string
    {
        $token = $this->getStoredToken();
        if (!$token) {
            return null;
        }

        $client = $this->getClient();

        if ($this->isTokenExpired($token)) {
            if (!isset($token['refresh_token'])) {
                Log::warning('Google OAuth: Token expired with no refresh token');
                return null;
            }
            try {
                $newToken = $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                if (isset($newToken['error'])) {
                    Log::error('Google OAuth: Token refresh failed', ['error' => $newToken['error']]);
                    return null;
                }
                $merged = array_merge($token, $newToken);
                $this->storeToken($merged);
                $token = $merged;
            } catch (\Exception $e) {
                Log::error('Google OAuth: Token refresh exception', ['error' => $e->getMessage()]);
                return null;
            }
        }

        return $token['access_token'];
    }

    public function applyAccessToken(Client $client): bool
    {
        $token = $this->getStoredToken();
        if (!$token) {
            return false;
        }

        if ($this->isTokenExpired($token)) {
            $accessToken = $this->getValidAccessToken();
            if (!$accessToken) {
                return false;
            }
            $token = $this->getStoredToken();
        }

        $client->setAccessToken($token);
        return true;
    }

    public function isTokenExpired(?array $token = null): bool
    {
        if ($token === null) {
            $token = $this->getStoredToken();
        }
        if (!$token || !isset($token['expires_in'])) {
            return true;
        }
        $createdAt = $token['created'] ?? 0;
        return (time() - $createdAt) >= ($token['expires_in'] - 60);
    }

    public function hasDriveToken(): bool
    {
        return $this->getStoredToken() !== null;
    }

    public function hasSheetsToken(): bool
    {
        return $this->hasDriveToken();
    }

    public function revokeToken(): void
    {
        $token = $this->getStoredToken();
        if ($token && isset($token['access_token'])) {
            try {
                $client = $this->getClient();
                $client->revokeToken($token['access_token']);
            } catch (\Exception $e) {
                Log::warning('Google OAuth: Token revocation failed', ['error' => $e->getMessage()]);
            }
        }
        Setting::setValue(self::TOKEN_KEY, '', 'google');
        Setting::setValue(self::DRIVE_FOLDER_KEY, '', 'google');
        Setting::setValue(self::SHEETS_SPREADSHEET_KEY, '', 'google');
    }

    public function getDriveService(): ?Drive
    {
        $client = new Client();
        if (!$this->applyAccessToken($client)) {
            return null;
        }
        $client->addScope(Drive::DRIVE_FILE);
        return new Drive($client);
    }

    public function getSheetsService(): ?Sheets
    {
        $client = new Client();
        if (!$this->applyAccessToken($client)) {
            return null;
        }
        $client->addScope(Sheets::SPREADSHEETS);
        return new Sheets($client);
    }
}