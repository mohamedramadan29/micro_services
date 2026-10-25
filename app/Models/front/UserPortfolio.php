<?php

namespace App\Models\front;

use App\Models\admin\SubCategory;
use App\Models\User;
use App\Models\admin\Category;
use Illuminate\Database\Eloquent\Model;

class UserPortfolio extends Model
{
    /** Works are reviewed by the team before they show on the site. */
    public const STATUS_PENDING = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_REJECTED = 2;

    public const STATUSES = [
        self::STATUS_PENDING => 'بانتظار المراجعة',
        self::STATUS_PUBLISHED => 'منشور',
        self::STATUS_REJECTED => 'غير مقبول',
    ];

    protected $table = 'user_portfolios';
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'slug',
        'description',
        'link',
        'image',
        'more_images',
        'tools',
        'category_id',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return (int) $this->status === self::STATUS_PUBLISHED;
    }

    /** Back to the team's review (new work or an edit): the admins are told once per wait. */
    public function sendToReview(): void
    {
        $alreadyWaiting = $this->exists && (int) $this->getOriginal('status') === self::STATUS_PENDING;
        $this->forceFill(['status' => self::STATUS_PENDING, 'review_note' => null])->save();
        \Illuminate\Support\Facades\Cache::forget('admin-menu-badges');
        if ($alreadyWaiting && ! $this->wasRecentlyCreated) {
            return;
        }
        \Illuminate\Support\Facades\Notification::send(User::activeAdmins()->get(), new \App\Notifications\RequestUpdateNotification(
            \App\Support\Msg::t('admin.portfolio_pending', ['name' => (string) $this->User?->name, 'title' => $this->title]),
            '/admin/freelancer-works',
            'admin_alerts'
        ));
    }

    public function Category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSubCategoriesAttribute()
    {
        if (!$this->tools) return collect();

        $ids = array_filter(explode(',', $this->tools));

        return SubCategory::whereIn('id', $ids)->get();
    }

    protected $casts = [
        'more_images' => 'array',
    ];
}
