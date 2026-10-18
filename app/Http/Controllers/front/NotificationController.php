<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Open a notification: mark it read and go to its page on this site. */
    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $path = (string) ($notification->data['url'] ?? '');
        // Only same-site paths ("/my/requests/…"), never an external URL.
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return redirect('dashboard');
        }

        return redirect(url($path));
    }
}
