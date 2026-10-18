<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Allow only active admin accounts into the admin panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->account_type !== 'admin' || (int) $user->status !== 1) {
            abort(403, 'غير مصرح لك بالدخول');
        }

        // Roles of the client file «المربعات الخمسة»: the analyst only looks; the content
        // editor changes site content only (texts as drafts; publishing is the manager's).
        $role = \App\Support\StaffPermissions::role($user);
        if (in_array($role, ['analyst', 'content'], true)) {
            $route = (string) $request->route()?->getName();
            $uri = (string) $request->route()?->uri();
            // Legacy pages that change data on a plain link (send a campaign, delete, activate).
            $changesOnGet = (bool) preg_match('#(^|/)(delete|destroy|send|active)(/|$)#', $uri);
            $contentRoute = str_starts_with($route, 'admin.content.') || str_starts_with($route, 'admin.media.') || str_starts_with($route, 'admin.notification-texts.')
                || (str_starts_with($route, 'admin.landing.') && ! preg_match('/\.(publish|unpublish|destroy|delete)$/', $route));
            $writing = ! $request->isMethodSafe() || $changesOnGet;
            if ($writing && ! in_array($route, ['logout', 'admin.logout'], true) && ($role === 'analyst' || ! $contentRoute)) {
                abort(403, 'دورك لا يسمح بهذا الإجراء');
            }
        }

        return $next($request);
    }
}
