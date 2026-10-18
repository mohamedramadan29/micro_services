<?php

namespace App\Http\Middleware;

use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Services\Analytics\Tracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Records page views of the public site for journey analytics, after the
 * response is ready and only for pages people actually see (GET, 200, HTML).
 * Service pages are recorded as «زيارة الخدمة», the first funnel step.
 */
class TrackVisit
{
    public function __construct(private Tracker $tracker)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $page = $request->isMethod('GET') && ! $request->ajax() && ! $request->expectsJson()
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html');

        if ($page && ! $this->tracker->consented()) {
            // Not recorded; only where they came in is kept in the session, so a visitor
            // from Google who accepts on the next click is not counted as «direct».
            $this->tracker->rememberEntry();
        } elseif ($page) {
            try {
                [$type, $service] = $this->classify($request);
                $this->tracker->record($type, ['sub_category_id' => $service]);
            } catch (\Throwable $e) {
                Log::warning('Visit tracking failed', ['error' => $e->getMessage()]); // never breaks a page
            }
        }

        return $response;
    }

    /** @return array{0: string, 1: ?int} event type and the service (sub-category) it is about */
    private function classify(Request $request): array
    {
        $route = $request->route();
        $name = $route?->getName();

        return match (true) {
            $name === 'requests.create' => ['service_view', SubCategory::whereKey($request->integer('service'))->value('id')],
            in_array($name, ['services.index', 'site-tests.index'], true) => ['service_view', null],
            $route?->uri() !== null && str_ends_with($route->uri(), 'service/{id}-{slug}') => ['service_view', Service::whereKey((int) $route->parameter('id'))->value('sub_cat_id')],
            default => ['page_view', null],
        };
    }
}
