<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractDelivery as D;
use App\Models\ContractDispute as Dsp;
use App\Models\ContractFreelancerChange as Chg;
use App\Models\ContractStage as S;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\Contracts\AgreementService;
use App\Services\Contracts\ContractLedger;
use App\Services\Contracts\StageWorkflow;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Service 3, phase 5: disputes (3 days, frozen money, admin decisions) and changing the freelancer. */
class ContractDisputesTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $freelancer;
    private User $admin;
    private Contract $contract;
    private ServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('private_uploads');
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $this->client = User::factory()->create(['balance' => 10000]);
        $this->freelancer = User::factory()->freelancer()->create(['balance' => 0]);
        $this->admin = User::factory()->admin()->create();

        $this->request = ServiceRequest::create(['user_id' => $this->client->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => 'in_progress', 'kind' => 'staged', 'project_title' => 'متجر', 'problem' => 'متجر بالدفع والشحن', 'contact_name' => 'سارة', 'contact_phone' => '0100', 'contact_email' => 's@example.com', 'budget_min' => 5000]);
        $this->request->update(['number' => 'REQ-' . (1000 + $this->request->id)]);
        $this->request->answers()->create(['label' => 'نوع الموقع', 'type' => 'single_choice', 'value' => 'متجر', 'sort_order' => 1]);
        $this->contract = Contract::create(['service_request_id' => $this->request->id, 'client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر', 'status' => 'agreement'])->fresh();
        $agreements = app(AgreementService::class);
        $agreements->approve($agreements->propose($this->contract, $this->freelancer, ['scope' => str_repeat('نطاق ', 5), 'stages' => [
            ['title' => 'التصميم', 'deliverable' => 'نموذج أولي', 'duration_days' => 7, 'amount' => 3000, 'revisions' => 1],
            ['title' => 'البرمجة', 'deliverable' => 'نسخة للمراجعة', 'duration_days' => 14, 'amount' => 4500, 'revisions' => 1],
            ['title' => 'الإطلاق', 'deliverable' => 'النسخة النهائية', 'duration_days' => 5, 'amount' => 1500, 'revisions' => 1],
        ]]), $this->client);
        $this->contract->refresh();
        app(ContractLedger::class)->fundFromBalance($this->contract, $this->contract->stages()->whereIn('title', ['التصميم', 'البرمجة'])->pluck('id')->all(), $this->client);
        app(StageWorkflow::class)->advance($this->contract);
        app(StageWorkflow::class)->deliver($this->stage('التصميم'), $this->freelancer, 'سلّمت النموذج الأولي', [], [UploadedFile::fake()->create('mockup.pdf', 100, 'application/pdf')]);
        Notification::fake();
    }

    private function stage(string $title): S
    {
        return $this->contract->stages()->where('title', $title)->firstOrFail();
    }

    private function sentTo(User $user, string $needle): bool
    {
        return Notification::sent($user, RequestUpdateNotification::class)->contains(fn ($n) => str_contains($n->toArray($user)['text'], $needle));
    }

    private function openDispute(User $by = null)
    {
        return $this->actingAs($by ?? $this->client)->post(route('contracts.disputes.store', $this->contract), [
            'reason' => 'bad_quality', 'description' => 'النموذج لا يطابق ما اتفقنا عليه في نطاق المرحلة', 'files' => [UploadedFile::fake()->create('evidence.png', 50, 'image/png')],
        ]);
    }

    private function decide(Dsp $dispute, array $data)
    {
        return $this->actingAs($this->admin)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'decide']), $data + ['note' => 'بعد مراجعة الأدلة من الطرفين']);
    }

    public function test_a_dispute_freezes_the_stage_and_the_admin_splits_it(): void
    {
        $this->openDispute()->assertSessionHas('Success_message');
        $dispute = Dsp::sole();
        $this->assertSame([Dsp::STATUS_OPEN, $this->stage('التصميم')->id, 1], [$dispute->status, $dispute->contract_stage_id, $dispute->files()->count()]);
        $this->assertTrue($this->sentTo($this->freelancer, 'أمامك 3 أيام للرد'));
        $this->assertTrue($this->sentTo($this->admin, 'خلاف جديد'));
        $this->openDispute($this->freelancer)->assertSessionHasErrors(); // one open dispute at a time

        // Frozen: the client cannot approve while it is open.
        $this->actingAs($this->client)->post(route('contracts.review', [$this->contract, D::sole()]), ['decision' => 'approve'])->assertSessionHasErrors();

        $this->actingAs($this->client)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'respond']), ['response' => 'لا أستطيع الرد على خلافي'])->assertSessionHasErrors();
        $this->actingAs($this->freelancer)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'respond']), ['response' => 'سلّمت حسب الخريطة المتفق عليها تمامًا'])->assertSessionHas('Success_message');
        $this->assertSame(Dsp::STATUS_AWAITING, $dispute->fresh()->status);

        $this->actingAs($this->client)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'decide']), ['decision' => 'release', 'note' => 'قرار'])->assertForbidden();
        $this->decide($dispute, ['decision' => 'split', 'percent' => 100])->assertSessionHasErrors('percent');
        $this->decide($dispute, ['decision' => 'split', 'percent' => 40])->assertSessionHas('Success_message');

        $design = $this->stage('التصميم');
        $this->assertSame(S::STATUS_SPLIT, $design->status);
        $this->assertEquals([1200, 120, 1080, 1800], [$design->released_amount, $design->commission_amount, $design->freelancer_amount, $design->refunded_amount]);
        $this->assertEquals(1080, $this->freelancer->fresh()->balance);
        $this->assertEquals(2500 + 1800, $this->client->fresh()->balance);
        $this->assertSame([Dsp::STATUS_DECIDED, 'split', 40], [$dispute->fresh()->status, $dispute->fresh()->decision, $dispute->fresh()->freelancer_percent]);
        $this->assertNotNull($this->stage('البرمجة')->started_at); // the next funded stage starts
        $this->assertTrue($this->sentTo($this->client, 'تقسيم المبلغ بنسبة'));
    }

    public function test_unanswered_disputes_go_to_the_admin_and_other_decisions_apply(): void
    {
        $this->openDispute();
        $this->travel(3)->days(); $this->travel(1)->minutes();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('disputes_to_admin: 1');
        $dispute = Dsp::sole();
        $this->assertSame(Dsp::STATUS_AWAITING, $dispute->status);
        $this->assertTrue($this->sentTo($this->admin, 'انتهت مهلة الرد على الخلاف'));

        // A decided revision: the pending delivery goes back, a new deadline, no client revision used.
        $this->decide($dispute, ['decision' => 'revision'])->assertSessionHasErrors('revision_due');
        $this->decide($dispute, ['decision' => 'revision', 'revision_due' => now()->addDays(4)->toDateString()])->assertSessionHas('Success_message');
        $design = $this->stage('التصميم');
        $this->assertSame([D::STATUS_REVISION, 0], [D::sole()->status, $design->revisions_used]);
        $this->assertSame(now()->addDays(4)->toDateString(), $design->due_at->toDateString());

        // Next dispute: the admin refunds the stage to the client's balance.
        $this->openDispute($this->freelancer);
        $second = Dsp::latest('id')->first();
        $this->actingAs($this->freelancer)->post(route('contracts.disputes.action', [$this->contract, $second, 'withdraw']))->assertSessionHas('Success_message');
        $this->assertSame(Dsp::STATUS_WITHDRAWN, $second->fresh()->status);
        $this->openDispute();
        $this->decide(Dsp::latest('id')->first(), ['decision' => 'refund'])->assertSessionHas('Success_message');
        $this->assertSame(S::STATUS_REFUNDED, $this->stage('التصميم')->status);
        $this->assertEquals(2500 + 3000, $this->client->fresh()->balance);
        $this->assertEquals(0, Setting::first()->website_balance); // no commission on a refund
    }

    public function test_changing_the_freelancer_settles_the_money_and_republishes_the_rest(): void
    {
        // The client may ask only after 7 late days.
        $this->actingAs($this->client)->post(route('contracts.change.request', $this->contract), ['reason' => 'المستقل لا يلتزم بالمواعيد'])->assertSessionHasErrors();
        $this->actingAs($this->freelancer)->post(route('contracts.change.request', $this->contract), ['reason' => 'ظروف صحية تمنعني من الإكمال'])->assertSessionHas('Success_message');
        $change = Chg::sole();
        $this->assertSame(['withdrawal', Chg::STATUS_PENDING], [$change->kind, $change->status]);
        $this->assertTrue($this->sentTo($this->admin, 'انسحاب المستقل'));

        $this->actingAs($this->client)->post(route('contracts.change.decide', $this->contract), ['change' => $change->id, 'decision' => 'approve', 'percent' => 30, 'note' => 'x'])->assertForbidden();
        $this->actingAs($this->admin)->post(route('contracts.change.decide', $this->contract), ['change' => $change->id, 'decision' => 'approve', 'percent' => 30, 'note' => 'أُنجز نحو ثلث التصميم'])
            ->assertSessionHas('Success_message');

        // 30% of the design to the leaving freelancer, the rest and the prefunded stage back to the client.
        $this->assertEquals(900 - 90, $this->freelancer->fresh()->balance);
        $this->assertEquals(2500 + 2100 + 4500, $this->client->fresh()->balance);
        $this->assertSame([S::STATUS_SPLIT, S::STATUS_REFUNDED, S::STATUS_UNFUNDED], $this->contract->stages()->pluck('status')->all());
        $this->assertSame(Contract::STATUS_REPLACED, $this->contract->fresh()->status);
        $this->assertSame([ServiceRequest::STATUS_NOT_COMPLETED, 'freelancer_replaced'], [$this->request->fresh()->status, $this->request->fresh()->closed_reason]);

        $next = ServiceRequest::where('continues_contract_id', $this->contract->id)->sole();
        $this->assertSame([ServiceRequest::KIND_STAGED, ServiceRequest::STATUS_OPEN, 'متجر (استكمال)', true], [$next->kind, $next->status, $next->project_title, $next->stopped_project]);
        $this->assertStringContainsString('المتبقي: التصميم', $next->done_before);
        $this->assertEquals(5000, $next->budget_min);
        $this->assertSame(['متجر'], $next->answers()->pluck('value')->all());
        $this->assertSame(['mockup.pdf'], $next->files()->pluck('original_name')->all());
        Storage::disk('private_uploads')->assertExists($next->files()->first()->path);
        $this->assertSame($next->id, $change->fresh()->continuation_request_id);
        $this->assertTrue($this->sentTo($this->client, $next->number));
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('تغيّر المستقل')->assertSee($next->number);
    }

    public function test_the_admin_changes_the_freelancer_directly_or_rejects_a_request_and_sees_the_list(): void
    {
        $this->actingAs($this->freelancer)->post(route('contracts.change.request', $this->contract), ['reason' => 'أريد الانسحاب من المشروع']);
        $this->actingAs($this->admin)->post(route('contracts.change.decide', $this->contract), ['change' => Chg::sole()->id, 'decision' => 'reject', 'note' => 'أكمل المرحلة الحالية أولًا'])->assertSessionHas('Success_message');
        $this->assertSame(Chg::STATUS_REJECTED, Chg::sole()->status);
        $this->assertTrue($this->sentTo($this->freelancer, 'أكمل المرحلة الحالية أولًا'));

        $this->openDispute();
        $this->actingAs(User::factory()->create())->get(route('admin.contracts.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.contracts.index', ['tab' => 'disputes']))->assertOk()->assertSee($this->contract->number);
        $this->actingAs($this->admin)->get(route('contracts.show', $this->contract))->assertOk()->assertSee('قرار الإدارة')->assertSee('تغيير المستقل مباشرة');

        $this->actingAs($this->admin)->post(route('contracts.change.decide', $this->contract), ['decision' => 'approve', 'percent' => 0, 'note' => 'مخالفة واضحة لشروط المنصة'])->assertSessionHas('Success_message');
        $this->assertSame(['admin', Chg::STATUS_APPROVED, 0], [Chg::latest('id')->first()->kind, Chg::latest('id')->first()->status, Chg::latest('id')->first()->leaving_percent]);
        $this->assertSame(Dsp::STATUS_DECIDED, Dsp::sole()->status); // the open dispute closes with the change
        $this->assertEquals(0, $this->freelancer->fresh()->balance);
        $this->assertEquals(10000, $this->client->fresh()->balance); // everything held came back
    }
}
