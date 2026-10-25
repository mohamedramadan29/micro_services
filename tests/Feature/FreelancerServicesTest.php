<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\AdminMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** «خدماتي المعروضة»: a freelancer's ready-made services are reviewed before they show or can be bought. */
class FreelancerServicesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $group;
    private SubCategory $logo;
    private SubCategory $other;
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = User::factory()->admin()->create();
        $this->group = Category::create(['name' => 'التصميم', 'slug' => 'd', 'image' => 'x.png', 'status' => 1]);
        $this->logo = SubCategory::create(['name' => 'تصميم الشعارات', 'slug' => 'logo', 'image' => 'x.png', 'parent_id' => $this->group->id, 'status' => 1]);
        $otherGroup = Category::create(['name' => 'برمجة', 'slug' => 'p', 'image' => 'x.png', 'status' => 1]);
        $this->other = SubCategory::create(['name' => 'مواقع', 'slug' => 'web', 'image' => 'x.png', 'parent_id' => $otherGroup->id, 'status' => 1]);
    }

    protected function tearDown(): void
    {
        foreach (Service::pluck('image') as $image) {
            @unlink(public_path('assets/uploads/services/' . $image));
        }
        parent::tearDown();
    }

    private function form(array $override = []): array
    {
        return $override + ['name' => 'تصميم شعار احترافي مع 3 نماذج', 'cat_id' => $this->group->id, 'sub_cat_id' => $this->logo->id,
            'description' => 'ثلاثة نماذج مبدئية وتعديلان وملفات مصدرية بكل الصيغ', 'price' => 600, 'tags' => 'شعار، هوية , تصميم',
            'image' => UploadedFile::fake()->image('s.jpg', 1200, 800)];
    }

    public function test_a_new_service_waits_for_review_and_cannot_be_bought_until_published(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($freelancer)->get('service/add')->assertOk()->assertSee('بعد عمولة نفّذها');
        $this->actingAs($freelancer)->post('service/add', $this->form())->assertRedirect('service/index');

        $service = Service::firstOrFail();
        $this->assertSame([Service::STATUS_PENDING, 'شعار,هوية,تصميم'], [(int) $service->status, $service->tags]);
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->admin)['text'], 'تصميم شعار احترافي'));
        Cache::forget('admin-menu-badges');
        $this->assertSame(1, AdminMenu::badges()['services']);
        $this->actingAs($freelancer)->get('service/index')->assertSee('بانتظار المراجعة');

        // Hidden and not for sale while pending; the owner still opens it.
        $page = 'service/' . $service->id . '-' . rawurlencode($service->slug);
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->get($page)->assertNotFound();
        $this->actingAs($buyer)->post('create_order', ['service_id' => $service->id])->assertNotFound();
        $this->actingAs($buyer)->post('cart/add', ['service_id' => $service->id])->assertNotFound();
        $this->actingAs($freelancer)->get($page)->assertOk()->assertSee('تعديل الخدمة');

        // Published by the admin: public, buyable, the freelancer is told.
        $this->actingAs($this->admin)->get('admin/services?tab=pending')->assertOk()->assertSee('تصميم شعار احترافي')->assertSee('نشر الخدمة');
        $this->actingAs($this->admin)->post(route('admin.services.publish', $service->id))->assertSessionHas('Success_message');
        $this->assertTrue($service->fresh()->isActive());
        Notification::assertSentTo($freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($freelancer)['text'], 'تم نشر خدمتك'));
        $this->actingAs($buyer)->get($page)->assertOk()->assertSee('اطلب الخدمة الآن')->assertSee('600 ج.م')->assertDontSee($freelancer->email);
        $this->actingAs($buyer)->post('cart/add', ['service_id' => $service->id])->assertSessionHas('Success_message');
    }

    public function test_editing_returns_to_review_and_the_sub_service_must_match_the_group(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($freelancer)->post('service/add', $this->form(['sub_cat_id' => $this->other->id]))->assertSessionHasErrors('sub_cat_id');
        $this->actingAs($freelancer)->post('service/add', $this->form(['name' => 'قصير']))->assertSessionHasErrors('name');
        $this->actingAs($freelancer)->post('service/add', $this->form());
        $service = Service::firstOrFail();
        $service->forceFill(['status' => Service::STATUS_ACTIVE])->save();

        $this->actingAs($freelancer)->post('service/update/' . $service->id, $this->form(['image' => null, 'price' => 800]))->assertRedirect('service/index');
        $this->assertSame([Service::STATUS_PENDING, '800'], [(int) $service->fresh()->status, (string) $service->fresh()->price]);

        // Another freelancer cannot edit it; a client cannot add services; a rejection needs a reason.
        $this->actingAs(User::factory()->freelancer()->create())->get('service/update/' . $service->id)->assertNotFound();
        $this->actingAs(User::factory()->create())->get('service/add')->assertRedirect('dashboard');
        $this->actingAs($this->admin)->post(route('admin.services.reject', $service->id), ['note' => ''])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.services.reject', $service->id), ['note' => 'الصورة فيها رقم هاتف']);
        $this->actingAs($freelancer)->get('service/index')->assertSee('غير مقبولة')->assertSee('الصورة فيها رقم هاتف');
    }

    public function test_publishing_from_the_admin_edit_page_also_tells_the_freelancer(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($freelancer)->post('service/add', $this->form());
        $service = Service::firstOrFail();
        Notification::fake();

        $this->actingAs($this->admin)->post('admin/service/update/' . $service->id, ['name' => $service->name, 'cat_id' => $this->group->id,
            'sub_cat_id' => $this->logo->id, 'description' => $service->description, 'price' => 600, 'tags' => 'شعار', 'status' => 1]);
        $this->assertTrue($service->fresh()->isActive());
        Notification::assertSentTo($freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($freelancer)['text'], 'تم نشر خدمتك'));

        // Saving a rejected service from the edit page keeps it rejected (a rejection always has a reason).
        $service->forceFill(['status' => Service::STATUS_REJECTED, 'review_note' => 'سبب'])->save();
        $this->actingAs($this->admin)->post('admin/service/update/' . $service->id, ['name' => $service->name, 'cat_id' => $this->group->id,
            'sub_cat_id' => $this->logo->id, 'description' => $service->description, 'price' => 600, 'tags' => 'شعار', 'status' => 0]);
        $this->assertSame(Service::STATUS_REJECTED, (int) $service->fresh()->status);
    }

    public function test_published_services_are_reachable_from_the_services_page_the_ready_page_and_the_homepage(): void
    {
        $seller = User::factory()->freelancer()->create(['name' => 'منى البائعة']);
        $make = fn (string $name, int $status, int $price, ?SubCategory $sub = null) => Service::create(['name' => $name, 'slug' => 's' . $price, 'cat_id' => ($sub ?? $this->logo)->parent_id,
            'sub_cat_id' => ($sub ?? $this->logo)->id, 'user_id' => $seller->id, 'image' => 'x.jpg', 'description' => 'وصف الخدمة الجاهزة', 'tags' => 'شعار', 'price' => (string) $price, 'status' => $status]);
        $make('شعار سريع', Service::STATUS_ACTIVE, 300);
        $make('هوية كاملة', Service::STATUS_ACTIVE, 1500);
        $make('موقع تعريفي', Service::STATUS_ACTIVE, 4000, $this->other);
        $make('خدمة لم تُراجع', Service::STATUS_PENDING, 200);

        // «خدمات جاهزة»: published only, with group / sub-service / price filters and sorts.
        $this->get(route('services.ready'))->assertOk()->assertSee('شعار سريع')->assertSee('موقع تعريفي')->assertDontSee('خدمة لم تُراجع')->assertSee('3 خدمات');
        $this->get(route('services.ready', ['group' => $this->group->id]))->assertSee('هوية كاملة')->assertDontSee('موقع تعريفي');
        $this->get(route('services.ready', ['service' => $this->other->id]))->assertSee('موقع تعريفي')->assertDontSee('شعار سريع');
        $this->get(route('services.ready', ['max_price' => 1000]))->assertSee('شعار سريع')->assertDontSee('هوية كاملة');
        $this->get(route('services.ready', ['q' => 'هوية']))->assertSee('هوية كاملة')->assertDontSee('شعار سريع');
        $this->get(route('services.ready', ['sort' => 'price_desc']))->assertSeeInOrder(['موقع تعريفي', 'هوية كاملة', 'شعار سريع']);

        // The services page counts them per sub-service and links to them; so does the homepage.
        $this->get(route('services.index'))->assertOk()->assertSee('2 جاهزة')->assertSee('تصفح الخدمات الجاهزة (3)')
            ->assertSee('"ready":2', false)->assertSee(json_encode(url('services/logo')), false);
        $this->get('/')->assertOk()->assertSee('عرض كل الخدمات (3)')->assertSee('منى البائعة')->assertDontSee('خدمة لم تُراجع');
    }
}
