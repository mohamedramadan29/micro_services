<?php

namespace Tests\Feature;

use App\Models\ContentMedia;
use App\Models\LandingPage;
use App\Models\User;
use App\Services\Landing\LandingBuilder;
use Database\Seeders\LandingPagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Landing pages (client file «تعديلات», item 07): everything from the admin, draft → preview → publish. */
class LandingPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function create(string $template = 'starter'): LandingPage
    {
        $this->actingAs($this->admin)->post(route('admin.landing.store'), ['title' => 'حملة رمضان', 'slug' => 'Ramadan Offer', 'template' => $template])->assertRedirect();

        return LandingPage::sole();
    }

    private function section(LandingPage $page, string $type): array
    {
        return collect($page->fresh()->working())->firstWhere('type', $type);
    }

    public function test_only_admins_manage_landing_pages(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.landing.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('admin.landing.store'), ['title' => 'x', 'slug' => 'x', 'template' => 'blank'])->assertForbidden();
        $this->assertSame(0, LandingPage::count());
    }

    public function test_a_new_page_starts_from_the_reference_design_and_stays_hidden_until_published(): void
    {
        $page = $this->create();
        $this->assertSame('ramadan-offer', $page->slug);
        $this->assertSame(['hero', 'cards', 'steps', 'cta'], array_column($page->working(), 'type'));

        // Unpublished: nobody sees it, the admin only through ?preview=1.
        $this->get('/lp/ramadan-offer')->assertNotFound();
        auth()->logout();
        $this->get('/lp/ramadan-offer?preview=1')->assertNotFound();

        $this->actingAs($this->admin)->get('/lp/ramadan-offer?preview=1')->assertOk()->assertSee('معاينة صفحة الهبوط')->assertSee('اطلب عرضاً لمشروعك');
        $this->actingAs($this->admin)->get(route('admin.landing.edit', $page))->assertOk()->assertSee('بطاقات المسارات');

        $this->post(route('admin.landing.publish', $page))->assertRedirect();
        auth()->logout();
        $this->get('/lp/ramadan-offer')->assertOk()
            ->assertSee('مشروعك له طريق واضح من الفكرة للتنفيذ.')->assertSee('أربع خطوات تفصلك عن بداية التنفيذ')
            ->assertSee('href="' . url('/staged-project') . '"', false) // card 3 = service 3
            ->assertSee('id="steps"', false)->assertSee('href="#steps"', false)
            ->assertDontSee('معاينة صفحة الهبوط');
    }

    public function test_edits_are_drafts_until_published_and_can_be_discarded(): void
    {
        $page = $this->create();
        $this->post(route('admin.landing.publish', $page));
        $hero = $this->section($page, 'hero');

        $this->post(route('admin.landing.sections.update', [$page, $hero['id']]), ['fields' => [
            'title' => 'عنوان جديد للحملة', 'subtitle' => 'وصف', 'button_text' => 'اطلب', 'button_link' => '/request',
            'bg_color' => '#102030', 'text_color' => '#ffffff', 'accent_color' => '#ff6600',
        ]])->assertRedirect();

        auth()->logout();
        $this->get($page->url())->assertSee('مشروعك له طريق واضح')->assertDontSee('عنوان جديد للحملة');
        $this->actingAs($this->admin)->get($page->url() . '?preview=1')->assertSee('عنوان جديد للحملة')
            ->assertSee('background-color: #102030', false)->assertSee('--lp-accent: #ff6600', false);

        $this->post(route('admin.landing.discard', $page));
        $this->assertNull($page->fresh()->draft);
        $this->assertSame('مشروعك له طريق واضح من الفكرة للتنفيذ.', $this->section($page, 'hero')['fields']['title']);

        $this->post(route('admin.landing.sections.update', [$page, $hero['id']]), ['fields' => ['title' => 'عنوان جديد للحملة']]);
        $this->post(route('admin.landing.publish', $page));
        auth()->logout();
        $this->get($page->url())->assertSee('عنوان جديد للحملة');
    }

    public function test_sections_are_added_ordered_hidden_duplicated_and_deleted(): void
    {
        $page = $this->create('blank');
        $this->assertSame([], $page->working());

        foreach (['faq', 'hero', 'stats'] as $type) {
            $this->post(route('admin.landing.sections.store', $page), ['type' => $type])->assertRedirect();
        }
        $this->post(route('admin.landing.sections.store', $page), ['type' => 'nonsense'])->assertSessionHasErrors('type');
        $ids = array_column($page->fresh()->working(), 'id');

        $this->post(route('admin.landing.sections.action', [$page, $ids[1], 'up']));
        $this->assertSame(['hero', 'faq', 'stats'], array_column($page->fresh()->working(), 'type'));

        $this->post(route('admin.landing.sections.action', [$page, $ids[2], 'duplicate']));
        $this->post(route('admin.landing.sections.action', [$page, $ids[0], 'delete']));
        $this->assertSame(['hero', 'stats', 'stats'], array_column($page->fresh()->working(), 'type'));

        $this->post(route('admin.landing.sections.action', [$page, $ids[1], 'toggle']));
        $this->post(route('admin.landing.publish', $page));
        auth()->logout();
        $this->get($page->url())->assertOk()->assertDontSee('rf-lp-type-hero')->assertSee('rf-lp-type-stats');

        $this->actingAs($this->admin)->post(route('admin.landing.sections.action', [$page, 'missing', 'up']))->assertSessionHasErrors();
    }

    public function test_items_keep_the_submitted_order_and_are_validated(): void
    {
        $page = $this->create();
        $steps = $this->section($page, 'steps');
        $url = route('admin.landing.sections.update', [$page, $steps['id']]);

        // Rows moved in the browser arrive in their new order, whatever their keys.
        $this->post($url, ['fields' => ['title' => 'كيف نعمل؟', 'anchor' => 'how'], 'items' => [
            'n1001' => ['title' => 'ثانيًا', 'description' => ''],
            '0' => ['title' => 'أولًا', 'icon' => 'bi-1-circle'],
        ]])->assertSessionHasNoErrors();
        $saved = $this->section($page, 'steps');
        $this->assertSame(['ثانيًا', 'أولًا'], array_column($saved['items'], 'title'));
        $this->assertSame('how', $saved['fields']['anchor']);

        $this->post($url, ['fields' => ['anchor' => 'two words'], 'items' => [['title' => '']]])
            ->assertSessionHasErrors(['fields.anchor', 'items.0.title']);
        $this->post($url, ['items' => array_fill(0, 9, ['title' => 'x'])])->assertSessionHasErrors('items');
        $this->post($url, ['items' => [['title' => 'x', 'icon' => 'fa fa-x']]])->assertSessionHasErrors('items.0.icon');

        $cards = $this->section($page, 'cards');
        $this->post(route('admin.landing.sections.update', [$page, $cards['id']]), ['items' => [['title' => 'x', 'link' => 'javascript:alert(1)']]])
            ->assertSessionHasErrors('items.0.link');
        $this->post(route('admin.landing.sections.update', [$page, $cards['id']]), ['items' => [['title' => 'x', 'image' => 999]]])
            ->assertSessionHasErrors('items.0.image');
        $this->assertSame('ثانيًا', $this->section($page, 'steps')['items'][0]['title']);
    }

    public function test_page_settings_seo_and_the_simple_header(): void
    {
        $page = $this->create();
        $image = ContentMedia::create(['path' => 'assets/uploads/content/og.jpg', 'kind' => 'image']);
        LandingPage::create(['title' => 'أخرى', 'slug' => 'other']);

        $this->post(route('admin.landing.settings', $page), ['title' => 'حملة', 'slug' => 'other'])->assertSessionHasErrors('slug');
        $this->post(route('admin.landing.settings', $page), [
            'title' => 'حملة الصيف', 'slug' => 'summer', 'meta_description' => 'وصف للبحث',
            'og_image_id' => $image->id, 'header_button_text' => 'اطلب الآن', 'header_button_link' => '/request',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.landing.publish', $page->fresh()));

        auth()->logout();
        $this->get('/lp/summer')->assertOk()
            ->assertSee('<title>', false)->assertSee('حملة الصيف')
            ->assertSee('content="وصف للبحث"', false)->assertSee('og.jpg')
            ->assertSee('rf-lp-topbar')->assertSee('اطلب الآن')
            ->assertDontSee('id="navigation"', false);
        $this->get('/lp/ramadan-offer')->assertNotFound();

        $this->actingAs($this->admin)->post(route('admin.landing.unpublish', $page));
        auth()->logout();
        $this->get('/lp/summer')->assertNotFound();
    }

    public function test_hidden_text_is_escaped_and_the_seeder_publishes_the_starter_page_once(): void
    {
        $page = $this->create('blank');
        $this->post(route('admin.landing.sections.store', $page), ['type' => 'text_image']);
        $id = $page->fresh()->working()[0]['id'];
        $this->post(route('admin.landing.sections.update', [$page, $id]), ['fields' => ['title' => '<script>x</script>', 'body' => "سطر\n<b>ثاني</b>", 'image_side' => 'start']]);
        $this->get($page->url() . '?preview=1')->assertDontSee('<script>x</script>', false)->assertSee('&lt;b&gt;ثاني&lt;/b&gt;', false);

        $this->seed(LandingPagesSeeder::class);
        $this->seed(LandingPagesSeeder::class);
        $start = LandingPage::where('slug', 'start')->sole();
        $this->assertTrue($start->is_published);
        auth()->logout();
        $this->get('/lp/start')->assertOk()->assertSee('اطلب عرضاً لمشروعك');

        $this->actingAs($this->admin)->post(route('admin.landing.destroy', $start))->assertRedirect(route('admin.landing.index'));
        $this->get('/lp/start')->assertNotFound();
    }
}
