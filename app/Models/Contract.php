<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Service 3 «مشروعك ما بيوقف»: a staged contract between a client and a freelancer. */
class Contract extends Model
{
    public const STATUS_AGREEMENT = 'agreement'; // freelancer chosen, the agreement is being written/approved
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REPLACED = 'replaced'; // the freelancer changed: the rest continues in a new request

    public const STATUSES = [
        self::STATUS_AGREEMENT => 'كتابة الاتفاق',
        self::STATUS_ACTIVE => 'قيد التنفيذ',
        self::STATUS_COMPLETED => 'مكتمل',
        self::STATUS_CANCELLED => 'ملغي',
        self::STATUS_REPLACED => 'تغيّر المستقل',
    ];

    protected $guarded = [];

    protected $casts = ['completed_at' => 'datetime', 'rating_reminder_sent_at' => 'datetime'];

    protected static function booted(): void
    {
        static::created(fn (self $contract) => $contract->updateQuietly(['number' => 'CNT-' . (1000 + $contract->id)]));
    }

    /** Agreement versions, newest first. */
    public function agreements()
    {
        return $this->hasMany(ContractAgreement::class)->orderByDesc('version');
    }

    public function latestAgreement(): ?ContractAgreement
    {
        return $this->agreements()->first();
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** The contracts a user takes part in, as client or as freelancer («مشاريعي»). */
    public function scopeInvolving($query, User $user)
    {
        return $query->where(fn ($q) => $q->where('client_id', $user->id)->orWhere('freelancer_id', $user->id));
    }

    /**
     * What the contract waits for, from the viewer's side (needs stages.deliveries,
     * agreements and disputes loaded for lists): [text, is the viewer's turn].
     */
    public function nextStep(User $viewer): ?array
    {
        $isClient = (int) $viewer->id === (int) $this->client_id;
        if ($this->status === self::STATUS_AGREEMENT) {
            $latest = $this->agreements->first();

            return match (true) {
                ! $latest || $latest->status !== ContractAgreement::STATUS_PROPOSED => [$isClient ? 'بانتظار اقتراح المستقل للاتفاق' : 'اكتب الاتفاق واقترحه على العميل', ! $isClient],
                default => [$isClient ? 'راجع الاتفاق ووافق عليه' : 'بانتظار موافقة العميل على الاتفاق', $isClient],
            };
        }
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }
        if ($this->disputes->whereIn('status', ContractDispute::OPEN_STATUSES)->isNotEmpty()) {
            return ['يوجد خلاف مفتوح بانتظار الإدارة', false];
        }
        $stage = $this->stages->first(fn ($s) => ! in_array($s->status, [ContractStage::STATUS_RELEASED, ContractStage::STATUS_REFUNDED, ContractStage::STATUS_SPLIT], true));
        if (! $stage) {
            return null;
        }
        $label = '«' . $stage->title . '»';

        return match (true) {
            in_array($stage->status, [ContractStage::STATUS_UNFUNDED, ContractStage::STATUS_FUNDING], true) => [$isClient ? 'موّل المرحلة ' . $label : 'المرحلة ' . $label . ' بانتظار تمويل العميل', $isClient],
            $stage->deliveries->contains('status', ContractDelivery::STATUS_SUBMITTED) => [$isClient ? 'راجع تسليم المرحلة ' . $label : 'تسليمك للمرحلة ' . $label . ' بانتظار مراجعة العميل', $isClient],
            default => [$isClient ? 'المستقل يعمل على المرحلة ' . $label : 'سلّم المرحلة ' . $label . ($stage->due_at ? ' قبل ' . $stage->due_at->format('Y-m-d') : ''), ! $isClient],
        };
    }

    public const WAITING_ON = ['client' => 'العميل', 'freelancer' => 'المستقل', 'team' => 'الإدارة'];

    /**
     * Expected end of the project: the running stage's deadline plus the days of the stages
     * after it (or today + all remaining days before work starts). Null when nothing is left.
     */
    public function expectedEnd(): ?\Illuminate\Support\Carbon
    {
        $left = $this->stages->reject(fn ($s) => $s->isSettled())->values();
        if ($left->isEmpty() || $this->status !== self::STATUS_ACTIVE) {
            return null;
        }
        $first = $left->first();
        $start = $first->started_at && $first->due_at ? $first->due_at->copy() : now()->addDays((int) $first->duration_days);

        return $start->addDays((int) $left->slice(1)->sum('duration_days'));
    }

