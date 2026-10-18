<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountTypeChosen
{
    /**
     * Send users without an account type (e.g. Google sign-ups, old accounts)
     * to pick one first, then bring them back here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && blank($user->account_type)) {
            if ($request->isMethod('get')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('account.type');
        }

        return $next($request);
    }
}
