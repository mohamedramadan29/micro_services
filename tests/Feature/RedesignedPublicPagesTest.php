<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\admin\Package;
use App\Models\admin\Product;
use App\Models\front\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Group A of the old-design pages moved to «الرسومات التنفيذية» (2026-10-31). */
class RedesignedPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $overrides = []): Product
    {
        return Product::create($overrides + [
            'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name) ?: uniqid('p'), 'image' => 'a.jpg',
            'short_description' => 'وصف قصير', 'description' => '<p>وصف</p>', 'price' => 500, 'discount' => 0,
            'status' => 1, 'type' => 'digital', 'category_id' => 0, 'brand_id' => 0,
        ]);
    }

    public function test_products_list_only_active_ones_with_search_and_the_old_search_key(): void
    {
        $this->product('قالب متجر', ['slug' => 'store']);
        $this->product('كتيب الهوية', ['slug' => 'book', 'discount' => 300]);
        $this->product('منتج مخفي', ['slug' => 'hidden', 'status' => 0]);

        $this->get('/products')->assertOk()->assertSee('rf-sl-head', false)
            ->assertSee('قالب متجر')->assertSee('كتيب الهوية')->assertDontSee('منتج مخفي');
        $this->get('/products?q=' . urlencode('كتيب'))->assertSee('كتيب الهوية')->assertDontSee('قالب متجر');
        $this->get('/products?search=' . urlencode('قالب'))->assertSee('قالب متجر')->assertDontSee('كتيب الهوية');
        $this->get('/products?sort=price')->assertOk();
    }

    public function test_product_page_is_404_when_missing_or_hidden_and_shows_shipping_fields_only_for_physical(): void
    {
        $this->get('/product/nothing')->assertNotFound();
        $this->product('منتج مخفي', ['slug' => 'hidden', 'status' => 0]);
        $this->get('/product/hidden')->assertNotFound();

        $this->product('قالب متجر', ['slug' => 'store']);
        $this->product('كتيب مطبوع', ['slug' => 'book', 'type' => 'physical']);
        $this->get('/product/store')->assertOk()->assertSee('سجّل دخولك لشراء المنتج')->assertDontSee('تضمن حقوقك');

        $client = User::factory()->create();
        $this->actingAs($client)->get('/product/store')->assertSee('name="email"', false)->assertDontSee('name="country"', false);
        $this->actingAs($client)->get('/product/book')->assertSee('name="country"', false)->assertSee('name="address"', false);
    }

    public function test_packages_page_uses_the_footer_whatsapp_and_no_confetti(): void
    {
        Package::create(['name' => 'الباقة الأساسية', 'slug' => 'basic', 'description' => 'ميزة أولى, ميزة ثانية', 'price' => 2500, 'status' => 1, 'title' => 'السوشيال ميديا']);

        $this->get(route('packages.index'))->assertOk()
            ->assertSee('الباقة الأساسية')->assertSee('ميزة ثانية')->assertSee('rf-pk-card', false)
            ->assertDontSee('confetti', false)->assertDontSee('wa.me/+963', false);
    }

    public function test_testimonials_show_approved_reviews_and_filter_by_rating(): void
    {
        Review::create(['client_name' => 'أحمد', 'rating' => 5, 'content' => 'رائع جدًا', 'is_approved' => true, 'is_active' => true]);
        Review::create(['client_name' => 'منى', 'rating' => 4, 'content' => 'جيد جدًا', 'is_approved' => true, 'is_active' => true]);
        Review::create(['client_name' => 'مخفي', 'rating' => 5, 'content' => 'لم يراجع', 'is_approved' => false, 'is_active' => true]);

        $this->get(route('testimonials.index'))->assertOk()->assertSee('رائع جدًا')->assertSee('جيد جدًا')->assertDontSee('لم يراجع');
        $this->get(route('testimonials.index', ['filter' => '4']))->assertSee('جيد جدًا')->assertDontSee('رائع جدًا');
        $this->get('/testimonials?filter[]=x')->assertOk();
    }

    public function test_a_review_needs_a_rating_between_one_and_five(): void
    {
        $this->from(route('reviews.index'))->post(route('front.reviews.post'), ['name' => 'سارة', 'rating' => 9, 'notes' => 'ممتاز'])
            ->assertSessionHasErrors('rating');
        $this->post(route('front.reviews.post'), ['name' => 'سارة', 'rating' => 5, 'notes' => 'ممتاز']);
        $this->assertDatabaseHas('reviews', ['client_name' => 'سارة', 'rating' => 5, 'is_approved' => false]);
    }

    public function test_about_faq_terms_and_privacy_use_the_new_frame(): void
    {
        foreach (['/about', '/faq', '/terms', '/privacy-policy'] as $url) {
            $this->get($url)->assertOk()->assertSee('rf-sl-head', false)->assertDontSee('hero-banner', false);
        }
        $this->get('/terms')->assertDontSee('**', false);
    }
}
