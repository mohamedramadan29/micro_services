<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\EmailTrackingService;
use Illuminate\Http\Request;

class EmailTrackingController extends Controller
{
    protected EmailTrackingService $trackingService;

    public function __construct(EmailTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function open(string $token)
    {
        $recipient = $this->trackingService->getRecipientByToken($token);

        if ($recipient && $recipient->campaign->tracking_enabled) {
            $this->trackingService->trackOpen(
                $recipient,
                request()->userAgent() ?? '',
                request()->ip() ?? ''
            );
        }

        $pixel = base64_decode(
            'R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
        );

        return response($pixel, 200)->header('Content-Type', 'image/gif');
    }

    public function click(Request $request, string $token)
    {
        $recipient = $this->trackingService->getRecipientByToken($token);
        $url = $request->query('url', '/');

        if ($recipient && $recipient->campaign->tracking_enabled) {
            $this->trackingService->trackClick(
                $recipient,
                $url,
                request()->userAgent() ?? '',
                request()->ip() ?? ''
            );
        }

        return redirect($url);
    }

    public function unsubscribe(string $token)
    {
        $recipient = $this->trackingService->getRecipientByToken($token);

        if ($recipient) {
            $this->trackingService->trackUnsubscribe($recipient);
        }

        return view('admin.email.unsubscribe', [
            'success' => $recipient !== null,
            'email' => $recipient?->email ?? '',
        ]);
    }
}
