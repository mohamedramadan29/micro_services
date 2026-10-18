<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Fixes from the audit of the client files (2026-10-16): public profiles, services without a form, double submits. */
class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private Category $group;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_uploads');
        $this->group = Category::create(['name' => 'برمجة', 'slug' => 'dev', 'image' => 'x.png', 'status' => 1, 'sort_order' => 1]);
    }

    private function service(string $name, bool $withForm = false, ?string $link = null): SubCategory
    {
        $service = SubCategory::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name) ?: uniqid(), 'image' => '', 'parent_id' => $this->group->id, 'status' => 1, 'link' => $link]);
        if ($withForm) {
            $form = ServiceForm::create(['sub_category_id' => $service->id, 'status' => ServiceForm::STATUS_PUBLISHED]);
            $section = $form->sections()->create(['title' => 'تفاصيل', 'sort_order' => 1]);
            $form->questions()->create(['service_form_section_id' => $section->id, 'type' => Q::TYPE_SHORT_TEXT, 'label' => 'اسم المشروع', 'is_required' => false, 'sort_order' => 1]);
        }

        return $service;
    }

    public function test_the_public_profile_shows_the_owners_services_and_only_listed_freelancers(): void
    {
        $owner = User::factory()->freelancer()->create(['user_name' => 'mona', 'email' => 'mona@example.com']);
        $viewer = User::factory()->freelancer()->create();
        $sub = $this->service('مواقع');
        Service::create(['name' => 'خدمة منى', 'slug' => 'mona-s', 'cat_id' => $this->group->id, 'sub_cat_id' => $sub->id, 'user_id' => $owner->id, 'image' => 'x.png', 'description' => 'd', 'tags' => 't', 'price' => 100, 'status' => 1]);
        Service::create(['name' => 'خدمة الزائر', 'slug' => 'viewer-s', 'cat_id' => $this->group->id, 'sub_cat_id' => $sub->id, 'user_id' => $viewer->id, 'image' => 'x.png', 'description' => 'd', 'tags' => 't', 'price' => 100, 'status' => 1]);

        $this->actingAs($viewer)->get('user/mona')->assertOk()->assertSee('خدمة منى')->assertDontSee('خدمة الزائر')
            ->assertDontSee('mona@example.com'); // contact stays inside the platform
        $this->get('user/nobody')->assertNotFound();

        $pending = User::factory()->pendingFreelancer()->create(['user_name' => 'pending']);
        $client = User::factory()->create(['user_name' => 'client1']);
        auth()->logout();
        $this->get('user/pending')->assertNotFound();
        $this->get('user/client1')->assertNotFound();
        $this->get('user/pending/services')->assertNotFound();
        $this->actingAs($pending)->get('user/pending')->assertOk(); // the owner sees theirs
        $this->actingAs(User::factory()->admin()->create())->get('user/pending')->assertOk();
    }

    public function test_a_service_without_a_form_or_link_is_asked_for_through_help(): void
    {
        $withForm = $this->service('برمجة مواقع', true);
        $linked = $this->service('تصميم شعار', false, '/lp/logos');
        $bare = $this->service('تحسين سرعة المواقع');

        $this->assertSame(route('requests.create', ['service' => $withForm->id]), $withForm->requestUrl());
        $this->assertSame(url('/lp/logos'), $linked->requestUrl());
        $this->assertNull($bare->requestUrl());
        $this->get(route('services.index'))->assertOk()->assertSee('هذه الخدمة تُطلب حاليًا عبر «ساعدوني أختار»');

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.catalog.services'))->assertOk()->assertSee('لا نموذج طلب منشور ولا رابط');
        // An old link to the request page explains instead of showing an empty choice.
        $this->actingAs(User::factory()->create())->get(route('requests.create', ['service' => $bare->id]))->assertOk()
            ->assertSee('خدمة «تحسين سرعة المواقع» لا تُطلب من هذه الصفحة حاليًا');
    }

    public function test_a_double_submit_creates_one_request(): void
    {
        $service = $this->service('برمجة مواقع', true);
        $client = User::factory()->create();
        $payload = ['sub_category_id' => $service->id, 'action' => ServiceRequest::PATH_EXECUTE, 'problem' => 'أريد موقعًا لشركتي', 'contact_name' => 'سارة', 'contact_phone' => '01000000000', 'contact_email' => 's@example.com'];

        $this->actingAs($client)->post(route('requests.store'), $payload)->assertSessionHasNoErrors();
        $first = ServiceRequest::sole();
        $this->actingAs($client)->post(route('requests.store'), $payload)->assertRedirect(route('requests.show', $first))->assertSessionHas('Success_message', 'طلبك أُرسل بالفعل مرة واحدة.');
        $this->assertSame(1, ServiceRequest::count());

        $this->travel(1)->minutes();
        $this->actingAs($client)->post(route('requests.store'), $payload); // a real second request later is fine
        $this->assertSame(2, ServiceRequest::count());
    }
}
