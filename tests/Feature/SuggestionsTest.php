<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\Suggestion;
use App\Models\SuggestionCategory;
use App\Models\User;
use App\Notifications\SuggestionAdminNotification;
use App\Notifications\SuggestionUpdateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Client file «نظام الاقتراحات والتطوير المستمر»: the acceptance checks of its page 6. */
class SuggestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_key' => (string) Str::uuid(),
            'category_id' => SuggestionCategory::first()->id,
            'title' => 'إضافة فلتر للأسعار',
            'description' => 'أقترح إضافة فلتر للأسعار في صفحة الخدمات حتى أصل للخدمة المناسبة أسرع.',
        ], $overrides);
    }

    private function submitAs(?User $user, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        $user ? $this->actingAs($user) : $this->asGuest();

        return $this->post(route('suggestions.store'), $this->payload($overrides));
    }

    /** actingAs() lasts for the rest of the test; a visitor's request needs the guard cleared. */
    private function asGuest(): void
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            \Illuminate\Support\Facades\Auth::logout();
        }
    }

    public function test_footer_link_shows_on_pages_and_can_be_hidden_from_the_admin(): void
    {
        $this->get('/')->assertOk()->assertSee(route('suggestions.create'), false)->assertSee('شاركنا اقتراحك');
        $this->get(route('services.index'))->assertSee(route('suggestions.create'), false);

        ContentBlock::create(['area' => 'suggestions.footer', 'data' => ['show' => '0', 'title' => 'ساهم', 'text' => '', 'button' => 'شاركنا اقتراحك']]);
        \Illuminate\Support\Facades\Cache::flush();

        $this->get('/')->assertOk()->assertDontSee(route('suggestions.create'), false);
    }

    public function test_page_shows_the_admin_managed_types(): void
    {
        SuggestionCategory::create(['name' => 'نوع جديد من الأدمن', 'sort_order' => 9]);
        SuggestionCategory::create(['name' => 'نوع مخفي', 'is_active' => false]);

        $this->get(route('suggestions.create'))->assertOk()
            ->assertSee('ساهم في تطوير نفّذها')->assertSee('ميزة جديدة')->assertSee('نوع جديد من الأدمن')->assertDontSee('نوع مخفي');
    }

    public function test_a_freelancer_sends_a_suggestion_gets_a_number_and_sees_it_in_his_list(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $admin = User::factory()->admin()->create();

        $this->submitAs($freelancer)->assertRedirect(route('suggestions.create'))->assertSessionHas('suggestion_created');

        $suggestion = Suggestion::sole();
        $this->assertSame('SUG-' . (1000 + $suggestion->id), $suggestion->reference_code);
        $this->assertSame('freelancer', $suggestion->user_role);
        $this->assertSame($freelancer->id, $suggestion->user_id);
        $this->assertSame(Suggestion::STATUS_NEW, $suggestion->status);
        $this->assertSame('created', $suggestion->history()->sole()->action);

        $this->actingAs($freelancer)->get(route('suggestions.mine'))->assertOk()
            ->assertSee($suggestion->reference_code)->assertSee('إضافة فلتر للأسعار')->assertSee('جديد');

        Notification::assertSentTo($admin, SuggestionAdminNotification::class);
        Notification::assertSentTo($freelancer, SuggestionUpdateNotification::class);
    }

    public function test_only_staff_who_can_open_suggestions_are_notified_or_assignable(): void
    {
        $manager = User::factory()->admin()->create();
        $handler = User::factory()->admin()->create(['admin_role' => 'suggestions']);
        $accountant = User::factory()->admin()->create(['admin_role' => 'accounts']);
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);

        $this->submitAs(null);
        $suggestion = Suggestion::sole();
        Notification::assertSentTo([$manager, $handler], SuggestionAdminNotification::class);
        Notification::assertNotSentTo([$accountant, $analyst], SuggestionAdminNotification::class);

        $this->actingAs($manager)->get(route('admin.suggestions.show', $suggestion))->assertSee($handler->name)->assertDontSee($accountant->name);
        $this->actingAs($manager)->post(route('admin.suggestions.update', $suggestion), ['status' => 'new', 'internal_priority' => 'normal', 'assigned_admin_id' => $accountant->id])
            ->assertSessionHasErrors('assigned_admin_id');
        $this->actingAs($manager)->post(route('admin.suggestions.update', $suggestion), ['status' => 'new', 'internal_priority' => 'normal', 'assigned_admin_id' => $handler->id])
            ->assertSessionHasNoErrors();
        Notification::assertSentTo($handler, SuggestionAdminNotification::class, fn ($n) => $n->toArray($handler)['text'] === \App\Support\Msg::t('admin.suggestion_assigned', ['number' => $suggestion->reference_code, 'title' => $suggestion->title]));
    }

    public function test_success_message_with_the_number_is_shown_only_after_saving(): void
    {
        $response = $this->followingRedirects()->post(route('suggestions.store'), $this->payload());
        $code = Suggestion::sole()->reference_code;
        $response->assertOk()->assertSee('وصل اقتراحك بنجاح، رقم المتابعة', false)->assertSee('<bdi dir="ltr" class="text-nowrap">' . $code . '</bdi>', false);
    }

    public function test_a_visitor_can_send_with_optional_name_and_email_only_when_asking_for_updates(): void
    {
        $this->submitAs(null, ['guest_name' => 'زائر كريم'])->assertSessionHasNoErrors();
        $first = Suggestion::sole();
        $this->assertSame('guest', $first->user_role);
        $this->assertNull($first->guest_email);
        Notification::assertNothingSentTo(new AnonymousNotifiable);

        // Updates asked → the email becomes required and must be valid.
        $this->submitAs(null, ['wants_updates' => '1'])->assertSessionHasErrors('guest_email');
        $this->submitAs(null, ['wants_updates' => '1', 'guest_email' => 'not-an-email'])->assertSessionHasErrors('guest_email');
        $this->submitAs(null, ['wants_updates' => '1', 'guest_email' => 'visitor@example.com'])->assertSessionHasNoErrors();

        $second = Suggestion::latest('id')->first();
        $this->assertSame('visitor@example.com', $second->guest_email);
        Notification::assertSentOnDemand(SuggestionUpdateNotification::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'visitor@example.com');
    }

    public function test_an_account_cannot_send_guest_fields_and_its_identity_comes_from_the_account(): void
    {
        $client = User::factory()->create();
        $this->submitAs($client, ['guest_email' => 'other@example.com'])->assertSessionHasErrors('guest_email');
        $this->assertSame(0, Suggestion::count());

        $this->submitAs($client)->assertSessionHasNoErrors();
        $this->assertSame('client', Suggestion::sole()->user_role);
    }

    public function test_missing_or_invalid_input_shows_errors_and_saves_nothing(): void
    {
        $this->submitAs(null, ['title' => ''])->assertSessionHasErrors('title');
        $this->submitAs(null, ['title' => str_repeat('ا', 121)])->assertSessionHasErrors('title');
        $this->submitAs(null, ['description' => 'قصير جدًا'])->assertSessionHasErrors('description');
        $this->submitAs(null, ['description' => str_repeat('ا', 2001)])->assertSessionHasErrors('description');
        $this->submitAs(null, ['category_id' => 9999])->assertSessionHasErrors('category_id');
        $hidden = SuggestionCategory::create(['name' => 'مخفي', 'is_active' => false]);
        $this->submitAs(null, ['category_id' => $hidden->id])->assertSessionHasErrors('category_id');
        $this->submitAs(null, ['submission_key' => 'x'])->assertSessionHasErrors('submission_key');
        // Honeypot filled by a bot.
        $this->submitAs(null, ['nf_extra' => 'http://spam.example'])->assertSessionHasErrors('title');

        $this->assertSame(0, Suggestion::count());
    }

    public function test_sending_the_same_form_twice_creates_one_suggestion(): void
    {
        $payload = $this->payload();
        $this->post(route('suggestions.store'), $payload)->assertSessionHas('suggestion_created');
        $this->post(route('suggestions.store'), $payload)->assertSessionHas('suggestion_created', Suggestion::sole()->reference_code);

        $this->assertSame(1, Suggestion::count());
        Notification::assertSentTimes(SuggestionAdminNotification::class, 0); // no admins in this test
    }

    public function test_someone_elses_submission_key_is_treated_as_a_new_suggestion(): void
    {
        $owner = User::factory()->create();
        $payload = $this->payload();
        $this->submitAs($owner, $payload);
        $original = Suggestion::sole();

        // A replay of the owner's key by another account (or a visitor) never returns the owner's suggestion.
        $other = User::factory()->create();
        $this->submitAs($other, $payload + ['title' => 'اقتراح الحساب الآخر'])->assertSessionHas('suggestion_created');
        $this->submitAs(null, $payload + ['title' => 'اقتراح الزائر'])->assertSessionHas('suggestion_created');

        $this->assertSame(3, Suggestion::count());
        $this->assertSame([$owner->id, $other->id, null], Suggestion::orderBy('id')->pluck('user_id')->all());
        $this->assertNotEquals($original->reference_code, session('suggestion_created'));
        // The owner resending his own form still gets his suggestion back.
        $this->submitAs($owner, $payload)->assertSessionHas('suggestion_created', $original->reference_code);
        $this->assertSame(3, Suggestion::count());
    }

    public function test_sending_is_rate_limited_per_visitor(): void
    {
        // One browser (its session cookie) sends 5, the 6th is refused…
        $this->withCookie(config('session.cookie'), Str::random(40));
        for ($i = 0; $i < 5; $i++) {
            $this->submitAs(null)->assertSessionHasNoErrors();
        }
        $this->submitAs(null)->assertSessionHasErrors('title');
        $this->assertSame(5, Suggestion::count());
        // …failed attempts never count, so a second browser with a few errors can still send…
        $this->withCookie(config('session.cookie'), Str::random(40));
        for ($i = 0; $i < 6; $i++) {
            $this->submitAs(null, ['title' => ''])->assertSessionHasErrors('title');
        }
        $this->submitAs(null)->assertSessionHasNoErrors();
        $this->assertSame(6, Suggestion::count());
        // …and a sender without cookies (a bot) is stopped by the route's per-IP throttle (30 attempts/hour).
        $this->defaultCookies = [];
        $requests = 13; // every attempt above counted on this IP
        while ($this->submitAs(null)->getStatusCode() !== 429) {
            $this->assertLessThan(40, ++$requests, 'the per-IP throttle never fired');
        }
        $this->assertSame(30, $requests);
        $this->assertSame(23, Suggestion::count());
    }

    public function test_a_user_cannot_see_another_users_suggestion_by_changing_the_link(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->submitAs($owner);
        $code = Suggestion::sole()->reference_code;

        $this->actingAs($owner)->get(route('suggestions.show', $code))->assertOk()->assertSee('إضافة فلتر للأسعار');
        $this->actingAs($other)->get(route('suggestions.show', $code))->assertNotFound();
        $this->actingAs($other)->get(route('suggestions.mine'))->assertDontSee($code);
        $this->asGuest();
        $this->get(route('suggestions.show', $code))->assertRedirect(); // guests → login
    }

    public function test_admin_filters_searches_changes_status_and_every_change_is_logged(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();
        $this->submitAs($client, ['title' => 'إشعارات عند تحديث الطلب']);
        $this->submitAs(null, ['title' => 'تبسيط خطوات إنشاء الخدمة']);
        [$mine, $guest] = Suggestion::orderBy('id')->get()->all();

        $this->actingAs($admin)->get(route('admin.suggestions.index'))->assertOk()->assertSee($mine->reference_code)->assertSee($guest->reference_code);
        $this->actingAs($admin)->get(route('admin.suggestions.index', ['q' => 'تبسيط']))->assertSee($guest->reference_code)->assertDontSee('إشعارات عند تحديث الطلب');
        $this->actingAs($admin)->get(route('admin.suggestions.index', ['q' => $mine->reference_code]))->assertSee('إشعارات عند تحديث الطلب')->assertDontSee('تبسيط خطوات');
        $this->actingAs($admin)->get(route('admin.suggestions.index', ['role' => 'guest']))->assertSee('تبسيط خطوات')->assertDontSee('إشعارات عند تحديث الطلب');

        Notification::fake();
        $this->actingAs($admin)->post(route('admin.suggestions.update', $mine), [
            'status' => Suggestion::STATUS_REVIEWING, 'internal_priority' => 'high', 'assigned_admin_id' => $admin->id,
        ])->assertSessionHasNoErrors();
        Notification::assertNotSentTo($client, SuggestionUpdateNotification::class); // «قيد المراجعة» is not notified

        $this->actingAs($admin)->post(route('admin.suggestions.update', $mine), [
            'status' => Suggestion::STATUS_DONE, 'status_reason' => 'أضفناها في التحديث الأخير', 'internal_priority' => 'high', 'assigned_admin_id' => $admin->id,
        ])->assertSessionHasNoErrors();
        Notification::assertSentTo($client, SuggestionUpdateNotification::class, fn ($n) => str_contains($n->toArray($client)['text'], 'تم تنفيذ اقتراحك')
            && str_contains($n->toArray($client)['text'], 'أضفناها في التحديث الأخير'));

        // Closing later without typing a reply keeps the previous reply instead of wiping it.
        $this->actingAs($admin)->post(route('admin.suggestions.update', $mine), [
            'status' => Suggestion::STATUS_CLOSED, 'status_reason' => '', 'internal_priority' => 'high', 'assigned_admin_id' => $admin->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('أضفناها في التحديث الأخير', $mine->fresh()->status_reason);
        $this->actingAs($admin)->get(route('admin.suggestions.show', $mine))->assertSee('أضفناها في التحديث الأخير');
        $this->actingAs($admin)->post(route('admin.suggestions.update', $mine), [
            'status' => Suggestion::STATUS_DONE, 'status_reason' => 'رد جديد', 'internal_priority' => 'high', 'assigned_admin_id' => $admin->id,
        ]);

        $mine->refresh();
        $this->assertSame(Suggestion::STATUS_DONE, $mine->status);
        $this->assertSame('رد جديد', $mine->status_reason);
        $this->assertSame(['created', 'status', 'assigned', 'priority', 'status', 'status', 'status'], $mine->history()->reorder('id')->pluck('action')->all());
        $last = $mine->history()->first();
        $this->assertSame([Suggestion::STATUS_CLOSED, Suggestion::STATUS_DONE, $admin->id], [$last->from_status, $last->to_status, $last->user_id]);

        $this->actingAs($admin)->get(route('admin.suggestions.show', $mine))->assertOk()->assertSee('سجل الإجراءات')->assertSee('أضفناها في التحديث الأخير');
        $this->actingAs($client)->get(route('suggestions.mine'))->assertSee('تم التنفيذ')->assertSee('رد جديد');
    }

    public function test_internal_notes_are_never_notified_or_shown_to_the_sender(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create();
        $this->submitAs($client);
        $suggestion = Suggestion::sole();
        Notification::fake();

        $this->actingAs($admin)->post(route('admin.suggestions.notes', $suggestion), ['body' => 'فكرة مكلفة، نؤجلها'])->assertSessionHasNoErrors();

        Notification::assertNothingSent();
        $this->actingAs($admin)->get(route('admin.suggestions.show', $suggestion))->assertSee('فكرة مكلفة، نؤجلها');
        $this->actingAs($client)->get(route('suggestions.show', $suggestion->reference_code))->assertDontSee('فكرة مكلفة');
    }

    public function test_duplicates_are_linked_without_losing_the_original(): void
    {
        $admin = User::factory()->admin()->create();
        $this->submitAs(null, ['title' => 'الفكرة الأصلية']);
        $this->submitAs(null, ['title' => 'نفس الفكرة مرة ثانية']);
        $this->submitAs(null, ['title' => 'نفس الفكرة مرة ثالثة']);
        [$original, $repeat, $third] = Suggestion::orderBy('id')->get()->all();

        $this->actingAs($admin)->post(route('admin.suggestions.duplicate', $repeat), ['original' => strtolower($original->reference_code)])->assertSessionHasNoErrors();
        // Linking to a repeat points at the root, never a chain.
        $this->actingAs($admin)->post(route('admin.suggestions.duplicate', $third), ['original' => $repeat->reference_code])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.suggestions.duplicate', $original), ['original' => $original->reference_code])->assertSessionHasErrors('original');

        $this->assertSame($original->id, $repeat->fresh()->duplicate_of_id);
        $this->assertSame($original->id, $third->fresh()->duplicate_of_id);
        $this->assertSame('نفس الفكرة مرة ثانية', $repeat->fresh()->title);
        $this->actingAs($admin)->get(route('admin.suggestions.show', $original))->assertSee($repeat->reference_code)->assertSee($third->reference_code);

        $this->actingAs($admin)->post(route('admin.suggestions.duplicate', $repeat), ['unlink' => 1]);
        $this->assertNull($repeat->fresh()->duplicate_of_id);
    }

    public function test_permissions_are_enforced_on_the_server(): void
    {
        $this->submitAs(null);
        $suggestion = Suggestion::sole();
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);
        $handler = User::factory()->admin()->create(['admin_role' => 'suggestions']);
        $accounts = User::factory()->admin()->create(['admin_role' => 'accounts']);
        $client = User::factory()->create();

        // View only.
        $this->actingAs($analyst)->get(route('admin.suggestions.show', $suggestion))->assertOk()->assertSee('اطلاع فقط');
        $this->actingAs($analyst)->get(route('admin.suggestions.export'))->assertForbidden();
        $this->actingAs($analyst)->get(route('admin.suggestions.settings'))->assertForbidden();

        // Handler: statuses + export, no settings.
        $this->actingAs($handler)->post(route('admin.suggestions.update', $suggestion), ['status' => 'reviewing', 'internal_priority' => 'normal'])->assertSessionHasNoErrors();
        $this->actingAs($handler)->get(route('admin.suggestions.export'))->assertOk();
        $this->actingAs($handler)->get(route('admin.suggestions.settings'))->assertForbidden();
        $this->actingAs($handler)->post(route('admin.suggestions.categories.store'), ['name' => 'x'])->assertForbidden();
        // Outside his box the handler only looks (like the analyst).
        $this->actingAs($handler)->get(route('admin.help-requests.index'))->assertOk();
        $this->actingAs($handler)->post(route('admin.catalog.groups.store'), ['name' => 'x'])->assertForbidden();

        // Other staff roles and site users: nothing.
        $this->actingAs($accounts)->get(route('admin.suggestions.index'))->assertForbidden();
        $this->actingAs($accounts)->post(route('admin.suggestions.update', $suggestion), ['status' => 'done', 'internal_priority' => 'normal'])->assertForbidden();
        $this->actingAs($client)->get(route('admin.suggestions.index'))->assertForbidden();
        $this->assertSame('reviewing', $suggestion->fresh()->status);
    }

    public function test_csv_export_follows_filters_and_neutralises_formulas(): void
    {
        $admin = User::factory()->admin()->create();
        $this->submitAs(null, ['title' => '=HYPERLINK("http://evil")', 'wants_updates' => '1', 'guest_email' => 'v@example.com']);
        $this->submitAs(null, ['title' => 'اقتراح آخر عادي']);

        $csv = $this->actingAs($admin)->get(route('admin.suggestions.export', ['q' => 'HYPERLINK']))->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('v@example.com', $csv);
        $this->assertStringNotContainsString('اقتراح آخر عادي', $csv);
    }

    public function test_types_are_managed_in_settings_and_a_used_type_is_only_hidden(): void
    {
        $manager = User::factory()->admin()->create();
        $this->actingAs($manager)->post(route('admin.suggestions.categories.store'), ['name' => 'تحسين الأمان', 'sort_order' => 5, 'is_active' => 1])->assertSessionHasNoErrors();
        $this->get(route('suggestions.create'))->assertSee('تحسين الأمان');

        $used = SuggestionCategory::first();
        $this->submitAs(null, ['category_id' => $used->id]);
        $this->actingAs($manager)->post(route('admin.suggestions.categories.destroy', $used));
        $this->assertFalse($used->fresh()->is_active);
        $this->assertSame($used->name, Suggestion::sole()->categoryLabel());

        $unused = SuggestionCategory::where('name', 'تحسين الأمان')->first();
        $this->actingAs($manager)->post(route('admin.suggestions.categories.destroy', $unused));
        $this->assertNull($unused->fresh());
    }
}
