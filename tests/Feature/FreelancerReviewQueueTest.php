<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\front\UserPortfolio;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\AdminMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Freelancers' works are published after the team's review; ready freelancers reach the admin's queue. */
class FreelancerReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $group;
    private SubCategory $skill;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = User::factory()->admin()->create();
        $this->group = Category::create(['name' => 'التصميم والمواقع', 'slug' => 'd', 'image' => 'x.png', 'status' => 1]);
        $this->skill = SubCategory::create(['name' => 'تصميم الشعارات', 'slug' => 'logo', 'image' => 'x.png', 'parent_id' => $this->group->id, 'status' => 1]);
    }

    private function work(array $override = []): array
    {
        return $override + ['title' => 'هوية بصرية لمطعم', 'description' => 'شعار وألوان وقائمة طعام', 'image' => 'cover.jpg',
            'category' => $this->group->id, 'skills' => [$this->skill->id]];
    }

    public function test_a_new_work_waits_for_the_team_then_shows_on_the_site(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $freelancer->forceFill(['professional_photo' => 'p1'])->save(); // listed freelancer (the works page shows only those)
        $this->actingAs($freelancer)->post(route('user.portfolio.store'), $this->work())->assertRedirect(route('user.portfolio.index'));
        $work = UserPortfolio::firstOrFail();
        $page = "portfolio/{$work->id}-" . rawurlencode($work->slug);
        $this->assertSame(UserPortfolio::STATUS_PENDING, (int) $work->status);
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->admin)['text'], 'هوية بصرية لمطعم'));
        Cache::forget('admin-menu-badges');
        $this->assertSame(1, AdminMenu::badges()['works']);

        // Not public yet: hidden from the list and the page (the owner still opens it).
        auth()->logout();
        $this->get('portfolios')->assertOk()->assertDontSee('هوية بصرية لمطعم');
        $this->get($page)->assertNotFound();
        $this->actingAs($freelancer)->get($page)->assertOk();
        $this->actingAs($freelancer)->get(route('user.portfolio.index'))->assertSee('بانتظار المراجعة');

        // The admin sees it in the queue and publishes it; the freelancer is told.
        $this->actingAs($this->admin)->get(route('admin.freelancer-works.index'))->assertOk()->assertSee('هوية بصرية لمطعم')->assertSee('نشر العمل');
        $this->actingAs($this->admin)->post(route('admin.freelancer-works.publish', $work))->assertSessionHas('success');
        $this->assertTrue($work->fresh()->isPublished());
        Notification::assertSentTo($freelancer, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($freelancer)['text'], 'تم نشر عملك'));
        auth()->logout();
        $this->get('portfolios')->assertSee('هوية بصرية لمطعم');
        $this->get($page)->assertOk();
        $this->assertSame(0, AdminMenu::badges()['works']);
    }

    public function test_an_edited_work_goes_back_to_review_and_a_rejection_needs_a_reason(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $this->actingAs($freelancer)->post(route('user.portfolio.store'), $this->work());
        $work = UserPortfolio::firstOrFail();
        $work->forceFill(['status' => UserPortfolio::STATUS_PUBLISHED])->save();
        Notification::fake();

        $this->actingAs($freelancer)->post(route('user.portfolio.update', [$work->id, $work->slug]), $this->work(['title' => 'هوية بصرية لمقهى']))
            ->assertRedirect(route('user.portfolio.index'));
        $work->refresh();
        $this->assertSame(UserPortfolio::STATUS_PENDING, (int) $work->status);
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class);

        // A second edit while it still waits does not alert the team again.
        Notification::fake();
        $this->actingAs($freelancer)->post(route('user.portfolio.update', [$work->id, $work->slug]), $this->work(['title' => 'هوية بصرية لمقهى صغير']));
        Notification::assertNothingSent();

        $work->refresh();
        $this->actingAs($this->admin)->post(route('admin.freelancer-works.reject', $work), ['note' => ''])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.freelancer-works.reject', $work), ['note' => 'الصور غير واضحة'])->assertSessionHas('success');
        $this->assertSame([UserPortfolio::STATUS_REJECTED, 'الصور غير واضحة'], [(int) $work->fresh()->status, $work->fresh()->review_note]);
        $this->actingAs($freelancer)->get(route('user.portfolio.index'))->assertSee('غير مقبول')->assertSee('الصور غير واضحة');

        // The analyst role cannot decide.
        $analyst = User::factory()->admin()->create();
        $analyst->forceFill(['admin_role' => 'analyst'])->save();
        $this->actingAs($analyst)->post(route('admin.freelancer-works.publish', $work))->assertForbidden();
    }

    public function test_a_freelancer_reaches_the_review_queue_once_his_profile_is_ready(): void
    {
        $freelancer = User::factory()->pendingFreelancer()->create(['bio' => 'مصمم هويات بخبرة سبع سنوات']);
        $freelancer->forceFill(['email_verified_at' => now()])->save();
        $freelancer->specializations()->attach($this->skill->id);

        // Without a photo he is still filling in his profile: not counted, no alert.
        $this->assertFalse($freelancer->fresh()->isReadyForReview());
        $this->assertFalse($freelancer->fresh()->noteReadyForReview());
        $this->assertSame(0, User::readyForReview()->count());

        $freelancer->forceFill(['professional_photo' => 'abc'])->save();
        $this->assertTrue($freelancer->fresh()->isReadyForReview());
        $this->assertSame(1, User::readyForReview()->count());
        $this->assertTrue($freelancer->fresh()->noteReadyForReview());
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($this->admin)['text'], 'جاهز للمراجعة'));
        $this->assertFalse($freelancer->fresh()->noteReadyForReview()); // once

        Cache::forget('admin-menu-badges');
        $this->assertSame(1, AdminMenu::badges()['freelancers']);
        $this->actingAs($this->admin)->get(route('admin.freelancers.index', ['status' => 'ready']))->assertOk()->assertSee($freelancer->name)->assertSee('جاهز للمراجعة (1)');

        // Approved = no longer waiting.
        $freelancer->forceFill(['freelancer_status' => User::FREELANCER_APPROVED])->save();
        $this->assertSame(0, User::readyForReview()->count());
    }

    public function test_the_works_page_lists_published_works_of_listed_freelancers_with_filters(): void
    {
        $listed = User::factory()->freelancer()->create(['name' => 'منى المصممة']);
        $listed->forceFill(['professional_photo' => 'p1'])->save();
        $pending = User::factory()->pendingFreelancer()->create();
        $other = Category::create(['name' => 'برمجة', 'slug' => 'p', 'image' => 'x.png', 'status' => 1]);
        $make = function (User $owner, string $title, int $status, ?Category $group = null) {
            $work = new UserPortfolio();
            $work->forceFill(['user_id' => $owner->id, 'title' => $title, 'slug' => 's', 'description' => 'وصف', 'image' => 'w.jpg', 'tools' => '', 'category_id' => ($group ?? $this->group)->id, 'status' => $status])->save();
        };
        $make($listed, 'هوية مطعم', UserPortfolio::STATUS_PUBLISHED);
        $make($listed, 'متجر ملابس', UserPortfolio::STATUS_PUBLISHED, $other);
        $make($listed, 'عمل ينتظر', UserPortfolio::STATUS_PENDING);
        $make($pending, 'عمل مستقل غير معتمد', UserPortfolio::STATUS_PUBLISHED);

        $this->get(route('portfolios.index'))->assertOk()->assertSee('هوية مطعم')->assertSee('متجر ملابس')->assertSee('منى المصممة')
            ->assertDontSee('عمل ينتظر')->assertDontSee('عمل مستقل غير معتمد')->assertSee('عملان');
        $this->get(route('portfolios.index', ['group' => $other->id]))->assertSee('متجر ملابس')->assertDontSee('هوية مطعم');
        $this->get(route('portfolios.index', ['q' => 'مطعم']))->assertSee('هوية مطعم')->assertDontSee('متجر ملابس');
    }
}
