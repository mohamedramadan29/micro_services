<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Account types a user can pick (stored in users.account_type). */
    public const TYPE_CLIENT = 'مشتري';
    public const TYPE_FREELANCER = 'بائع';
    public const TYPE_EMPLOYEE = 'موظف';
    public const TYPE_ADMIN = 'admin';

    /**
     * How each stored type is called today (the stored values are old words kept for compatibility:
     * «مشتري» = client, «بائع» = freelancer). Show these labels, never the raw value.
     */
    public const TYPE_LABELS = [
        self::TYPE_CLIENT => 'عميل',
        self::TYPE_FREELANCER => 'مستقل',
        self::TYPE_EMPLOYEE => 'موظف',
        self::TYPE_ADMIN => 'إدارة',
    ];

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->account_type] ?? 'لم يختر نوع الحساب';
    }

    /** Types a user may choose for themselves (never admin). */
    public const SELF_SELECTABLE_TYPES = [self::TYPE_CLIENT, self::TYPE_FREELANCER, self::TYPE_EMPLOYEE];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'info',
        'phone',
        'image',
        'user_name',
        'balance',
        'account_type',
        'rate',
        'status',
        'block_status',
        'job_title',
        // Freelancer profile (approval, verification and photo are set by the admin with forceFill).
        'whatsapp',
        'bio',
        'availability',
        'languages',
        'starting_price',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'admin_verified_at' => 'datetime',
        'freelancer_profile_completed_at' => 'datetime',
        'freelancer_reviewed_at' => 'datetime',
        'freelancer_ready_at' => 'datetime',
        'is_featured' => 'boolean',
        'starting_price' => 'decimal:2',
    ];

    public const FREELANCER_PENDING = 'pending';
    public const FREELANCER_APPROVED = 'approved';
    public const FREELANCER_REJECTED = 'rejected';

    public const FREELANCER_STATUSES = [
        self::FREELANCER_PENDING => 'قيد المراجعة',
        self::FREELANCER_APPROVED => 'معتمد',
        self::FREELANCER_REJECTED => 'غير مقبول',
    ];

    public const AVAILABILITY = [
        'available' => 'متاح الآن',
        'busy' => 'مشغول حاليًا',
    ];

    /** WhatsApp numbers: international format, digits with an optional + (client file «تعديلات», item 11). */
    public const WHATSAPP_RULE = 'regex:/^\+?[1-9][0-9\s\-]{7,18}$/';

    /** Verified by a confirmed email or by the admin (client decision 2026-10-16; WhatsApp is contact only). */
    public function isVerifiedFreelancer(): bool
    {
        return $this->email_verified_at !== null || $this->admin_verified_at !== null;
    }

    /** Verified and approved by the admin: may send offers and be listed. */
    public function isApprovedFreelancer(): bool
    {
        return $this->isFreelancer() && $this->freelancer_status === self::FREELANCER_APPROVED && $this->isVerifiedFreelancer();
    }

    /**
     * «انضم كمستقل» checklist (client file «المربعات الخمسة»): each step => done?
     * The freelancer adds the photo himself; the team reviews everything with the approval.
     */
    public function freelancerChecklist(): array
    {
        return [
            'email' => $this->isVerifiedFreelancer(),
            'whatsapp' => ! empty($this->whatsapp),
            // The freelancer uploads his professional photo himself; the team reviews it with the approval.
            'photo' => ! empty($this->professional_photo),
            // One bio: the profile page's bio or the account page's «النبذة التعريفية» (availability defaults to available).
            'profile' => trim((string) ($this->bio ?: $this->info)) !== '',
            'specializations' => $this->specializations()->exists(),
            'portfolio' => \App\Models\front\UserPortfolio::where('user_id', $this->id)->exists(),
            'approved' => $this->isApprovedFreelancer(),
        ];
    }

    /** The steps the team needs before reviewing a freelancer (works help but are not required). */
    public const READY_STEPS = ['email', 'whatsapp', 'photo', 'profile', 'specializations'];

    /** A pending freelancer who finished every step the team needs to review him. */
    public function isReadyForReview(): bool
    {
        if (! $this->isFreelancer() || $this->freelancer_status !== self::FREELANCER_PENDING) {
            return false;
        }

        return ! in_array(false, array_intersect_key($this->freelancerChecklist(), array_flip(self::READY_STEPS)), true);
    }

    /** Same rule as isReadyForReview(), as a query (admin tab and menu counter). */
    public function scopeReadyForReview($query)
    {
        return $query->where('account_type', self::TYPE_FREELANCER)
            ->where('freelancer_status', self::FREELANCER_PENDING)
            ->where(fn ($q) => $q->whereNotNull('email_verified_at')->orWhereNotNull('admin_verified_at'))
            ->whereNotNull('whatsapp')->where('whatsapp', '!=', '')
            ->whereNotNull('professional_photo')->where('professional_photo', '!=', '')
            ->where(fn ($q) => $q->where(fn ($b) => $b->whereNotNull('bio')->where('bio', '!=', ''))
                ->orWhere(fn ($i) => $i->whereNotNull('info')->where('info', '!=', '')))
            ->whereHas('specializations');
    }

    /** The first time a pending freelancer becomes ready, the admins are told (once). */
    public function noteReadyForReview(): bool
    {
        if ($this->freelancer_ready_at || ! $this->isReadyForReview()) {
            return false;
        }
        $this->forceFill(['freelancer_ready_at' => now()])->save();
        \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
        \Illuminate\Support\Facades\Notification::send(self::activeAdmins()->get(), new \App\Notifications\RequestUpdateNotification(
            \App\Support\Msg::t('admin.freelancer_ready', ['name' => $this->name]),
            '/admin/freelancers/' . $this->id,
            'admin_alerts'
        ));

        return true;
    }

    /** First time every step he controls is done: noted once (event freelancer_profile_completed). */
    public function markFreelancerProfileCompleted(): bool
    {
        // Called after every profile step the freelancer saves: also the moment he may become ready for review.
        $this->noteReadyForReview();
        if (! $this->isFreelancer() || $this->freelancer_profile_completed_at) {
            return false;
        }
        $steps = $this->freelancerChecklist();
        unset($steps['approved']);
        if (in_array(false, $steps, true)) {
            return false;
        }
        $this->forceFill(['freelancer_profile_completed_at' => now()])->save();
        app(\App\Services\Analytics\Tracker::class)->record('freelancer_profile_completed');

        return true;
    }

    /** Freelancers shown in the public directory. */
    public function scopeListedFreelancers($query)
    {
        return $query->where('account_type', self::TYPE_FREELANCER)
            ->where('status', 1)->where('block_status', '!=', 0)
            ->where('freelancer_status', self::FREELANCER_APPROVED)
            ->where(fn ($q) => $q->whereNotNull('email_verified_at')->orWhereNotNull('admin_verified_at'))
            ->whereNotNull('professional_photo');
    }

    /** The admin's professional photo (or the profile image), at the closest generated size. */
    public function avatarUrl(int $size = 256): string
    {
        if ($this->professional_photo) {
            return asset(\App\Support\ImageVariants::url('assets/uploads/freelancers', $this->professional_photo, $size));
        }

        return $this->image ? asset('assets/uploads/users_image/' . $this->image) : asset('assets/uploads/User.png');
    }

    /** Active admin accounts (who receive admin alerts). */
    public function scopeActiveAdmins($query)
    {
        return $query->where('account_type', self::TYPE_ADMIN)->where('status', 1);
    }

    public function isClient(): bool
    {
        return $this->account_type === self::TYPE_CLIENT;
    }

    public function isFreelancer(): bool
    {
        return $this->account_type === self::TYPE_FREELANCER;
    }

    /** Works in «معرض أعمالي» (published ones show on the public profile). */
    public function portfolios()
    {
        return $this->hasMany(\App\Models\front\UserPortfolio::class, 'user_id');
    }

    /** Clients' ratings of this freelancer after completed projects (public). */
    public function receivedRatings()
    {
        return $this->hasMany(ContractRating::class, 'ratee_id')->where('direction', ContractRating::CLIENT_TO_FREELANCER);
    }

    public function freelancerContracts()
    {
        return $this->hasMany(Contract::class, 'freelancer_id');
    }

    public function freelancerOffers()
    {
        return $this->hasMany(RequestOffer::class, 'freelancer_id');
    }

    /**
     * Public counters for freelancer cards/profiles: published works, completed staged projects (contracts)
     * and completed one-payment requests (an accepted offer; staged requests are counted by their contract).
     */
    public function scopeWithFreelancerStats($query)
    {
        return $query->withCount([
            'portfolios as works_count' => fn ($q) => $q->where('status', \App\Models\front\UserPortfolio::STATUS_PUBLISHED),
            'freelancerContracts as staged_done_count' => fn ($q) => $q->where('status', Contract::STATUS_COMPLETED),
            'freelancerOffers as single_done_count' => fn ($q) => $q->where('status', RequestOffer::STATUS_ACCEPTED)
                ->whereHas('request', fn ($r) => $r->where('status', ServiceRequest::STATUS_COMPLETED)
                    ->where(fn ($k) => $k->whereNull('kind')->orWhere('kind', '!=', ServiceRequest::KIND_STAGED))),
        ]);
    }

    /** Completed projects (needs withFreelancerStats()). */
    public function completedProjects(): int
    {
        return (int) ($this->staged_done_count ?? 0) + (int) ($this->single_done_count ?? 0);
    }

    /** Rating as shown to visitors: «4.5» (no trailing zeros), or null for a new freelancer. */
    public function ratingLabel(): ?string
    {
        return (float) $this->rate > 0 ? rtrim(rtrim(number_format((float) $this->rate, 1), '0'), '.') : null;
    }

    /** Sub-categories (services) a freelancer works in. */
    public function specializations()
    {
        return $this->belongsToMany(\App\Models\admin\SubCategory::class, 'freelancer_specializations', 'user_id', 'sub_category_id')
            ->withTimestamps();
    }
}