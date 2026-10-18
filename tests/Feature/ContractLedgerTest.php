<?php

namespace Tests\Feature;

use App\Models\admin\Setting;
use App\Models\Contract;
use App\Models\ContractStage as Stage;
use App\Models\OnlinePayment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Contracts\ContractLedger;
use App\Services\Payments\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/** Service 3 money (client decisions 2026-10-15): fund, hold, release, refund, split, change freelancer. */
class ContractLedgerTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private ContractLedger $ledger;
    private User $client;
    private User $freelancer;
    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->gateway);
        Setting::create(['website_commission' => '10', 'website_balance' => 0]);
        $this->ledger = app(ContractLedger::class);

        $this->client = User::factory()->create(['balance' => 1000]);
        $this->freelancer = User::factory()->freelancer()->create(['balance' => 0]);
        $this->contract = Contract::create(['client_id' => $this->client->id, 'freelancer_id' => $this->freelancer->id, 'title' => 'متجر إلكتروني']);
        foreach ([['التصميم', 400], ['البرمجة', 600], ['الإطلاق', 200]] as $i => [$title, $amount]) {
            $this->contract->stages()->create(['title' => $title, 'amount' => $amount, 'sort_order' => $i + 1]);
        }
    }

    private function stage(string $title): Stage
    {
        return $this->contract->stages()->where('title', $title)->firstOrFail();
    }

    public function test_the_client_funds_several_stages_ahead_from_the_site_balance(): void
    {
        $this->assertSame('CNT-' . (1000 + $this->contract->id), $this->contract->fresh()->number);
        $ids = [$this->stage('التصميم')->id, $this->stage('البرمجة')->id];

        $this->ledger->fundFromBalance($this->contract, $ids, $this->client);

        $this->assertEquals(0, $this->client->fresh()->balance);
        foreach ($ids as $id) {
            $stage = Stage::find($id);
            $this->assertSame([Stage::STATUS_FUNDED, 'balance'], [$stage->status, $stage->funding_source]);
            $this->assertEquals(10, $stage->commission_rate);
        }
        $this->assertSame('المبلغ لدى نفّذها حتى تعتمد المرحلة', Stage::find($ids[0])->statusLabel());
        $this->assertSame(2, WalletTransaction::where('type', WalletTransaction::TYPE_ESCROW_IN)->whereNotNull('contract_stage_id')->count());
        $this->assertEquals(['total' => 1200.0, 'held' => 1000.0, 'released' => 0.0, 'commission' => 0.0, 'refunded' => 0.0, 'unfunded' => 200.0], $this->contract->totals());

        // Not enough balance, a stage funded twice, someone else's contract: nothing changes.
        foreach ([
            fn () => $this->ledger->fundFromBalance($this->contract, [$this->stage('الإطلاق')->id], $this->client),
            fn () => $this->ledger->fundFromBalance($this->contract, [$ids[0]], $this->client->fresh()->forceFill(['balance' => 5000])),
            fn () => $this->ledger->fundFromBalance($this->contract, [$this->stage('الإطلاق')->id], User::factory()->create(['balance' => 5000])),
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('Expected a refusal');
            } catch (RuntimeException) {
            }
        }
        $this->assertSame(Stage::STATUS_UNFUNDED, $this->stage('الإطلاق')->status);
        $this->assertEquals(0, $this->client->fresh()->balance);
    }

    public function test_easykash_funding_adds_the_fees_and_a_failed_payment_frees_the_stages(): void
    {
        Setting::first()->update(['stage_fee_percent' => 2, 'stage_fee_fixed' => 3]);
        $stage = $this->stage('البرمجة');

        $this->assertSame('https://pay.test/1', $this->ledger->startFunding($this->contract, [$stage->id], $this->client));
        $online = OnlinePayment::sole();
        $this->assertEquals(600 + 12 + 3, $online->amount);
        $this->assertSame(OnlinePayment::PURPOSE_CONTRACT_STAGE, $online->purpose);
        $this->assertSame(Stage::STATUS_FUNDING, $stage->fresh()->status);

        $this->gateway->markFailed((string) $online->id);
        $this->artisan('payments:sync');
        $this->assertSame(Stage::STATUS_UNFUNDED, $stage->fresh()->status);

        $this->ledger->startFunding($this->contract, [$stage->id], $this->client);
        $second = OnlinePayment::latest('id')->first();
        $this->gateway->markPaid((string) $second->id);
        $this->artisan('payments:sync');
        $stage->refresh();
        $this->assertSame([Stage::STATUS_FUNDED, 'easykash', $second->id], [$stage->status, $stage->funding_source, $stage->online_payment_id]);
        $this->assertEquals(1000, $this->client->fresh()->balance); // fees went to EasyKash, the balance was not touched

        // Refunding gives back the stage amount only: the client paid the fees.
        $this->ledger->refund($stage, 'تعذّر التنفيذ');
        $this->assertEquals(1600, $this->client->fresh()->balance);
        $this->assertEquals(0, Setting::first()->website_balance);
    }

    public function test_money_paid_for_a_stage_that_no_longer_waits_goes_to_the_client_balance(): void
    {
        $stage = $this->stage('الإطلاق');
        $this->ledger->startFunding($this->contract, [$stage->id], $this->client);
        $late = OnlinePayment::sole();
        // The checkout was abandoned and cancelled on the site, so the client funded from the balance…
        $late->update(['status' => OnlinePayment::STATUS_CANCELLED]);
        $this->ledger->fundingFailed($late);
        $this->ledger->fundFromBalance($this->contract, [$stage->id], $this->client);
        $this->assertEquals(800, $this->client->fresh()->balance);

        // …and EasyKash later reports that checkout paid after all (Fawry code paid late).
        $this->gateway->markPaid((string) $late->id);
        $this->call('POST', route('easykash.webhook'), ['customerReference' => $late->id, 'status' => 'PAID', 'signatureHash' => 'valid-signature'])->assertOk();

        $this->assertSame('balance', $stage->fresh()->funding_source);
        $this->assertEquals(1000, $this->client->fresh()->balance);
    }

    public function test_release_takes_the_commission_from_the_freelancer_and_refund_and_split_follow_the_rules(): void
    {
        $this->client->update(['balance' => 1200]);
        $this->ledger->fundFromBalance($this->contract, $this->contract->stages()->pluck('id')->all(), $this->client);
        Setting::first()->update(['website_commission' => '20']); // the rate stays the one at funding

        $released = $this->ledger->release($this->stage('التصميم'));
        $this->assertEquals([400, 40, 360, 0], [$released->released_amount, $released->commission_amount, $released->freelancer_amount, $released->refunded_amount]);
        $this->assertSame($this->freelancer->id, $released->released_to);
        $this->assertEquals(360, $this->freelancer->fresh()->balance);
        $this->assertEquals(40, Setting::first()->website_balance);

        // Split: commission only on the freelancer's part, the rest to the client's balance.
        $split = $this->ledger->split($this->stage('البرمجة'), 250, 'نُفّذ نصف العمل تقريبًا');
        $this->assertEquals([250, 25, 225, 350], [$split->released_amount, $split->commission_amount, $split->freelancer_amount, $split->refunded_amount]);
        $this->assertEquals(585, $this->freelancer->fresh()->balance);
        $this->assertEquals(350, $this->client->fresh()->balance);

        // Refund: everything back to the client, no commission.
        $refunded = $this->ledger->refund($this->stage('الإطلاق'));
        $this->assertEquals([0, 0, 200], [$refunded->commission_amount, $refunded->freelancer_amount, $refunded->refunded_amount]);
        $this->assertEquals(550, $this->client->fresh()->balance);
        $this->assertEquals(65, Setting::first()->website_balance);

        // The last stage ended in a refund: the project ends without being «completed» (audit 2026-10-17).
        $this->assertSame(Contract::STATUS_CANCELLED, $this->contract->fresh()->status);
        $this->assertEquals(['total' => 1200.0, 'held' => 0.0, 'released' => 585.0, 'commission' => 65.0, 'refunded' => 550.0, 'unfunded' => 0.0], $this->contract->totals());

        // A settled stage cannot move again; a split must leave something on both sides.
        $this->expectException(RuntimeException::class);
        $this->ledger->release($this->stage('التصميم'));
    }

    public function test_after_a_change_of_freelancer_each_one_is_paid_and_charged_only_for_their_stages(): void
    {
        $this->client->update(['balance' => 1200]);
        $this->ledger->fundFromBalance($this->contract, $this->contract->stages()->pluck('id')->all(), $this->client);
        $this->ledger->release($this->stage('التصميم'));

        $replacement = User::factory()->freelancer()->create(['balance' => 0]);
        $this->ledger->changeFreelancer($this->contract, $replacement);
        $this->ledger->release($this->stage('البرمجة'));

        $this->assertEquals(360, $this->freelancer->fresh()->balance);
        $this->assertEquals(540, $replacement->fresh()->balance);
        $this->assertSame($replacement->id, $this->stage('البرمجة')->released_to);
        $this->assertSame(Stage::STATUS_FUNDED, $this->stage('الإطلاق')->status); // still held for the new freelancer

        $this->expectException(RuntimeException::class);
        $this->ledger->split($this->stage('الإطلاق'), 200);
    }
}
