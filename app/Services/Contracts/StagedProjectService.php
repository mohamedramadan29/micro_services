<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\RequestOffer;
use App\Models\ServiceRequest;
use App\Notifications\OfferNotSelectedNotification;
use App\Notifications\RequestUpdateNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service 3, phase 1: on a staged project the client picks an offer without paying it all
 * at once — a contract opens with the freelancer, the agreement (stages, dates) comes next,
 * and each stage is funded on its own (ContractLedger).
 */
class StagedProjectService
{
    public function chooseOffer(RequestOffer $offer): Contract
    {
        [$contract, $others] = DB::transaction(function () use ($offer) {
            $request = ServiceRequest::whereKey($offer->service_request_id)->lockForUpdate()->first();
            $offer = RequestOffer::whereKey($offer->id)->lockForUpdate()->first();
            if (! $request->isStaged() || ! $request->isOpen() || ! $offer->isActive()) {
                throw new RuntimeException('لا يمكن اختيار هذا العرض الآن');
            }

            $offer->update(['status' => RequestOffer::STATUS_ACCEPTED, 'is_saved' => false]);
            $others = $request->offers()->whereKeyNot($offer->id)->whereIn('status', RequestOffer::ACTIVE_STATUSES)->get();
            $request->offers()->whereKeyNot($offer->id)->whereIn('status', RequestOffer::ACTIVE_STATUSES)
                ->update(['status' => RequestOffer::STATUS_REJECTED]);
            $request->statusChangeNote = 'اختار العميل عرض المستقل؛ يبدأ إعداد الاتفاق والمراحل';
            $request->update(['status' => ServiceRequest::STATUS_IN_PROGRESS, 'accepted_offer_id' => $offer->id, 'agreed_price' => $offer->price]);

            $contract = Contract::create([
                'service_request_id' => $request->id,
                'client_id' => $request->user_id,
                'freelancer_id' => $offer->freelancer_id,
                'title' => $request->project_title ?: $request->service_name,
                'status' => Contract::STATUS_AGREEMENT,
            ]);

            return [$contract->fresh(), $others];
        });

        $request = $contract->request;
        $offer->freelancer?->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('contracts.offer_chosen', ['number' => $request->number]),
            '/client-requests/' . $request->number,
            'offer_selected'
        ));
        foreach ($others as $other) {
            $other->fresh()->freelancer?->notify(new OfferNotSelectedNotification($other->fresh()));
        }

        return $contract;
    }
}
