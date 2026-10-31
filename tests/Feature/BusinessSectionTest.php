<?php

namespace Tests\Feature;

use App\Models\BusinessCompany;
use App\Models\BusinessDelivery;
use App\Models\BusinessProject;
use App\Models\BusinessRequest;
use App\Models\BusinessTask;
use App\Models\OnlinePayment;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\User;
use App\Models\admin\Setting;
use App\Notifications\BusinessNotification;
use App\Services\Payments\PaymentGateway;
use Database\Seeders\BusinessFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** «نفّذها للأعمال» (client file «قسم الشركات», phase 1) — the acceptance checks of its page 15 and more. */
class BusinessSectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('private_uploads');
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $this->admin = User::factory()->admin()->create();
    }

    private function form(): ServiceForm
    {
        return ServiceForm::published()->where('sub_category_id', BusinessFormSeeder::service()->id)->with('questions')->firstOrFail();
    }

    private function question(string $label): Q
    {
        return $this->form()->questions->firstWhere('label', $label);
    }

    private function free(array $overrides = [])
    {
        return $this->post(route('business.free'), array_merge([
            'submission_key' => (string) Str::uuid(),
            'details' => str_repeat('نحن شركة ونبحث عن فريق يتولى إدارة حساباتنا وتقطيع البودكاست. ', 40),
            'phone' => '+20 100 123 4567',
            'consent' => '1',
        ], $overrides));
    }

    /** A guided request through the hidden form: podcast chosen, so its conditional questions apply. */
    private function guided(User $client, array $answers = [])
    {
        $services = $this->question('الخدمات المطلوبة');
        $base = [
            $this->question('ما التحدي أو ما تحتاجه؟')->id => 'نحتاج 12 مقطعًا شهريًا من حلقات البودكاست',
            $this->question('ما الأولوية؟')->id => 'عادي',
            $services->id => ['تقطيع بودكاست'],
            $this->question('عدد الحلقات شهريًا')->id => 4,
            $this->question('نوع التعاقد')->id => 'شهري مستمر',
            $this->question('الميزانية المتوقعة')->id => ['min' => 3000, 'max' => 5000],
        ];

        return $this->actingAs($client)->post(route('business.guided.store'), [
            'submission_key' => (string) Str::uuid(),
            'company_name' => 'شركة الريادة', 'company_size' => 'medium', 'phone' => '+201000000000', 'email' => 'team@example.com', 'consent' => '1',
            'answers' => array_replace($base, $answers),
        ]);
    }

    public function test_a_visitor_sends_a_long_free_request_with_a_phone_and_no_account(): void
    {
        $this->get(route('business.index'))->assertOk()->assertSee('ننفّذها عن شركتك')->assertSee('اكتب طلبك بحرية')->assertSee('حدّد طلبك خطوة بخطوة');

        $this->free(['company_name' => 'شركة المدار', 'attachments' => [UploadedFile::fake()->create('brief.pdf', 300, 'application/pdf')]])
            ->assertRedirect(route('business.index'))->assertSessionHas('business_created');

        $request = BusinessRequest::sole();
        $this->assertSame(['BIZ-' . (1000 + $request->id), 'free', 'new', null], [$request->number, $request->source, $request->status, $request->user_id]);
        $this->assertGreaterThan(2000, mb_strlen($request->details)); // the whole text is kept
        $this->assertSame('+20 100 123 4567', $request->phone); // kept as typed (the phone picker's format), wa.me strips non-digits
        $this->assertNotNull($request->consent_at);
        Storage::disk('private_uploads')->assertExists($request->files()->sole()->path);
        Notification::assertSentTo($this->admin, BusinessNotification::class);

        $this->actingAs($this->admin)->get(route('admin.business.requests'))->assertOk()->assertSee($request->number)->assertSee('شركة المدار')->assertSee('زائر بلا حساب');
        $this->actingAs($this->admin)->get(route('admin.business.requests.show', $request))->assertOk()->assertSee('نحن شركة ونبحث عن فريق');
    }

    public function test_free_request_validation_honeypot_and_double_submit(): void
    {
        $this->free(['details' => 'قصير'])->assertSessionHasErrors('details');
        $this->free(['phone' => 'abc'])->assertSessionHasErrors('phone');
        $this->free(['consent' => null])->assertSessionHasErrors('consent');
        $this->free(['details' => str_repeat('ا', 20001)])->assertSessionHasErrors('details');
        $this->free(['nf_extra' => 'bot'])->assertSessionHasErrors('details');
        $this->assertSame(0, BusinessRequest::count());

        $key = (string) Str::uuid();
        $this->free(['submission_key' => $key]);
        $this->free(['submission_key' => $key])->assertSessionHas('business_created', BusinessRequest::sole()->number);
        $this->assertSame(1, BusinessRequest::count());
    }

    public function test_the_guided_form_is_dynamic_saves_answers_and_the_company(): void
    {
        $client = User::factory()->create();
        $this->get(route('business.guided'))->assertRedirect(); // login first
        $this->actingAs($client)->get(route('business.guided'))->assertOk()->assertSee('تعريف الشركة')->assertSee('الخدمات المطلوبة')->assertSee('مدة الحلقة تقريبًا');

        // A required question missing → error, nothing saved.
        $this->guided($client, [$this->question('نوع التعاقد')->id => null])->assertSessionHasErrors('answers.' . $this->question('نوع التعاقد')->id);
        $this->assertSame(0, BusinessRequest::count());

        $this->guided($client)->assertSessionHasNoErrors();
        $request = BusinessRequest::sole();
        $this->assertSame(['guided', $client->id, 'medium'], [$request->source, $request->user_id, $request->company_size]);
        $labels = collect($request->answers)->pluck('label');
        $this->assertTrue($labels->contains('عدد الحلقات شهريًا'));
        $this->assertFalse($labels->contains('عدد المقاطع شهريًا')); // reels not chosen: its question stays hidden
        $this->assertSame('شركة الريادة', BusinessCompany::where('user_id', $client->id)->value('name'));

        // The admin changes the form in the builder → the page follows without code changes.
        $form = $this->form();
        $form->questions()->create(['service_form_section_id' => $form->sections()->first()->id, 'type' => Q::TYPE_SHORT_TEXT, 'label' => 'سؤال أضافه الأدمن', 'sort_order' => 99]);
        $this->actingAs($client)->get(route('business.guided'))->assertSee('سؤال أضافه الأدمن');
        $this->actingAs($client)->get(route('business.requests.show', $request))->assertOk()->assertSee('عدد الحلقات شهريًا');
    }

    public function test_the_hidden_form_never_shows_with_the_public_services(): void
    {
        $service = BusinessFormSeeder::service();
        $this->get(route('services.index'))->assertDontSee($service->name);
        $this->actingAs(User::factory()->create())->get(route('requests.create'))->assertDontSee($service->name);
        $this->actingAs(User::factory()->create())->get(route('requests.create', ['service' => $service->id]))->assertDontSee('الخدمات المطلوبة');
    }

    public function test_full_journey_quote_acceptance_project_tasks_versions_and_approval(): void
    {
        $client = User::factory()->create();
        $this->guided($client);
        $request = BusinessRequest::sole();

        // Quote by the team → the client accepts in his account.
        $this->actingAs($this->admin)->post(route('admin.business.requests.quote', $request), ['quote_amount' => 4500, 'quote_scope' => '12 مقطعًا شهريًا + جدولة'])->assertSessionHasNoErrors();
        $this->actingAs($client)->get(route('business.requests.show', $request))->assertSee('4,500 ج.م')->assertSee('اعتماد عرض السعر');
        $this->actingAs($client)->post(route('business.requests.decide', $request), ['decision' => 'accept'])->assertSessionHasNoErrors();
        $this->assertSame('accepted', $request->fresh()->status);
        Notification::assertSentTo($this->admin, BusinessNotification::class, fn ($n) => str_contains($n->toArray($this->admin)['text'], 'قبل عرض السعر'));

        // Converted into a project with tasks and owners.
        $this->actingAs($this->admin)->post(route('admin.business.requests.convert', $request), ['title' => 'بودكاست الريادة', 'manager_id' => $this->admin->id])
            ->assertRedirect();
        $project = BusinessProject::sole();
        $this->assertSame(['converted', $project->id], [$request->fresh()->status, $request->fresh()->business_project_id]);
        $this->actingAs($this->admin)->post(route('admin.business.tasks.store', $project), ['title' => 'مونتاج 4 ريلز', 'status' => 'todo', 'priority' => 'high', 'assignee_id' => $this->admin->id])->assertSessionHasNoErrors();
        $task = BusinessTask::sole();
        $this->actingAs($this->admin)->post(route('admin.business.tasks.move', [$project, $task->id]), ['status' => 'in_progress']);
        $this->assertSame('in_progress', $task->fresh()->status);

        // Version 1 → the client asks for changes (comment required) → the task goes back to work.
        $this->actingAs($this->admin)->post(route('admin.business.deliveries.store', $project), ['title' => 'ريلز رقم 4', 'business_task_id' => $task->id,
            'links' => 'https://drive.example.com/v1', 'files' => [UploadedFile::fake()->create('reels_v1.mp4', 2000, 'video/mp4')]])->assertSessionHasNoErrors();
        $v1 = BusinessDelivery::sole();
        $this->assertSame([1, 'review'], [$v1->version, $task->fresh()->status]);
        $this->actingAs($client)->post(route('business.deliveries.review', $v1->id), ['decision' => 'changes'])->assertSessionHasErrors('comment');
        $this->actingAs($client)->post(route('business.deliveries.review', $v1->id), ['decision' => 'changes', 'comment' => 'غيّروا الموسيقى'])->assertSessionHasNoErrors();
        $this->assertSame(['changes_requested', 'in_progress'], [$v1->fresh()->status, $task->fresh()->status]);

        // Version 2 of the same deliverable → approved → the task is done; everything is in the log.
        $this->actingAs($this->admin)->post(route('admin.business.deliveries.store', $project), ['previous_id' => $v1->id, 'note' => 'غيّرنا الموسيقى'])->assertSessionHasNoErrors();
        $v2 = BusinessDelivery::latest('id')->first();
        $this->assertSame([2, $v1->id, 'ريلز رقم 4'], [$v2->version, $v2->root_id, $v2->title]);
        $this->actingAs($client)->get(route('business.projects.show', $project))->assertOk()->assertSee('نسخة 2')->assertSee('اعتماد النسخة');
        $this->actingAs($client)->post(route('business.deliveries.review', $v2->id), ['decision' => 'approve'])->assertSessionHasNoErrors();
        $this->assertSame(['approved', 'done'], [$v2->fresh()->status, $task->fresh()->status]);
        $this->actingAs($client)->post(route('business.deliveries.review', $v2->id), ['decision' => 'changes', 'comment' => 'x'])->assertSessionHasErrors('comment');
        $this->assertEqualsCanonicalizing(['created', 'task', 'delivery', 'changes', 'approved'],
            $project->events()->pluck('action')->unique()->values()->all());
        $this->actingAs($this->admin)->get(route('admin.business.projects.show', $project))->assertOk()->assertSee('غيّروا الموسيقى')->assertSee('لوحة المهام');
    }

    public function test_a_company_never_sees_another_companys_data(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->guided($owner);
        $request = BusinessRequest::sole();
        $this->actingAs($this->admin)->post(route('admin.business.requests.quote', $request), ['quote_amount' => 1000, 'quote_scope' => 'نطاق']);
        $this->actingAs($this->admin)->post(route('admin.business.requests.decision', $request), ['decision' => 'accept']);
        $this->actingAs($this->admin)->post(route('admin.business.requests.convert', $request), ['title' => 'مشروع']);
        $project = BusinessProject::sole();
        $this->actingAs($this->admin)->post(route('admin.business.deliveries.store', $project), ['title' => 'تصميم', 'files' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]]);
        $delivery = BusinessDelivery::sole();
        $file = $delivery->files()->sole();

        $this->actingAs($other)->get(route('business.requests.show', $request))->assertNotFound();
        $this->actingAs($other)->get(route('business.projects.show', $project))->assertNotFound();
        $this->actingAs($other)->post(route('business.deliveries.review', $delivery->id), ['decision' => 'approve'])->assertNotFound();
        $this->actingAs($other)->post(route('business.requests.decide', $request), ['decision' => 'reject'])->assertNotFound();
        $this->actingAs($other)->get(route('business.files.delivery', $file->id))->assertNotFound();
        $this->actingAs($other)->get(route('business.mine'))->assertOk()->assertDontSee($request->number)->assertDontSee($project->number);
        $this->actingAs($owner)->get(route('business.files.delivery', $file->id))->assertOk();
        $this->assertSame('pending', $delivery->fresh()->status);
    }

    public function test_a_visitor_request_is_linked_to_an_account_paid_offline_and_converted(): void
    {
        $this->free(['company_name' => 'شركة صغيرة', 'email' => 'boss@example.com']);
        $request = BusinessRequest::sole();
        $this->actingAs($this->admin)->post(route('admin.business.requests.quote', $request), ['quote_amount' => 2000, 'quote_scope' => 'تصميم 10 منشورات']);
        $this->actingAs($this->admin)->post(route('admin.business.requests.decision', $request), ['decision' => 'accept', 'note' => 'وافق بالهاتف']);
        // Not linked yet → cannot become a project.
        $this->actingAs($this->admin)->post(route('admin.business.requests.convert', $request), ['title' => 'منشورات'])->assertSessionHasErrors('title');

        $this->actingAs($this->admin)->post(route('admin.business.requests.account', $request), ['email' => 'boss@example.com', 'name' => 'المدير'])->assertSessionHasNoErrors();
        $client = User::where('email', 'boss@example.com')->sole();
        $this->assertSame([User::TYPE_CLIENT, $client->id], [$client->account_type, $request->fresh()->user_id]);
        $this->assertSame('شركة صغيرة', BusinessCompany::where('user_id', $client->id)->value('name'));

        $this->actingAs($this->admin)->post(route('admin.business.requests.payment', $request), ['payment_method' => 'cash', 'payment_reference' => 'إيصال 55'])->assertSessionHasNoErrors();
        $this->assertSame(['paid', 'cash'], [$request->fresh()->payment_status, $request->fresh()->payment_method]);
        $this->actingAs($this->admin)->post(route('admin.business.requests.convert', $request), ['title' => 'منشورات'])->assertRedirect();
        $this->actingAs($client)->get(route('business.mine'))->assertOk()->assertSee('منشورات');
    }

    public function test_the_client_pays_the_accepted_quote_online(): void
    {
        $gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $gateway);
        $client = User::factory()->create();
        $this->guided($client);
        $request = BusinessRequest::sole();
        $this->actingAs($client)->post(route('business.requests.pay', $request))->assertSessionHasErrors('pay'); // no accepted quote yet

        $this->actingAs($this->admin)->post(route('admin.business.requests.quote', $request), ['quote_amount' => 3000, 'quote_scope' => 'نطاق']);
        $this->actingAs($client)->post(route('business.requests.decide', $request), ['decision' => 'accept']);
        $this->actingAs($client)->post(route('business.requests.pay', $request))->assertRedirect();
        $payment = OnlinePayment::latest('id')->firstOrFail();
        $this->assertSame([OnlinePayment::PURPOSE_BUSINESS, 3000.0], [$payment->purpose, (float) $payment->amount]);

        $gateway->markPaid((string) $payment->id);
        $this->actingAs($client)->get(route('payments.return', ['onlinePayment' => $payment->id, 'token' => $payment->return_token, 'status' => 'PAID']))
            ->assertRedirect(route('business.requests.show', $request));
        $this->assertSame(['paid', 'online'], [$request->fresh()->payment_status, $request->fresh()->payment_method]);
    }

    public function test_read_only_staff_cannot_act_and_clients_cannot_open_the_admin(): void
    {
        $this->free();
        $request = BusinessRequest::sole();
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);

        $this->actingAs($analyst)->get(route('admin.business.requests.show', $request))->assertOk();
        $this->actingAs($analyst)->post(route('admin.business.requests.quote', $request), ['quote_amount' => 10, 'quote_scope' => 'x'])->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('admin.business.requests'))->assertForbidden();
        $this->assertNull($request->fresh()->quote_amount);
    }
}
