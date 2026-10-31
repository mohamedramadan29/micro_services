<?php

namespace Tests\Feature;

use App\Models\admin\Blog;
use App\Models\admin\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Client file «تعديلات», item 04: blog organised by section, unified cards, private drafts. */
class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private BlogCategory $marketing;
    private BlogCategory $design;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['name' => 'فريق نفّذها']);
        $this->marketing = BlogCategory::create(['name' => 'التسويق الرقمي', 'slug' => 'marketing', 'image' => 'x.png', 'status' => 1, 'description' => 'مقالات عن الإعلانات']);
        $this->design = BlogCategory::create(['name' => 'التصميم والهوية', 'slug' => 'design', 'image' => 'x.png', 'status' => 1]);
    }

    private function blog(string $name, array $attributes = []): Blog
    {
        static $i = 0;
        $i++;

        return Blog::create($attributes + [
            'name' => $name, 'slug' => 'post-' . $i, 'category_id' => $this->marketing->id,
            'short_desc' => 'ملخص ' . $name, 'desc' => '<h2>مقدمة</h2><p>' . str_repeat('كلمة ', 450) . '</p>',
            'publish_date' => now()->subDays(10 - min($i, 9))->toDateString(),
            'meta_title' => $name, 'meta_desc' => 'وصف ' . $name, 'meta_keywords' => 'تسويق', 'status' => 1, 'author' => $this->admin->id,
        ]);
    }

    public function test_the_blog_lists_only_published_articles_in_unified_cards(): void
    {
        $this->blog('دليل إعلانات فيسبوك');
        $this->blog('مسودة لم تنشر', ['status' => 0]);
        $this->blog('مقال مجدول', ['publish_date' => now()->addDays(3)->toDateString()]);

        $this->get(route('blogCategories'))->assertOk()
            ->assertSee('مدونة نفّذها')->assertSee('أفكار وخبرات تساعدك على تطوير مشروعك')
            ->assertSee('دليل إعلانات فيسبوك')->assertSee('ملخص دليل إعلانات فيسبوك')->assertSee('3 د قراءة')->assertSee('اقرأ المقال')
            ->assertDontSee('مسودة لم تنشر')->assertDontSee('مقال مجدول')
            // Only sections that have published articles get a chip.
            ->assertSee('التسويق الرقمي')->assertDontSee('التصميم والهوية');
    }

    public function test_sections_search_and_pagination(): void
    {
        foreach (range(1, 13) as $n) {
            $this->blog('مقال تسويق ' . $n);
        }
        $this->blog('أساسيات الهوية البصرية', ['category_id' => $this->design->id]);

        $this->get(route('blogCategories', ['category' => 'design']))->assertOk()
            ->assertSee('أساسيات الهوية البصرية')->assertDontSee('مقال تسويق 1<', false)->assertSee('1 مقال');
        $this->get(route('blogCategories', ['q' => 'الهوية']))->assertSee('أساسيات الهوية البصرية')->assertDontSee('مقال تسويق 5');
        $this->get(route('blogCategories', ['category' => 'marketing']))->assertSee('مقالات عن الإعلانات')->assertSee('13 مقال')->assertSee('page=2');
        $this->get(route('blogCategories', ['category' => 'marketing', 'page' => 2]))->assertOk();
    }

    public function test_old_section_links_redirect_to_the_filtered_blog(): void
    {
        $this->get(route('blogCategoryDetails', 'marketing'))->assertRedirect(route('blogCategories', ['category' => 'marketing']))->assertStatus(301);
        $this->get(route('blogCategoryDetails', 'nothing'))->assertNotFound();
    }

    public function test_article_page_has_meta_author_and_related_articles(): void
    {
        $post = $this->blog('دليل إعلانات فيسبوك');
        $this->blog('إعلانات إنستغرام للمبتدئين');
        $this->blog('ألوان الهوية', ['category_id' => $this->design->id]);

        $this->get(route('blogDetails', $post->slug))->assertOk()
            ->assertSee('<meta name="description"', false)->assertSee('content="وصف دليل إعلانات فيسبوك"', false)
            ->assertSee('<h2>مقدمة</h2>', false)->assertSee('فريق نفّذها')->assertSee('3 د قراءة')
            ->assertSeeInOrder(['مقالات مرتبطة', 'إعلانات إنستغرام للمبتدئين', 'ألوان الهوية']);
    }

    public function test_drafts_and_scheduled_articles_are_private_except_for_admins(): void
    {
        $draft = $this->blog('مسودة', ['status' => 0]);
        $scheduled = $this->blog('مجدول', ['publish_date' => now()->addDay()->toDateString()]);

        $this->get(route('blogDetails', $draft->slug))->assertNotFound();
        $this->get(route('blogDetails', $scheduled->slug))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('blogDetails', $draft->slug))->assertNotFound();

        $this->actingAs($this->admin)->get(route('blogDetails', $scheduled->slug))->assertOk()->assertSee('معاينة للأدمن');
    }

    public function test_admins_can_edit_an_old_article_without_changing_its_date(): void
    {
        $post = $this->blog('مقال قديم', ['publish_date' => '2025-01-15']);

        $this->actingAs($this->admin)->post('admin/blog/update/' . $post->id, [
            'name' => 'مقال قديم محدّث', 'desc' => '<p>نص</p>', 'publish_date' => '2025-01-15', 'status' => 1,
            'category_id' => $this->marketing->id, 'meta_title' => 'x', 'meta_url_final' => null, 'meta_description' => 'x', 'meta_keywords' => 'x',
        ])->assertSessionHasNoErrors();

        $this->assertSame(['مقال قديم محدّث', '2025-01-15'], [$post->fresh()->name, (string) $post->fresh()->publish_date]);
    }

    public function test_admins_add_a_section_and_an_article_from_the_admin_pages(): void
    {
        // Check 2026-10-31: the admin creates the blog from zero (sections, then articles) and it shows on /blog.
        $image = fn () => \Illuminate\Http\UploadedFile::fake()->image('cover.jpg', 800, 450);
        $seo = ['meta_title' => 'عنوان', 'meta_url_final' => null, 'meta_description' => 'وصف', 'meta_keywords' => 'تسويق'];

        $this->actingAs($this->admin)->get('admin/blog_category/add')->assertOk();
        $this->actingAs($this->admin)->post('admin/blog_category/add', ['name' => 'ريادة الأعمال', 'description' => 'نصائح للمشاريع', 'status' => 1, 'image' => $image()] + $seo)
            ->assertSessionHasNoErrors();
        $section = BlogCategory::where('name', 'ريادة الأعمال')->firstOrFail();

        $this->actingAs($this->admin)->get('admin/blog/add')->assertOk()->assertSee('ريادة الأعمال');
        $this->actingAs($this->admin)->post('admin/blog/add', ['name' => 'كيف تبدأ مشروعك', 'category_id' => $section->id, 'desc' => '<p>خطوات البداية</p>',
            'publish_date' => now()->toDateString(), 'status' => 1, 'image' => $image()] + $seo)->assertSessionHasNoErrors();
        $article = Blog::where('name', 'كيف تبدأ مشروعك')->firstOrFail();

        $this->get(route('blogCategories'))->assertOk()->assertSee('كيف تبدأ مشروعك')->assertSee('ريادة الأعمال');
        $this->get(route('blogDetails', $article->slug))->assertOk()->assertSee('خطوات البداية', false);

        // Edited and deleted from the admin too.
        $this->actingAs($this->admin)->post('admin/blog/update/' . $article->id, ['name' => 'كيف تبدأ مشروعك بخطوات واضحة', 'category_id' => $section->id,
            'desc' => '<p>خطوات</p>', 'publish_date' => now()->toDateString(), 'status' => 1] + $seo)->assertSessionHasNoErrors();
        $this->assertSame('كيف تبدأ مشروعك بخطوات واضحة', $article->fresh()->name);
        $this->actingAs($this->admin)->post('admin/blog/delete/' . $article->id);
        $this->assertNull(Blog::find($article->id));

        foreach ([public_path('assets/uploads/Blogs/' . $article->image), public_path('assets/uploads/BlogCategory/' . $section->image)] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
