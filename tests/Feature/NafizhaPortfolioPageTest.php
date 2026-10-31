<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\admin\Category;
use App\Models\admin\nafizhaPortfolio;
use Database\Seeders\ServicesCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** «أعمال فريق نفّذها» (/nafizha/portfolios): active works only, on the new design. */
class NafizhaPortfolioPageTest extends TestCase
{
    use RefreshDatabase;

    private function work(string $title, int $status = 1, ?int $category = null): nafizhaPortfolio
    {
        return nafizhaPortfolio::create([
            'user_id' => User::factory()->admin()->create()->id,
            'title' => $title, 'slug' => 'work', 'description' => 'وصف العمل', 'link' => null,
            'image' => 'a.jpg', 'more_images' => [], 'tools' => '', 'category_id' => $category, 'status' => $status,
        ]);
    }

    public function test_the_list_shows_active_works_with_search_and_groups(): void
    {
        $this->seed(ServicesCatalogSeeder::class);
        $groups = Category::orderBy('id')->take(2)->get();
        $this->work('متجر أزياء', 1, $groups[0]->id);
        $this->work('هوية مطعم', 1, $groups[1]->id);
        $this->work('عمل مؤرشف', 0, $groups[0]->id);

        $this->get('/nafizha/portfolios')->assertOk()
            ->assertSee('أعمال فريق نفّذها')->assertSee('rf-sl-head', false)
            ->assertSee('متجر أزياء')->assertSee('هوية مطعم')->assertDontSee('عمل مؤرشف')
            ->assertDontSee('wa.me/+963', false);

        $this->get('/nafizha/portfolios?q=' . urlencode('هوية'))->assertSee('هوية مطعم')->assertDontSee('متجر أزياء');
        $this->get('/nafizha/portfolios?group=' . $groups[0]->id)->assertSee('متجر أزياء')->assertDontSee('هوية مطعم');
        $this->get('/nafizha/portfolios?q[]=x')->assertOk();
    }

    public function test_an_archived_work_is_hidden_except_for_admins(): void
    {
        $this->seed(ServicesCatalogSeeder::class);
        $group = Category::value('id');
        $active = $this->work('متجر أزياء', 1, $group);
        $archived = $this->work('عمل مؤرشف', 0, $group);

        $this->get('/nafizha/portfolio/' . $active->id . '-work')->assertOk()
            ->assertSee('فريق نفّذها')->assertSee('اطلب عرض سعر')->assertSee('ساعدوني أختار');
        $this->get('/nafizha/portfolio/' . $archived->id . '-work')->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())
            ->get('/nafizha/portfolio/' . $archived->id . '-work')->assertOk()->assertSee('في الأرشيف');
    }
}
