<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Client file «تعديلات موقع نفذها», items 02 (navbar) and 09 (old gateway traces). */
class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_shows_freelancers_and_blog_instead_of_courses(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(route('freelancers.index'), $html);
        $this->assertStringContainsString(route('blogCategories'), $html);
        $this->assertStringNotContainsString('الكورسات المجانية', $html);
        $this->assertStringNotContainsString('href="' . url('courses') . '"', $html);

        $this->actingAs(User::factory()->create())->get('/')->assertOk()
            ->assertDontSee('href="' . url('my/courses') . '"', false);
    }

    public function test_blog_and_service_categories_no_longer_share_a_url(): void
    {
        $this->assertSame(url('blog'), route('blogCategories'));
        $this->get('blog')->assertOk()->assertViewIs('website.blog.index');
        $this->get('categories')->assertOk()->assertViewIs('website.categories');
        $this->get('freelancers')->assertOk()->assertSee('المستقلين');
    }

    public function test_an_unknown_service_section_is_not_found_instead_of_an_error(): void
    {
        $this->get('services/no-such-section')->assertNotFound();
    }

    public function test_homepage_lists_easykash_payment_methods_only(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('طرق الدفع المتاحة')->assertSee('فوري')->assertSee('المحافظ الإلكترونية')
            ->assertDontSee('PayPal')->assertDontSee('ApplePay')->assertDontSee('دولار');
    }
}
