<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\NewOfferNotification;
use App\Notifications\RequestUpdateNotification;
use App\Services\ServiceRequests\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestNotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function offer(): RequestOffer
    {
        $request = new ServiceRequest(['number' => 'REQ-1001', 'service_name' => 'إعلانات']);
        $offer = new RequestOffer();
        $offer->setRelation('request', $request);

        return $offer;
    }

    public function test_defaults_email_the_important_events_only(): void
    {
        $user = User::factory()->create();

        $this->assertSame(['database', 'mail'], (new NewOfferNotification($this->offer()))->via($user));
        $this->assertSame(['database'], (new RequestUpdateNotification('x', '/x'))->via($user));
        $this->assertFalse(NotificationSettings::emailEnabled('offer_messages'));
    }

    public function test_admin_turns_emails_off_and_the_bell_stays(): void
    {
        $admin = User::factory()->admin()->create();
        Setting::create(['website_commission' => '10']);

        $this->actingAs($admin)->post('admin/public_settings/update', [
            'website_title' => 'نفذها', 'website_desc' => '', 'website_color' => '#3fb697',
            'website_commission' => 10, 'repeat_client_threshold' => 2,
            'request_emails' => ['admin_alerts', 'offer_messages'],
        ])->assertSessionHasNoErrors();

        $this->assertFalse(NotificationSettings::emailEnabled('new_offer'));
        $this->assertTrue(NotificationSettings::emailEnabled('offer_messages'));
        $this->assertSame(['database'], (new NewOfferNotification($this->offer()))->via($admin));
        $this->assertSame(['database', 'mail'], (new RequestUpdateNotification('x', '/x', 'admin_alerts'))->via($admin));

        $this->actingAs($admin)->get('admin/public_settings')->assertOk()->assertSee('إرسال التنبيهات بالبريد');
    }
}
