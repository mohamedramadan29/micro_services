<?php

namespace Tests\Feature;

use App\Livewire\Chat\Main;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\admin\Service;
use App\Models\admin\Ticket;
use App\Models\front\Conversation;
use App\Models\front\WithDraw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Review of the client and freelancer account pages (2026-10-18). */
class AccountAreaReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\admin\Setting::create(['website_commission' => '10', 'website_balance' => 0]);
    }

    private function service(User $owner): Service
    {
        return Service::create(['name' => 'تصميم', 'slug' => 'design', 'cat_id' => 1, 'user_id' => $owner->id, 'image' => 'x.png',
            'description' => 'وصف طويل للخدمة', 'tags' => '', 'price' => '100', 'status' => 1]);
    }

    public function test_the_account_home_and_menu_fit_the_account_type(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client)->get('dashboard')->assertOk()
            ->assertSee('اطلب خدمة')->assertSee('آخر طلباتي')->assertSee('طلباتي ومشاريعي')
            ->assertDontSee('عروضي')->assertDontSee('8%')->assertDontSee('زوم')->assertDontSee('كبائعين');

        $freelancer = User::factory()->pendingFreelancer()->create();
        $this->actingAs($freelancer)->get('dashboard')->assertOk()
            ->assertSee('جهّز حسابك لتقديم العروض')->assertSee('عملي كمستقل')->assertDontSee('ملفي كمستقل')
            ->assertDontSee('آخر طلباتي');

        // Old sections are never linked (their pages still open by URL); a client's old services show only when he has some.
        \App\Models\front\Jobs::forceCreate(['user_id' => $client->id, 'title' => 'وظيفة', 'category_id' => \App\Models\admin\Category::forceCreate(['name' => 'قسم', 'slug' => 'c', 'image' => 'x.png'])->id, 'description' => 'x', 'sex' => 'الكل', 'city' => 'القاهرة', 'country' => 'مصر', 'address' => 'x', 'type' => 'دوام كامل']);
        $this->actingAs($client)->get('dashboard')->assertDontSee('وظائفي')->assertDontSee('صيانة العقارات')->assertDontSee('العقارات')->assertDontSee('خدماتي المعروضة');
        $this->actingAs($client)->get('my/jobs')->assertOk();
        $this->service($client);
        $this->actingAs($client)->get('dashboard')->assertSee('خدماتي المعروضة');
    }

    public function test_others_items_cannot_be_deleted_and_deletes_are_post_only(): void
    {
        $owner = User::factory()->freelancer()->create();
        $other = User::factory()->freelancer()->create();
        $service = $this->service($owner);

        $this->actingAs($other)->post('service/delete/' . $service->id);
        $this->assertNotNull($service->fresh());
        $this->actingAs($owner)->get('service/delete/' . $service->id)->assertStatus(405);
        $this->actingAs($owner)->post('service/delete/' . $service->id);
        $this->assertNull($service->fresh());

        // Clients do not add services.
        $this->actingAs(User::factory()->create())->get('service/add')->assertRedirect(url('dashboard'));
    }

    public function test_tickets_and_conversations_are_private(): void
    {
        $this->get('chats')->assertRedirect(); // guests are sent to log in
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = Ticket::create(['user_id' => $owner->id, 'title' => 'مشكلة', 'content' => 'تفاصيل', 'status' => 1]);
        $this->actingAs($stranger)->get('ticket/' . $ticket->id)->assertNotFound();
        $this->actingAs($owner)->get('ticket/' . $ticket->id)->assertOk();

        $seller = User::factory()->freelancer()->create();
        $conversation = Conversation::create(['sender_id' => $owner->id, 'receiver_id' => $seller->id, 'service_id' => 0]);
        $this->actingAs($stranger);
        Livewire::test(Main::class, ['conversation_id' => $conversation->id])->assertForbidden();
    }

    public function test_a_conversation_about_a_service_is_always_with_its_owner(): void
    {
        $seller = User::factory()->freelancer()->create();
        $victim = User::factory()->create();
        $buyer = User::factory()->create();
        $service = $this->service($seller);

        $this->actingAs($buyer)->post('conversation/start', ['service_id' => $service->id, 'receiver_id' => $victim->id])->assertRedirect();
        $conversation = Conversation::sole();
        $this->assertSame($seller->id, (int) $conversation->receiver_id);
    }

    public function test_withdraw_to_a_wallet_and_balance_history(): void
    {
        $freelancer = User::factory()->freelancer()->create(['balance' => 500]);
        WalletTransaction::create(['user_id' => $freelancer->id, 'type' => WalletTransaction::TYPE_RELEASE, 'amount' => 450, 'description' => 'مستحقات المرحلة الأولى']);

        $this->actingAs($freelancer)->post('withdraw_balance', ['amount' => 200, 'method' => 'wallet', 'account_name' => 'أحمد', 'account_number' => '01012345678'])
            ->assertSessionHasNoErrors();
        $this->assertSame('wallet', WithDraw::sole()->method);
        $this->assertEquals(300, $freelancer->fresh()->balance);

        $this->actingAs($freelancer)->get('balance')->assertOk()
            ->assertSee('مستحقات عمل')->assertSee('مستحقات المرحلة الأولى')->assertSee('بانتظار التحويل')
            ->assertSee('محفظة إلكترونية')->assertDontSee('تضمن حقوقك');

        // A bank transfer still needs the bank name.
        WithDraw::query()->update(['status' => 1]);
        $this->actingAs($freelancer)->post('withdraw_balance', ['amount' => 10, 'method' => 'bank', 'account_name' => 'أحمد', 'account_number' => '123'])
            ->assertSessionHasErrors('bank_name');
    }

    public function test_the_account_type_cannot_change_with_open_work(): void
    {
        $client = User::factory()->create();
        ServiceRequest::create(['user_id' => $client->id, 'service_name' => 'متجر', 'path' => 'execute', 'status' => ServiceRequest::STATUS_OPEN,
            'problem' => 'x', 'contact_name' => 'x', 'contact_phone' => '0100', 'contact_email' => 'x@x.x']);

        $this->actingAs($client)->post('update-account', ['name' => 'x', 'email' => $client->email, 'phone' => '0100', 'account_type' => User::TYPE_FREELANCER, 'whatsapp' => '+201012345678'])
            ->assertSessionHasErrors('account_type');
        $this->assertSame(User::TYPE_CLIENT, $client->fresh()->account_type);

        // The edit page shows today's words, not «مشتري / بائع».
        $this->actingAs($client)->get('update-account')->assertOk()->assertDontSee('(مشتري)')->assertDontSee('(بائع)');
    }

    public function test_digital_product_files_are_private_and_only_paying_buyers_download_them(): void
    {
        $name = 'test-' . uniqid() . '.pdf';
        $legacy = \App\Support\DigitalFiles::legacyDir() . '/' . $name;
        \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($legacy));
        file_put_contents($legacy, '%PDF-test');

        try {
            $product = \App\Models\admin\Product::forceCreate(['name' => 'كتاب التسويق', 'slug' => 'book', 'image' => 'x.png', 'price' => 100, 'type' => 'digital', 'digital_file' => $name]);
            $buyer = User::factory()->create();
            $order = fn (string $status) => \App\Models\front\ProductOrder::forceCreate(['user_id' => $buyer->id, 'product_id' => $product->id, 'product_name' => $product->name,
                'price' => '100', 'name' => 'x', 'email' => 'x@x.x', 'country' => 'مصر', 'city' => 'القاهرة', 'address' => 'x', 'order_status' => $status]);

            $unpaid = $order('بانتظار الدفع');
            $this->actingAs($buyer)->get(route('download.product', $unpaid->id))->assertNotFound();
            $this->actingAs(User::factory()->create())->get(route('download.product', $order('تم الدفع')->id))->assertNotFound(); // not his order

            $paid = \App\Models\front\ProductOrder::where('order_status', 'تم الدفع')->first();
            $paid->update(['user_id' => $buyer->id]);
            $response = $this->actingAs($buyer)->get(route('download.product', $paid->id))->assertOk();
            $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));

            // The old public copy moved into the private folder.
            $this->assertFileDoesNotExist($legacy);
            $this->assertFileExists(\App\Support\DigitalFiles::dir() . '/' . $name);
            $this->assertStringStartsWith(public_path('uploads/private'), \App\Support\DigitalFiles::dir());

            // Admins download it through the panel, never by a public link.
            $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.digital-file', $product->id))->assertOk();
        } finally {
            \App\Support\DigitalFiles::delete($name);
        }
    }

    public function test_editing_the_profile_never_asks_for_a_new_password_and_a_change_needs_the_current_one(): void
    {
        $freelancer = User::factory()->freelancer()->create(['password' => \Illuminate\Support\Facades\Hash::make('current-secret-1'), 'whatsapp' => '+201012345678']);
        $base = ['name' => 'اسم جديد', 'email' => $freelancer->email, 'phone' => '01011112222', 'account_type' => User::TYPE_FREELANCER];

        // The browser autofilled the current password; only the name changed.
        $this->actingAs($freelancer)->post('update-account', $base + ['old_password' => 'current-secret-1', 'new_password' => '', 'confirm_password' => ''])
            ->assertSessionHasNoErrors();
        $this->assertSame('اسم جديد', $freelancer->fresh()->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('current-secret-1', $freelancer->fresh()->password));

        // A new password without (or with a wrong) current password is refused.
        $this->actingAs($freelancer)->post('update-account', $base + ['new_password' => 'hacked-pass-9', 'confirm_password' => 'hacked-pass-9'])
            ->assertSessionHasErrors('old_password');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('current-secret-1', $freelancer->fresh()->password));

        // With the current password it changes.
        $this->actingAs($freelancer)->post('update-account', $base + ['old_password' => 'current-secret-1', 'new_password' => 'brand-new-pass', 'confirm_password' => 'brand-new-pass'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('brand-new-pass', $freelancer->fresh()->password));
    }

    public function test_a_freelancer_adds_his_own_photo_and_has_one_bio(): void
    {
        $freelancer = User::factory()->pendingFreelancer()->create(['whatsapp' => '+201012345678', 'info' => null, 'bio' => null]);

        // The bio written on «تعديل الحساب» is the bio of the freelancer profile and counts in the checklist.
        $this->actingAs($freelancer)->post('update-account', ['name' => 'مستقل', 'email' => $freelancer->email, 'phone' => '01011112222',
            'account_type' => User::TYPE_FREELANCER, 'info' => 'مصمم مواقع بخبرة خمس سنوات في المتاجر'])->assertSessionHasNoErrors();
        $this->assertSame('مصمم مواقع بخبرة خمس سنوات في المتاجر', $freelancer->fresh()->bio);
        $this->assertTrue($freelancer->fresh()->freelancerChecklist()['profile']);
        $this->actingAs($freelancer->fresh())->get('update-account')->assertOk()
            ->assertSee('مصمم مواقع بخبرة خمس سنوات في المتاجر')->assertSee('ملفك كمستقل');
        // The old page is gone: its link (old notifications) lands on «تعديل الحساب».
        $this->actingAs($freelancer)->get('my/freelancer-profile')->assertRedirect(url('update-account') . '#rf-freelancer');

        // A too small photo is refused; a clear one becomes the professional photo.
        $this->actingAs($freelancer)->post('update-account', ['name' => 'مستقل', 'email' => $freelancer->email, 'phone' => '01011112222', 'account_type' => User::TYPE_FREELANCER, 'whatsapp' => '+201012345678', 'info' => 'مصمم مواقع بخبرة خمس سنوات في المتاجر',
            'availability' => 'available', 'image' => \Illuminate\Http\UploadedFile::fake()->image('small.jpg', 200, 200)])->assertSessionHasErrors('image');
        $this->assertFalse($freelancer->fresh()->freelancerChecklist()['photo']);

        $this->actingAs($freelancer)->post('update-account', ['name' => 'مستقل', 'email' => $freelancer->email, 'phone' => '01011112222', 'account_type' => User::TYPE_FREELANCER, 'whatsapp' => '+201012345678', 'info' => 'أصمم متاجر ومواقع شركات سريعة وواضحة',
            'availability' => 'available', 'image' => \Illuminate\Http\UploadedFile::fake()->image('me.jpg', 600, 600)])->assertSessionHasNoErrors();
        $fresh = $freelancer->fresh();
        @unlink(public_path('assets/uploads/users_image/' . $fresh->image));
        try {
            $this->assertNotNull($fresh->professional_photo);
            $this->assertTrue($fresh->freelancerChecklist()['photo']);
            $this->assertSame('أصمم متاجر ومواقع شركات سريعة وواضحة', $fresh->bio); // clients see the same text
        } finally {
            \App\Support\ImageVariants::delete(\App\Support\FreelancerPhoto::DIR, $fresh->professional_photo);
        }
    }

    public function test_a_sharp_account_picture_uploaded_before_becomes_the_professional_photo(): void
    {
        $dir = public_path('assets/uploads/users_image');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dir);
        $name = 'test-old-' . uniqid() . '.jpg';
        imagejpeg(imagecreatetruecolor(500, 500), $dir . '/' . $name);
        $small = 'test-small-' . uniqid() . '.jpg';
        imagejpeg(imagecreatetruecolor(200, 200), $dir . '/' . $small);

        $sharp = User::factory()->pendingFreelancer()->create(['image' => $name]);
        $tiny = User::factory()->pendingFreelancer()->create(['image' => $small]);
        try {
            $this->assertTrue(\App\Support\FreelancerPhoto::promoteProfileImage($sharp));
            $this->assertTrue($sharp->fresh()->freelancerChecklist()['photo']);
            $this->assertFalse(\App\Support\FreelancerPhoto::promoteProfileImage($tiny)); // too small to be sharp
            $this->assertFalse($tiny->fresh()->freelancerChecklist()['photo']);
        } finally {
            \App\Support\ImageVariants::delete(\App\Support\FreelancerPhoto::DIR, $sharp->fresh()->professional_photo);
            @unlink($dir . '/' . $name);
            @unlink($dir . '/' . $small);
        }
    }
}
