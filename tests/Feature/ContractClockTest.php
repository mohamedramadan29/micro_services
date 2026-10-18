<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractDelivery as D;
use App\Models\ContractPause as P;
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

/** Service 3, phase 4: clock pauses, lateness, review and pause reminders (client decisions 2026-10-16). */
class ContractClockTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private User $freelancer;
    private User $admin;
    private Contract $contract;
    private S $stage;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('private_uploads');
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        Setting::create(['website_commission' => '10']);
        $this->client = User::factory()->create(['balance' => 10000]);
        $this->freelancer = User::factory()->freelancer()->create();
        $this->admin = User::factory()->admin()->create();

        $request = ServiceRequest::create(['user_id' => $this->client->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => 'in_progress', 'kind' => 'staged', 'project_title' => 'متجر', 'problem' => 'متجر', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@example.com']);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);
        $this->contract = Contract::create(['service_request_id' => $request->id, 'client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر', 'status' => 'agreement'])->fresh();
        $agreements = app(AgreementService::class);
        $agreements->approve($agreements->propose($this->contract, $this->freelancer, ['scope' => str_repeat('نطاق ', 5), 'stages' => [
            ['title' => 'التصميم', 'deliverable' => 'نموذج أولي', 'duration_days' => 7, 'amount' => 3000, 'revisions' => 2],
        ]]), $this->client);
        $this->stage = $this->contract->stages()->sole();
        app(ContractLedger::class)->fundFromBalance($this->contract, [$this->stage->id], $this->client);
        app(StageWorkflow::class)->advance($this->contract->fresh());
        $this->stage->refresh();
        Notification::fake(); // only what the clock sends from here on
    }

    private function sentTo(User $user, string $needle): bool
    {
        return Notification::sent($user, RequestUpdateNotification::class)->contains(fn ($n) => str_contains($n->toArray($user)['text'], $needle));
    }

    private function pause(string $request = 'شعار الشركة بصيغة PNG وبيانات الاستضافة')
    {
        return $this->actingAs($this->freelancer)->post(route('contracts.pause', [$this->contract, $this->stage]), ['request' => $request]);
    }

    public function test_a_pause_stops_the_clock_and_the_answer_moves_the_deadline(): void
    {
        $due = $this->stage->due_at->copy();
        $this->pause('قصير')->assertSessionHasErrors('request');
        $this->pause()->assertSessionHas('Success_message');
        $pause = P::sole();
        $this->assertTrue($this->sentTo($this->client, 'شعار الشركة بصيغة PNG'));
        $this->pause()->assertSessionHasErrors(); // already paused
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('العدّاد متوقف منذ')->assertSee('أرسلت المطلوب');

        $this->travel(2)->days();
        $this->actingAs($this->client)->post(route('contracts.pause.action', [$this->contract, $pause, 'answer']), [
            'response' => 'أرفقت الشعار، وبيانات الاستضافة في المحادثة',
            'files' => [UploadedFile::fake()->create('logo.png', 50, 'image/png')],
        ])->assertSessionHas('Success_message');

        $pause->refresh();
        $this->assertSame([P::STATUS_RESUMED, 2 * 24 * 60], [$pause->status, $pause->extended_minutes]);
        $this->assertSame($due->copy()->addDays(2)->toDateTimeString(), $this->stage->fresh()->due_at->toDateTimeString());
        $file = $pause->files()->sole();
        Storage::disk('private_uploads')->assertExists($file->path);
        $this->actingAs($this->freelancer)->get(route('contracts.file', [$this->contract, $file]))->assertOk();
        $this->assertTrue($this->sentTo($this->freelancer, 'أرسل العميل المطلوب'));
        $this->actingAs($this->client)->post(route('contracts.pause.action', [$this->contract, $pause, 'answer']), ['response' => 'مرة أخرى'])->assertSessionHasErrors();
    }

    public function test_the_freelancer_may_resume_and_the_client_may_object_without_an_extension(): void
    {
        $due = $this->stage->due_at->copy();
        $this->pause();
        $this->travel(1)->days();
        $this->actingAs($this->client)->post(route('contracts.pause.action', [$this->contract, P::sole(), 'resume']))->assertForbidden();
        $this->actingAs($this->freelancer)->post(route('contracts.pause.action', [$this->contract, P::sole(), 'resume']))->assertSessionHas('Success_message');
        $this->assertSame($due->copy()->addDay()->toDateTimeString(), $this->stage->fresh()->due_at->toDateTimeString());

        $this->stage->refresh();
        $this->pause('أحتاج صورًا لكل المنتجات الآن');
        $this->travel(1)->days();
        $objected = P::latest('id')->first();
        $this->actingAs($this->client)->post(route('contracts.pause.action', [$this->contract, $objected, 'object']), ['reason' => 'أرسلت الصور من البداية'])->assertSessionHas('Success_message');
        $this->assertSame(P::STATUS_OBJECTED, $objected->fresh()->status);
        $this->assertSame($due->copy()->addDay()->toDateTimeString(), $this->stage->fresh()->due_at->toDateTimeString()); // no extension
        $this->assertTrue($this->sentTo($this->admin, 'اعتراض على إيقاف العدّاد'));
    }

    public function test_due_reminder_lateness_and_the_seven_day_alert_are_sent_once(): void
    {
        $this->travel(6)->days(); $this->travel(1)->hours();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('due_soon: 1');
        $this->artisan('contracts:deadlines');
        $this->assertSame(1, Notification::sent($this->freelancer, RequestUpdateNotification::class)->filter(fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'تذكير: موعد تسليم'))->count());

        $this->travel(1)->days();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('late: 1');
        $this->assertTrue($this->sentTo($this->client, 'تأخر المستقل'));
        $this->assertTrue($this->sentTo($this->admin, 'تأخرت المرحلة'));
        $this->actingAs($this->freelancer)->get(route('contracts.show', $this->contract))->assertSee('متأخرة');

        $this->travel(7)->days();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('late_7: 1');
        $this->artisan('contracts:deadlines')->doesntExpectOutputToContain('late');
        $this->assertTrue($this->sentTo($this->client, 'يحق لك طلب تغيير المستقل'));
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('«طلب تغيير المستقل» أسفل الصفحة');
    }

    public function test_a_paused_stage_is_not_late_and_long_pauses_go_to_the_admin(): void
    {
        $this->pause();
        $this->travel(3)->days();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('pause_reminder: 1');
        $this->assertTrue($this->sentTo($this->client, 'ما زال ينتظر منك'));
        $this->travel(5)->days(); // past the stage deadline, but paused
        $this->artisan('contracts:deadlines')->expectsOutputToContain('pause_admin: 1');
        $this->assertTrue($this->sentTo($this->admin, 'العدّاد متوقف منذ 7 أيام'));
        $this->assertNull($this->stage->fresh()->late_notified_at);

        // Delivering ends the pause.
        $this->actingAs($this->freelancer)->post(route('contracts.deliver', [$this->contract, $this->stage]), ['note' => 'سلّمت رغم الانتظار'])->assertSessionHas('Success_message');
        $this->assertSame(P::STATUS_RESUMED, P::sole()->status);
    }

    public function test_review_reminder_at_three_days_and_the_admin_at_five_without_auto_approval(): void
    {
        $this->actingAs($this->freelancer)->post(route('contracts.deliver', [$this->contract, $this->stage]), ['note' => 'سلّمت النموذج']);
        $this->travel(3)->days(); $this->travel(1)->minutes();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('review_reminder: 1');
        $this->assertTrue($this->sentTo($this->client, 'بانتظار مراجعتك'));
        $this->travel(2)->days();
        $this->artisan('contracts:deadlines')->expectsOutputToContain('review_admin: 1');
        $this->assertTrue($this->sentTo($this->admin, 'انتهت مهلة مراجعة العميل'));
        $this->assertSame(D::STATUS_SUBMITTED, D::sole()->status); // never approved automatically
        $this->assertSame(S::STATUS_FUNDED, $this->stage->fresh()->status);
        $this->assertNull($this->stage->fresh()->late_notified_at); // waiting for the client's review is not lateness
    }
}
