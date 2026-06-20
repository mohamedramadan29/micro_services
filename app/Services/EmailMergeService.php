<?php

namespace App\Services;

class EmailMergeService
{
    public function merge(string $content, array $data): string
    {
        $search = [];
        $replace = [];

        foreach ($data as $key => $value) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $value ?? '';
        }

        return str_replace($search, $replace, $content);
    }

    public function mergeSubject(string $subject, array $data): string
    {
        return $this->merge($subject, $data);
    }

    public function mergeBody(string $body, array $data): string
    {
        $merged = $this->merge($body, $data);

        // Convert plain URLs to clickable links if not already HTML
        if (strip_tags($merged) === $merged) {
            $merged = nl2br(e($merged));
        }

        return $merged;
    }

    public function replaceTrackingPixel(string $body, string $trackingUrl): string
    {
        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="" />';
        return $body . "\n" . $pixel;
    }

    public function replaceLinks(string $body, string $baseTrackingUrl): string
    {
        return preg_replace_callback(
            '/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/i',
            function ($matches) use ($baseTrackingUrl) {
                $originalUrl = $matches[1];
                if (str_starts_with($originalUrl, '{{') || str_starts_with($originalUrl, '#') || str_starts_with($originalUrl, 'http')) {
                    $trackingUrl = $baseTrackingUrl . '?url=' . urlencode($originalUrl);
                    return str_replace($matches[1], $trackingUrl, $matches[0]);
                }
                return $matches[0];
            },
            $body
        );
    }

    public function appendUnsubscribeLink(string $body, string $unsubscribeUrl): string
    {
        $link = '<br><hr><p style="font-size:12px;color:#888;">'
              . '<a href="' . $unsubscribeUrl . '">الغاء الاشتراك</a>'
              . ' - إذا كنت لا تريد استلام هذه الإيميلات، يمكنك إلغاء الاشتراك.</p>';

        if (str_contains($body, '{{unsubscribe_url}}')) {
            $body = str_replace('{{unsubscribe_url}}', $unsubscribeUrl, $body);
        } else {
            $body .= $link;
        }

        return $body;
    }
}
