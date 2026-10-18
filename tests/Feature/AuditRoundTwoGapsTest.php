<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\Contract;
use App\Models\ContractFreelancerChange;
use App\Models\ContractPolicyChange;
use App\Models\ContractReplyRequest;
use App\Models\ContractStage;
use App\Models\NotificationLog;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Content\ContentManager;
use App\Services\Contracts\ContractAdminTools;
use App\Services\Contracts\ContractLedger;
use App\Services\Contracts\ContractPolicy;
use App\Services\SiteTests\SiteTestSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/** Small gaps from the second audit of the six client files (2026-10-17). */
class AuditRoundTwoGapsTest extends TestCase
{
    use RefreshDatabase;

    private function contract(): Contract
    {
        $client = User::factory()->create(['balance' => 1000]);
        $freelancer = User::factory()->freelancer()->create();
        $contract = Contract::create(['client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'متجر', 'status' => Contract::STATUS_ACTIVE]);
        $contract->stages()->create(['sort_order' => 1, 'title' => 'أولى', 'deliverable' => 'x', 'duration_days' => 5, 'amount' => 100, 'revisions' => 2,
            'status' => ContractStage::STATUS_FUNDED, 'started_at' => now(), 'due_at' => now()->addDays(5)]);
        $contract->stages()->create(['sort_order' => 2, 'title' => 'ثانية', 'deliverable' => 'x', 'duration_days' => 7, 'amount' => 200, 'revisions' => 2, 'status' => ContractStage::STATUS_UNFUNDED]);

        return $contract->fresh();
    }

    public function test_expected_end_and_no_funding_while_a_freelancer_change_waits(): void
    {
        $contract = $this->contract();
        $this->assertSame(now()->addDays(12)->format('Y-m-d'), $contract->expectedEnd()->format('Y-m-d'));

        $contract->freelancerChanges()->create(['requested_by' => $contract->client_id, 'kind' => 'client', 'reason' => 'تأخر', 'leaving_freelancer_id' => $contract->freelancer_id, 'status' => ContractFreelancerChange::STATUS_PENDING]);
        $this->expectException(RuntimeException::class);
        app(ContractLedger::class)->fundFromBalance($contract, [$contract->stages[1]->id], $contract->client);
    }

    public function test_the_team_asks_both_sides_for_a_reply_by_a_date(): void
    {
        $contract = $this->contract();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('contracts.admin.reply-request', $contract), ['question' => 'هل وصل الشعار؟', 'days' => 2])->assertSessionHasNoErrors();
        $reply = ContractReplyRequest::sole();
        $this->actingAs($contract->client)->get(route('contracts.show', $contract))->assertSee('هل وصل الشعار؟')->assertSee('إرسال ردي');
        $this->actingAs($contract->client)->post(route('contracts.replies.answer', [$contract, $reply]), ['reply' => 'نعم أرسلته'])->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create())->post(route('contracts.replies.answer', [$contract, $reply]), ['reply' => 'x'])->assertSessionHasErrors();

        $this->travel(3)->days();
        $this->assertSame(1, app(ContractAdminTools::class)->tick()); // the freelancer never answered
        $this->assertSame(0, app(ContractAdminTools::class)->tick()); // told once
        $this->actingAs($admin)->get(route('contracts.show', $contract))->assertSee('نعم أرسلته')->assertSee('ملخص للإدارة')->assertSee('سجل المشروع');
    }

    public function test_policy_changes_are_logged_with_who_and_what(): void
    {
        $manager = User::factory()->admin()->create(['name' => 'المدير العام']);
        $this->actingAs($manager);
        ContractPolicy::save(['review_days' => 8]);
        $change = ContractPolicyChange::sole();
        $this->assertSame([5, 8], $change->changes['review_days']);
        $this->get(route('admin.contract-policy.index'))->assertSee('سجل التعديلات')->assertSee('المدير العام');
    }

    public function test_cards_move_inside_their_own_group(): void
    {
        $content = app(ContentManager::class);
        $cards = $content->blocks('home.cards');
        $third = $cards[2];
        $content->move($third, 1); // down: there is no primary card after it, so it stays
        $content->publish(['home.cards']);
        $this->assertSame(['اطلب عرضًا لمشروعك', 'شخّص مشروعك مجانًا', 'مشروعك ما بيوقف', 'انشر مشروعك', 'انضم كمستقل'], $content->items('home.cards')->pluck('title')->all());

        $content->move($cards[4], -1); // «انضم كمستقل» before «انشر مشروعك»
        $content->publish(['home.cards']);
        $this->assertSame(['انضم كمستقل', 'انشر مشروعك'], $content->items('home.cards')->where('group', 'secondary')->pluck('title')->values()->all());
    }

    public function test_request_source_is_shown_and_filtered_for_the_team_and_the_client_sees_a_timeline(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();
        $make = fn (string $source, string $title) => tap(ServiceRequest::create(['user_id' => $client->id, 'service_name' => 'خدمة', 'path' => 'execute', 'status' => 'open', 'source' => $source,
            'project_title' => $title, 'problem' => 'x', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@x.x', 'published_at' => now()]), fn ($r) => $r->update(['number' => 'REQ-' . (1000 + $r->id)]));
        $direct = $make(ServiceRequest::SOURCE_DIRECT, 'مشروع مباشر');
        $guided = $make(ServiceRequest::SOURCE_GUIDED, 'طلب موجه');

        $this->actingAs($admin)->get(route('admin.requests.index', ['tab' => 'active', 'source' => 'direct']))->assertOk()->assertSee($direct->number)->assertDontSee($guided->number)->assertSee('نشر مباشر');
        $this->actingAs($admin)->get(route('admin.requests.show', $direct))->assertSee('المصدر: نشر مباشر');
        $this->actingAs($client)->get(route('requests.show', $direct))->assertOk()->assertSeeInOrder(['تم الإرسال', 'مفتوح للعروض', 'وصلت عروض', 'تم اختيار مستقل', 'تم التنفيذ']);
    }

    public function test_conditional_report_questions_and_the_notification_log(): void
    {
        SiteTestSettings::save(['questions' => [
            ['key' => 'kind', 'label' => 'نوع الموقع', 'type' => 'select', 'options' => ['متجر', 'تعريفي'], 'required' => true, 'active' => true],
            ['key' => 'orders', 'label' => 'كم طلبًا شهريًا؟', 'type' => 'text', 'options' => [], 'required' => true, 'active' => true, 'show_if' => 'kind', 'show_value' => 'متجر'],
        ]]);
        $orders = SiteTestSettings::questions()[1];
        $this->assertFalse(SiteTestSettings::questionShown($orders, ['kind' => 'تعريفي']));
        $this->assertTrue(SiteTestSettings::questionShown($orders, ['kind' => 'متجر']));

        // Every delivered notification is logged (bell channel here).
        $user = User::factory()->create();
        $user->notify(new \App\Notifications\RequestUpdateNotification('تجربة السجل', '/my/requests', 'request_updates'));
        $this->assertTrue(NotificationLog::where('text', 'تجربة السجل')->where('status', 'sent')->where('channel', 'database')->exists());
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.notification-log.index'))->assertOk()->assertSee('تجربة السجل');
    }
}