    /** The stage being worked on now (first not settled); uses the loaded stages. */
    public function currentStageLoaded(): ?ContractStage
    {
        return $this->stages->first(fn ($s) => ! $s->isSettled());
    }

    /**
     * Who the contract waits for, for the admin board: [client|freelancer|team, what].
     * Needs disputes, freelancerChanges, agreements, addenda, stages.deliveries and stages.pauses loaded.
     */
    public function waitingOn(): ?array
    {
        if ($this->disputes->whereIn('status', ContractDispute::OPEN_STATUSES)->isNotEmpty()) {
            return ['team', 'خلاف مفتوح'];
        }
        if ($this->freelancerChanges->where('status', ContractFreelancerChange::STATUS_PENDING)->isNotEmpty()) {
            return ['team', 'طلب تغيير المستقل'];
        }
        if ($this->status === self::STATUS_AGREEMENT) {
            return $this->agreements->first()?->status === ContractAgreement::STATUS_PROPOSED ? ['client', 'الموافقة على الاتفاق'] : ['freelancer', 'كتابة الاتفاق'];
        }
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }
        if ($addendum = $this->addenda->firstWhere('status', ContractAddendum::STATUS_PROPOSED)) {
            return [(int) $addendum->proposed_by === (int) $this->client_id ? 'freelancer' : 'client', 'الرد على ملحق'];
        }
        $stage = $this->currentStageLoaded();

        return match (true) {
            ! $stage => null,
            in_array($stage->status, [ContractStage::STATUS_UNFUNDED, ContractStage::STATUS_FUNDING], true) => ['client', 'تمويل المرحلة'],
            $stage->deliveries->contains('status', ContractDelivery::STATUS_SUBMITTED) => ['client', 'مراجعة التسليم'],
            $stage->pauses->contains('status', ContractPause::STATUS_ACTIVE) => ['client', 'الرد على طلب المستقل (العدّاد متوقف)'],
            default => ['freelancer', 'تسليم المرحلة'],
        };
    }

    /** The client, the contract's freelancer or an active admin. */
    public function visibleTo(?User $user): bool
    {
        return $user && ((int) $user->id === (int) $this->client_id || (int) $user->id === (int) $this->freelancer_id
            || ($user->account_type === User::TYPE_ADMIN && (int) $user->status === 1));
    }

    public function disputes()
    {
        return $this->hasMany(ContractDispute::class)->latest('id');
    }

    public function openDispute(): ?ContractDispute
    {
        return $this->disputes()->whereIn('status', ContractDispute::OPEN_STATUSES)->first();
    }

    public function freelancerChanges()
    {
        return $this->hasMany(ContractFreelancerChange::class)->latest('id');
    }

    public function replyRequests()
    {
        return $this->hasMany(ContractReplyRequest::class)->latest('id');
    }

    public function addenda()
    {
        return $this->hasMany(ContractAddendum::class)->latest('id');
    }

    public function ratings()
    {
        return $this->hasMany(ContractRating::class);
    }

    public function stages()
    {
        return $this->hasMany(ContractStage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    /**
     * Money of the contract across its stages.
     *
     * @return array{total: float, held: float, released: float, commission: float, refunded: float, unfunded: float}
     */
    public function totals(): array
    {
        $stages = $this->stages()->get();
        $sum = fn ($filter, $field = 'amount') => round((float) $stages->filter($filter)->sum($field), 2);

        return [
            'total' => $sum(fn () => true),
            'held' => $sum(fn ($s) => $s->status === ContractStage::STATUS_FUNDED),
            'released' => $sum(fn () => true, 'freelancer_amount'),
            'commission' => $sum(fn () => true, 'commission_amount'),
            'refunded' => $sum(fn () => true, 'refunded_amount'),
            'unfunded' => $sum(fn ($s) => in_array($s->status, [ContractStage::STATUS_UNFUNDED, ContractStage::STATUS_FUNDING], true)),
        ];
    }
}
