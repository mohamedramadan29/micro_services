<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\SeoKeyword;
use App\Models\SeoSearchQuery;
use App\Models\User;
use App\Services\Seo\SearchConsoleClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Fakes\FakeSearchConsoleClient;
use Tests\TestCase;

/** Client file «تعديلات», item 05: keywords, CSV, and real Google data only once connected. */
class SeoKeywordsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private FakeSearchConsoleClient $gsc;
    private SubCategory $seoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gsc = new FakeSearchConsoleClient();
        $this->app->instance(SearchConsoleClient::class, $this->gsc);
        $this->admin = User::factory()->admin()->create();
        $group = Category::create(['name' => 'التسويق', 'slug' => 'm', 'status' => 1]);
        $this->seoService = SubCategory::create(['name' => 'تحسين محركات البحث (SEO)', 'slug' => 'seo', 'parent_id' => $group->id, 'status' => 1]);
    }

    public function test_only_admins_manage_keywords(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.seo.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('admin.seo.store'), ['keywords' => 'x'])->assertForbidden();
    }

    public function test_many_keywords_are_added_at_once_without_duplicates(): void
    {
        SeoKeyword::create(['keyword' => 'تصميم شعار', 'status' => 'unlinked']);
        $lines = "تصميم شعار\n  تصميم   مواقع  \n\nمتجر إلكتروني\n" . implode("\n", array_map(fn ($i) => "كلمة {$i}", range(1, 300)));

        $this->actingAs($this->admin)->post(route('admin.seo.store'), ['keywords' => $lines, 'sub_category_id' => $this->seoService->id])
            ->assertSessionHas('success', 'أُضيفت 302 كلمة، وتُركت 1 موجودة من قبل.');

        $this->assertSame(303, SeoKeyword::count());
        $added = SeoKeyword::where('keyword', 'تصميم مواقع')->sole();
        $this->assertSame(['linked', $this->seoService->id], [$added->status, $added->sub_category_id]);
        $this->assertSame('unlinked', SeoKeyword::where('keyword', 'تصميم شعار')->value('status'));

        $this->actingAs($this->admin)->get(route('admin.seo.index'))->assertOk()->assertSee('كلمة 300')->assertSee('page=2');
        $this->actingAs($this->admin)->post(route('admin.seo.store'), ['keywords' => 'x', 'page_url' => 'javascript:alert(1)'])->assertSessionHasErrors('page_url');
    }

    public function test_keywords_are_edited_filtered_and_bulk_deleted(): void
    {
        $a = SeoKeyword::create(['keyword' => 'برمجة مواقع', 'status' => 'unlinked']);
        $b = SeoKeyword::create(['keyword' => 'إعلانات فيسبوك', 'status' => 'review']);

        $this->actingAs($this->admin)->post(route('admin.seo.update', $a), ['keyword' => 'برمجة مواقع', 'page_url' => '/services', 'status' => 'linked', 'notes' => 'صفحة الخدمات'])
            ->assertSessionHas('success');
        $this->assertSame(['/services', 'linked', 'صفحة الخدمات'], [$a->fresh()->page_url, $a->fresh()->status, $a->fresh()->notes]);
        $this->actingAs($this->admin)->post(route('admin.seo.update', $a), ['keyword' => 'إعلانات فيسبوك', 'status' => 'linked'])->assertSessionHasErrors();

        $this->actingAs($this->admin)->get(route('admin.seo.index', ['status' => 'review']))->assertSee('إعلانات فيسبوك')->assertDontSee('برمجة مواقع</strong>', false);
        $this->actingAs($this->admin)->get(route('admin.seo.index', ['q' => 'برمجة']))->assertSee('برمجة مواقع')->assertDontSee('إعلانات فيسبوك</strong>', false);

        $this->actingAs($this->admin)->post(route('admin.seo.destroy'), ['ids' => [$a->id, $b->id]])->assertSessionHas('success', 'حُذفت 2 كلمة.');
        $this->assertSame(0, SeoKeyword::count());
    }

    public function test_csv_export_and_import_round_trip(): void
    {
        SeoKeyword::create(['keyword' => 'تصميم شعار', 'page_url' => '/services', 'sub_category_id' => $this->seoService->id, 'status' => 'linked', 'notes' => 'مهم']);

        $csv = $this->actingAs($this->admin)->get(route('admin.seo.export'))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"تصميم شعار",/services,"تحسين محركات البحث (SEO)",مربوطة,مهم', $csv);

        $file = UploadedFile::fake()->createWithContent('k.csv', "\xEF\xBB\xBFالكلمة المفتاحية,الصفحة المرتبطة,الخدمة,الحالة,ملاحظات\nتصميم شعار,/services,,قيد المراجعة,حدّثت\nتسويق رقمي,/blog,تحسين محركات البحث (SEO),,\nرابط سيء,javascript:x,,,\n,,,,\n");
        $this->actingAs($this->admin)->post(route('admin.seo.import'), ['file' => $file])
            ->assertSessionHas('success', 'الاستيراد: أُضيفت 1، وتحدّثت 1، وتُخطّيت 2 (فارغة أو رابط غير صحيح).');

        $this->assertSame(['review', 'حدّثت'], [SeoKeyword::where('keyword', 'تصميم شعار')->value('status'), SeoKeyword::where('keyword', 'تصميم شعار')->value('notes')]);
        $new = SeoKeyword::where('keyword', 'تسويق رقمي')->sole();
        $this->assertSame(['/blog', $this->seoService->id, 'linked'], [$new->page_url, $new->sub_category_id, $new->status]);

        // Without a header: one keyword per row.
        $this->actingAs($this->admin)->post(route('admin.seo.import'), ['file' => UploadedFile::fake()->createWithContent('plain.csv', "هوية بصرية\nمونتاج فيديو\n")]);
        $this->assertTrue(SeoKeyword::where('keyword', 'مونتاج فيديو')->exists());
    }

    public function test_without_search_console_nothing_is_shown_as_data(): void
    {
        $this->gsc->connected = false;
        SeoKeyword::create(['keyword' => 'تصميم شعار', 'status' => 'unlinked']);

        $this->actingAs($this->admin)->get(route('admin.seo.index'))->assertSee('بيانات Google غير مربوطة بعد')->assertSee('لا بيانات بعد');
        $this->actingAs($this->admin)->post(route('admin.seo.sync'))->assertSessionHasErrors();
        $this->artisan('seo:sync')->expectsOutputToContain('not connected')->assertSuccessful();
        $this->assertSame([], $this->gsc->calls);
    }

    public function test_search_console_figures_fill_keywords_and_the_queries_page(): void
    {
        $tracked = SeoKeyword::create(['keyword' => 'تصميم شعار', 'status' => 'linked']);
        $absent = SeoKeyword::create(['keyword' => 'كلمة لا يبحث عنها أحد', 'status' => 'unlinked']);
        $this->gsc->rows = [
            ['query' => 'تصميم  شعار', 'clicks' => 12, 'impressions' => 340, 'ctr' => 0.0353, 'position' => 8.4],
            ['query' => 'متجر إلكتروني', 'clicks' => 3, 'impressions' => 90, 'ctr' => 0.0333, 'position' => 15.2],
        ];

        $this->actingAs($this->admin)->post(route('admin.seo.sync'))->assertSessionHas('success', 'تم التحديث من Google Search Console: 2 عبارة بحث.');

        $this->assertSame([12, 340], [$tracked->fresh()->gsc_clicks, $tracked->fresh()->gsc_impressions]);
        $this->assertEquals(8.4, $tracked->fresh()->gsc_position);
        $this->assertSame([0, 0, null], [$absent->fresh()->gsc_clicks, $absent->fresh()->gsc_impressions, $absent->fresh()->gsc_position]);
        $this->assertSame(2, SeoSearchQuery::count());

        $this->actingAs($this->admin)->get(route('admin.seo.index', ['sort' => 'impressions']))
            ->assertSee('مربوط بـ Google Search Console')->assertSeeInOrder(['تصميم شعار', '340 ظهور', 'كلمة لا يبحث عنها أحد']);
        $this->actingAs($this->admin)->get(route('admin.seo.queries'))->assertOk()
            ->assertSeeInOrder(['تصميم  شعار', 'متابَعة', 'متجر إلكتروني', 'متابعة الكلمة']);

        $this->actingAs($this->admin)->post(route('admin.seo.track'), ['query' => 'متجر إلكتروني'])->assertSessionHas('success');
        $this->assertSame(90, SeoKeyword::where('keyword', 'متجر إلكتروني')->value('gsc_impressions'));

        $this->artisan('seo:sync')->expectsOutputToContain('Synced 2 queries.')->assertSuccessful();
    }
}
