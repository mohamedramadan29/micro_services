<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Payments\PaymentGateway::class, \App\Services\Payments\EasyKashPaymentGateway::class);
        // One per request: it remembers the media it already loaded.
        $this->app->scoped(\App\Services\Content\ContentManager::class);
        $this->app->bind(\App\Services\SiteTests\SiteTestEngine::class, \App\Services\SiteTests\PageSpeedEngine::class);
        $this->app->bind(\App\Services\Seo\SearchConsoleClient::class, \App\Services\Seo\GoogleSearchConsoleClient::class);
        // Stateless; the current visit is cached on the request itself.
        $this->app->singleton(\App\Services\Analytics\Tracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();
        \Illuminate\Support\Facades\Event::listen([\Illuminate\Notifications\Events\NotificationSent::class, \Illuminate\Notifications\Events\NotificationFailed::class], \App\Listeners\LogNotificationDelivery::class);
    }
}
