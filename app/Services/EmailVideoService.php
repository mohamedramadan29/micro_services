<?php

namespace App\Services;

class EmailVideoService
{
    public function generateEmbedHtml(string $url): array
    {
        $videoId = $this->extractYouTubeId($url);
        if ($videoId) {
            $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
            $embedUrl = "https://www.youtube.com/watch?v={$videoId}";
            $html = <<<HTML
<div style="text-align:center; margin:20px 0;">
    <a href="{$embedUrl}" target="_blank" style="display:inline-block; position:relative; max-width:560px; width:100%;">
        <img src="{$thumbnail}" alt="YouTube Video" style="width:100%; height:auto; border-radius:8px;">
        <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:68px; height:48px; background:rgba(0,0,0,0.7); border-radius:12px; display:flex; align-items:center; justify-content:center;">
            <span style="display:inline-block; width:0; height:0; border-style:solid; border-width:12px 0 12px 20px; border-color:transparent transparent transparent #fff; margin-left:4px;"></span>
        </span>
    </a>
    <p style="margin-top:8px; font-size:13px; color:#666;">
        <a href="{$embedUrl}" target="_blank" style="color:#1a73e8; text-decoration:none;">شاهد الفيديو على YouTube →</a>
    </p>
</div>
HTML;
            return ['success' => true, 'html' => $html, 'type' => 'youtube'];
        }

        $vimeoId = $this->extractVimeoId($url);
        if ($vimeoId) {
            $oembed = $this->fetchVimeoOembed($vimeoId);
            $thumbnail = $oembed['thumbnail_url'] ?? 'https://i.vimeocdn.com/video/default.jpg';
            $embedUrl = "https://vimeo.com/{$vimeoId}";
            $html = <<<HTML
<div style="text-align:center; margin:20px 0;">
    <a href="{$embedUrl}" target="_blank" style="display:inline-block; position:relative; max-width:560px; width:100%;">
        <img src="{$thumbnail}" alt="Vimeo Video" style="width:100%; height:auto; border-radius:8px;">
        <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:68px; height:48px; background:rgba(0,0,0,0.7); border-radius:12px; display:flex; align-items:center; justify-content:center;">
            <span style="display:inline-block; width:0; height:0; border-style:solid; border-width:12px 0 12px 20px; border-color:transparent transparent transparent #fff; margin-left:4px;"></span>
        </span>
    </a>
    <p style="margin-top:8px; font-size:13px; color:#666;">
        <a href="{$embedUrl}" target="_blank" style="color:#1a73e8; text-decoration:none;">شاهد الفيديو على Vimeo →</a>
    </p>
</div>
HTML;
            return ['success' => true, 'html' => $html, 'type' => 'vimeo'];
        }

        return ['success' => false, 'message' => 'رابط الفيديو غير مدعوم. يرجى استخدام رابط YouTube أو Vimeo.'];
    }

    private function extractYouTubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
        return $matches[1] ?? null;
    }

    private function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    private function fetchVimeoOembed(string $videoId): array
    {
        try {
            $url = "https://vimeo.com/api/oembed.json?url=https://vimeo.com/{$videoId}";
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                return json_decode($response, true) ?? [];
            }
        } catch (\Exception $e) {
            // fallback
        }
        return [];
    }
}
