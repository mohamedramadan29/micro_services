<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractAgreement;
use App\Models\ContractStage;
use App\Models\ContentBlock;
use App\Models\ServiceRequest;
use App\Models\SiteTest;
use App\Models\User;
use App\Services\Content\ContentManager;
use App\Services\Contracts\ContractLedger;
use App\Services\Contracts\StageWorkflow;
use App\Services\SiteTests\SiteTestSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/** Audit group 2 (2026-10-16): small gaps across services 1–3, the site check and content. */
class AuditGroupTwoTest extends TestCase
{
    use RefreshDatabase;

    private function contract(array $stages): Contract
    {
        $client = User::factory()->create(['balance' => 10000]);
        $freelancer = User::factory()->freelancer()->create();
        $contract = Contract::create(['client_id' => $client->id, 'freelancer_id' => $freelancer->id, 'title' => 'متجر إلكتروني', 'status' => Contract::STATUS_ACTIVE]);
        foreach ($stages as $i => $amount) {
            $contract->stages()->create(['sort_order' => $i + 1, 'title' => 'مرحلة ' . ($i + 1), 'deliverable' => 'تسليم', 'acceptance' => $i === 0 ? 'تعمل على الهاتف' : null,
                'duration_days' => 3, 'amount' => $amount, 'revisions' => 2, 'status' => ContractStage::STATUS_UNFUNDED]);
        }

        return $contract->fresh();
    }

    public function test_default_stages_split_the_offer_and_sum_to_it(): void
    {
        $stages = ContractAgreement::defaultStages(1000.5, 10);
        $this->assertCount(3, $stages);
        $this->assertSame(1000.5, round(array_sum(array_column($stages, 'amount')), 2));
        $this->assertSame(10, array_sum(array_column($stages, 'duration_days')));
    }

    public function test_funding_is_logged_notified_and_the_next_stage_asks_for_money(): void
    {
        $contract = $this->contract([100, 200]);
        $first = $contract->stages->first();

        app(ContractLedger::class)->fundFromBalance($contract, [$first->id], $contract->client);
        app(StageWorkflow::class)->advance($contract);
        $this->assertTrue($contract->freelancer->notifications()->get()->contains(fn ($n) => str_contains($n->data['text'], 'بدأت المرحلة «مرحلة 1»')));
        $this->assertFalse($contract->freelancer->notifications()->get()->contains(fn ($n) => str_contains($n->data['text'], 'موّل العميل مقدمًا')));
        $this->assertTrue($contract->client->notifications()->get()->contains(fn ($n) => str_contains($n->data['text'], 'تم تمويل «مرحلة 1»')));

        // Approving stage 1 leaves stage 2 unfunded: the client is asked once to fund it.
        app(ContractLedger::class)->release($first->fresh());
        app(StageWorkflow::class)->afterSettlement($contract);
        app(StageWorkflow::class)->afterSettlement($contract);
        $asks = $contract->client->notifications()->get()->filter(fn ($n) => str_contains($n->data['text'], 'جاهزة للبدء'));
        $this->assertCount(1, $asks);
        $this->assertNotNull($contract->stages()->where('sort_order', 2)->first()->funding_requested_at);
    }

    public function test_my_projects_lists_contracts_with_whose_turn_it_is(): void
    {
        $contract = $this->contract([100]);

        $this->actingAs($contract->client)->get(route('contracts.index'))->assertOk()
            ->assertSee($contract->number)->assertSee('موّل المرحلة «مرحلة 1»');
        $this->actingAs($contract->freelancer)->get(route('contracts.index'))->assertOk()
            ->assertSee('بانتظار تمويل العميل');
        $this->actingAs(User::factory()->create())->get(route('contracts.index'))->assertOk()->assertDontSee($contract->number);
        $this->actingAs($contract->client)->get(route('contracts.show', $contract))->assertOk()->assertSee('معيار القبول: تعمل على الهاتف');
    }

    public function test_site_check_failure_reason_is_arabic_and_closed_status_and_retention(): void
    {
        $this->assertSame('استغرق الموقع وقتًا طويلًا جدًا في الاستجابة.', SiteTest::friendlyFailure('Lighthouse returned error: Request timed out'));
        $this->assertStringContainsString('4xx', SiteTest::friendlyFailure('FAILED_DOCUMENT_REQUEST status code 404'));

        $admin = User::factory()->admin()->create();
        $old = SiteTest::create(['number' => 'ST-1', 'url' => 'https://a.com', 'host' => 'a.com', 'device' => 'mobile', 'categories' => ['seo'], 'status' => SiteTest::STATUS_DONE, 'token' => 'x', 'review_status' => SiteTest::REVIEW_NONE]);
        $kept = SiteTest::create(['number' => 'ST-2', 'url' => 'https://b.com', 'host' => 'b.com', 'device' => 'mobile', 'categories' => ['seo'], 'status' => SiteTest::STATUS_DONE, 'token' => 'y', 'review_status' => SiteTest::REVIEW_NONE, 'contact_phone' => '+201000000000']);
        $kept->results()->create(['device' => 'mobile', 'engine' => 'fake', 'ok' => true, 'raw' => '{}', 'fetched_at' => now()]);

        $this->actingAs($admin)->post(route('admin.site-tests.review', $kept), ['review_status' => SiteTest::REVIEW_CLOSED]);
        $this->assertSame('مغلق', $kept->fresh()->stageLabel());

        SiteTestSettings::save(['retention_days' => 30]);
        SiteTest::query()->update(['created_at' => now()->subDays(40)]);
        $this->artisan('site-tests:prune')->assertSuccessful();
        $this->assertNull($old->fresh());
        $this->assertNull($kept->fresh()->results()->first()->raw);
    }

    public function test_a_field_added_after_saving_content_takes_its_default(): void
    {
        ContentBlock::create(['area' => 'services.help', 'data' => ['title' => 'عنوان محفوظ', 'login_button' => '']]);
        Cache::flush();
        $help = app(ContentManager::class)->first('services.help');

        $this->assertSame('عنوان محفوظ', $help['title']);
        $this->assertSame('', $help['login_button']); // saved empty on purpose
        $this->assertSame('تابع عبر واتساب', $help['whatsapp_button']); // added later
    }
}
