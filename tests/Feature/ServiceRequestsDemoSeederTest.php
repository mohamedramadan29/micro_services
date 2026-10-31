<?php

namespace Tests\Feature;

use App\Models\RequestOffer;
use App\Models\ServiceForm;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceRequests\ClientClassifier;
use Database\Seeders\ServiceRequestsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestsDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_covers_every_state_and_runs_twice_safely(): void
    {
        User::factory()->admin()->create();

        $this->seed(ServiceRequestsDemoSeeder::class);
        $this->seed(ServiceRequestsDemoSeeder::class);

        $client = User::where('email', 'demo-client@nafizha.test')->firstOrFail();
        $this->assertSame(5, ServiceRequest::where('user_id', $client->id)->count());
        $this->assertSame(2, ServiceForm::published()->whereHas('subCategory', fn ($q) => $q->where('status', 1))->count()); // + the hidden companies form
        foreach ([ServiceRequest::STATUS_OPEN, ServiceRequest::STATUS_INQUIRY, ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_NOT_COMPLETED] as $status) {
            $this->assertTrue(ServiceRequest::where('status', $status)->exists(), "missing $status");
        }
        $this->assertSame(1, RequestOffer::where('status', RequestOffer::STATUS_IN_DISCUSSION)->count());

        // The answers went through the real form rules, including the conditional link.
        $this->assertTrue(ServiceRequest::first()->answers()->where('label', 'ما رابط الموقع؟')->exists());

        // The quiet request is picked up by the one-hour alert.
        $this->artisan('requests:alert-no-offers')->expectsOutputToContain('1 request');

        $tags = app(ClientClassifier::class)->classify([$client->id])->get($client->id)['tags'];
        $this->assertSame(['repeat', 'completed_before', 'answering'], $tags);
    }

    public function test_it_refuses_to_run_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        // Called directly: the seeder's own guard, not db:seed's confirmation prompt.
        $this->app->make(ServiceRequestsDemoSeeder::class)->run();

        $this->assertSame(0, ServiceRequest::count());
        $this->assertNull(User::where('email', 'demo-client@nafizha.test')->first());
    }
}
