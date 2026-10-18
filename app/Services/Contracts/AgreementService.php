<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractAgreement;
use App\Models\ContractStage;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service 3, phase 2 (client decisions 2026-10-16): the freelancer proposes the agreement,
 * the client approves or asks for changes, nothing starts without both. Each version is kept;
 * the approved one fixes the value (stages always sum to it) and creates the stages.
 */
class AgreementService
{
    /** @param array{scope: string, out_of_scope?: ?string, client_materials?: ?string, freelancer_note?: ?string, stages: array} $data */
    public function propose(Contract $contract, User $freelancer, array $data): ContractAgreement
    {
        $agreement = DB::transaction(function () use ($contract, $freelancer, $data) {
            $contract = Contract::whereKey($contract->id)->lockForUpdate()->first();
            if ($contract->status !== Contract::STATUS_AGREEMENT || (int) $contract->freelancer_id !== (int) $freelancer->id) {
                throw new RuntimeException('لا يمكن اقتراح اتفاق على هذا العقد الآن');
            }

            $stages = array_values(array_map(fn ($stage) => [
                'title' => trim($stage['title']),
                'deliverable' => trim($stage['deliverable']),
                'acceptance' => trim((string) ($stage['acceptance'] ?? '')) ?: null,
                'duration_days' => (int) $stage['duration_days'],
                'amount' => round((float) $stage['amount'], 2),
                'revisions' => ! empty($stage['unlimited_revisions']) ? null : (int) ($stage['revisions'] ?? ContractPolicy::number('default_revisions')),
            ], $data['stages']));

            // A new proposal replaces any version still waiting for the client.
            $contract->agreements()->whereIn('status', [ContractAgreement::STATUS_PROPOSED, ContractAgreement::STATUS_CHANGES_REQUESTED])
                ->update(['status' => ContractAgreement::STATUS_SUPERSEDED]);

            $agreement = $contract->agreements()->create([
                'version' => (int) $contract->agreements()->max('version') + 1,
                'proposed_by' => $freelancer->id,
                'status' => ContractAgreement::STATUS_PROPOSED,
                'scope' => $data['scope'],
                'out_of_scope' => $data['out_of_scope'] ?? null,
                'client_materials' => $data['client_materials'] ?? null,
                'freelancer_note' => $data['freelancer_note'] ?? null,
                'stages' => $stages,
                'total' => round(array_sum(array_column($stages, 'amount')), 2),
                'freelancer_approved_at' => now(),
            ]);
            $this->log($contract, 'اقترح المستقل الاتفاق (نسخة ' . $agreement->version . '): ' . count($stages) . ' مراحل بإجمالي ' . Money::format($agreement->total) . ' خلال ' . $agreement->totalDays() . ' يومًا');

            return $agreement;
        });

        $this->notify($contract->client, \App\Support\Msg::t('contracts.agreement_proposed', ['number' => $contract->number, 'version' => $agreement->version]), $contract);

        return $agreement;
    }

    public function requestChanges(ContractAgreement $agreement, User $client, string $note): void
    {
        DB::transaction(function () use ($agreement, $client, $note) {
            $agreement = $this->pending($agreement, $client);
            $agreement->update(['status' => ContractAgreement::STATUS_CHANGES_REQUESTED, 'client_note' => $note]);
            $this->log($agreement->contract, 'طلب العميل تعديل الاتفاق (نسخة ' . $agreement->version . '): ' . $note);
        });

        $contract = $agreement->contract;
        $this->notify($contract->freelancer, \App\Support\Msg::t('contracts.agreement_changes_requested', ['number' => $contract->number, 'note' => $note]), $contract);
    }

    /** Both sides agreed: the value is fixed, the stages exist, the contract is active. */
    public function approve(ContractAgreement $agreement, User $client, ?string $ip = null): Contract
    {
        $contract = DB::transaction(function () use ($agreement, $client, $ip) {
            $agreement = $this->pending($agreement, $client);
            $contract = Contract::whereKey($agreement->contract_id)->lockForUpdate()->first();

            $agreement->update(['status' => ContractAgreement::STATUS_APPROVED, 'client_approved_at' => now(), 'client_approved_ip' => $ip]);
            foreach (array_values($agreement->stages) as $index => $stage) {
                $contract->stages()->create([
                    'contract_agreement_id' => $agreement->id,
                    'sort_order' => $index + 1,
                    'title' => $stage['title'],
                    'deliverable' => $stage['deliverable'],
                    'acceptance' => $stage['acceptance'] ?? null,
                    'duration_days' => $stage['duration_days'],
                    'amount' => $stage['amount'],
                    'revisions' => $stage['revisions'],
                    'status' => ContractStage::STATUS_UNFUNDED,
                ]);
            }
            $contract->update(['status' => Contract::STATUS_ACTIVE]);
            $contract->request?->update(['agreed_price' => $agreement->total]);
            $this->log($contract, 'وافق الطرفان على الاتفاق (نسخة ' . $agreement->version . ') بإجمالي ' . Money::format($agreement->total) . '. بعد تمويل المرحلة الأولى يبدأ العمل.');

            return $contract;
        });

        $this->notify($contract->freelancer, \App\Support\Msg::t('contracts.agreement_approved', ['number' => $contract->number]), $contract);

        return $contract;
    }

    /** The version the client may act on: the latest one, still waiting, on their own contract. */
    private function pending(ContractAgreement $agreement, User $client): ContractAgreement
    {
        $agreement = ContractAgreement::whereKey($agreement->id)->lockForUpdate()->first();
        $contract = $agreement->contract;
        if ((int) $contract->client_id !== (int) $client->id || $contract->status !== Contract::STATUS_AGREEMENT
            || $agreement->status !== ContractAgreement::STATUS_PROPOSED || (int) $contract->agreements()->max('version') !== (int) $agreement->version) {
            throw new RuntimeException('هذه النسخة من الاتفاق لم تعد بانتظار ردك');
        }

        return $agreement;
    }

    private function log(Contract $contract, string $note): void
    {
        if ($contract->request) {
            ServiceRequestEvent::record($contract->request, ServiceRequestEvent::TYPE_AGREEMENT, ['note' => $note]);
        }
    }

    private function notify(?User $user, string $text, Contract $contract): void
    {
        $user?->notify(new RequestUpdateNotification($text, '/contracts/' . $contract->number, 'contracts'));
    }
}
