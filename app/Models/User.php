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
     * The photo is set by the admin while reviewing, so it is the last step with the approval.
     */
    public function freelancerChecklist(): array
    {
        return [
            'email' => $this->isVerifiedFreelancer(),
            'whatsapp' => ! empty($this->whatsapp),
            'profile' => ! empty($this->bio) && ! empty($this->availability),
            'specializations' => $this->specializations()->exists(),
            'portfolio' => \App\Models\front\UserPortfolio::where('user_id', $this->id)->exists(),
            'approved' => $this->isApprovedFreelancer(),
        ];
    }

    /** First time every step he controls is done: noted once (event freelancer_profile_completed). */
    public function markFreelancerProfileCompleted(): bool
    {
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

    /** Sub-categories (services) a freelancer works in. */
    public function specializations()
    {
        return $this->belongsToMany(\App\Models\admin\SubCategory::class, 'freelancer_specializations', 'user_id', 'sub_category_id')
            ->withTimestamps();
    }
}