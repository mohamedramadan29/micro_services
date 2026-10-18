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
use App\Services\Contracts\DisputeService;
use App\Services\Contracts\StageWorkflow;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Service 3, phase 6: staff roles with approvals, ratings after completion, the public dispute policy. */
class ContractRolesAndRatingsTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $freelancer;
    private User $manager;
    private User $reviewer;
    private User $accountant;
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
        $this->manager = User::factory()->admin()->create(); // no role = manager
        $this->reviewer = User::factory()->admin()->create(['admin_role' => 'disputes']);
        $this->accountant = User::factory()->admin()->create(['admin_role' => 'accounts']);

        $this->request = ServiceRequest::create(['user_id' => $this->client->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => 'in_progress', 'kind' => 'staged', 'project_title' => 'متجر', 'problem' => 'متجر', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@example.com']);
        $this->request->update(['number' => 'REQ-' . (1000 + $this->request->id)]);
        $this->contract = Contract::create(['service_request_id' => $this->request->id, 'client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر', 'status' => 'agreement'])->fresh();
        $agreements = app(AgreementService::class);
        $agreements->approve($agreements->propose($this->contract, $this->freelancer, ['scope' => str_repeat('نطاق ', 5), 'stages' => [
            ['title' => 'التصميم', 'deliverable' => 'نموذج أولي', 'duration_days' => 7, 'amount' => 3000, 'revisions' => 1],
        ]]), $this->client);
        $this->contract->refresh();
        app(ContractLedger::class)->fundFromBalance($this->contract, $this->contract->stages()->pluck('id')->all(), $this->client);
        app(StageWorkflow::class)->advance($this->contract);
        app(StageWorkflow::class)->deliver($this->contract->stages()->sole(), $this->freelancer, 'سلّمت', [], []);
    }

    private function dispute(): Dsp
    {
        return app(DisputeService::class)->open($this->contract, $this->client, 'bad_quality', 'التسليم لا يطابق المتفق عليه إطلاقًا');
    }

    private function decide(User $as, Dsp $dispute, array $data)
    {
        return $this->actingAs($as)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'decide']), $data + ['note' => 'بعد مراجعة الأدلة']);
    }

    public function test_a_reviewers_money_decision_waits_for_the_accountant(): void
    {
        $dispute = $this->dispute();
        $this->decide($this->reviewer, $dispute, ['decision' => 'refund'])->assertSessionHas('Success_message', 'حُفظ القرار كمقترح بانتظار اعتماد المحاسب أو المدير.');
        $dispute->refresh();
        $this->assertSame([Dsp::STATUS_OPEN, 'refund'], [$dispute->status, $dispute->proposed['decision']]);
        $this->assertSame(S::STATUS_FUNDED, $this->contract->stages()->sole()->status); // nothing moved
        Notification::assertSentTo($this->accountant, RequestUpdateNotification::class);

        $this->actingAs($this->reviewer)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'approve-proposal']))->assertForbidden();
        $this->actingAs($this->accountant)->get(route('contracts.show', $this->contract))->assertSee('قرار مقترح بانتظار الاعتماد')->assertSee('اعتماد وتنفيذ');
        $this->actingAs($this->accountant)->post(route('contracts.disputes.action', [$this->contract, $dispute, 'approve-proposal']))->assertSessionHas('Success_message');
        $this->assertSame(S::STATUS_REFUNDED, $this->contract->stages()->sole()->status);
        $this->assertSame([Dsp::STATUS_DECIDED, null], [$dispute->fresh()->status, $dispute->fresh()->proposed]);
    }

    public function test_non_money_decisions_by_the_reviewer_and_the_threshold_for_the_accountant(): void
    {
        $dispute = $this->dispute();
        $this->decide($this->accountant, $dispute, ['decision' => 'continue'])->assertForbidden(); // the accountant does not judge disputes
        $this->decide($this->reviewer, $dispute, ['decision' => 'continue'])->assertSessionHas('Success_message', 'سُجّل القرار ونُفّذ، ووصل للطرفين.');

        Setting::first()->update(['contract_approval_threshold' => 2000]);
        $second = $this->dispute();
        $this->decide($this->accountant, $second, ['decision' => 'release'])->assertSessionHas('Success_message', 'حُفظ القرار كمقترح بانتظار اعتماد المحاسب أو المدير.');
        $this->actingAs($this->accountant)->post(route('contracts.disputes.action', [$this->contract, $second, 'approve-proposal']))->assertForbidden(); // 3000 > 2000
        $this->actingAs($this->manager)->post(route('contracts.disputes.action', [$this->contract, $second, 'approve-proposal']))->assertSessionHas('Success_message');
        $this->assertSame(S::STATUS_RELEASED, $this->contract->stages()->sole()->status);
        $this->assertEquals(2700, $this->freelancer->fresh()->balance);
    }

    public function test_a_proposed_change_of_freelancer_is_carried_out_on_approval(): void
    {
        $this->actingAs($this->reviewer)->post(route('contracts.change.decide', $this->contract), ['decision' => 'approve', 'percent' => 20, 'note' => 'مخالفة متكررة للمواعيد'])
            ->assertSessionHas('Success_message');
        $change = Chg::sole();
        $this->assertSame([Chg::STATUS_PENDING, 20], [$change->status, $change->proposed['percent']]);
        $this->assertSame(Contract::STATUS_ACTIVE, $this->contract->fresh()->status);

        $this->actingAs($this->manager)->post(route('contracts.change.proposal', [$this->contract, $change]), ['decision' => 'approve'])->assertSessionHas('Success_message');
        $this->assertSame([Chg::STATUS_APPROVED, 20], [$change->fresh()->status, $change->fresh()->leaving_percent]);
        $this->assertSame(Contract::STATUS_REPLACED, $this->contract->fresh()->status);
    }

    public function test_only_a_manager_sets_roles_and_one_manager_always_remains(): void
    {
        $this->actingAs($this->reviewer)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('admin.staff.index'))->assertOk()->assertSee('مراجع خلافات');

        $this->actingAs($this->manager)->post(route('admin.staff.update'), ['roles' => [$this->manager->id => 'accounts', $this->reviewer->id => 'disputes', $this->accountant->id => 'accounts'], 'threshold' => 0])
            ->assertSessionHasErrors();
        $this->actingAs($this->manager)->post(route('admin.staff.update'), ['roles' => [$this->reviewer->id => 'manager', $this->accountant->id => 'accounts'], 'threshold' => 5000])
            ->assertSessionHas('success');
        $this->assertSame('manager', $this->reviewer->fresh()->admin_role);
        $this->assertEquals(5000, Setting::first()->contract_approval_threshold);
    }

    public function test_ratings_after_completion_and_who_sees_them(): void
    {
        $this->actingAs($this->client)->post(route('contracts.rate', $this->contract), ['stars' => 5])->assertSessionHasErrors(); // not completed yet
        $this->actingAs($this->client)->post(route('contracts.review', [$this->contract, D::sole()]), ['decision' => 'approve']);
        $this->assertSame(Contract::STATUS_COMPLETED, $this->contract->fresh()->status);

        $this->actingAs($this->client)->post(route('contracts.rate', $this->contract), ['stars' => 5, 'comment' => 'ممتاز وملتزم'])->assertSessionHas('Success_message');
        $this->actingAs($this->client)->post(route('contracts.rate', $this->contract), ['stars' => 1])->assertSessionHasErrors();
        $this->actingAs($this->freelancer)->post(route('contracts.rate', $this->contract), ['stars' => 3, 'comment' => 'تأخر في إرسال المواد'])->assertSessionHas('Success_message');
        $this->assertSame([5.0, 1], [(float) $this->freelancer->fresh()->rate, $this->freelancer->fresh()->rating_count]);
        $this->assertSame([3.0, 1], [(float) $this->client->fresh()->client_rating, $this->client->fresh()->client_rating_count]);

        // The freelancer's rating of the client is never shown to the client.
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('ممتاز وملتزم')->assertDontSee('تأخر في إرسال المواد');
        $this->actingAs($this->freelancer)->get(route('contracts.show', $this->contract))->assertSee('تأخر في إرسال المواد');

        // Freelancers see it on the client's next request.
        $category = \App\Models\admin\Category::create(['name' => 'برمجة', 'slug' => 'dev', 'image' => 'x.png', 'status' => 1]);
        $service = \App\Models\admin\SubCategory::create(['name' => 'مواقع', 'slug' => 'web', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $next = ServiceRequest::create(['user_id' => $this->client->id, 'category_id' => $category->id, 'sub_category_id' => $service->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => 'open', 'problem' => 'طلب جديد', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@example.com', 'published_at' => now()]);
        $next->update(['number' => 'REQ-' . (1000 + $next->id)]);
        $other = User::factory()->freelancer()->create();
        $other->specializations()->attach($service->id);
        $this->actingAs($other)->get(route('freelancer.requests.show', $next))->assertOk()->assertSee('تقييم المستقلين لهذا العميل');
    }

    public function test_one_rating_reminder_two_days_after_completion(): void
    {
        $this->actingAs($this->client)->post(route('contracts.review', [$this->contract, D::sole()]), ['decision' => 'approve']);
        $this->actingAs($this->client)->post(route('contracts.rate', $this->contract), ['stars' => 4]);
        Notification::fake();
        $this->travel(2)->days(); $this->travel(1)->minutes();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('rating_reminder: 1');
        $this->artisan('contracts:deadlines')->doesntExpectOutputToContain('rating_reminder');
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'قيّم تجربتك'));
        Notification::assertNotSentTo($this->client, RequestUpdateNotification::class); // already rated
    }

    public function test_the_public_dispute_policy_page(): void
    {
        $this->get(route('policy.disputes'))->assertOk()
            ->assertSee('سياسة الخلافات وحماية الحقوق')->assertSee('للطرف الآخر 3 أيام للرد')->assertSee('لا عمولة على المبلغ المسترد');
        $this->actingAs($this->manager)->get(route('admin.content.index', ['page' => 'policy']))->assertOk()->assertSee('بنود السياسة');
    }
}
