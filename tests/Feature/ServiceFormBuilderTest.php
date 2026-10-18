<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFormBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function service(string $name = 'إعلانات المواقع'): SubCategory
    {
        $category = Category::firstOrCreate(['name' => 'تسويق'], ['slug' => 'marketing', 'image' => 'x.png', 'status' => 1]);

        return SubCategory::create(['name' => $name, 'slug' => md5($name), 'image' => 'x.png', 'parent_id' => $category->id, 'status' => 1]);
    }

    private function form(?SubCategory $service = null): ServiceForm
    {
        $service ??= $this->service();
        $this->actingAs($this->admin)->post(route('admin.request-forms.store', $service));

        return ServiceForm::where('sub_category_id', $service->id)->firstOrFail();
    }

    private function addQuestion(ServiceForm $form, array $data)
    {
        return $this->actingAs($this->admin)->post(route('admin.request-forms.questions.store', $form), $data + [
            'service_form_section_id' => $form->sections()->value('id'),
            'is_required' => 0,
        ]);
    }

    private function yesNo(ServiceForm $form, string $label = 'هل لديك موقع؟'): Q
    {
        $this->addQuestion($form, ['type' => Q::TYPE_YES_NO, 'label' => $label])->assertSessionHasNoErrors();

        return $form->questions()->where('label', $label)->firstOrFail();
    }

    public function test_only_admins_can_open_the_builder(): void
    {
        $this->service();
        $this->actingAs(User::factory()->create())->get(route('admin.request-forms.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.request-forms.index'))->assertOk()->assertSee('إعلانات المواقع', false);
    }

    public function test_creating_a_form_starts_as_a_draft_with_one_section(): void
    {
        $form = $this->form();

        $this->assertSame(ServiceForm::STATUS_DRAFT, $form->status);
        $this->assertSame(1, $form->sections()->count());
        $this->actingAs($this->admin)->get(route('admin.request-forms.show', $form->sub_category_id))->assertOk()->assertSee('تفاصيل المشروع');
    }

    public function test_every_field_type_can_be_added(): void
    {
        $form = $this->form();
        $cases = [
            [Q::TYPE_SHORT_TEXT, ['settings' => ['max_length' => 100]]],
            [Q::TYPE_LONG_TEXT, ['settings' => ['min_length' => 10, 'max_length' => 2000]]],
            [Q::TYPE_SINGLE_CHOICE, ['options_text' => "زيادة المبيعات\nجذب عملاء"]],
            [Q::TYPE_MULTI_CHOICE, ['options_text' => "فيسبوك\nإنستغرام\nتيك توك"]],
            [Q::TYPE_DROPDOWN, ['options_text' => "مصر\nالسعودية"]],
            [Q::TYPE_YES_NO, []],
            [Q::TYPE_NUMBER, ['settings' => ['mode' => 'range', 'min' => 50, 'max' => 5000, 'unit' => '$']]],
            [Q::TYPE_DATE, []],
            [Q::TYPE_URL, []],
            [Q::TYPE_LOCATION, []],
            [Q::TYPE_RATING, ['settings' => ['scale' => 5, 'min_label' => 'منخفضة', 'max_label' => 'عالية']]],
            [Q::TYPE_FILE, ['settings' => ['max_files' => 3, 'max_size_mb' => 5, 'extensions' => 'JPG, png,.pdf']]],
        ];

        foreach ($cases as [$type, $extra]) {
            $this->addQuestion($form, ['type' => $type, 'label' => "سؤال $type"] + $extra)->assertSessionHasNoErrors();
        }

        $this->assertSame(12, $form->questions()->count());
        $this->assertSame(['فيسبوك', 'إنستغرام', 'تيك توك'], $form->questions()->where('type', Q::TYPE_MULTI_CHOICE)->first()->options);
        $this->assertSame('jpg,png,pdf', $form->questions()->where('type', Q::TYPE_FILE)->first()->setting('extensions'));
        $this->assertSame('range', $form->questions()->where('type', Q::TYPE_NUMBER)->first()->setting('mode'));
    }

    public function test_question_validation(): void
    {
        $form = $this->form();

        $this->addQuestion($form, ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'x', 'options_text' => 'واحد فقط'])->assertSessionHasErrors('options_list');
        $this->addQuestion($form, ['type' => Q::TYPE_DROPDOWN, 'label' => 'x', 'options_text' => "أ\nأ"])->assertSessionHasErrors('options_list.1');
        $this->addQuestion($form, ['type' => Q::TYPE_FILE, 'label' => 'x', 'settings' => ['max_files' => 2, 'max_size_mb' => 5, 'extensions' => 'pdf,svg,exe']])->assertSessionHasErrors('settings.extensions');
        $this->addQuestion($form, ['type' => Q::TYPE_NUMBER, 'label' => 'x', 'settings' => ['mode' => 'range', 'min' => 10, 'max' => 5]])->assertSessionHasErrors('settings.max');
        $this->addQuestion($form, ['type' => 'html', 'label' => 'x'])->assertSessionHasErrors('type');
        $this->addQuestion($form, ['type' => Q::TYPE_SHORT_TEXT, 'label' => ''])->assertSessionHasErrors('label');

        $other = $this->form($this->service('تصميم هوية'));
        $this->addQuestion($form, ['type' => Q::TYPE_SHORT_TEXT, 'label' => 'x', 'settings' => ['max_length' => 50], 'service_form_section_id' => $other->sections()->value('id')])
            ->assertSessionHasErrors('service_form_section_id');

        $this->assertSame(0, $form->questions()->count());
    }

    public function test_condition_shows_a_question_based_on_another_answer(): void
    {
        $form = $this->form();
        $hasSite = $this->yesNo($form);

        $this->addQuestion($form, [
            'type' => Q::TYPE_URL, 'label' => 'ما رابط الموقع؟', 'is_required' => 1,
            'condition_question_id' => $hasSite->id, 'condition_values' => ['نعم'],
        ])->assertSessionHasNoErrors();

        $link = $form->questions()->where('label', 'ما رابط الموقع؟')->first();
        $this->assertSame(['question_id' => $hasSite->id, 'values' => ['نعم'], 'negate' => false], $link->condition);

        $this->addQuestion($form, [
            'type' => Q::TYPE_URL, 'label' => 'x', 'condition_question_id' => $hasSite->id, 'condition_values' => ['ربما'],
        ])->assertSessionHasErrors('condition_values');
        $this->addQuestion($form, [
            'type' => Q::TYPE_URL, 'label' => 'x', 'condition_question_id' => $link->id, 'condition_values' => ['x'],
        ])->assertSessionHasErrors('condition_question_id');
    }

    public function test_conditions_cannot_loop(): void
    {
        $form = $this->form();
        $a = $this->yesNo($form, 'أ؟');
        $this->addQuestion($form, ['type' => Q::TYPE_YES_NO, 'label' => 'ب؟', 'condition_question_id' => $a->id, 'condition_values' => ['نعم']]);
        $b = $form->questions()->where('label', 'ب؟')->first();

        $this->actingAs($this->admin)->post(route('admin.request-forms.questions.update', $a), [
            'service_form_section_id' => $a->service_form_section_id, 'type' => Q::TYPE_YES_NO, 'label' => 'أ؟',
            'condition_question_id' => $b->id, 'condition_values' => ['نعم'],
        ])->assertSessionHasErrors('condition_question_id');
    }

    public function test_options_used_by_a_condition_cannot_be_removed(): void
    {
        $form = $this->form();
        $this->addQuestion($form, ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'الهدف؟', 'options_text' => "مبيعات\nزيارات"]);
        $goal = $form->questions()->where('label', 'الهدف؟')->first();
        $this->addQuestion($form, ['type' => Q::TYPE_SHORT_TEXT, 'label' => 'المنتج؟', 'settings' => ['max_length' => 100], 'condition_question_id' => $goal->id, 'condition_values' => ['مبيعات']]);

        $update = fn (array $data) => $this->actingAs($this->admin)->post(route('admin.request-forms.questions.update', $goal), $data + [
            'service_form_section_id' => $goal->service_form_section_id, 'label' => 'الهدف؟',
        ]);

        $update(['type' => Q::TYPE_SINGLE_CHOICE, 'options_text' => "زيارات\nوعي"])->assertSessionHasErrors('options_text');
        $update(['type' => Q::TYPE_SHORT_TEXT, 'settings' => ['max_length' => 10]])->assertSessionHasErrors('type');
        $update(['type' => Q::TYPE_DROPDOWN, 'options_text' => "مبيعات\nزيارات\nوعي"])->assertSessionHasNoErrors();
    }

    public function test_deleting_a_question_releases_dependent_conditions(): void
    {
        $form = $this->form();
        $hasSite = $this->yesNo($form);
        $this->addQuestion($form, ['type' => Q::TYPE_URL, 'label' => 'الرابط', 'condition_question_id' => $hasSite->id, 'condition_values' => ['نعم']]);

        $this->actingAs($this->admin)->post(route('admin.request-forms.questions.destroy', $hasSite))
            ->assertSessionHas('success', fn ($message) => str_contains($message, '«الرابط»'));

        $this->assertNull($form->questions()->where('label', 'الرابط')->first()->condition);
    }

    public function test_filter_keys_match_type_and_are_unique_per_form(): void
    {
        $form = $this->form();
        $budget = ['type' => Q::TYPE_NUMBER, 'settings' => ['mode' => 'range'], 'filter_key' => 'budget'];

        $this->addQuestion($form, ['type' => Q::TYPE_URL, 'label' => 'x', 'filter_key' => 'budget'])->assertSessionHasErrors('filter_key');
        $this->addQuestion($form, $budget + ['label' => 'الميزانية'])->assertSessionHasNoErrors();
        $this->addQuestion($form, $budget + ['label' => 'ميزانية أخرى'])->assertSessionHasErrors('filter_key');
    }

    public function test_reorder_moves_questions_between_sections(): void
    {
        $form = $this->form();
        $first = $form->sections()->first();
        $this->actingAs($this->admin)->post(route('admin.request-forms.sections.store', $form), ['title' => 'القسم الثاني']);
        $second = $form->sections()->where('title', 'القسم الثاني')->first();
        $a = $this->yesNo($form, 'أ؟');
        $b = $this->yesNo($form, 'ب؟');

        $this->actingAs($this->admin)->postJson(route('admin.request-forms.reorder', $form), ['sections' => [
            ['id' => $second->id, 'questions' => [$b->id]],
            ['id' => $first->id, 'questions' => [$a->id]],
        ]])->assertOk();

        $this->assertSame($second->id, $b->fresh()->service_form_section_id);
        $this->assertSame([$second->id, $first->id], $form->sections()->pluck('id')->all());

        $this->actingAs($this->admin)->postJson(route('admin.request-forms.reorder', $form), ['sections' => [
            ['id' => $first->id, 'questions' => [$a->id]],
        ]])->assertStatus(422);
    }

    public function test_duplicating_a_section_copies_questions_and_inner_conditions(): void
    {
        $form = $this->form();
        $hasSite = $this->yesNo($form);
        $this->addQuestion($form, ['type' => Q::TYPE_URL, 'label' => 'الرابط', 'condition_question_id' => $hasSite->id, 'condition_values' => ['نعم']]);

        $this->actingAs($this->admin)->post(route('admin.request-forms.sections.duplicate', $form->sections()->first()))->assertSessionHasNoErrors();

        $copySection = $form->sections()->where('title', 'like', '%(نسخة)')->first();
        $copiedYesNo = $copySection->questions()->where('type', Q::TYPE_YES_NO)->first();
        $copiedLink = $copySection->questions()->where('type', Q::TYPE_URL)->first();
        $this->assertSame($copiedYesNo->id, $copiedLink->condition['question_id']);
    }

    public function test_copying_a_form_from_another_service(): void
    {
        $source = $this->form();
        $hasSite = $this->yesNo($source);
        $this->addQuestion($source, ['type' => Q::TYPE_URL, 'label' => 'الرابط', 'condition_question_id' => $hasSite->id, 'condition_values' => ['نعم']]);

        $target = $this->service('إعلانات ممولة');
        $this->actingAs($this->admin)->post(route('admin.request-forms.store', $target), ['copy_from' => $source->id]);

        $copy = ServiceForm::where('sub_category_id', $target->id)->first();
        $this->assertSame(2, $copy->questions()->count());
        $this->assertSame(
            $copy->questions()->where('type', Q::TYPE_YES_NO)->value('id'),
            $copy->questions()->where('type', Q::TYPE_URL)->first()->condition['question_id']
        );
    }

    public function test_publishing_requires_questions(): void
    {
        $form = $this->form();
        $status = fn ($status) => $this->actingAs($this->admin)->post(route('admin.request-forms.status', $form), ['status' => $status]);

        $status(ServiceForm::STATUS_PUBLISHED)->assertSessionHasErrors();
        $this->assertSame(ServiceForm::STATUS_DRAFT, $form->fresh()->status);

        $this->yesNo($form);
        $status(ServiceForm::STATUS_PUBLISHED)->assertSessionHasNoErrors();
        $this->assertTrue($form->fresh()->isPublished());
        $this->assertNotNull($form->fresh()->published_at);

        $status(ServiceForm::STATUS_PAUSED);
        $this->assertSame(ServiceForm::STATUS_PAUSED, $form->fresh()->status);
    }

    public function test_preview_renders_the_client_form(): void
    {
        $form = $this->form();
        $hasSite = $this->yesNo($form);
        $this->addQuestion($form, ['type' => Q::TYPE_URL, 'label' => 'ما رابط الموقع؟', 'condition_question_id' => $hasSite->id, 'condition_values' => ['نعم']]);

        $this->actingAs($this->admin)->get(route('admin.request-forms.preview', $form))
            ->assertOk()
            ->assertSee('هل لديك موقع؟')
            ->assertSee('ما رابط الموقع؟')
            ->assertSee('data-condition', false)
            ->assertSee('أريد تنفيذ المشروع')
            ->assertSee('بيانات التواصل');
    }

    public function test_published_form_is_edited_as_a_draft_revision_then_published(): void
    {
        $form = $this->form();
        $old = $this->yesNo($form, 'سؤال قديم؟');
        $form->update(['status' => ServiceForm::STATUS_PUBLISHED]);
        $client = User::factory()->create();

        // A request answered with the published version.
        $this->actingAs($client)->post(route('requests.store'), [
            'sub_category_id' => $form->sub_category_id, 'action' => 'execute', 'answers' => [$old->id => 'نعم'],
            'problem' => 'حملة إعلانية كاملة', 'contact_name' => 'س', 'contact_phone' => '01000000000', 'contact_email' => 's@example.com',
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post(route('admin.request-forms.revision', $form))->assertRedirect();
        $revision = ServiceForm::where('revision_of_id', $form->id)->firstOrFail();
        $this->assertSame(1, $revision->questions()->count());
        $this->actingAs($this->admin)->post(route('admin.request-forms.revision', $form)); // no second copy
        $this->assertSame(1, ServiceForm::where('revision_of_id', $form->id)->count());

        // Editing the draft does not touch what clients see.
        $this->addQuestion($revision, ['type' => Q::TYPE_SHORT_TEXT, 'label' => 'سؤال جديد', 'settings' => ['max_length' => 100]])
            ->assertRedirect(route('admin.request-forms.builder', $revision));
        $this->actingAs($client)->get(route('requests.fields', $form->sub_category_id))->assertSee('سؤال قديم؟')->assertDontSee('سؤال جديد');
        $this->actingAs($this->admin)->post(route('admin.request-forms.status', $revision), ['status' => 'published'])->assertSessionHasErrors();
        $this->actingAs($this->admin)->get(route('admin.request-forms.show', $form->sub_category_id))->assertSee('افتح مسودة التعديل');

        $this->actingAs($this->admin)->post(route('admin.request-forms.revision.publish', $revision))->assertRedirect(route('admin.request-forms.builder', $form));

        $this->assertNull(ServiceForm::find($revision->id));
        $this->assertSame(['سؤال قديم؟', 'سؤال جديد'], $form->fresh()->questions()->pluck('label')->all());
        $this->assertTrue($form->fresh()->isPublished());
        $this->actingAs($client)->get(route('requests.fields', $form->sub_category_id))->assertSee('سؤال جديد');
        $this->assertSame('سؤال قديم؟', \App\Models\ServiceRequest::first()->answers()->value('label'));
    }

    public function test_a_draft_revision_can_be_discarded(): void
    {
        $form = $this->form();
        $this->yesNo($form);
        $form->update(['status' => ServiceForm::STATUS_PUBLISHED]);
        $revision = $form->createRevision();

        $this->actingAs($this->admin)->post(route('admin.request-forms.revision.discard', $revision))->assertRedirect(route('admin.request-forms.builder', $form));
        $this->assertSame(0, ServiceForm::where('revision_of_id', $form->id)->count());
        $this->assertSame(1, $form->questions()->count());
    }
}
