<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A blocked (`block_status = 0`) or deactivated (`status = 0`) account is signed out on its next
 * request, on every device — the login only checks this when signing in.
 */
class LogoutBlockedUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            // A session whose account was deleted: Auth::id() would still return the old id
            // (and break inserts that reference users), so end it quietly.
            if ($request->hasSession() && $request->session()->has(Auth::guard()->getName())) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $next($request);
        }
        if ((string) $user->block_status !== '0' && (int) $user->status !== 0) {
            return $next($request);
        }

        $message = (string) $user->block_status === '0'
            ? 'تم حظر الحساب الخاص بك، من فضلك تواصل مع الإدارة'
            : 'هذا الحساب موقوف، من فضلك تواصل مع الإدارة';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return response()->json(['message' => $message], 401);
        }

        $to = $request->is('admin', 'admin/*') ? url('admin') : route('user_login');

        return redirect($to)->withErrors(['error' => $message]);
    }
}
