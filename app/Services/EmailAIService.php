<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailAIService
{
    protected string $model = 'gpt-4o-mini';

    public function __construct()
    {
        $this->client = \OpenAI::client(config('openai.api_key'));
    }

    public function generateSubject(string $context, ?string $tone = 'professional'): string
    {
        $prompt = "قم بإنشاء عنوان إيميل تسويقي {$tone} باللغة العربية.\n";
        $prompt .= "السياق: {$context}\n";
        $prompt .= "المطلوب: عنوان إيميل واحد فقط (لا تضع علامات اقتباس).";

        return $this->ask($prompt);
    }

    public function generateBody(string $context, string $subject, ?string $tone = 'professional'): string
    {
        $prompt = "قم بكتابة إيميل تسويقي باللغة العربية بطريقة {$tone}.\n";
        $prompt .= "عنوان الإيميل: {$subject}\n";
        $prompt .= "التفاصيل: {$context}\n";
        $prompt .= "يجب أن يكون الإيميل منسقاً بوسوم HTML (h2, p, ul, li, a) ومناسب للإرسال البريد الإلكتروني.\n";
        $prompt .= "لا تضع علامات ```html أو ``` في البداية أو النهاية. أخرج HTML فقط.";

        return $this->ask($prompt);
    }

    public function improveText(string $text, string $instruction): string
    {
        $prompt = "النص التالي باللغة العربية:\n\n{$text}\n\n";
        $prompt .= "المطلوب: {$instruction}\n";
        $prompt .= "أعد كتابة النص فقط بدون إضافات.";

        return $this->ask($prompt);
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        $prompt = "Translate the following text to {$targetLanguage}. Return only the translated text:\n\n{$text}";

        return $this->ask($prompt);
    }

    protected function ask(string $prompt): string
    {
        try {
            $result = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2048,
                'temperature' => 0.7,
            ]);

            return $result->choices[0]->message->content ?? '';
        } catch (\Exception $e) {
            Log::error('AI Service error: ' . $e->getMessage());
            throw $e;
        }
    }
}
