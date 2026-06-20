<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailList extends Model
{
    protected $fillable = [
        'name',
        'description',
        'total_contacts',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_contacts' => 'integer',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(EmailContact::class, 'email_list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activeContacts()
    {
        return $this->contacts()->where('is_active', true)->whereNull('unsubscribed_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
