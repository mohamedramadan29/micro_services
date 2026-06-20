<?php

namespace App\Services;

use App\Models\admin\Setting;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;

class GmailService
{
    protected ?GoogleClient $client = null;
    protected ?Gmail $gmail = null;

    public function __construct()
    {
        $this->authenticate();
    }

    protected function authenticate(): void
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json || !$setting->gmail_token_json) {
            return;
        }

        try {
            $this->client = new GoogleClient();
            $this->client->setApplicationName('Marwa Email Campaign');
            $this->client->setScopes([Gmail::GMAIL_SEND, Gmail::GMAIL_COMPOSE]);
            $this->client->setAccessType('offline');
            $this->client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));
            $this->client->setAccessToken(json_decode($setting->gmail_token_json, true));

            if ($this->client->isAccessTokenExpired()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                    $this->client->getRefreshToken()
                );
                if (!empty($newToken)) {
                    $this->client->setAccessToken($newToken);
                    $setting->gmail_token_json = json_encode($newToken);
                    $setting->save();
                }
            }

            $this->gmail = new Gmail($this->client);
        } catch (\Exception $e) {
            Log::error('Gmail auth failed: ' . $e->getMessage());
        }
    }

    public function isReady(): bool
    {
        return $this->gmail !== null;
    }

    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        if (!$this->isReady()) {
            throw new \Exception('Gmail not authenticated');
        }

        $message = new Message();
        $mime = $this->createMimeMessage($to, $subject, $body, $options);
        $message->setRaw(base64_encode($mime));

        try {
            $this->gmail->users_messages->send('me', $message);
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail send failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createMimeMessage(string $to, string $subject, string $body, array $options): string
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (!empty($options['reply_to'])) {
            $headers .= "Reply-To: {$options['reply_to']}\r\n";
        }
        if (!empty($options['cc'])) {
            $headers .= "Cc: {$options['cc']}\r\n";
        }
        if (!empty($options['bcc'])) {
            $headers .= "Bcc: {$options['bcc']}\r\n";
        }
        if (!empty($options['from_name']) && !empty($options['from_email'])) {
            $headers .= "From: {$options['from_name']} <{$options['from_email']}>\r\n";
        }

        $headers .= "\r\n{$body}";

        return $headers;
    }

    public function getAuthUrl(): ?string
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json) {
            return null;
        }

        $this->client = new GoogleClient();
        $this->client->setApplicationName('Marwa Email Campaign');
        $this->client->setScopes([Gmail::GMAIL_SEND, Gmail::GMAIL_COMPOSE]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));

        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $authCode): bool
    {
        $setting = Setting::first();
        if (!$setting || !$setting->gmail_credentials_json) {
            return false;
        }

        $this->client = new GoogleClient();
        $this->client->setApplicationName('Marwa Email Campaign');
        $this->client->setScopes([Gmail::GMAIL_SEND, Gmail::GMAIL_COMPOSE]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setAuthConfig(json_decode($setting->gmail_credentials_json, true));

        $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($token['error'])) {
            Log::error('Gmail callback error: ' . ($token['error_description'] ?? $token['error']));
            return false;
        }

        $setting->gmail_token_json = json_encode($token);
        $setting->save();

        return true;
    }
}
