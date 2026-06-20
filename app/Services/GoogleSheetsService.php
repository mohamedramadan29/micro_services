<?php

namespace App\Services;

use App\Models\admin\Setting;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected ?GoogleClient $client = null;
    protected ?Sheets $sheets = null;

    public function __construct()
    {
        $this->authenticate();
    }

    protected function authenticate(): void
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json) {
            return;
        }

        try {
            $this->client = new GoogleClient();
            $this->client->setApplicationName('Marwa Email Campaign');
            $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));

            if ($setting->sheets_token_json) {
                $this->client->setAccessToken(json_decode($setting->sheets_token_json, true));
                if ($this->client->isAccessTokenExpired()) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                        $this->client->getRefreshToken()
                    );
                    if (!empty($newToken)) {
                        $this->client->setAccessToken($newToken);
                        $setting->sheets_token_json = json_encode($newToken);
                        $setting->save();
                    }
                }
            }

            $this->sheets = new Sheets($this->client);
        } catch (\Exception $e) {
            Log::error('Google Sheets auth failed: ' . $e->getMessage());
        }
    }

    public function isReady(): bool
    {
        return $this->client !== null && $this->sheets !== null;
    }

    public function getAuthUrl(): ?string
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json) {
            return null;
        }

        $client = new GoogleClient();
        $client->setApplicationName('Marwa Email Campaign');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));
        $client->setRedirectUri(route('admin.email.sheets.callback'));

        return $client->createAuthUrl();
    }

    public function handleCallback(string $authCode): bool
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json) {
            return false;
        }

        $client = new GoogleClient();
        $client->setApplicationName('Marwa Email Campaign');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));
        $client->setRedirectUri(route('admin.email.sheets.callback'));

        $token = $client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($token['error'])) {
            Log::error('Sheets callback error: ' . ($token['error_description'] ?? $token['error']));
            return false;
        }

        $setting->sheets_token_json = json_encode($token);
        $setting->save();

        return true;
    }

    public function readSheet(string $spreadsheetId, string $range = 'A:Z'): array
    {
        if (!$this->isReady()) {
            throw new \Exception('Google Sheets not authenticated');
        }

        try {
            $response = $this->sheets->spreadsheets_values->get($spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (\Exception $e) {
            Log::error('Google Sheets read failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function readPublicSheet(string $spreadsheetId, string $gid = '0'): array
    {
        $url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $csv = @file_get_contents($url, false, $context);

        if (!$csv) {
            throw new \Exception('فشل في قراءة الجدول. تأكد من أن الجدول عام (Anyone with link can view)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csv)));
        return $rows;
    }
}
