<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractDelivery as D;
use App\Models\ContractDeliveryFile;
use App\Models\ContractStage as S;
use App\Models\OnlinePayment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\Contracts\AgreementService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Service 3, phase 3: fund → stage starts → deliver → revise/approve → next stage → completed. */
class ContractStagesTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $client;
    private User $freelancer;
    private Contract $contract;
    private ServiceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('private_uploads');
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $this->client = User::factory()->create(['balance' => 20000]);
        $this->freelancer = User::factory()->freelancer()->create(['balance' => 0]);

        $this->request = ServiceRequest::create([
            'user_id' => $this->client->id, 'service_name' => 'مواقع', 'path' => 'execute', 'status' => ServiceRequest::STATUS_IN_PROGRESS,
            'kind' => ServiceRequest::KIND_STAGED, 'project_title' => 'متجر', 'problem' => 'متجر', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@example.com',
        ]);
        $this->request->update(['number' => 'REQ-' . (1000 + $this->request->id)]);
        $this->contract = Contract::create(['service_request_id' => $this->request->id, 'client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر', 'status' => Contract::STATUS_AGREEMENT])->fresh();
        $agreements = app(AgreementService::class);
        $agreement = $agreements->propose($this->contract, $this->freelancer, ['scope' => str_repeat('نطاق العمل ', 4), 'stages' => [
            ['title' => 'التصميم', 'deliverable' => 'نموذج أولي', 'duration_days' => 7, 'amount' => 3000, 'revisions' => 1],
            ['title' => 'البرمجة', 'deliverable' => 'نسخة للمراجعة', 'duration_days' => 14, 'amount' => 4500, 'unlimited_revisions' => 1],
            ['title' => 'الإطلاق', 'deliverable' => 'النسخة النهائية', 'duration_days' => 5, 'amount' => 1500, 'revisions' => 0],
        ]]);
        $agreements->approve($agreement, $this->client);
        $this->contract->refresh();
    }

    private function stage(string $title): S
    {
        return $this->contract->stages()->where('title', $title)->firstOrFail();
    }

    private function fund(array $titles, string $source = 'balance')
    {
        return $this->actingAs($this->client)->post(route('contracts.fund', $this->contract), [
            'stages' => array_map(fn ($t) => $this->stage($t)->id, $titles), 'source' => $source,
        ]);
    }

    private function deliver(string $title, array $extra = [])
    {
        return $this->actingAs($this->freelancer)->post(route('contracts.deliver', [$this->contract, $this->stage($title)]), $extra + ['note' => 'سلّمت النموذج الأولي للمتجر']);
    }

    private function review(D $delivery, string $decision, ?string $note = null)
    {
        return $this->actingAs($this->client)->post(route('contracts.review', [$this->contract, $delivery]), ['decision' => $decision, 'note' => $note]);
    }

    public function test_a_stage_starts_when_funded_and_runs_through_delivery_revision_and_approval(): void
    {
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertOk()->assertSee('تمويل المراحل')->assertSee('من رصيدي');

        // Fund the first stage and the second one ahead.
        $this->fund(['التصميم', 'البرمجة'])->assertSessionHas('Success_message');
        $design = $this->stage('التصميم');
        $this->assertSame(S::STATUS_FUNDED, $design->status);
        $this->assertSame(now()->addDays(7)->toDateString(), $design->due_at->toDateString());
        $this->assertNull($this->stage('البرمجة')->started_at); // waits for the design to be approved
        $this->assertEquals(12500, $this->client->fresh()->balance);
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'بدأت المرحلة «التصميم»'));
        $this->actingAs($this->freelancer)->get(route('contracts.show', $this->contract))->assertSee('تسليم المرحلة')->assertSee('مموّلة — تبدأ بعد اعتماد السابقة');

        // Only the current started stage can be delivered; links must be real URLs; files up to 20 MB.
        $this->deliver('البرمجة')->assertSessionHasErrors();
        $this->deliver('التصميم', ['links' => 'not-a-link'])->assertSessionHasErrors('links');
        $this->deliver('التصميم', ['files' => [UploadedFile::fake()->create('big.zip', 21 * 1024, 'application/zip')]])->assertSessionHasErrors('files.0');
        $this->deliver('التصميم', ['links' => "https://drive.google.com/x\nhttps://preview.example.com", 'files' => [UploadedFile::fake()->create('mockup.pdf', 300, 'application/pdf')]])
            ->assertSessionHas('Success_message');
        $first = D::sole();
        $this->assertSame([D::STATUS_SUBMITTED, 2], [$first->status, count($first->links)]);
        $this->assertSame(now()->addDays(5)->toDateString(), $first->review_due_at->toDateString());
        $file = ContractDeliveryFile::sole();
        Storage::disk('private_uploads')->assertExists($file->path);
        $this->deliver('التصميم')->assertSessionHasErrors(); // the previous one waits for review
        Notification::assertSentTo($this->client, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->client)['text'], 'سلّم المستقل المرحلة «التصميم»'));

        // Files: the parties download them, nobody else.
        $this->actingAs($this->client)->get(route('contracts.file', [$this->contract, $file]))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('contracts.file', [$this->contract, $file]))->assertForbidden();

        // One revision allowed, then only approval.
        $this->review($first, 'revision')->assertSessionHasErrors('note');
        $this->review($first, 'revision', 'كبّر الشعار')->assertSessionHas('Success_message');
        $this->assertSame([D::STATUS_REVISION, 1], [$first->fresh()->status, $this->stage('التصميم')->revisions_used]);
        $this->deliver('التصميم', ['note' => 'عدّلت حجم الشعار'])->assertSessionHas('Success_message');
        $second = D::latest('id')->first();
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('انتهت التعديلات المتفق عليها');
        $this->review($second, 'revision', 'تعديل آخر')->assertSessionHasErrors();

        $this->review($second, 'approve')->assertSessionHas('Success_message');
        $this->assertSame(S::STATUS_RELEASED, $this->stage('التصميم')->status);
        $this->assertEquals(2700, $this->freelancer->fresh()->balance);
        $this->assertEquals(300, Setting::first()->website_balance);
        // The prefunded next stage starts at once.
        $this->assertSame(now()->addDays(14)->toDateString(), $this->stage('البرمجة')->due_at->toDateString());
        $this->review($second, 'approve')->assertSessionHasErrors(); // already approved
    }

    public function test_the_last_approval_completes_the_contract_and_the_request(): void
    {
        $this->fund(['التصميم', 'البرمجة', 'الإطلاق']);
        foreach (['التصميم', 'البرمجة', 'الإطلاق'] as $title) {
            $this->deliver($title)->assertSessionHas('Success_message');
            $this->review(D::latest('id')->first(), 'approve')->assertSessionHas('Success_message');
        }

        $this->assertSame(Contract::STATUS_COMPLETED, $this->contract->fresh()->status);
        $this->assertSame(ServiceRequest::STATUS_COMPLETED, $this->request->fresh()->status);
        $this->assertEquals(8100, $this->freelancer->fresh()->balance);
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('اكتمل المشروع');
        $this->actingAs($this->client)->get(route('requests.show', $this->request))->assertSee('اكتمل المشروع');
    }

    public function test_easykash_funding_adds_the_fees_and_starts_the_stage_once_paid(): void
    {
        Setting::first()->update(['stage_fee_percent' => 2, 'stage_fee_fixed' => 5]);
        $this->actingAs($this->client)->get(route('contracts.show', $this->contract))->assertSee('تُضاف إليه رسوم 2%');

        $this->fund(['التصميم'], 'easykash')->assertRedirect('https://pay.test/1');
        $online = OnlinePayment::sole();
        $this->assertEquals(3000 + 60 + 5, $online->amount);
        $this->assertSame(S::STATUS_FUNDING, $this->stage('التصميم')->status);

        $this->gateway->markPaid((string) $online->id);
        $this->actingAs($this->client)->get(route('payments.return', ['onlinePayment' => $online->id, 'token' => $online->return_token]))
            ->assertRedirect(route('contracts.show', $this->contract));
        $design = $this->stage('التصميم');
        $this->assertSame([S::STATUS_FUNDED, 'easykash'], [$design->status, $design->funding_source]);
        $this->assertNotNull($design->started_at);
        $this->assertEquals(20000, $this->client->fresh()->balance);
    }

    public function test_only_the_client_funds_and_reviews_and_only_the_freelancer_delivers(): void
    {
        $this->actingAs($this->freelancer)->post(route('contracts.fund', $this->contract), ['stages' => [$this->stage('التصميم')->id], 'source' => 'balance'])->assertForbidden();
        $this->fund([])->assertSessionHasErrors('stages');
        $this->fund(['التصميم']);
        $this->actingAs($this->client)->post(route('contracts.deliver', [$this->contract, $this->stage('التصميم')]), ['note' => 'تسليم من العميل'])->assertForbidden();
        $this->deliver('التصميم');
        $this->actingAs($this->freelancer)->post(route('contracts.review', [$this->contract, D::sole()]), ['decision' => 'approve'])->assertForbidden();
        $this->assertSame(S::STATUS_FUNDED, $this->stage('التصميم')->status);
    }
}
