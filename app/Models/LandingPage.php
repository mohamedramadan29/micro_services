<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A landing page: settings + a list of sections. `content` is what visitors
 * see, `draft` the admin's unpublished edits (see App\Services\Landing).
 */
class LandingPage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
        'draft' => 'array',
        'show_site_nav' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** The sections being edited: the draft, else what is published. */
    public function working(): array
    {
        return $this->draft ?? $this->content ?? [];
    }

    public function hasDraft(): bool
    {
        return $this->draft !== null;
    }

    public function url(): string
    {
        return route('landing.show', $this->slug);
    }

    public function ogImage()
    {
        return $this->belongsTo(ContentMedia::class, 'og_image_id');
    }
}
