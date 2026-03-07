<?php

namespace App\Models\front;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $guarded = [];

    protected $fillable = [
        'client_name',
        'client_position',
        'client_company',
        'client_image',
        'content',
        'rating',
        'is_featured',
        'is_active',
        'sort_order',
        'is_approved'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function getRatingStarsAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '<i class="bi bi-star-fill text-warning"></i>';
            } else {
                $stars .= '<i class="bi bi-star text-warning"></i>';
            }
        }
        return $stars;
    }
}
