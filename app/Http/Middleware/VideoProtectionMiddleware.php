<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\NewCourseLesson;
use Symfony\Component\HttpFoundation\Response;

class VideoProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lessonId = $request->route('lessonId');

        if (!$lessonId) {
            return response()->json(['error' => 'Lesson ID required'], 400);
        }

        $lesson = NewCourseLesson::find($lessonId);

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found'], 404);
        }

        // Check if user has access to this lesson
        // For now, we'll allow access to free lessons
        // You can implement your own access control logic here

        // Add security headers
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.youtube.com https://www.youtube-nocookie.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; media-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com");

        return $response;
    }
}
