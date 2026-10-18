<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private SubCategory $service;
    private ServiceForm $form;
    /** @var array<string, Q> */
    private array $q = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_uploads');

        $category = Category::create(['name' => 'تسويق', 'slug' => 'marketing', 'image' => 'x.png', 'status' => 1]);
        $this->service = SubCategory::create(['name' => 'إعلانات المواقع', 'slug' => 'ads', 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
        $this->form = ServiceForm::create(['sub_category_id' => $this->service->id, 'status' => ServiceForm::STATUS_PUBLISHED]);
        $section = $this->form->sections()->create(['title' => 'تفاصيل الحملة', 'sort_order' => 1]);

        $make = fn (string $key, array $data) => $this->q[$key] = $this->form->questions()->create($data + [
            'service_form_section_id' => $section->id, 'sort_order' => count($this->q) + 1,
        ]);
        $make('goal', ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'ما الهدف؟', 'is_required' => true, 'options' => ['مبيعات', 'زيارات']]);
        $make('site', ['type' => Q::TYPE_YES_NO, 'label' => 'هل لديك موقع؟', 'is_required' => true]);
        $make('url', ['type' => Q::TYPE_URL, 'label' => 'رابط الموقع', 'is_required' => true]);
        $this->q['url']->update(['condition' => ['question_id' => $this->q['site']->id, 'values' => ['نعم'], 'negate' => false]]);
        $make('places', ['type' => Q::TYPE_MULTI_CHOICE, 'label' => 'أين؟', 'options' => ['مصر', 'السعودية']]);
        $make('budget', ['type' => Q::TYPE_NUMBER, 'label' => 'الميزانية', 'filter_key' => 'budget', 'settings' => ['mode' => 'range', 'min' => 10, 'max' => 10000, 'unit' => '$']]);
        $make('start', ['type' => Q::TYPE_DATE, 'label' => 'موعد البدء', 'filter_key' => 'deadline']);
        $make('urgency', ['type' => Q::TYPE_RATING, 'label' => 'الاستعجال', 'settings' => ['scale' => 5]]);
        $make('logo', ['type' => Q::TYPE_FILE, 'label' => 'الشعار', 'settings' => ['max_files' => 2, 'max_size_mb' => 1, 'extensions' => 'png,pdf']]);
    }

    private function payload(array $answers = [], array $extra = []): array
    {
        $q = $this->q;

        return array_replace([
            'sub_category_id' => $this->service->id,
            'action' => ServiceRequest::PATH_EXECUTE,
            'answers' => array_replace([
                $q['goal']->id => 'مبيعات',
                $q['site']->id => 'لا',
                $q['places']->id => ['مصر'],
                $q['budget']->id => ['min' => 300, 'max' => 600],
                $q['start']->id => '2026-11-01',
                $q['urgency']->id => 4,
            ], $answers),
            'problem' => 'أريد زيادة مبيعات متجري الإلكتروني',
            'contact_name' => 'عميل تجريبي',
            'contact_phone' => '+20 100 000 0000',
            'contact_email' => 'client@example.com',
        ], $extra);
    }

    public function test_private_files_live_in_public_uploads_closed_to_direct_access(): void
    {
        // User decision 2026-10-16: no files under storage/.
        $this->assertSame(public_path('uploads/private'), config('filesystems.disks.private_uploads.root'));
        $this->assertSame('private_uploads', \App\Models\ServiceRequestFile::DISK);
        $this->assertSame('private_uploads', \App\Models\RequestOfferFile::DISK);
        $this->assertStringContainsString('Require all denied', file_get_contents(public_path('uploads/private/.htaccess')));
    }

    public function test_guests_must_log_in_first(): void
    {
        $this->get(route('requests.create'))->assertRedirect(route('user_login'));
        $this->post(route('requests.store'), $this->payload())->assertRedirect(route('user_login'));
        $this->assertSame(0, ServiceRequest::count());
    }

    public function test_only_published_services_are_offered(): void
    {
        $draft = SubCategory::create(['name' => 'خدمة مسودة', 'slug' => 'draft', 'image' => 'x.png', 'parent_id' => $this->service->parent_id, 'status' => 1]);
        ServiceForm::create(['sub_category_id' => $draft->id]);
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('requests.create'))
            ->assertOk()->assertSee('إعلانات المواقع')->assertDontSee('خدمة مسودة');
        $this->actingAs($client)->get(route('requests.fields', $this->service))->assertOk()->assertSee('هل لديك موقع؟');
        $this->actingAs($client)->get(route('requests.fields', $draft))->assertNotFound();
        $this->actingAs($client)->post(route('requests.store'), $this->payload([], ['sub_category_id' => $draft->id]))
            ->assertSessionHasErrors('sub_category_id');
    }

    public function test_freelancers_cannot_send_requests(): void
    {
        $freelancer = User::factory()->freelancer()->create();

        $this->actingAs($freelancer)->get(route('requests.create'))->assertOk()->assertSee('متاح لحسابات العملاء');
        $this->actingAs($freelancer)->post(route('requests.store'), $this->payload())->assertForbidden();
    }

    public function test_execute_request_is_published_with_answers_filters_and_private_files(): void
    {
        $client = User::factory()->create();
        $q = $this->q;

        $response = $this->actingAs($client)->post(route('requests.store'), $this->payload([], [
            'answer_files' => [$q['logo']->id => [UploadedFile::fake()->create('logo.png', 100, 'image/png')]],
            'attachments' => [UploadedFile::fake()->create('brief.pdf', 200, 'application/pdf')],
        ]));

        $request = ServiceRequest::firstOrFail();
        $response->assertRedirect(route('requests.show', $request));

        $this->assertSame('REQ-' . (1000 + $request->id), $request->number);
        $this->assertSame(ServiceRequest::STATUS_OPEN, $request->status);
        $this->assertNotNull($request->published_at);
        $this->assertSame('إعلانات المواقع', $request->service_name);
        $this->assertSame('تسويق', $request->category_name);
        $this->assertEquals(300, $request->budget_min);
        $this->assertEquals(600, $request->budget_max);
        $this->assertSame('2026-11-01', $request->deadline->format('Y-m-d'));

        $labels = $request->answers->pluck('label')->all();
        $this->assertContains('ما الهدف؟', $labels);
        $this->assertNotContains('رابط الموقع', $labels); // hidden: site = لا
        $this->assertSame(['مصر'], $request->answers->firstWhere('question_id', $q['places']->id)->value);
        $this->assertSame(['value' => 4, 'scale' => 5], $request->answers->firstWhere('question_id', $q['urgency']->id)->value);

        $this->assertSame(2, $request->files()->count());
        foreach ($request->files as $file) {
            Storage::disk('private_uploads')->assertExists($file->path);
            $this->assertStringStartsWith('service-requests/' . $request->id . '/', $file->path);
        }
        $this->assertSame(1, $request->attachments()->count());
    }

    public function test_conditional_question_is_required_only_when_shown(): void
    {
        $client = User::factory()->create();
        $q = $this->q;

        $this->actingAs($client)->post(route('requests.store'), $this->payload([$q['site']->id => 'نعم']))
            ->assertSessionHasErrors('answers.' . $q['url']->id);

        $this->actingAs($client)->post(route('requests.store'), $this->payload([$q['site']->id => 'نعم', $q['url']->id => 'https://shop.example.com']))
            ->assertSessionHasNoErrors();
        $this->assertSame('https://shop.example.com', ServiceRequest::first()->answers->firstWhere('question_id', $q['url']->id)->value);
    }

    public function test_answers_are_validated_against_the_form(): void
    {
        $client = User::factory()->create();
        $q = $this->q;
        $post = fn (array $answers, array $extra = []) => $this->actingAs($client)->post(route('requests.store'), $this->payload($answers, $extra));

        $post([$q['goal']->id => 'شيء آخر'])->assertSessionHasErrors('answers.' . $q['goal']->id);
        $post([$q['places']->id => ['اليابان']])->assertSessionHasErrors('answers.' . $q['places']->id . '.0');
        $post([$q['budget']->id => ['min' => 900, 'max' => 100]])->assertSessionHasErrors('answers.' . $q['budget']->id);
        $post([$q['budget']->id => ['min' => 5, 'max' => 100]])->assertSessionHasErrors('answers.' . $q['budget']->id . '.min');
        $post([$q['urgency']->id => 9])->assertSessionHasErrors('answers.' . $q['urgency']->id);
        $post([$q['goal']->id => null])->assertSessionHasErrors('answers.' . $q['goal']->id);
        $post([], ['answer_files' => [$q['logo']->id => [UploadedFile::fake()->create('virus.exe', 10)]]])->assertSessionHasErrors('answer_files.' . $q['logo']->id . '.0');
        $post([], ['problem' => ''])->assertSessionHasErrors('problem');
        $post([], ['contact_email' => 'not-an-email'])->assertSessionHasErrors('contact_email');
        $post([], ['action' => 'publish-everywhere'])->assertSessionHasErrors('action');

        $this->assertSame(0, ServiceRequest::count());
        Storage::disk('private_uploads')->assertDirectoryEmpty('/');
    }

    public function test_explore_path_stays_private_until_the_client_publishes_it(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client)->post(route('requests.store'), $this->payload([], ['action' => ServiceRequest::PATH_EXPLORE]));

        $request = ServiceRequest::firstOrFail();
        $this->assertSame(ServiceRequest::STATUS_INQUIRY, $request->status);
        $this->assertNull($request->published_at);

        $this->actingAs(User::factory()->create())->post(route('requests.publish', $request))->assertForbidden();
        $this->actingAs($client)->post(route('requests.publish', $request))->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertSame(ServiceRequest::STATUS_OPEN, $request->status);
        $this->assertSame(ServiceRequest::PATH_EXECUTE, $request->path);
        $this->assertNotNull($request->published_at);
        $this->assertSame(1, ServiceRequest::count());
    }

    public function test_only_the_owner_and_admins_can_see_a_request_and_its_files(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client)->post(route('requests.store'), $this->payload([], [
            'attachments' => [UploadedFile::fake()->create('brief.pdf', 20, 'application/pdf')],
        ]));
        $request = ServiceRequest::firstOrFail();
        $file = $request->files()->first();

        $this->actingAs($client)->get(route('requests.show', $request))->assertOk()->assertSee($request->number)->assertSee('client@example.com');
        $this->actingAs($client)->get(route('requests.file', [$request, $file]))->assertOk()->assertDownload('brief.pdf');
        $this->actingAs($client)->get(route('requests.index'))->assertOk()->assertSee($request->number);

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('requests.show', $request))->assertForbidden();
        $this->actingAs($stranger)->get(route('requests.file', [$request, $file]))->assertForbidden();
        $this->actingAs($stranger)->get(route('requests.index'))->assertDontSee($request->number);

        $this->actingAs(User::factory()->freelancer()->create())->get(route('requests.show', $request))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('requests.show', $request))->assertOk();
    }

    public function test_editing_the_form_later_does_not_change_saved_answers(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client)->post(route('requests.store'), $this->payload());

        $this->q['goal']->update(['label' => 'سؤال معدّل', 'options' => ['أخرى', 'غيرها']]);
        $this->q['places']->delete();

        $answers = ServiceRequest::firstOrFail()->answers;
        $this->assertSame('مبيعات', $answers->firstWhere('label', 'ما الهدف؟')->value);
        $this->assertSame(['مصر'], $answers->firstWhere('label', 'أين؟')->value);

        $this->actingAs($client)->get(route('requests.show', ServiceRequest::first()))->assertOk()->assertSee('ما الهدف؟')->assertSee('أين؟');
    }

    public function test_paused_form_stops_accepting_requests(): void
    {
        $this->form->update(['status' => ServiceForm::STATUS_PAUSED]);

        $this->actingAs(User::factory()->create())->post(route('requests.store'), $this->payload())
            ->assertSessionHasErrors('sub_category_id');
        $this->assertSame(0, ServiceRequest::count());
    }

    public function test_non_client_accounts_get_the_right_notice(): void
    {
        $employee = User::factory()->create(['account_type' => User::TYPE_EMPLOYEE]);

        $this->actingAs($employee)->get(route('requests.create'))
            ->assertOk()->assertSee('غيّر نوع حسابك إلى «عميل»')->assertDontSee('حسابك مسجّل كمستقل');
    }

    public function test_preferred_contact_method_is_optional_and_validated(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->post(route('requests.store'), $this->payload([], ['contact_method' => 'pigeon']))->assertSessionHasErrors('contact_method');
        $this->actingAs($client)->post(route('requests.store'), $this->payload([], ['contact_method' => 'whatsapp']))->assertSessionHasNoErrors();
        $this->actingAs($client)->post(route('requests.store'), $this->payload())->assertSessionHasNoErrors();

        $this->assertSame(['whatsapp', null], ServiceRequest::orderBy('id')->pluck('contact_method')->all());
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.requests.show', ServiceRequest::first()))
            ->assertSee('يفضّل التواصل عبر')->assertSee('واتساب');
    }
}
