<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $guarded = [];
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class,'parent_id');
    }

    public function Services()
    {
        return $this->hasMany(Service::class,'sub_cat_id');
    }

    public function serviceForm()
    {
        return $this->hasOne(\App\Models\ServiceForm::class, 'sub_category_id')->whereNull('revision_of_id');
    }

    public function CountServices()
    {
        return $this->Services()->count();
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Where «أطلب الخدمة» goes: the admin's link, or this service's request form. */
    /**
     * Where «أطلب الخدمة» goes: the admin's link, else the request page when the service has a
     * published request form; null when neither exists (the catalog then offers «ساعدوني أختار»).
     */
    public function requestUrl(?bool $hasPublishedForm = null): ?string
    {
        if ($link = \App\Support\SiteContent::url($this->link)) {
            return $link;
        }
        $hasPublishedForm ??= \App\Models\ServiceForm::published()->where('sub_category_id', $this->id)->exists();

        return $hasPublishedForm ? route('requests.create', ['service' => $this->id]) : null;
    }

    /** The uploaded image, when its file exists (public/assets/uploads/service_category). */
    public function imageUrl(): ?string
    {
        return $this->image && file_exists(public_path('assets/uploads/service_category/' . $this->image))
            ? asset('assets/uploads/service_category/' . $this->image) : null;
    }
}
