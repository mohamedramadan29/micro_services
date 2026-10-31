<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search boxes read the `q` query string as text (str_replace, «like» queries, the search field's value).
 * A crafted `?q[]=x` used to reach them as an array and break the page with a 500; it is dropped here,
 * once for every page, so the page simply shows unfiltered results.
 */
class ScalarSearchQuery
{
    public const KEYS = ['q', 'search', 'filter'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::KEYS as $key) {
            if ($request->query->has($key) && ! is_scalar($request->query->all()[$key] ?? null)) {
                $request->query->remove($key);
            }
        }

        return $next($request);
    }
}
