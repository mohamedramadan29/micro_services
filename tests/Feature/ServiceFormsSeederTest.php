<?php

namespace Tests\Feature;

use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\ServiceFormsSeeder;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Ready request forms for the 42 services (user request 2026-10-31). */
class ServiceFormsSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedForms(): ServiceFormsSeeder
    {
        $seeder = new ServiceFormsSeeder;
        $seeder->setContainer($this->app)->__invoke();

        return $seeder;
    }

    public function test_every_service_gets_a_published_form_with_valid_questions(): void
    {
        $seeder = $this->seedForms();
        // Active services only: the hidden companies form («نفّذها للأعمال», status 0) is not part of the catalog.
        $services = SubCategory::where('status', 1)->get();
        $catalogForms = fn () => ServiceForm::published()->whereHas('subCategory', fn ($q) => $q->where('status', 1));

        $this->assertSame(42, $services->count());
        $this->assertSame(42, $seeder->created);
        $this->assertSame(42, $catalogForms()->count());

        foreach ($catalogForms()->with(['sections', 'questions', 'subCategory'])->get() as $form) {
            $name = $form->subCategory->name;
            $this->assertNotNull($form->subCategory->requestUrl(), $name);
            $this->assertSame(['الميزانية والموعد'], $form->sections->pluck('title')->slice(1)->values()->all(), $name);
            $this->assertGreaterThanOrEqual(8, $form->questions->count(), $name);

            $byId = $form->questions->keyBy('id');
            $filters = $form->questions->pluck('filter_key')->filter();
            $this->assertSame($filters->count(), $filters->unique()->count(), "$name: a filter is used twice");
            $this->assertContains('budget', $filters, $name);

            foreach ($form->questions as $question) {
                $this->assertArrayHasKey($question->type, Q::TYPES, $name);
                if ($question->hasOptions()) {
                    $this->assertNotEmpty($question->options, "$name: {$question->label}");
                }
                if ($question->filter_key) {
                    $this->assertContains($question->type, Q::FILTER_KEYS[$question->filter_key]['types'], "$name: {$question->label}");
                }
                if ($question->condition) {
                    $source = $byId[$question->condition['question_id']] ?? null;
                    $this->assertNotNull($source, "$name: {$question->label} depends on a question of another form");
                    $this->assertTrue($source->canBeConditionSource() && $source->sort_order < $question->sort_order, "$name: {$question->label}");
                    $this->assertEmpty(array_diff($question->condition['values'], $source->choiceValues()), "$name: {$question->label} waits for an answer that does not exist");
                }
            }
        }
    }

    public function test_running_again_never_touches_existing_forms(): void
    {
        (new ServicesCatalogSeeder)->run();
        // The admin already built (and is still drafting) the logo form.
        $logo = SubCategory::where('name', 'تصميم الشعارات')->first();
        $mine = ServiceForm::create(['sub_category_id' => $logo->id, 'status' => ServiceForm::STATUS_DRAFT]);
        $mine->sections()->create(['title' => 'قسم الأدمن', 'sort_order' => 1]);

        $this->assertSame(41, $this->seedForms()->created);
        $this->assertSame(0, $this->seedForms()->created);

        $this->assertSame(1, ServiceForm::main()->where('sub_category_id', $logo->id)->count());
        $this->assertSame(['قسم الأدمن'], $mine->fresh()->sections->pluck('title')->all());
        $this->assertSame(ServiceForm::STATUS_DRAFT, $mine->fresh()->status);
    }

    public function test_a_client_can_send_a_request_through_every_seeded_form(): void
    {
        Notification::fake();
        $this->seedForms();
        $client = User::factory()->create();

        foreach (ServiceForm::published()->whereHas('subCategory', fn ($q) => $q->where('status', 1))->with(['questions', 'subCategory'])->get() as $form) {
            $answers = [];
            foreach ($form->questions as $question) {
                $answer = match ($question->type) {
                    Q::TYPE_SINGLE_CHOICE, Q::TYPE_DROPDOWN => $question->options[0],
                    Q::TYPE_MULTI_CHOICE => [$question->options[0]],
                    Q::TYPE_YES_NO => Q::YES,
                    Q::TYPE_NUMBER => $question->setting('mode') === 'range' ? ['min' => 500, 'max' => 1500] : 3,
                    Q::TYPE_DATE => now()->addWeeks(2)->toDateString(),
                    Q::TYPE_URL => 'https://example.com',
                    Q::TYPE_LOCATION => 'القاهرة',
                    Q::TYPE_RATING => 3,
                    Q::TYPE_FILE => null,
                    default => 'إجابة تجريبية واضحة',
                };
                if ($answer !== null) {
                    $answers[$question->id] = $answer;
                }
            }

            $this->actingAs($client)->post(route('requests.store'), [
                'sub_category_id' => $form->sub_category_id,
                'action' => ServiceRequest::PATH_EXECUTE,
                'answers' => $answers,
                'problem' => 'طلب تجريبي لخدمة ' . $form->subCategory->name,
                'contact_name' => 'عميل تجريبي',
                'contact_phone' => '+20 100 000 0000',
                'contact_email' => 'client@example.com',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(42, ServiceRequest::count());
        // The shared section feeds the freelancers' filters.
        $request = ServiceRequest::where('sub_category_id', SubCategory::where('name', 'تصميم الشعارات')->value('id'))->sole();
        $this->assertEquals([500, 1500, 'عادي'], [$request->budget_min, $request->budget_max, $request->urgency]);
    }

    public function test_the_logo_form_shows_its_questions_on_the_request_page(): void
    {
        $this->seedForms();
        $logo = SubCategory::where('name', 'تصميم الشعارات')->first();

        $this->actingAs(User::factory()->create())->get(route('requests.create', ['service' => $logo->id]))->assertOk()
            ->assertSee('تفاصيل الشعار')->assertSee('الاسم المكتوب في الشعار')->assertSee('الميزانية والموعد')->assertSee('ما مدى استعجالك؟');
    }
}
