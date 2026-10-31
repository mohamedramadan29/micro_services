<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\admin\Service;
use App\Models\front\Conversation;
use App\Models\front\Message;
use App\Models\front\WithDraw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Deleting freelancers from admin/freelancers (user request 2026-10-31, for test accounts). */
class FreelancerDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /** A freelancer with the usual things a test account collects: an offer, a chat, a ready service, a specialization. */
    private function busyFreelancer(): User
    {
        $freelancer = User::factory()->freelancer()->create(['name' => 'مستقل تجريبي']);
        $client = User::factory()->create();
        $request = ServiceRequest::create(['user_id' => $client->id, 'service_name' => 'خدمة', 'path' => 'execute', 'status' => 'open',
            'problem' => 'x', 'contact_name' => 'c', 'contact_phone' => '0100', 'contact_email' => 'c@example.com']);
        RequestOffer::create(['service_request_id' => $request->id, 'freelancer_id' => $freelancer->id, 'price' => 500, 'duration_days' => 5, 'scope' => 's', 'message' => 'm']);
        $conversation = Conversation::create(['sender_id' => $client->id, 'receiver_id' => $freelancer->id, 'service_id' => 0]);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $client->id, 'receiver_id' => $freelancer->id, 'body' => 'مرحبا']);
        Service::create(['name' => 'خدمة جاهزة', 'slug' => 'ready', 'cat_id' => 1, 'user_id' => $freelancer->id, 'image' => 'x.png',
            'description' => 'd', 'tags' => '', 'price' => 100, 'status' => 1]);
        $category = \App\Models\admin\Category::create(['name' => 'مجموعة', 'slug' => 'g', 'status' => 1]);
        $sub = \App\Models\admin\SubCategory::create(['name' => 'خدمة', 'slug' => 's', 'parent_id' => $category->id, 'image' => 'x.png', 'status' => 1]);
        $freelancer->specializations()->attach($sub->id);
        WithDraw::create(['user_id' => $freelancer->id, 'amount' => 50, 'status' => 1, 'method' => 'wallet', 'paypal_mail' => '']);

        return $freelancer;
    }

    public function test_the_manager_deletes_a_test_freelancer_with_everything_he_owns(): void
    {
        $freelancer = $this->busyFreelancer();

        $this->actingAs($this->admin)->get(route('admin.freelancers.index'))->assertOk()
            ->assertSee(route('admin.freelancers.destroy', $freelancer), false);
        $this->actingAs($this->admin)->get(route('admin.freelancers.show', $freelancer))->assertOk()->assertSee('حذف المستقل');

        $this->actingAs($this->admin)->post(route('admin.freelancers.destroy', $freelancer))
            ->assertRedirect(route('admin.freelancers.index'))->assertSessionHas('success');

        $this->assertNull(User::find($freelancer->id));
        $this->assertSame(0, RequestOffer::count());
        $this->assertSame(0, Conversation::count() + Message::count());
        $this->assertSame(0, Service::where('user_id', $freelancer->id)->count());
        $this->assertSame(0, WithDraw::where('user_id', $freelancer->id)->count());
        $this->assertSame(0, DB::table('freelancer_specializations')->count());
        $this->assertSame(1, ServiceRequest::count()); // the client's request stays
    }

    public function test_money_or_project_history_blocks_the_deletion_with_the_reason(): void
    {
        $withContract = User::factory()->freelancer()->create();
        Contract::create(['client_id' => User::factory()->create()->id, 'freelancer_id' => $withContract->id, 'title' => 'متجر', 'status' => Contract::STATUS_ACTIVE]);
        $withBalance = User::factory()->freelancer()->create();
        $withBalance->forceFill(['balance' => 250])->save();

        foreach ([[$withContract, 'مشروع بمراحل'], [$withBalance, 'لم يُسحب']] as [$freelancer, $reason]) {
            $this->actingAs($this->admin)->get(route('admin.freelancers.show', $freelancer))->assertSee('لا يمكن حذف هذا الحساب')->assertSee($reason);
            $this->actingAs($this->admin)->post(route('admin.freelancers.destroy', $freelancer))->assertSessionHasErrors('delete');
            $this->assertNotNull(User::find($freelancer->id));
        }
    }

    public function test_only_the_manager_deletes_and_only_freelancers(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $accountant = User::factory()->admin()->create(['admin_role' => 'accounts']);

        $this->actingAs($accountant)->get(route('admin.freelancers.index'))->assertOk()->assertDontSee(route('admin.freelancers.destroy', $freelancer), false);
        $this->actingAs($accountant)->post(route('admin.freelancers.destroy', $freelancer))->assertForbidden();
        $this->actingAs($this->admin)->post(route('admin.freelancers.destroy', User::factory()->create()))->assertNotFound(); // a client
        $this->assertNotNull(User::find($freelancer->id));
    }
}
