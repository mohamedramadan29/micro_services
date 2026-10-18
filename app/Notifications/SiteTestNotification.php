<?php

namespace App\Notifications;

use App\Models\SiteTest;

/**
 * To admins about a site check (spec pages 12 and 17): «yes» is a green card
 * with the freelancers link once published, «no» a red one; a full-report
 * request is neutral. The bell renders data.tone as the colour.
 */
class SiteTestNotification extends ServiceRequestNotification
{
    protected ?string $emailKey = 'admin_alerts';

    /** @param  string  $event  report | yes | no | published | norule */
    public function __construct(protected SiteTest $test, protected string $event)
    {
    }

    protected function text(): string
    {
        $name = $this->test->contact_name ?: ($this->test->user?->name ?: 'عميل');
        $service = $this->test->suggestedService?->name ?? 'الخدمة المقترحة';

        $key = match ($this->event) {
            'yes' => 'site_tests.offer_yes',
            'no' => 'site_tests.offer_no',
            'norule' => 'site_tests.no_rule',
            'published' => 'site_tests.published',
            default => 'site_tests.report_requested',
        };

        return \App\Support\Msg::t($key, [
            'name' => $name, 'service' => $service, 'number' => $this->test->number,
            'host' => $this->test->host, 'request' => $this->test->serviceRequest?->number,
        ]);
    }

    protected function path(): string
    {
        // The green «published» card opens the request it created.
        return $this->event === 'published' && $this->test->serviceRequest
            ? '/admin/requests/' . $this->test->serviceRequest->number
            : '/admin/site-tests/' . $this->test->number;
    }

    public function toArray(object $notifiable): array
    {
        return parent::toArray($notifiable) + [
            'kind' => 'site_test',
            'tone' => match ($this->event) {
                'yes', 'published' => 'success',
                'no' => 'danger',
                default => 'info',
            },
            // Only an accepted, published request links to the freelancers' page.
            'freelancers_url' => $this->event === 'published' && $this->test->serviceRequest ? '/client-requests/' . $this->test->serviceRequest->number : null,
            'details' => $this->event === 'published' && $this->test->serviceRequest ? 'رابط الطلب عند المستقلين: ' . url('/client-requests/' . $this->test->serviceRequest->number) : null,
        ];
    }
}
