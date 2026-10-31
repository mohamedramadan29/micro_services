<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\admin\Category;
use App\Models\admin\Service;
use App\Models\admin\SubCategory;
use App\Models\front\Cart;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Group B of the old-design pages (search, service groups, cart, checkout) on the new design (2026-10-31). */
class RedesignedBuyingPathTest extends TestCase
{
    use RefreshDatabase;

    private SubCategory $sub;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ServicesCatalogSeeder::class);
        $this->sub = SubCategory::where('name', 'تصميم الشعارات')->firstOrFail();
    }

    private function service(string $name, int $status = 1, ?User $owner = null): Service
    {
        return Service::create(['name' => $name, 'slug' => 's' . uniqid(), 'cat_id' => $this->sub->parent_id, 'sub_cat_id' => $this->sub->id,
            'user_id' => ($owner ?? User::factory()->freelancer()->create())->id, 'image' => 'x.png', 'description' => 'وصف', 'tags' => '', 'price' => 300, 'status' => $status]);
    }

    public function test_search_shows_catalog_services_and_only_published_ready_services(): void
    {
        $this->service('شعار احترافي سريع');
        $this->service('شعار بانتظار المراجعة', 0);

        $this->get('/search?search=' . urlencode('شعار'))->assertOk()
            ->assertSee('rf-sl-head', false)
            ->assertSee('تصميم الشعارات')            // catalog service to request
            ->assertSee('شعار احترافي سريع')
            ->assertDontSee('شعار بانتظار المراجعة');

        $this->get('/search?search=' . urlencode('لا يوجد شيء كهذا'))->assertOk()->assertSee('لا توجد خدمات جاهزة تطابق بحثك');
        $this->get('/search?search[]=x')->assertOk();
    }

    public function test_groups_page_and_a_group_page_list_active_services(): void
    {
        $group = Category::findOrFail($this->sub->parent_id);
        $this->service('شعار جاهز');

        $this->get('/categories')->assertOk()->assertSee($group->name)->assertSee('category/service/' . $group->slug, false);
        $this->get('/category/service/' . rawurlencode($group->slug))->assertOk()->assertSee('تصميم الشعارات')->assertSee('1 جاهزة');
        $this->get('/category/service/no-such-group')->assertNotFound();

        $group->update(['status' => 0]);
        $this->get('/category/service/' . rawurlencode($group->slug))->assertNotFound();
    }

    public function test_cart_counts_only_services_that_can_still_be_bought(): void
    {
        $client = User::factory()->create();
        $ok = $this->service('خدمة متاحة');
        $hidden = $this->service('خدمة أُخفيت');
        foreach ([$ok, $hidden] as $s) {
            Cart::create(['session_id' => 'x', 'user_id' => $client->id, 'service_id' => $s->id, 'service_name' => $s->name, 'price' => $s->price, 'user_serv' => $s->user_id, 'quantity' => 1]);
        }
        $hidden->update(['status' => 0]);

        $this->actingAs($client)->get('/cart')->assertOk()
            ->assertSee('خدمة متاحة')->assertSee('غير متاحة للشراء الآن')->assertSee('إتمام الشراء');
        $this->actingAs($client)->get('/checkout')->assertOk()
            ->assertSee('خدمة متاحة')->assertDontSee('خدمة أُخفيت')->assertSee('ادفع وأتمّ الطلب');
    }

    public function test_an_empty_cart_points_to_ready_services(): void
    {
        $this->get('/cart')->assertOk()->assertSee('سلتك فارغة')->assertSee(route('services.ready'), false);
    }
}
