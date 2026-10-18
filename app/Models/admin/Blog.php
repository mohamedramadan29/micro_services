<?php

namespace App\Models\admin;

use App\Models\User;
use ReflectionFunctionAbstract;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $guarded = [];

    public function Category(){
        return $this->belongsTo(BlogCategory::class,'category_id');
    }
    public function Author(){
        return $this->belongsTo(User::class,'author');
    }
    public function Image(){
        return asset('assets/uploads/Blogs/'.$this->image);
    }

    /** What visitors may read: published (status 1) and its publish date has come. */
    public function scopePublished($query)
    {
        return $query->where('status', 1)->whereDate('publish_date', '<=', now()->toDateString());
    }

    public function isPublished(): bool
    {
        return (int) $this->status === 1 && $this->publish_date && \Illuminate\Support\Carbon::parse($this->publish_date)->startOfDay()->lte(now());
    }

    /** Reading time at ~200 Arabic words a minute. */
    public function readMinutes(): int
    {
        return max(1, (int) ceil(count(preg_split('/\s+/u', trim(strip_tags((string) $this->desc)), -1, PREG_SPLIT_NO_EMPTY)) / 200));
    }

    public function excerpt(int $length = 160): string
    {
        return \Illuminate\Support\Str::limit(trim(strip_tags($this->short_desc ?: html_entity_decode((string) $this->desc))), $length);
    }
}
