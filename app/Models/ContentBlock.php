<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A managed piece of site content (see App\Services\Content\ContentManager). */
class ContentBlock extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'draft' => 'array',
    ];

    /** The fields as the admin is editing them (draft if any, else published). */
    public function working(): array
    {
        return $this->draft ?? $this->data ?? [];
    }

    public function hasDraft(): bool
    {
        return $this->draft !== null;
    }

    public function isDeletedInDraft(): bool
    {
        return (bool) ($this->draft['_deleted'] ?? false);
    }

    public function isNew(): bool
    {
        return $this->data === null;
    }
}
