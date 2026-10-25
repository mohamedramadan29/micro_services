<?php

namespace Tests\Feature;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\ImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Client file «تعديلات», items 03, 11 and 12: review, WhatsApp check, professional photo, directory. */
class FreelancerApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private SubCategory $design;
    private SubCategory $video;
    private array $photos = [];

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = User::factory()->admin()->create();
        $designGroup = Category::create(['name' => 'التصميم والمواقع', 'slug' => 'd', 'status' => 1, 'sort_order' => 1]);
        $mediaGroup = Category::create(['name' => 'الإعلام والإنتاج', 'slug' => 'm', 'status' => 1, 'sort_order' => 2]);
        $this->design = SubCategory::create(['name' => 'تصميم الشعارات', 'slug' => 'logo', 'parent_id' => $designGroup->id, 'status' => 1]);
        $this->video = SubCategory::create(['name' => 'إنتاج بودكاست', 'slug' => 'podcast', 'parent_id' => $mediaGroup->id, 'status' => 1]);
    }

    protected function tearDown(): void
    {
        foreach (User::whereNotNull('professional_photo')->pluck('professional_photo') as $base) {
            ImageVariants::delete('assets/uploads/freelancers', $base);
        }
        parent::tearDown();
    }

    /** An approved, checked freelancer with a photo, as the directory needs. */
    private function listed(array $attributes = [], ?SubCategory $service = null): User
    {
        $user = User::factory()->freelancer()->create($attributes + ['bio' => 'مصمم هويات بصرية بخبرة طويلة', 'availability' => 'available']);
        $user->forceFill(['professional_photo' => 'photo' . $user->id])->save();
        $user->specializations()->attach(($service ?? $this->design)->id);

        return $user;
    }

    public function test_a_pending_freelancer_browses_but_cannot_send_offers(): void
    {
        $client = User::factory()->create();
        $request = ServiceRequest::create([
            'user_id' => $client->id, 'sub_category_id' => $this->design->id, 'service_name' => 'تصميم الشعارات', 'path' => 'execute',
            'status' => ServiceRequest::STATUS_OPEN, 'problem' => 'شعار', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'c@example.com', 'published_at' => now(),
        ]);
        $request->update(['number' => 'REQ-' . (1000 + $request->id)]);
        $offer = ['price' => 500, 'duration_days' => 5, 'scope' => str_repeat('نطاق العمل ', 4), 'message' => str_repeat('رسالة للعميل ', 4)];

        $pending = User::factory()->pendingFreelancer()->create();
        $pending->specializations()->attach($this->design->id);
        $this->actingAs($pending)->get(route('freelancer.requests.index'))->assertOk()->assertSee('حسابك كمستقل قيد المراجعة');
        $this->actingAs($pending)->get(route('freelancer.requests.show', $request))->assertOk()->assertDontSee('نموذج عرض المستقل');
        $this->actingAs($pending)->post(route('freelancer.offers.save', $request), $offer)->assertSessionHasErrors();
        $this->assertSame(0, $request->offers()->count());

        $approved = $this->listed();
        $this->actingAs($approved)->post(route('freelancer.offers.save', $request), $offer)->assertSessionHasNoErrors();
        $this->assertSame(1, $request->offers()->count());
    }

    public function test_freelancer_profile_changes_reset_the_check_and_resubmit(): void
    {
        $user = User::factory()->freelancer()->create(['whatsapp' => '+201000000000']);
        $user->forceFill(['freelancer_status' => User::FREELANCER_REJECTED, 'freelancer_review_note' => 'أضف نماذج أعمال'])->save();

        $this->actingAs($user)->get('update-account')->assertOk()->assertSee('غير معتمد')->assertSee('أضف نماذج أعمال')->assertSee('حفظ وإعادة الإرسال للمراجعة');
        $account = ['name' => $user->name, 'email' => $user->email, 'phone' => '01055556666', 'account_type' => User::TYPE_FREELANCER];
        $this->actingAs($user)->post('update-account', $account + ['whatsapp' => 'abc', 'availability' => 'sleeping', 'starting_price' => -5])
            ->assertSessionHasErrors(['whatsapp', 'availability', 'starting_price']);
        $this->actingAs($user)->post('update-account', $account + [
            'whatsapp' => '+201111111111', 'info' => 'مصمم شعارات وهويات بخبرة سبع سنوات', 'availability' => 'busy', 'languages' => 'العربية، الإنجليزية', 'starting_price' => 750,
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame([User::FREELANCER_PENDING, null, '+201111111111', 'busy'], [$user->freelancer_status, $user->admin_verified_at, $user->whatsapp, $user->availability]);
        $this->assertEquals(750, $user->starting_price);
        Notification::assertSentTo($this->admin, RequestUpdateNotification::class);

        $this->assertSame('العربية، الإنجليزية', $user->languages);
        // A client's account page has no freelancer section.
        $this->actingAs(User::factory()->create())->get('update-account')->assertOk()->assertDontSee('ملفك كمستقل');
    }

    public function test_admin_verifies_the_account_adds_a_photo_then_approves(): void
    {
        $user = User::factory()->pendingFreelancer()->create(['name' => 'سارة المصممة']);

        $this->actingAs(User::factory()->create())->get(route('admin.freelancers.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.freelancers.index', ['status' => 'pending']))->assertOk()->assertSee('سارة المصممة')->assertSee('غير موثّق');
        $this->actingAs($this->admin)->get(route('admin.freelancers.show', $user))->assertOk()
            ->assertSee('https://wa.me/201000000000?text=', false)->assertSee('إرسال رابط تأكيد البريد')->assertSee('توثيق الحساب يدويًا');

        // Approval needs a verified account (email or admin) and the photo.
        $this->actingAs($this->admin)->post(route('admin.freelancers.decide', $user), ['decision' => 'approved'])->assertSessionHasErrors();
        $this->actingAs($this->admin)->post(route('admin.freelancers.verify', $user))->assertSessionHas('success');
        $this->assertNotNull($user->fresh()->admin_verified_at);
        $this->assertTrue($user->fresh()->isVerifiedFreelancer());
        // Verifying is step 1: the freelancer is told, the status stays pending and the admin sees what is left.
        $this->assertSame(User::FREELANCER_PENDING, $user->fresh()->freelancer_status);
        Notification::assertSentTo($user, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($user)['text'], 'وثّق فريق نفّذها حسابك'));
        $this->actingAs($this->admin)->get(route('admin.freelancers.show', $user))->assertSee('قبل الاعتماد ينقص')->assertSee('الصورة الاحترافية (خانة 2)');

        $this->actingAs($this->admin)->post(route('admin.freelancers.photo', $user), ['photo' => UploadedFile::fake()->image('small.jpg', 200, 200)])->assertSessionHasErrors('photo');
        $this->actingAs($this->admin)->post(route('admin.freelancers.photo', $user), ['photo' => UploadedFile::fake()->image('face.jpg', 900, 600)])->assertSessionHas('success');
        $base = $user->fresh()->professional_photo;
        foreach (ImageVariants::SIZES as $size) {
            $this->assertFileExists(public_path("assets/uploads/freelancers/{$base}-{$size}.jpg"));
        }
        $this->assertSame([512, 512], array_slice(getimagesize(public_path("assets/uploads/freelancers/{$base}-512.jpg")), 0, 2));
        $this->actingAs($this->admin)->get(route('admin.freelancers.show', $user))->assertSee('الخطوة التالية');

        $this->actingAs($this->admin)->post(route('admin.freelancers.decide', $user), ['decision' => 'approved'])->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue($user->isApprovedFreelancer());
        Notification::assertSentTo($user, RequestUpdateNotification::class, fn ($n) => str_contains($n->toArray($user)['text'], 'تم اعتماد حسابك'));

        $this->actingAs($this->admin)->post(route('admin.freelancers.feature', $user))->assertSessionHas('success');
        $this->assertTrue($user->fresh()->is_featured);
    }

    public function test_rejection_needs_a_reason_for_the_freelancer(): void
    {
        $user = User::factory()->pendingFreelancer()->create();

        $this->actingAs($this->admin)->post(route('admin.freelancers.decide', $user), ['decision' => 'rejected'])->assertSessionHasErrors('note');
        $this->actingAs($this->admin)->post(route('admin.freelancers.decide', $user), ['decision' => 'rejected', 'note' => 'أضف نماذج أعمال'])->assertSessionHas('success');

        $this->assertSame([User::FREELANCER_REJECTED, 'أضف نماذج أعمال'], [$user->fresh()->freelancer_status, $user->fresh()->freelancer_review_note]);
        $this->actingAs($this->admin)->post(route('admin.freelancers.decide', $this->admin), ['decision' => 'rejected', 'note' => 'x'])->assertNotFound();
    }

    public function test_photos_are_never_enlarged(): void
    {
        $base = ImageVariants::storeSquare(UploadedFile::fake()->image('mid.png', 450, 700), 'assets/uploads/freelancers');

        $this->assertSame([128, 256, 450], array_map(fn ($s) => getimagesize(public_path("assets/uploads/freelancers/{$base}-{$s}.jpg"))[0], ImageVariants::SIZES));
        $this->assertSame("assets/uploads/freelancers/{$base}-256.jpg", ImageVariants::url('assets/uploads/freelancers', $base, 200));
        ImageVariants::delete('assets/uploads/freelancers', $base);
    }

    public function test_directory_lists_only_approved_checked_freelancers_with_a_photo(): void
    {
        $this->listed(['name' => 'منى المعتمدة']);
        User::factory()->pendingFreelancer()->create(['name' => 'خالد قيد المراجعة'])->forceFill(['professional_photo' => 'p1'])->save();
        $unchecked = $this->listed(['name' => 'سامي بلا تحقق']);
        $unchecked->forceFill(['email_verified_at' => null, 'admin_verified_at' => null])->save();
        $this->listed(['name' => 'هالة موثقة يدويًا'])->forceFill(['email_verified_at' => null, 'admin_verified_at' => now()])->save();
        User::factory()->freelancer()->create(['name' => 'ليلى بلا صورة']);
        $this->listed(['name' => 'عميل باسم مستقل', 'account_type' => User::TYPE_CLIENT]);

        $this->get(route('freelancers.index'))->assertOk()
            ->assertSee('اكتشف المستقل المناسب لإنجاز مشروعك بثقة')->assertSee('منى المعتمدة')->assertSee('هالة موثقة يدويًا')
            ->assertDontSee('خالد قيد المراجعة')->assertDontSee('سامي بلا تحقق')->assertDontSee('ليلى بلا صورة')->assertDontSee('عميل باسم مستقل');
    }

    public function test_directory_search_filters_and_featured_row(): void
    {
        $mona = $this->listed(['name' => 'منى', 'rate' => 5, 'starting_price' => 500, 'languages' => 'العربية، الإنجليزية']);
        $mona->forceFill(['is_featured' => true])->save();
        $this->listed(['name' => 'كريم', 'rate' => 3, 'starting_price' => 3000, 'availability' => 'busy'], $this->video);

        $html = $this->get(route('freelancers.index'))->assertSee('مستقلون موثوقون بجودة عالية')->assertSee('يبدأ من <strong>500 ج.م</strong>', false)->getContent();
        $this->assertDoesNotMatchRegularExpression('/\d\s*\$|\$\s*\d|دولار/u', strip_tags($html)); // prices in EGP only
        $this->get(route('freelancers.index', ['q' => 'بودكاست']))->assertSee('كريم')->assertDontSee('>منى<', false);
        $this->get(route('freelancers.index', ['group' => $this->design->parent_id]))->assertSee('منى')->assertDontSee('كريم');
        $this->get(route('freelancers.index', ['service' => $this->video->id]))->assertSee('كريم')->assertDontSee('منى');
        $this->get(route('freelancers.index', ['rating' => 4]))->assertSee('منى')->assertDontSee('كريم');
        $this->get(route('freelancers.index', ['max_price' => 1000]))->assertSee('منى')->assertDontSee('كريم');
        $this->get(route('freelancers.index', ['availability' => 'busy']))->assertSee('كريم')->assertDontSee('منى');
        $this->get(route('freelancers.index', ['language' => 'الإنجليزية']))->assertSee('منى')->assertDontSee('كريم');
        $this->get(route('freelancers.index', ['sort' => 'price']))->assertSeeInOrder(['يبدأ من <strong>500 ج.م', 'يبدأ من <strong>3,000 ج.م'], false);
    }

    public function test_the_public_profile_shows_works_ratings_and_figures(): void
    {
        $mona = $this->listed(['name' => 'منى المصممة', 'user_name' => 'mona-d', 'starting_price' => 750, 'languages' => 'العربية، الإنجليزية', 'job_title' => 'مصممة هويات']);
        $make = function (string $title, int $status) use ($mona) {
            $work = new \App\Models\front\UserPortfolio();
            $work->forceFill(['user_id' => $mona->id, 'title' => $title, 'slug' => 's' . $status, 'description' => 'وصف', 'image' => 'w.jpg', 'tools' => '', 'category_id' => $this->design->parent_id, 'status' => $status])->save();
        };
        $make('هوية مطعم الشام', \App\Models\front\UserPortfolio::STATUS_PUBLISHED);
        $make('عمل لم يُراجع', \App\Models\front\UserPortfolio::STATUS_PENDING);

        // A completed staged project rated by its client.
        $client = User::factory()->create(['name' => 'أحمد سمير']);
        $contract = \App\Models\Contract::create(['client_id' => $client->id, 'freelancer_id' => $mona->id, 'title' => 'هوية', 'status' => \App\Models\Contract::STATUS_COMPLETED]);
        \App\Models\ContractRating::create(['contract_id' => $contract->id, 'rater_id' => $client->id, 'ratee_id' => $mona->id, 'direction' => \App\Models\ContractRating::CLIENT_TO_FREELANCER, 'stars' => 5, 'comment' => 'التزام بالمواعيد وتصميم رائع']);
        $mona->forceFill(['rate' => 5, 'rating_count' => 1])->save();

        $this->get('user/mona-d')->assertOk()
            ->assertSee('منى المصممة')->assertSee('مصممة هويات')->assertSee('معتمد')
            ->assertSee('هوية مطعم الشام')->assertDontSee('عمل لم يُراجع')
            ->assertSee('التزام بالمواعيد وتصميم رائع')->assertSee('أحمد س.')->assertDontSee('أحمد سمير')
            ->assertSee('تصميم الشعارات')->assertSee('750 ج.م')->assertSee('اطلب عرض سعر')
            ->assertDontSee($mona->email);
        $this->assertSame(1, User::whereKey($mona->id)->withFreelancerStats()->first()->completedProjects());

        // The directory card shows the same figures; the old services tab is a section of the profile now.
        $this->get(route('freelancers.index'))->assertSee('مشروع منجز')->assertSee('مصممة هويات');
        $this->get('user/mona-d/services')->assertRedirect(url('user/mona-d') . '#services');
    }
}
