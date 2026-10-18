<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractAgreement as A;
use App\Models\ContractStage;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Service 3, phase 2: the freelancer proposes the agreement, the client approves or asks for changes. */
class ContractAgreementTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $freelancer;
    private Contract $contract;
    private ServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Setting::create(['website_commission' => '10']);
        $this->client = User::factory()->create();
        $this->freelancer = User::factory()->freelancer()->create();

        $this->request = ServiceRequest::create([
            'user_id' => $this->client->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => ServiceRequest::STATUS_IN_PROGRESS,
            'kind' => ServiceRequest::KIND_STAGED, 'project_title' => 'متجر إلكتروني',
            'problem' => 'متجر', 'contact_name' => 'سارة', 'contact_phone' => '0100', 'contact_email' => 's@example.com', 'published_at' => now(),
        ]);
        $this->request->update(['number' => 'REQ-' . (1000 + $this->request->id)]);
        $offer = $this->request->offers()->create(['freelancer_id' => $this->freelancer->id, 'price' => 9000, 'duration_days' => 30, 'scope' => 'متجر كامل بالدفع والشحن', 'message' => str_repeat('رسالة ', 5), 'status' => 'accepted']);
        $this->request->update(['accepted_offer_id' => $offer->id, 'agreed_price' => 9000]);
        $this->contract = Contract::create(['service_request_id' => $this->request->id, 'client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر إلكتروني', 'status' => Contract::STATUS_AGREEMENT])->fresh();
    }

    private function proposal(array $stages = null): array
    {
        return [
            'scope' => 'متجر كامل: الصفحات، سلة الشراء، الدفع عبر EasyKash، ولوحة إدارة المنتجات',
            'out_of_scope' => 'كتابة المحتوى وتصوير المنتجات',
            'client_materials' => 'الشعار، صور المنتجات، بيانات حساب الاستضافة',
            'stages' => $stages ?? [
                ['title' => 'التصميم', 'deliverable' => 'خريطة الصفحات + نموذج أولي', 'duration_days' => 7, 'amount' => 3000, 'revisions' => 2],
                ['title' => 'البرمجة', 'deliverable' => 'نسخة قابلة للمراجعة', 'duration_days' => 14, 'amount' => 4500, 'unlimited_revisions' => 1],
                ['title' => 'الإطلاق', 'deliverable' => 'النسخة النهائية والملفات', 'duration_days' => 5, 'amount' => 1500, 'revisions' => 1],
            ],
        ];
    }

    public function test_the_freelancer_proposes_and_the_client_approves_which_opens_the_stages(): void
    {
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertOk()->assertSee('بانتظار أن يقترح المستقل')->assertDontSee('إرسال الاتفاق للعميل');
        $this->actingAs($this->freelancer)->get(route('contracts.show', $this->contract))->assertOk()
            ->assertSee('اقتراح الاتفاق')->assertSee('متجر كامل بالدفع والشحن'); // starts from the chosen offer

        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), $this->proposal())->assertSessionHasNoErrors();
        $agreement = A::sole();
        $this->assertSame([1, A::STATUS_PROPOSED, 26], [$agreement->version, $agreement->status, $agreement->totalDays()]);
        $this->assertEquals(9000, $agreement->total);
        $this->assertSame([2, null, 1], array_column($agreement->stages, 'revisions'));
        $this->assertNotNull($agreement->freelancer_approved_at);
        Notification::assertSentTo($this->client, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->client)['text'], 'اقترح المستقل اتفاق'));

        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertOk()
            ->assertSee('الاتفاق — نسخة 1')->assertSee('خريطة الصفحات')->assertSee('غير محدود')->assertSee('راجعت الاتفاق وأوافق');

        // Approval needs the confirmation box.
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $agreement]), ['decision' => 'approve'])->assertSessionHasErrors('confirm');
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $agreement]), ['decision' => 'approve', 'confirm' => 1])->assertSessionHas('Success_message');

        $this->contract->refresh();
        $this->assertSame(Contract::STATUS_ACTIVE, $this->contract->status);
        $this->assertNotNull($agreement->fresh()->client_approved_at);
        $stages = $this->contract->stages;
        $this->assertSame(['التصميم', 'البرمجة', 'الإطلاق'], $stages->pluck('title')->all());
        $this->assertSame([ContractStage::STATUS_UNFUNDED], $stages->pluck('status')->unique()->values()->all());
        $this->assertSame([7, 14, 5], $stages->pluck('duration_days')->all());
        $this->assertSame([2, null, 1], $stages->pluck('revisions')->all());
        $this->assertEquals(9000, $this->request->fresh()->agreed_price);
        $this->assertSame(2, $this->request->events()->where('type', ServiceRequestEvent::TYPE_AGREEMENT)->count()); // proposed + approved, in «سجل الطلب»
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'وافق العميل على اتفاق'));

        // Nothing can be proposed or approved again once active.
        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), $this->proposal())->assertSessionHasErrors();
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $agreement]), ['decision' => 'approve', 'confirm' => 1])->assertSessionHasErrors();
        $this->assertSame(3, $this->contract->stages()->count());
    }

    public function test_changes_requested_lead_to_a_new_version_and_old_ones_are_kept(): void
    {
        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), $this->proposal());
        $first = A::sole();
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $first]), ['decision' => 'changes'])->assertSessionHasErrors('note');
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $first]), ['decision' => 'changes', 'note' => 'أريد مرحلتين فقط'])->assertSessionHas('Success_message');
        $this->assertSame([A::STATUS_CHANGES_REQUESTED, 'أريد مرحلتين فقط'], [$first->fresh()->status, $first->fresh()->client_note]);
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'أريد مرحلتين فقط'));

        $this->actingAs($this->freelancer)->get(route('contracts.show', $this->contract))->assertSee('أريد مرحلتين فقط')->assertSee('إرسال نسخة معدّلة');
        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), $this->proposal([
            ['title' => 'التصميم والبرمجة', 'deliverable' => 'الموقع كاملًا للمراجعة', 'duration_days' => 20, 'amount' => 7000, 'revisions' => 3],
            ['title' => 'الإطلاق', 'deliverable' => 'النسخة النهائية', 'duration_days' => 5, 'amount' => 1500, 'revisions' => 1],
        ]))->assertSessionHasNoErrors();

        $second = A::where('version', 2)->sole();
        $this->assertEquals(8500, $second->total);
        $this->assertSame(A::STATUS_SUPERSEDED, $first->fresh()->status);
        // An old version cannot be approved.
        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $first]), ['decision' => 'approve', 'confirm' => 1])->assertSessionHasErrors();
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('النسخ السابقة (1)');

        $this->actingAs($this->client)->post(route('contracts.agreement.respond', [$this->contract, $second]), ['decision' => 'approve', 'confirm' => 1]);
        $this->assertSame(2, $this->contract->stages()->count());
        $this->assertEquals(8500, $this->request->fresh()->agreed_price); // the agreement fixes the value
    }

    public function test_only_the_parties_see_and_act_and_proposals_are_validated(): void
    {
        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('contracts.show', $this->contract))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('contracts.show', $this->contract))->assertOk();
        $this->actingAs($this->client)->post(route('contracts.agreement.propose', $this->contract), $this->proposal())->assertForbidden();

        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), [
            'scope' => 'قصير', 'stages' => [['title' => '', 'deliverable' => '', 'duration_days' => 0, 'amount' => 0, 'revisions' => 50]],
        ])->assertSessionHasErrors(['scope', 'stages.0.title', 'stages.0.deliverable', 'stages.0.duration_days', 'stages.0.amount', 'stages.0.revisions']);
        $this->actingAs($this->freelancer)->post(route('contracts.agreement.propose', $this->contract), ['scope' => str_repeat('نطاق ', 10), 'stages' => array_fill(0, 11, ['title' => 'x', 'deliverable' => 'تسليم', 'duration_days' => 1, 'amount' => 1])])
            ->assertSessionHasErrors('stages');
        $this->assertSame(0, A::count());

        // A link from the request pages.
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $this->request))->assertSee(route('contracts.show', $this->contract))->assertSee('اقترح الاتفاق والمراحل');
    }
}
