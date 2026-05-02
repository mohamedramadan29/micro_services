<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\SocialMedia\SocialMediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishSocialPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 500; // 5 دقائق (لأن رفع الفيديو يأخذ وقت)

    public function __construct(private SocialPost $post)
    {
    }

    public function handle(SocialMediaService $socialMedia): void
    {
        Log::info("بدء نشر البوست #{$this->post->id} على المنصات: " . implode(', ', $this->post->platforms));
        $socialMedia->publishPost($this->post);
        Log::info("انتهى نشر البوست #{$this->post->id} - الحالة: {$this->post->fresh()->status}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("فشل Job نشر البوست #{$this->post->id}: " . $exception->getMessage());
        $this->post->update(['status' => 'failed']);
    }
}
