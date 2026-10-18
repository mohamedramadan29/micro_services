<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Content\ContentAreas;
use App\Services\Content\ContentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Audit group 3, item 4: homepage hero/how-it-works, footer, about and FAQ from «إدارة محتوى الواجهة». */
class ManagedPagesContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_render_before_any_edit(): void
    {
        $this->get('/')->assertOk()->assertSee('قارن واختر العرض الأفضل')->assertSee('wa.me/963997610723', false)->assertSee('سياسة الخصوصية');
        $this->get('about')->assertOk()->assertSee('التركيز على الثقة والاحترافية');
        $this->get('faq')->assertOk()->assertSee('هل يوجد نظام دفع آمن في نفذها؟');
    }

    public function test_admin_edits_are_shown_after_publishing(): void
    {
        $admin = User::factory()->admin()->create();
        foreach (['hero', 'footer', 'about', 'faq'] as $page) {
            $this->actingAs($admin)->get(route('admin.content.index', ['page' => $page]))->assertOk();
        }
        $content = app(ContentManager::class);

        $faq = $content->blocks('faq.items')->first();
        $content->saveDraft($faq, ['question' => 'كيف يعمل مشروعك ما بيوقف؟', 'answer' => 'على مراحل مموّلة.']);
        $footer = $content->blocks('footer.main')->first();
        $content->saveDraft($footer, ['whatsapp' => '201011112222', 'copyright' => 'حقوق :year', 'show_tickets' => '0'] + $footer->working());
        $hero = $content->blocks('home.hero')->first();
        $content->saveDraft($hero, ['title' => 'عنوان جديد للواجهة', 'show_search' => '0', 'button_text' => '', 'button_link' => ''] + $hero->working());
        $content->publish(array_merge(ContentAreas::PAGES['faq']['areas'], ContentAreas::PAGES['footer']['areas'], ContentAreas::PAGES['hero']['areas']));
        auth()->logout();

        $this->get('faq')->assertSee('كيف يعمل مشروعك ما بيوقف؟')->assertDontSee('ما هو موقع نفذها؟');
        $this->get('/')->assertSee('عنوان جديد للواجهة')->assertSee('wa.me/201011112222', false)->assertSee('حقوق ' . date('Y'))
            ->assertDontSee('name="search"', false)->assertDontSee(url('tickets'));
    }
}
