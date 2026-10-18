<?php

namespace App\Services\ServiceRequests;

use App\Models\RequestOffer;
use App\Models\RequestPayment;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestEvent;
use App\Models\User;
use App\Notifications\RequestClosedNotification;
use App\Notifications\RequestUpdateNotification;
use App\Services\Payments\RequestPaymentService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Admin (and client) actions that move a request between result pages. */
class RequestAdminService
{
    public function __construct(private RequestPaymentService $payments)
    {
    }

    /**
     * Close a request that will not be carried out. Money held for it is
     * refunded first; the record is kept for the "not completed" page.
     */
    public function close(ServiceRequest $request, string $reason, ?string $note, User $by): ServiceRequest
    {
        if (in_array($request->status, [ServiceRequest::STATUS_COMPLETED, ServiceRequest::STATUS_NOT_COMPLETED], true)) {
            throw new RuntimeException('هذا الطلب مغلق بالفعل');
        }
        if (! isset(ServiceRequest::CLOSE_REASONS[$reason])) {
            throw new RuntimeException('سبب الإغلاق غير صحيح');
        }
        if ($reason === 'other' && trim((string) $note) === '') {
            throw new RuntimeException('اكتب السبب عند اختيار «سبب آخر»');
        }
        // A staged project with a live contract is closed from the contract (dispute or freelancer change), never from here.
        $contract = $request->contract;
        if ($contract && in_array($contract->status, [\App\Models\Contract::STATUS_AGREEMENT, \App\Models\Contract::STATUS_ACTIVE], true)) {
            throw new RuntimeException('هذا الطلب مرتبط بالعقد ' . $contract->number . ' الجاري. لإنهائه استخدم صفحة العقد: قرار خلاف (استرجاع) أو تغيير المستقل.');
        }

        // Every held payment (first payment + paid price differences) goes back; one refusal stops here.
        $held = $request->payments()->where('status', RequestPayment::STATUS_PAID)->get();
        foreach ($held as $payment) {
            $this->payments->refund($payment);
        }
        $held = $held->isNotEmpty();

        $chosenFreelancer = $request->acceptedOffer?->freelancer;
        $request->statusChangeNote = $note;
        DB::transaction(function () use ($request, $reason, $note, $by) {
            $request->offers()->whereIn('status', RequestOffer::ACTIVE_STATUSES)->update(['status' => RequestOffer::STATUS_REJECTED]);
            $request->payments()->where('status', RequestPayment::STATUS_PENDING)->update(['status' => RequestPayment::STATUS_CANCELLED]);
            $request->changes()->whereIn('status', \App\Models\ServiceRequestChange::OPEN_STATUSES)
                ->update(['status' => \App\Models\ServiceRequestChange::STATUS_CANCELLED]);
            $request->update([
                'status' => ServiceRequest::STATUS_NOT_COMPLETED,
                'closed_reason' => $reason,
                'admin_note' => $note ? trim(($request->admin_note ? $request->admin_note . "\n" : '') . $note) : $request->admin_note,
                'closed_at' => now(),
                'closed_by' => $by->id,
                'followup_status' => $request->no_offer_alerted_at ? ServiceRequest::FOLLOWUP_RESOLVED : $request->followup_status,
            ]);
        });

        if ((int) $by->id !== (int) $request->user_id) {
            $request->user->notify(new RequestClosedNotification($request, (bool) $held));
        }
        $chosenFreelancer?->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('requests.closed_for_freelancer', ['number' => $request->number, 'reason' => $request->closeReasonLabel()]),
            '/client-requests/' . $request->number,
            'request_updates'
        ));

        return $request;
    }

    /** Publish a price inquiry after the client agreed (same record, nothing refilled). */
    public function publishInquiry(ServiceRequest $request, User $by): void
    {
        if (! $request->isInquiry()) {
            throw new RuntimeException('هذا الطلب ليس استفسار أسعار');
        }

        $request->publish();
        $request->update(['admin_note' => trim(($request->admin_note ? $request->admin_note . "\n" : '') . 'نُشر بموافقة العميل بواسطة ' . $by->name . ' ' . now()->format('Y-m-d H:i'))]);
        ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_NOTE, ['note' => 'نُشر الاستفسار للمستقلين بعد موافقة العميل']);
        $request->user->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('requests.inquiry_published', ['number' => $request->number]),
            '/my/requests/' . $request->number,
            'request_updates'
        ));
    }

    /** Confirm the work on the client's behalf (documented approval), paying the freelancer. */
    public function completeOnBehalf(ServiceRequest $request, string $note, User $by): void
    {
        $this->payments->release($request);
        $request->refresh()->update(['admin_note' => trim(($request->admin_note ? $request->admin_note . "\n" : '') . 'اعتمد الأدمن ' . $by->name . ' التنفيذ: ' . $note)]);
        ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_NOTE, ['note' => 'اعتماد التنفيذ نيابة عن العميل: ' . $note]);
        $request->user->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('requests.completed_by_team', ['number' => $request->number]),
            '/my/requests/' . $request->number,
            'request_updates'
        ));
    }

    public function updateFollowup(ServiceRequest $request, string $status, ?string $note): void
    {
        if (! isset(ServiceRequest::FOLLOWUP_STATUSES[$status])) {
            throw new RuntimeException('حالة المتابعة غير صحيحة');
        }

        $request->update(['followup_status' => $status, 'followup_note' => $note, 'followup_updated_at' => now()]);
        ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_FOLLOWUP, ['to_status' => $status, 'note' => $note]);
    }

    /**
     * Freelancers who work in the request's service (page 7: the admin checks
     * them when no offer arrived), with whether they offered or were invited.
     */
    public function matchingFreelancers(ServiceRequest $request)
    {
        $offered = $request->offers()->pluck('freelancer_id')->all();
        $invited = $request->events()->getQuery()->where('type', ServiceRequestEvent::TYPE_INVITED)->pluck('freelancer_id')->all();

        return User::where('account_type', User::TYPE_FREELANCER)->where('status', 1)
            ->whereHas('specializations', fn ($q) => $q->where('sub_categories.id', $request->sub_category_id))
            ->orderBy('name')
            ->get()
            ->each(function (User $freelancer) use ($offered, $invited) {
                $freelancer->setAttribute('has_offered', in_array($freelancer->id, $offered));
                $freelancer->setAttribute('was_invited', in_array($freelancer->id, $invited));
            });
    }

    /** Invite a matching freelancer to send an offer (once per freelancer and request). */
    public function invite(ServiceRequest $request, User $freelancer): void
    {
        if (! $request->isOpen()) {
            throw new RuntimeException('يمكن دعوة المستقلين للطلبات المفتوحة للعروض فقط');
        }
        if (! $freelancer->isFreelancer() || ! $freelancer->specializations()->where('sub_categories.id', $request->sub_category_id)->exists()) {
            throw new RuntimeException('هذا المستقل لا يعمل في خدمة الطلب');
        }
        if ($request->events()->getQuery()->where('type', ServiceRequestEvent::TYPE_INVITED)->where('freelancer_id', $freelancer->id)->exists()) {
            throw new RuntimeException('تمت دعوة هذا المستقل من قبل');
        }

        ServiceRequestEvent::record($request, ServiceRequestEvent::TYPE_INVITED, ['freelancer_id' => $freelancer->id]);
        $freelancer->notify(new RequestUpdateNotification(
            \App\Support\Msg::t('requests.freelancer_invited', ['number' => $request->number, 'service' => $request->service_name]),
            '/client-requests/' . $request->number,
            'request_updates'
        ));
    }
}
