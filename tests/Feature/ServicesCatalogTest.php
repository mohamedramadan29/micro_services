<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ContentBlock;
use App\Models\HelpRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\HelpRequestNotification;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Client brief «متطلبات تطوير قسم الخدمات»: services page, help requests, admin catalog. */
class ServicesCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = User::factory()->admin()->create(['name' => 'المدير']);
        $this->seed(ServicesCatalogSeeder::class);
    }

    private function service(string $name): SubCategory
    {
        return SubCategory::where('name', $name)->firstOrFail();
    }

    public function test_the_seeder_creates_the_42_services_in_6_groups_once(): void
    {
        $this->seed(ServicesCatalogSeeder::class);

        // Active groups/services only: the hidden companies form group («نفّذها للأعمال», status 0) is not part of the catalog.
        $this->assertSame(6, Category::where('status', 1)->count());
        // The brief's 41 + «تحسين سرعة المواقع» (client, 2026-10-16).
        $this->assertSame(42, SubCategory::where('status', 1)->count());
        $this->assertSame([15, 6, 5, 11, 4, 1], Category::where('status', 1)->ordered()->withCount('subCategories')->pluck('sub_categories_count')->all());
        $this->assertTrue(SubCategory::where('name', 'إنتاج بودكاست')->exists());
    }

    public function test_services_page_shows_groups_in_order_with_arabic_counts(): void
    {
        $this->get(route('services.index'))->assertOk()
            ->assertSee('اختار الخدمة اللي بتحتاجها')
            ->assertSeeInOrder(['التسويق والإعلانات', '15 خدمة', 'السوشيال ميديا والمحتوى', '6 خدمات', 'التصميم والمواقع', '5 خدمات', 'الإعلام والإنتاج', '11 خدمة', 'التحليل ودراسة السوق', 'تطوير الذات', 'خدمة واحدة'])
            ->assertSee('إنتاج بودكاست')
            ->assertSee('مش عارف شو الخدمة المناسبة؟');
    }

    public function test_hidden_services_and_empty_groups_are_not_shown_except_in_admin_preview(): void
    {
        $this->service('تصوير الريلز')->update(['status' => 0]);
        Category::where('name', 'تطوير الذات')->first()->subCategories()->update(['status' => 0]);

        $this->get(route('services.index'))->assertDontSee('تصوير الريلز')->assertSee('10 خدمات')->assertDontSee('خدمة واحدة');

        $this->actingAs($this->admin)->get(route('admin.content.preview', 'services'))->assertRedirect('/services');
        $this->actingAs($this->admin)->get(route('services.index'))->assertSee('تصوير الريلز')->assertSee('مخفي');
    }

    public function test_request_option_opens_the_service_form_or_the_admins_link(): void
    {
        $logo = $this->service('تصميم الشعارات');
        // No published form and no link: «ساعدوني أختار» instead (audit fix 2026-10-16).
        $this->assertNull($logo->requestUrl());
        \App\Models\ServiceForm::create(['sub_category_id' => $logo->id, 'status' => \App\Models\ServiceForm::STATUS_PUBLISHED]);
        $this->assertSame(route('requests.create', ['service' => $logo->id]), $logo->requestUrl());

        $logo->update(['link' => '/products', 'button_text' => 'شوف العروض']);
        $html = $this->get(route('services.index'))->getContent();
        $this->assertStringContainsString(json_encode(url('/products')), $html);
        $this->assertStringContainsString(json_encode('شوف العروض', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), $html);
    }

    public function test_a_chosen_service_offers_request_ready_services_and_freelancers_not_help(): void
    {
        $logo = $this->service('تصميم الشعارات');
        \App\Models\ServiceForm::create(['sub_category_id' => $logo->id, 'status' => \App\Models\ServiceForm::STATUS_PUBLISHED]);
        // Two listed freelancers in this service; a pending one and one without a photo are not counted.
        foreach ([true, true, false] as $listed) {
            $freelancer = User::factory()->freelancer()->create(['professional_photo' => $listed ? 'photo.jpg' : null]);
            $freelancer->specializations()->attach($logo->id);
        }
        User::factory()->pendingFreelancer()->create(['professional_photo' => 'p.jpg'])->specializations()->attach($logo->id);

        $html = $this->get(route('services.index'))->assertOk()->getContent();

        $this->assertStringContainsString('"freelancers":2', $html);
        $this->assertStringContainsString(json_encode(route('freelancers.index', ['service' => $logo->id])), $html);
        $this->assertStringContainsString('شوف المستقلين في الخدمة دي', $html);
        // «ساعدوني أختار» is no longer a second path for a chosen service: only a hidden fallback
        // (for a service that cannot be requested yet) and the page's general button remain.
        $this->assertStringNotContainsString('المسار الثاني', $html);
        $this->assertStringContainsString('data-help-fallback hidden', $html);
        $this->assertStringContainsString("modalEl.querySelector('[data-help-fallback]').hidden = !!service.url", $html);
        // The directory link really shows those freelancers.
        $this->get(route('freelancers.index', ['service' => $logo->id]))->assertOk();
    }

    public function test_help_requests_need_a_logged_in_user(): void
    {
        $logo = $this->service('تصميم الشعارات');

        $this->post(route('services.help.store'), ['whatsapp' => '+201000000000', 'message' => 'محتاج شعار'])->assertRedirect(route('user_login'));
        $this->get(route('services.help.login', ['service' => $logo->id]))->assertRedirect(route('user_login'));
        $this->assertSame(0, HelpRequest::count());

        $client = User::factory()->create();
        $this->actingAs($client)->get(route('services.help.login', ['service' => $logo->id]))
            ->assertRedirect(route('services.index', ['help' => 1, 'service' => $logo->id]));
        $this->actingAs($client)->get(route('services.index', ['help' => 1, 'service' => $logo->id]))->assertOk()->assertSee('setService(' . $logo->id . ')', false);
    }

    public function test_a_help_request_is_saved_numbered_confirmed_and_sent_to_the_team(): void
    {
        $client = User::factory()->create(['name' => 'سارة']);
        $logo = $this->service('تصميم الشعارات');

        $this->actingAs($client)->post(route('services.help.store'), ['whatsapp' => 'abc', 'message' => 'x'])
            ->assertSessionHasErrors(['whatsapp', 'message']);

        $this->actingAs($client)->post(route('services.help.store'), ['whatsapp' => '+20 100 000 0000', 'message' => 'أحتاج شعاراً لمشروعي', 'service' => $logo->id])
            ->assertRedirect(route('services.index'));
        $help = HelpRequest::sole();
        $this->assertSame(['HLP-1001', 'تصميم الشعارات', HelpRequest::STATUS_NEW, $client->id], [$help->number, $help->service_name, $help->status, $help->user_id]);
        Notification::assertSentTo($this->admin, HelpRequestNotification::class);
        $this->assertSame(0, ServiceRequest::count()); // never mixed with service requests

        // The confirmation and number show once, on the page the client lands on.
        $this->actingAs($client)->get(route('services.index'))->assertSee('وصل طلبك وحفظناه')->assertSee('HLP-1001');
        $this->actingAs($client)->get(route('services.index'))->assertDontSee('HLP-1001');

        // No service chosen: a general inquiry. A hidden service cannot be chosen.
        $this->actingAs($client)->post(route('services.help.store'), ['whatsapp' => '+201000000000', 'message' => 'مش عارف شو بحتاج']);
        $this->assertSame('استفسار عام', HelpRequest::latest('id')->first()->serviceLabel());
        $logo->update(['status' => 0]);
        $this->actingAs($client)->post(route('services.help.store'), ['whatsapp' => '+201000000000', 'message' => 'شعار لو سمحت', 'service' => $logo->id])->assertSessionHasErrors('service');
    }

    public function test_admin_help_box_filters_assigns_and_keeps_internal_notes(): void
    {
        $client = User::factory()->create(['name' => 'سارة']);
        $logo = $this->service('تصميم الشعارات');
        $a = HelpRequest::create(['user_id' => $client->id, 'sub_category_id' => $logo->id, 'service_name' => $logo->name, 'whatsapp' => '+201000000000', 'message' => 'شعار', 'number' => 'HLP-1001']);
        $b = HelpRequest::create(['user_id' => $client->id, 'whatsapp' => '+201000000001', 'message' => 'عام', 'number' => 'HLP-1002']);
        $colleague = User::factory()->admin()->create(['name' => 'محمود']);

        $this->actingAs(User::factory()->create())->get(route('admin.help-requests.index'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.help-requests.index'))->assertOk()->assertSee('HLP-1001')->assertSee('HLP-1002');
        $this->actingAs($this->admin)->get(route('admin.help-requests.index', ['service' => 'general']))->assertSee('HLP-1002')->assertDontSee('HLP-1001');
        $this->actingAs($this->admin)->get(route('admin.help-requests.index', ['q' => 'الشعارات']))->assertSee('HLP-1001')->assertDontSee('HLP-1002');

        $this->actingAs($this->admin)->get(route('admin.help-requests.show', $a))->assertOk()
            ->assertSee('https://wa.me/201000000000?text=', false)->assertSee('فتح واتساب');

        $this->actingAs($this->admin)->post(route('admin.help-requests.update', $a), ['status' => 'following', 'assigned_to' => $colleague->id])->assertSessionHas('success');
        Notification::assertSentTo($colleague, HelpRequestNotification::class);
        $this->actingAs($this->admin)->post(route('admin.help-requests.update', $a), ['status' => 'closed', 'assigned_to' => $client->id])->assertSessionHasErrors('assigned_to');
        $this->actingAs($this->admin)->post(route('admin.help-requests.notes', $a), ['body' => 'اتصلت بها واقترحت الهوية البصرية']);

        $this->assertSame(['following', $colleague->id], [$a->fresh()->status, $a->fresh()->assigned_to]);
        $this->actingAs($this->admin)->get(route('admin.help-requests.show', $a))->assertSee('اتصلت بها واقترحت الهوية البصرية')->assertSee('المسؤول: محمود');
        $this->actingAs($this->admin)->get(route('admin.help-requests.index', ['assignee' => $colleague->id]))->assertSee('HLP-1001')->assertDontSee('HLP-1002');
    }

    public function test_admin_adds_edits_hides_and_safely_deletes_services(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.catalog.services'))->assertForbidden();

        $this->actingAs($this->admin)->post(route('admin.catalog.services.store'), [
            'name' => 'تصميم العروض التقديمية', 'description' => 'عروض بوربوينت احترافية', 'new_group' => 'العروض والتقديم', 'icon' => 'bi-easel', 'status' => 1,
        ])->assertSessionHas('success');
        $service = SubCategory::where('name', 'تصميم العروض التقديمية')->sole();
        $this->assertSame('العروض والتقديم', $service->category->name);
        $this->get(route('services.index'))->assertSee('العروض والتقديم')->assertSee('تصميم العروض التقديمية')->assertSee('عروض بوربوينت احترافية');

        $this->actingAs($this->admin)->post(route('admin.catalog.services.update', $service), ['name' => 'تصميم العروض التقديمية', 'parent_id' => $service->parent_id, 'link' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('link');
        $this->actingAs($this->admin)->post(route('admin.catalog.services.update', $service), ['name' => 'تصميم العروض التقديمية', 'parent_id' => $service->parent_id, 'icon' => '"><b>'])
            ->assertSessionHasErrors('icon');
        $this->actingAs($this->admin)->post(route('admin.catalog.services.update', $service), ['name' => 'تصميم العروض التقديمية', 'parent_id' => $service->parent_id]); // unchecked = hidden
        auth()->logout();
        $this->get(route('services.index'))->assertDontSee('تصميم العروض التقديمية');

        // Used services are hidden, not deleted; unused ones can be deleted.
        $used = $this->service('تصميم الشعارات');
        User::factory()->freelancer()->create()->specializations()->attach($used->id);
        $this->actingAs($this->admin)->post(route('admin.catalog.services.destroy', $used))->assertSessionHasErrors();
        $this->assertNotNull($used->fresh());
        $this->actingAs($this->admin)->post(route('admin.catalog.services.destroy', $service))->assertSessionHas('success');
        $this->assertNull($service->fresh());

        $this->actingAs($this->admin)->post(route('admin.catalog.groups.destroy', $used->category))->assertSessionHasErrors();
        $this->actingAs($this->admin)->get(route('admin.catalog.services', ['group' => $used->parent_id]))->assertOk()->assertSee('تصميم الشعارات')->assertDontSee('إنتاج بودكاست');
    }

    public function test_admin_edits_groups_and_their_order(): void
    {
        $design = Category::where('name', 'التصميم والمواقع')->sole();
        $this->actingAs($this->admin)->post(route('admin.catalog.groups.update', $design), [
            'name' => 'التصميم والمواقع', 'description' => 'هويات ومواقع', 'icon' => 'bi-brush', 'sort_order' => 0, 'status' => 1,
        ])->assertSessionHas('success');

        $this->get(route('services.index'))->assertSeeInOrder(['التصميم والمواقع', 'هويات ومواقع', 'التسويق والإعلانات']);
        $this->actingAs($this->admin)->get(route('admin.catalog.groups'))->assertOk()->assertSee('هويات ومواقع');
    }

    public function test_page_texts_are_edited_from_the_content_admin(): void
    {
        $this->actingAs($this->admin)->get(route('admin.content.index', ['page' => 'services']))->assertOk()->assertSee('نصوص صفحة الخدمات')->assertSee('نافذة اختيار الخدمة');
        $block = ContentBlock::where('area', 'services.page')->sole();
        $fields = $block->data;
        $fields['hero_title'] = 'شو حابب تنجز اليوم؟';
        $fields['hero_background'] = '';

        $this->actingAs($this->admin)->post(route('admin.content.update', $block), $fields)->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'services'));
        auth()->logout();
        $this->get(route('services.index'))->assertSee('شو حابب تنجز اليوم؟');
    }
}
