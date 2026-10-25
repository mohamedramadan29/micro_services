<?php

namespace App\Models\admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** A ready-made service a freelancer sells (bought from its page or the cart, paid at EasyKash). */
class Service extends Model
{
    use HasFactory;
    protected $guarded = [];

    /** Reviewed by the team before it shows on the site or can be bought. */
    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_REJECTED = 2;

    public const STATUSES = [
        self::STATUS_PENDING => 'بانتظار المراجعة',
        self::STATUS_ACTIVE => 'منشورة',
        self::STATUS_REJECTED => 'غير مقبولة',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // get the category name
    public function category()
    {
        return $this->belongsTo(Category::class,'cat_id');
    }
    public function subcategory()
    {

        return $this->belongsTo(SubCategory::class,'sub_cat_id');

    }

    // Get user
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');

    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function url(): string
    {
        return url('service/' . $this->id . '-' . $this->slug);
    }

    /** New or edited: back to the team's review; the admins are told once per wait. */
    public function sendToReview(): void
    {
        $alreadyWaiting = ! $this->wasRecentlyCreated && (int) $this->getOriginal('status') === self::STATUS_PENDING;
        $this->forceFill(['status' => self::STATUS_PENDING, 'review_note' => null])->save();
        \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
        if ($alreadyWaiting) {
            return;
        }
        \Illuminate\Support\Facades\Notification::send(User::activeAdmins()->get(), new \App\Notifications\RequestUpdateNotification(
            \App\Support\Msg::t('admin.service_pending', ['name' => (string) $this->user?->name, 'title' => $this->name]),
            '/admin/services?tab=pending',
            'admin_alerts'
        ));
    }
}
