<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Services\EmailAIService;
use App\Services\EmailVideoService;
use Illuminate\Http\Request;

class EmailAIController extends Controller
{
    public function __construct(
        protected EmailAIService $aiService,
        protected EmailVideoService $videoService
    ) {}

    public function embedVideo(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:500',
        ], [
            'url.required' => 'رابط الفيديو مطلوب',
            'url.url' => 'يرجى إدخال رابط صحيح',
        ]);

        $result = $this->videoService->generateEmbedHtml($request->url);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:subject,body,improve,translate',
            'context' => 'required|string|max:2000',
            'subject' => 'required_if:action,body|string|max:500',
            'tone' => 'nullable|in:professional,friendly,urgent,formal,persuasive',
            'text' => 'required_if:action,improve,translate|string',
            'instruction' => 'required_if:action,improve|string|max:500',
            'language' => 'required_if:action,translate|string|max:100',
        ], [
            'action.required' => 'نوع العملية مطلوب',
            'context.required' => 'السياق مطلوب',
        ]);

        try {
            $result = match ($request->action) {
                'subject' => $this->aiService->generateSubject($request->context, $request->tone),
                'body' => $this->aiService->generateBody($request->context, $request->subject, $request->tone),
                'improve' => $this->aiService->improveText($request->text, $request->instruction),
                'translate' => $this->aiService->translateText($request->text, $request->language),
            };

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }
}
