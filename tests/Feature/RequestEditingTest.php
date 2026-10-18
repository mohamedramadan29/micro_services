<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Setting;
use App\Models\admin\SubCategory;
use App\Models\OnlinePayment;
use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestChange as Change;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/**
 * Editing a request (client decision 2026-10-16): freely before an offer is chosen; after
 * work starts through a change the freelancer approves, with the price difference paid
 * (balance or EasyKash) or given back to the client's balance.
 */
class RequestEditingTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private User $client;
    private User $freelancer;
    private SubCategory $service;
    private ServiceForm $form;
    /** @var array<string, Q> */
    private array $q = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_uploads');
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        User::factory()->admin()->create();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);

        $category = Category::create(['name' => 'تسويق', 'slug' => 'm', 'image' => 'x.png', 'status' => 1]);
        $this->service = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $this->form = ServiceForm::create(['sub_category_id' => $this->service->id, 'status' => ServiceForm::STATUS_PUBLISHED]);
        $section = $this->form->sections()->create(['title' => 'تفاصيل', 'sort_order' => 1]);
        $make = fn (string $key, array $data) => $this->q[$key] = $this->form->questions()->create($data + ['service_form_section_id' => $section->id, 'sort_order' => count($this->q) + 1]);
        $make('goal', ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'ما الهدف؟', 'is_required' => true, 'options' => ['مبيعات', 'زيارات']]);
        $make('budget', ['type' => Q::TYPE_NUMBER, 'label' => 'الميزانية', 'filter_key' => 'budget', 'settings' => ['mode' => 'range']]);
        $make('logo', ['type' => Q::TYPE_FILE, 'label' => 'الشعار', 'is_required' => true, 'settings' => ['max_files' => 1, 'max_size_mb' => 1, 'extensions' => 'png,pdf']]);

        $this->client = User::factory()->create(['balance' => 0]);
        $this->freelancer = User::factory()->freelancer()->create(['balance' => 0]);
    }

    private function submit(): ServiceRequest
    {
        $this->actingAs($this->client)->post(route('requests.store'), [
            'sub_category_id' => $this->service->id,
            'action' => ServiceRequest::PATH_EXECUTE,
            'answers' => [$this->q['goal']->id => 'مبيعات', $this->q['budget']->id => ['min' => 300, 'max' => 600]],
            'answer_files' => [$this->q['logo']->id => [UploadedFile::fake()->create('logo.png', 50, 'image/png')]],
            'attachments' => [UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf')],
            'problem' => 'أريد حملة إعلانية لمتجري',
            'contact_name' => 'سارة', 'contact_phone' => '01000000000', 'contact_email' => 'sara@example.com',
        ])->assertSessionHasNoErrors();

        return ServiceRequest::latest('id')->firstOrFail();
    }

    private function editPayload(ServiceRequest $request, array $extra = []): array
    {
        return array_replace([
            'answers' => [$this->q['goal']->id => 'زيارات', $this->q['budget']->id => ['min' => 500, 'max' => 900]],
            'problem' => 'أريد حملة إعلانية لزيادة الزيارات',
            'contact_name' => 'سارة', 'contact_phone' => '01000000000', 'contact_email' => 'sara@example.com',
        ], $extra);
    }

    /** A request with the freelancer's offer chosen and paid (450). */
    private function started(): ServiceRequest
    {
        $request = $this->submit();
        $offer = $request->offers()->create(['freelancer_id' => $this->freelancer->id, 'price' => 450, 'duration_days' => 7, 'scope' => str_repeat('نطاق ', 5), 'message' => str_repeat('رسالة ', 5)]);
        $this->actingAs($this->client)->post(route('offers.accept', $offer));
        $this->gateway->markPaid((string) array_key_last($this->gateway->sessions));
        $this->artisan('payments:sync');
        $request->refresh();
        $this->assertTrue($request->isInProgress());
        $this->assertEquals(450, $request->agreed_price);

        return $request;
    }

    private function propose(ServiceRequest $request, array $data)
    {
        return $this->actingAs($this->client)->post(route('requests.changes.store', $request), $data + ['description' => 'إضافة حملة على إنستغرام أيضًا']);
    }

    private function respond(Change $change, string $decision, ?string $note = null)
    {
        return $this->actingAs($this->freelancer)->post(route('freelancer.changes.respond', [$change->request, $change]), ['decision' => $decision, 'note' => $note]);
    }

    public function test_before_an_offer_is_chosen_the_client_edits_everything_and_bidders_are_told(): void
    {
        $request = $this->submit();
        $offer = $request->offers()->create(['freelancer_id' => $this->freelancer->id, 'price' => 450, 'duration_days' => 7, 'scope' => str_repeat('نطاق ', 5), 'message' => str_repeat('رسالة ', 5), 'submitted_at' => now()->subMinute()]);
        $attachment = $request->files()->whereNull('question_id')->firstOrFail();

        $this->actingAs($this->client)->get(route('requests.edit', $request))->assertOk()
            ->assertSee('أريد حملة إعلانية لمتجري')->assertSee('brief.pdf')->assertSee('logo.png')->assertSee('وصلك 1 عرض')
            // Answers are pre-filled too (budget range, chosen option).
            ->assertSee('name="answers[' . $this->q['budget']->id . '][min]" value="300"', false)
            ->assertSee('value="مبيعات" checked', false);

        // The logo is kept (its required question needs no new upload), the brief is replaced.
        $this->post(route('requests.update', $request), $this->editPayload($request, [
            'remove_files' => [$attachment->id],
            'attachments' => [UploadedFile::fake()->create('brief-v2.pdf', 50, 'application/pdf')],
        ]))->assertRedirect(route('requests.show', $request))->assertSessionHas('Success_message', 'تم حفظ تعديلات طلبك.');

        $request->refresh();
        $this->assertSame(['أريد حملة إعلانية لزيادة الزيارات', ServiceRequest::STATUS_OPEN, ServiceRequest::PATH_EXECUTE], [$request->problem, $request->status, $request->path]);
        $this->assertEquals([500, 900], [$request->budget_min, $request->budget_max]);
        $this->assertSame('زيارات', $request->answers()->where('question_id', $this->q['goal']->id)->value('value'));
        $this->assertEqualsCanonicalizing(['logo.png', 'brief-v2.pdf'], $request->files()->pluck('original_name')->all());
        Storage::disk('private_uploads')->assertMissing($attachment->path);

        $event = $request->events()->where('type', ServiceRequestEvent::TYPE_EDITED)->sole();
        $this->assertStringContainsString('الوصف', $event->note);
        $this->assertStringContainsString('ما الهدف؟', $event->note);
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'عدّل العميل الطلب'));

        $this->assertTrue($offer->fresh()->requestEditedSince());
        $this->actingAs($this->freelancer)->get(route('freelancer.requests.show', $request))->assertSee('الطلب عُدّل بعد عرضك');
        $offer->update(['submitted_at' => now()->addSecond()]);
        $this->assertFalse($offer->fresh()->requestEditedSince());

        // Nothing changed → nothing logged.
        $this->actingAs($this->client)->post(route('requests.update', $request), $this->editPayload($request))->assertSessionHas('Success_message', 'لم يتغير شيء في طلبك.');
        $this->assertSame(1, $request->events()->where('type', ServiceRequestEvent::TYPE_EDITED)->count());
    }

    public function test_edits_follow_the_same_rules_and_only_the_owner_edits_before_work_starts(): void
    {
        $request = $this->submit();
        $logo = $request->files()->whereNotNull('question_id')->firstOrFail();

        // Removing the only file of a required question without a new one is refused.
        $this->actingAs($this->client)->post(route('requests.update', $request), $this->editPayload($request, ['remove_files' => [$logo->id], 'problem' => 'قصير']))
            ->assertSessionHasErrors(["answer_files.{$this->q['logo']->id}", 'problem']);
        $this->assertTrue($request->files()->whereKey($logo->id)->exists());

        $this->actingAs(User::factory()->create())->get(route('requests.edit', $request))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('requests.update', $request), $this->editPayload($request))->assertForbidden();

        $started = $this->started();
        $this->actingAs($this->client)->get(route('requests.edit', $started))->assertForbidden();
        $this->actingAs($this->client)->get(route('requests.show', $started))->assertOk()->assertSee('طلب تعديل على العمل أو الموعد أو السعر')->assertDontSee(route('requests.edit', $started));
    }

    public function test_a_lower_price_goes_back_to_the_client_balance_and_the_commission_follows(): void
    {
        $request = $this->started();
        $this->propose($request, ['new_price' => 300, 'new_deadline' => now()->addDays(10)->toDateString(), 'reason' => 'تقليص النطاق'])->assertSessionHasNoErrors();
        $change = Change::sole();
        $this->assertSame([Change::STATUS_PENDING, -150.0], [$change->status, $change->difference()]);
        Notification::assertSentTo($this->freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->freelancer)['text'], 'بانتظار موافقتك'));

        // One open change at a time; the freelancer must give a reason to refuse.
        $this->propose($request, ['new_price' => 200])->assertSessionHasErrors();
        $this->respond($change, 'reject')->assertSessionHasErrors('note');
        $this->actingAs(User::factory()->freelancer()->create())->post(route('freelancer.changes.respond', [$request, $change]), ['decision' => 'accept'])->assertForbidden();

        $this->respond($change, 'accept')->assertSessionHas('Success_message', 'تمت الموافقة واعتُمد التعديل.');
        $request->refresh();
        $this->assertSame(Change::STATUS_APPLIED, $change->fresh()->status);
        $this->assertEquals(300, $request->agreed_price);
        $this->assertSame(now()->addDays(10)->toDateString(), $request->deadline->toDateString());
        $this->assertEquals(150, $this->client->fresh()->balance);
        $payment = RequestPayment::where('status', RequestPayment::STATUS_PAID)->sole();
        $this->assertEquals([150, 30, 270], [$payment->refunded_amount, $payment->commission_amount, $payment->freelancer_amount]);
        $this->assertSame(['amount' => 300.0, 'freelancer' => 270.0, 'commission' => 30.0, 'rate' => 10.0], $request->moneyTotals());

        $this->actingAs($this->client)->post(route('requests.complete', $request))->assertRedirect();
        $this->assertEquals(270, $this->freelancer->fresh()->balance);
        $this->assertEquals(30, Setting::first()->website_balance);
    }

    public function test_a_higher_price_is_applied_once_the_client_pays_the_difference_from_the_balance(): void
    {
        $request = $this->started();
        $this->client->update(['balance' => 100]);
        $this->propose($request, ['new_price' => 600]);
        $change = Change::sole();
        $this->respond($change, 'accept')->assertSessionHas('Success_message', 'تمت الموافقة. يُعتمد التعديل بعد دفع العميل الفرق.');
        $this->assertSame(Change::STATUS_AWAITING_PAYMENT, $change->fresh()->status);
        $this->assertEquals(450, $request->fresh()->currentPrice());

        // Not enough balance yet.
        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, $change, 'pay-balance']))->assertSessionHasErrors();
        $this->client->update(['balance' => 200]);
        $this->actingAs($this->client)->get(route('requests.show', $request))->assertSee('ادفع الفرق من رصيدك');
        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, $change, 'pay-balance']))->assertSessionHas('Success_message');

        $this->assertSame(Change::STATUS_APPLIED, $change->fresh()->status);
        $this->assertEquals(50, $this->client->fresh()->balance);
        $this->assertEquals(600, $request->fresh()->agreed_price);
        $difference = RequestPayment::whereNotNull('service_request_change_id')->sole();
        $this->assertSame(['balance', RequestPayment::STATUS_PAID], [$difference->gateway, $difference->status]);
        $this->assertEquals([150, 15, 135], [$difference->amount, $difference->commission_amount, $difference->freelancer_amount]);

        $this->actingAs($this->client)->post(route('requests.complete', $request));
        $this->assertEquals(405 + 135, $this->freelancer->fresh()->balance);
        $this->assertEquals(45 + 15, Setting::first()->website_balance);
        $this->assertSame(0, RequestPayment::where('status', RequestPayment::STATUS_PAID)->count());
    }

    public function test_a_difference_paid_through_easykash_applies_the_change_and_a_cancelled_one_needs_a_refund(): void
    {
        $request = $this->started();
        $this->propose($request, ['new_price' => 500]);
        $change = Change::sole();
        $this->respond($change, 'accept');

        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, $change, 'pay']))->assertRedirect();
        $reference = (string) array_key_last($this->gateway->sessions);
        $this->assertEquals(50, OnlinePayment::findOrFail($reference)->amount);
        $this->gateway->markPaid($reference);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $reference, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();
        $this->artisan('payments:sync');

        $this->assertSame(Change::STATUS_APPLIED, $change->fresh()->status);
        $this->assertEquals(500, $request->fresh()->agreed_price);
        $this->assertSame(1, RequestPayment::whereNotNull('service_request_change_id')->where('status', RequestPayment::STATUS_PAID)->count());

        // A second change, cancelled while its EasyKash payment was still open, then paid anyway.
        $this->propose($request, ['new_price' => 550]);
        $second = Change::latest('id')->first();
        $this->respond($second, 'accept');
        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, $second, 'pay']));
        $late = (string) array_key_last($this->gateway->sessions);
        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, $second, 'cancel']))->assertSessionHas('Success_message');
        $this->gateway->markPaid($late);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $late, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();
        $this->assertSame(Change::STATUS_CANCELLED, $second->fresh()->status);
        $this->assertEquals(500, $request->fresh()->agreed_price);
        // The money arrived for nothing: it shows in the admin «دفعات تحتاج استرجاعًا».
        $this->assertSame(RequestPayment::STATUS_CONFLICT, RequestPayment::where('online_payment_id', $late)->value('status'));
        Notification::assertSentTo(User::activeAdmins()->get(), \App\Notifications\PaymentConflictNotification::class);
    }

    public function test_rejected_changes_keep_the_terms_and_closing_refunds_every_held_payment(): void
    {
        $request = $this->started();
        $this->propose($request, ['new_deadline' => now()->addDays(3)->toDateString()]);
        $this->respond(Change::sole(), 'reject', 'الموعد ضيق جدًا')->assertSessionHas('Success_message');
        $this->assertSame([Change::STATUS_REJECTED, 'الموعد ضيق جدًا'], [Change::sole()->status, Change::sole()->response_note]);
        Notification::assertSentTo($this->client, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->client)['text'], 'الموعد ضيق جدًا'));
        $this->assertEquals(450, $request->fresh()->currentPrice());

        // A paid difference from the balance + the first payment: closing gives both back.
        $this->client->update(['balance' => 100]);
        $this->propose($request, ['new_price' => 500]);
        $this->respond(Change::latest('id')->first(), 'accept');
        $this->actingAs($this->client)->post(route('requests.changes.action', [$request, Change::latest('id')->first(), 'pay-balance']));
        $this->assertEquals(50, $this->client->fresh()->balance);

        $this->propose($request, ['new_price' => 400]); // left pending: cancelled by the close
        app(\App\Services\ServiceRequests\RequestAdminService::class)->close($request->fresh(), 'not_feasible', null, User::factory()->admin()->create());

        $this->assertSame(0, RequestPayment::where('status', RequestPayment::STATUS_PAID)->count());
        $this->assertEquals(100, $this->client->fresh()->balance); // the balance part came back; EasyKash part refunded from its dashboard
        $this->assertSame(Change::STATUS_CANCELLED, Change::latest('id')->first()->status);
        $this->assertSame(ServiceRequest::STATUS_NOT_COMPLETED, $request->fresh()->status);
    }
}
