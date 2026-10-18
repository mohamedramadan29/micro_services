<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\ContentBlock;
use App\Models\ContentMedia;
use App\Models\User;
use App\Services\Content\ContentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/** Client file «تعديلات», items 01, 02 and 06: admin-managed site content with draft / preview / publish. */
class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        foreach (ContentMedia::all() as $media) {
            @unlink(public_path($media->path));
        }
        parent::tearDown();
    }

    private function cards()
    {
        $this->actingAs($this->admin)->get(route('admin.content.index'))->assertOk();

        return ContentBlock::where('area', 'home.cards')->orderBy('id')->get();
    }

    /** Titles of the cards visitors see on the homepage, in order. */
    private function cardTitles(): array
    {
        $html = $this->get('/')->assertOk()->getContent();
        $start = strpos($html, 'class="rf-home-cards ');
        $section = substr($html, $start, strpos($html, 'rf-home-paths-note') - $start);
        preg_match_all('#<strong>(.*?)</strong>#u', $section, $m);

        return $m[1];
    }

    private function cardInput(array $overrides = []): array
    {
        return $overrides + ['title' => 'عنوان', 'description' => 'وصف', 'button_text' => 'زر', 'action' => 'request', 'group' => 'primary', 'audience' => 'all', 'icon' => 'bi-star', 'image' => '', 'background' => '', 'background_color' => ''];
    }

    public function test_homepage_shows_the_five_cards_under_the_hero_before_any_edit(): void
    {
        // Client file «المربعات الخمسة»: the three paths, then a second group of two.
        $html = $this->get('/')->assertOk()
            ->assertSeeInOrder(['ابدأ من هنا', 'اختَر كيف نساعد مشروعك', 'اطلب عرضًا لمشروعك', 'اطلب عرضًا', 'شخّص مشروعك مجانًا', 'ابدأ التشخيص', 'مشروعك ما بيوقف', 'ابدأ مشروعك بمراحل',
                'أو ابدأ بالطريقة التي تناسبك', 'انشر مشروعك', 'انضم كمستقل', 'أنشئ ملفك كمستقل', 'كل بطاقة تفتح مسارًا واضحًا'])
            ->getContent();

        // Every card links through the click counter to its approved destination (card 3 = the staged-project page).
        $targets = ContentBlock::where('area', 'home.cards')->orderBy('id')->get()
            ->map(fn ($card) => [$card, $this->get(route('home.card', $card->id))->headers->get('Location')]);
        foreach ($targets as [$card]) {
            $this->assertStringContainsString('href="' . route('home.card', $card->id) . '"', $html);
        }
        $this->assertSame([url('/request'), url('/projects/diagnose'), url('/staged-project'), url('/projects/new'), url('/freelancers/join')], $targets->pluck(1)->all());
        $this->assertSame(0, substr_count($html, 'rf-home-soon'));
        $this->assertLessThan(strpos($html, 'طرق الدفع المتاحة'), strpos($html, 'اختَر كيف نساعد مشروعك'));
    }

    public function test_only_admins_manage_content(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.content.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.content.index'))->assertRedirect();
    }

    public function test_edits_stay_draft_until_published_and_the_admin_can_preview_them(): void
    {
        $card = $this->cards()[1];

        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['title' => 'افحص موقعك الآن', 'action' => 'site_test']))
            ->assertSessionHas('success');

        auth()->logout();
        $this->get('/')->assertSee('شخّص مشروعك مجانًا')->assertDontSee('افحص موقعك الآن');

        $this->actingAs($this->admin)->get(route('admin.content.preview', 'home'))->assertRedirect('/');
        $this->actingAs($this->admin)->get('/')->assertSee('افحص موقعك الآن')->assertSee('وضع المعاينة');
        // The preview flag is useless to anyone who is not an admin.
        $this->actingAs(User::factory()->create())->withSession([ContentManager::PREVIEW_SESSION => true])->get('/')->assertDontSee('افحص موقعك الآن');

        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'))->assertSessionHas('success');
        auth()->logout();
        $this->flushSession();
        $html = $this->get('/')->assertSee('افحص موقعك الآن')->getContent();
        // Published cards go through the click counter, then to their approved destination.
        $this->assertStringContainsString('href="' . route('home.card', $card->id) . '"', $html);
        $this->get(route('home.card', $card->id))->assertRedirect(url('/site-test'));
    }

    public function test_links_and_icons_are_validated(): void
    {
        $card = $this->cards()[0];

        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['action' => 'javascript:alert(1)']))->assertSessionHasErrors('action');
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['icon' => '"><script>']))->assertSessionHasErrors('icon');
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['title' => '']))->assertSessionHasErrors('title');
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['image' => 999]))->assertSessionHasErrors('image');
        $this->assertNull($card->fresh()->draft);
    }

    public function test_cards_can_be_added_hidden_reordered_and_deleted_through_publishing(): void
    {
        [$first, $second, $third] = $this->cards()->all();

        $this->actingAs($this->admin)->post(route('admin.content.store', 'home.cards'))->assertSessionHas('success');
        $new = ContentBlock::where('area', 'home.cards')->latest('id')->first();
        $this->actingAs($this->admin)->post(route('admin.content.update', $new), $this->cardInput(['title' => 'بطاقة رابعة']));
        $this->actingAs($this->admin)->post(route('admin.content.move', [$third, 'up']));
        $this->actingAs($this->admin)->post(route('admin.content.toggle', $first));
        $this->actingAs($this->admin)->post(route('admin.content.destroy', $second));

        auth()->logout();
        $this->assertSame(['اطلب عرضًا لمشروعك', 'شخّص مشروعك مجانًا', 'مشروعك ما بيوقف', 'انشر مشروعك', 'انضم كمستقل'], $this->cardTitles());

        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'));
        auth()->logout();
        $this->assertSame(['مشروعك ما بيوقف', 'بطاقة رابعة', 'انشر مشروعك', 'انضم كمستقل'], $this->cardTitles());
        $this->assertNull($second->fresh());

        // An item that was never published disappears at once when deleted.
        $this->actingAs($this->admin)->post(route('admin.content.store', 'home.cards'));
        $draft = ContentBlock::where('area', 'home.cards')->latest('id')->first();
        $this->actingAs($this->admin)->post(route('admin.content.destroy', $draft));
        $this->assertNull($draft->fresh());
    }

    public function test_the_number_of_cards_is_capped(): void
    {
        $this->cards();
        foreach (range(1, 3) as $i) {
            $this->actingAs($this->admin)->post(route('admin.content.store', 'home.cards'))->assertSessionHas('success');
        }
        $this->actingAs($this->admin)->post(route('admin.content.store', 'home.cards'))->assertSessionHasErrors();
        $this->actingAs($this->admin)->post(route('admin.content.store', 'home.paths'))->assertSessionHasErrors();
    }

    public function test_discarding_returns_to_the_published_content(): void
    {
        $card = $this->cards()[0];
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['title' => 'تعديل مؤقت']));
        $this->actingAs($this->admin)->post(route('admin.content.store', 'home.cards'));

        $this->actingAs($this->admin)->post(route('admin.content.discard', 'home'))->assertSessionHas('success');

        $this->assertNull($card->fresh()->draft);
        $this->assertSame(5, ContentBlock::where('area', 'home.cards')->count());
    }

    public function test_navbar_labels_links_and_audiences_are_managed(): void
    {
        $this->actingAs($this->admin)->get(route('admin.content.index', ['page' => 'navbar']))->assertOk()->assertSee('روابط القائمة الرئيسية');
        $blog = ContentBlock::where('area', 'navbar.links')->get()->first(fn ($b) => $b->data['link'] === '/blog');

        $this->actingAs($this->admin)->post(route('admin.content.update', $blog), ['label' => 'مقالات نفّذها', 'link' => '/blog', 'audience' => 'all'])->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'navbar'));

        auth()->logout();
        $this->get('/')->assertSee('مقالات نفّذها')->assertSee('اطلب عرضًا')->assertDontSee('طلبات العملاء');
        $this->actingAs(User::factory()->freelancer()->create())->get('/')->assertSee('طلبات العملاء')->assertDontSee('>  اطلب عرضًا <', false);
    }

    public function test_media_library_upload_alt_and_protected_delete(): void
    {
        $this->actingAs($this->admin)->post(route('admin.media.store'), ['file' => UploadedFile::fake()->create('x.svg', 5, 'image/svg+xml'), 'kind' => 'image'])->assertSessionHasErrors('file');

        $this->actingAs($this->admin)->post(route('admin.media.store'), ['file' => UploadedFile::fake()->image('logo.png', 200, 120), 'alt' => 'شعار', 'kind' => 'image'])->assertSessionHas('success');
        $media = ContentMedia::sole();
        $this->assertFileExists(public_path($media->path));
        $this->assertSame([200, 120], [$media->width, $media->height]);

        $card = $this->cards()[0];
        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput(['image' => $media->id]))->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'));
        auth()->logout();
        $this->get('/')->assertSee($media->url())->assertSee('alt="شعار"', false);

        $this->actingAs($this->admin)->post(route('admin.media.destroy', $media))->assertSessionHasErrors();
        $this->assertNotNull($media->fresh());

        $this->actingAs($this->admin)->post(route('admin.content.update', $card), $this->cardInput());
        $this->actingAs($this->admin)->post(route('admin.content.publish', 'home'));
        $this->actingAs($this->admin)->post(route('admin.media.destroy', $media))->assertSessionHas('success');
        $this->assertFileDoesNotExist(public_path($media->path));
    }

    public function test_brand_colour_comes_from_the_settings(): void
    {
        Setting::create(['website_commission' => '10', 'website_color' => '#123456']);
        $this->get('/')->assertSee('--main-color: #123456', false);

        Setting::first()->update(['website_color' => 'red;}</style><script>']);
        $this->get('/')->assertDontSee('<script>red', false)->assertDontSee('--main-color: red', false);
    }
}
