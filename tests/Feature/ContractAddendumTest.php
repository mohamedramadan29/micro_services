<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractAddendum;
use App\Models\ContractDelivery;
use App\Models\ContractPause;
use App\Models\ContractStage;
use App\Models\User;
use App\Services\Contracts\ContractLedger;
use App\Services\Contracts\StageWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Service 3, audit group 3: addendum after the start and the team's tools without a dispute. */
class ContractAddendumTest extends TestCase
{
    use RefreshDatabase;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $client = User::factory()->create(['balance' => 1000]);
        $freelancer = User::factory()->freelancer()->create();
        $this->contract = Contract::create(['client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'موقع شركة', 'status' => Contract::STATUS_ACTIVE]);
        foreach ([300, 200] as $i => $amount) {
            $this->contract->stages()->create(['sort_order' => $i + 1, 'title' => 'مرحلة ' . ($i + 1), 'deliverable' => 'تسليم المرحلة', 'duration_days' => 5,
                'amount' => $amount, 'revisions' => 2, 'status' => ContractStage::STATUS_UNFUNDED]);
        }
        $first = $this->contract->stages()->first();
        app(ContractLedger::class)->fundFromBalance($this->contract, [$first->id], $client);
        app(StageWorkflow::class)->advance($this->contract);
    }

    private function stage(int $order): ContractStage
    {
        return $this->contract->stages()->where('sort_order', $order)->first();
    }

    public function test_an_addendum_applies_only_after_the_other_side_approves(): void
    {
        $first = $this->stage(1);
        $second = $this->stage(2);
        $due = $first->due_at;

        $this->actingAs($this->contract->freelancer)->post(route('contracts.addenda.store', $this->contract), [
            'reason' => 'العميل طلب صفحة إضافية خارج النطاق',
            'stages' => [
                $first->id => ['title' => $first->title, 'duration_days' => 7, 'amount' => 250, 'revisions' => 2],
                $second->id => ['remove' => 1],
            ],
            'new' => [['title' => 'صفحة المدونة', 'deliverable' => 'تصميم وبرمجة المدونة', 'duration_days' => 4, 'amount' => 400, 'revisions' => 1]],
        ])->assertSessionHasNoErrors();

        $addendum = ContractAddendum::first();
        $this->assertSame(ContractAddendum::STATUS_PROPOSED, $addendum->status);
        $this->assertSame(500.0, (float) $addendum->total_before);
        $this->assertSame(650.0, (float) $addendum->total_after);
        $this->assertSame(2, $this->contract->stages()->count()); // nothing changed yet
        $this->actingAs($this->contract->client)->get(route('contracts.show', $this->contract))->assertSee('إضافة مرحلة «صفحة المدونة»')->assertSee('حذف المرحلة «مرحلة 2»');

        // The proposer cannot approve their own addendum.
        $this->actingAs($this->contract->freelancer)->post(route('contracts.addenda.action', [$this->contract, $addendum, 'approve']))->assertSessionHasErrors();

        $balance = (float) $this->contract->client->fresh()->balance;
        $this->actingAs($this->contract->client)->post(route('contracts.addenda.action', [$this->contract, $addendum, 'approve']))->assertSessionHasNoErrors();

        $stages = $this->contract->stages()->get();
        $this->assertSame(['مرحلة 1', 'صفحة المدونة'], $stages->pluck('title')->all());
        $this->assertSame([1, 2], $stages->pluck('sort_order')->all());
        $first->refresh();
        $this->assertSame(250.0, (float) $first->amount);
        $this->assertTrue($first->due_at->equalTo($due->copy()->addDays(2)));
        $this->assertSame($balance + 50, (float) $this->contract->client->fresh()->balance); // lowered funded stage
        $this->assertSame(ContractAddendum::STATUS_APPROVED, $addendum->fresh()->status);
    }

    public function test_a_funded_stage_cannot_be_raised_and_only_one_addendum_is_open(): void
    {
        $first = $this->stage(1);
        $this->actingAs($this->contract->client)->post(route('contracts.addenda.store', $this->contract), [
            'reason' => 'أريد زيادة مبلغ المرحلة الأولى',
            'stages' => [$first->id => ['amount' => 400]],
        ])->assertSessionHasErrors();

        $this->actingAs($this->contract->client)->post(route('contracts.addenda.store', $this->contract), [
            'reason' => 'نحتاج تعديلًا إضافيًا في المرحلة الثانية',
            'stages' => [$this->stage(2)->id => ['revisions' => 4]],
        ])->assertSessionHasNoErrors();
        $this->actingAs($this->contract->freelancer)->post(route('contracts.addenda.store', $this->contract), [
            'reason' => 'ملحق آخر في نفس الوقت',
            'new' => [['title' => 'إضافة', 'deliverable' => 'شيء إضافي', 'duration_days' => 2, 'amount' => 50]],
        ])->assertSessionHasErrors();

        $addendum = ContractAddendum::first();
        $this->actingAs($this->contract->freelancer)->post(route('contracts.addenda.action', [$this->contract, $addendum, 'reject']), ['note' => 'لا'])->assertSessionHasNoErrors();
        $this->assertSame(2, $this->stage(2)->revisions);
        $this->actingAs($this->contract->client)->get(route('contracts.show', $this->contract))->assertOk()->assertSee('رُفض')->assertSee('اقتراح ملحق');
    }

    public function test_team_extends_approves_for_the_client_and_ends_a_pause(): void
    {
        $admin = User::factory()->admin()->create();
        $first = $this->stage(1);
        $due = $first->due_at;

        $this->actingAs($admin)->post(route('contracts.admin.extend', [$this->contract, $first]), ['days' => 3, 'reason' => 'ظرف طارئ للمستقل'])->assertSessionHasNoErrors();
        $this->assertTrue($first->fresh()->due_at->equalTo($due->copy()->addDays(3)));

        $pause = $first->pauses()->create(['requested_by' => $this->contract->freelancer_id, 'request' => 'أحتاج الشعار', 'status' => ContractPause::STATUS_ACTIVE, 'started_at' => now()->subDay()]);
        $this->actingAs($admin)->post(route('contracts.admin.end-pause', [$this->contract, $pause]), ['reason' => 'العميل لا يرد', 'extend' => 1])->assertSessionHasNoErrors();
        $this->assertSame(ContractPause::STATUS_RESUMED, $pause->fresh()->status);
        $this->assertTrue($first->fresh()->due_at->gt($due->copy()->addDays(3)));

        $delivery = $first->deliveries()->create(['freelancer_id' => $this->contract->freelancer_id, 'note' => 'تم', 'status' => ContractDelivery::STATUS_SUBMITTED, 'review_due_at' => now()->subDay()]);
        $this->actingAs($this->contract->client)->post(route('contracts.admin.approve', [$this->contract, $delivery]), ['reason' => 'محاولة من غير الإدارة'])->assertForbidden();
        $reviewer = User::factory()->admin()->create(['admin_role' => 'disputes']);
        $this->actingAs($reviewer)->post(route('contracts.admin.approve', [$this->contract, $delivery]), ['reason' => 'راجعنا التسليم وهو مطابق'])->assertSessionHasErrors();

        $this->actingAs($admin)->post(route('contracts.admin.approve', [$this->contract, $delivery]), ['reason' => 'راجعنا التسليم وهو مطابق'])->assertSessionHasNoErrors();
        $this->assertSame(ContractStage::STATUS_RELEASED, $first->fresh()->status);
        $this->actingAs($admin)->get(route('contracts.show', $this->contract))->assertOk();
    }

    public function test_admin_board_shows_stage_money_deadline_and_who_it_waits_for(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.contracts.index'))->assertOk()
            ->assertSee($this->contract->number)->assertSee('1/2 — مرحلة 1')->assertSee('تسليم المرحلة');
        $this->actingAs($admin)->get(route('admin.contracts.index', ['waiting' => 'freelancer', 'sort' => 'due']))->assertOk()->assertSee($this->contract->number);
        $this->actingAs($admin)->get(route('admin.contracts.index', ['waiting' => 'client']))->assertOk()->assertDontSee($this->contract->number);
        $this->actingAs($admin)->get(route('admin.contracts.index', ['status' => Contract::STATUS_COMPLETED, 'sort' => 'value']))->assertOk()->assertDontSee($this->contract->number);
    }

    public function test_manager_edits_the_policy_and_it_drives_deadlines_reasons_and_templates(): void
    {
        $manager = User::factory()->admin()->create();
        $reviewer = User::factory()->admin()->create(['admin_role' => 'disputes']);
        $this->actingAs($reviewer)->get(route('admin.contract-policy.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.contract-policy.index'))->assertOk()->assertSee('قوالب المراحل');

        $numbers = collect(\App\Services\Contracts\ContractPolicy::NUMBERS)->map(fn ($d) => $d['default'])->all();
        $numbers['review_days'] = 9;
        $payload = [
            'numbers' => $numbers,
            'reasons' => [['key' => 'big_delay', 'label' => 'تأخر طويل'], ['key' => '', 'label' => 'إخلال بالسرية']],
            'templates' => [['name' => 'تصميم ثم برمجة', 'stages' => "التصميم | واجهات كاملة | 40\nالبرمجة | موقع يعمل | 60"]],
        ];
        $bad = $payload;
        $bad['templates'][0]['stages'] = "التصميم | واجهات | 40\nالبرمجة | موقع | 50";
        $this->actingAs($manager)->post(route('admin.contract-policy.save'), $bad)->assertSessionHasErrors();
        $this->actingAs($manager)->post(route('admin.contract-policy.save'), $payload)->assertSessionHas('success');

        $reasons = \App\Services\Contracts\ContractPolicy::disputeReasons();
        $this->assertSame('تأخر طويل', $reasons['big_delay']);
        $this->assertContains('إخلال بالسرية', $reasons);
        $this->assertSame('جودة غير مقبولة', \App\Services\Contracts\ContractPolicy::disputeReasonLabel('bad_quality')); // older disputes keep a label

        $stages = \App\Models\ContractAgreement::defaultStages(1000, 10);
        $this->assertSame(['التصميم', 'البرمجة'], array_column($stages, 'title'));
        $this->assertSame([400.0, 600.0], array_column($stages, 'amount'));

        $first = $this->stage(1);
        $delivery = app(StageWorkflow::class)->deliver($first, $this->contract->freelancer, 'تم التسليم', [], []);
        $this->assertSame(9, (int) round(now()->diffInDays($delivery->review_due_at)));
    }
}
