<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailContact extends Model
{
    protected $fillable = [
        'email_list_id',
        'email',
        'name',
        'phone',
        'custom_fields',
        'is_active',
        'unsubscribed_at',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_active' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'email_list_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('unsubscribed_at');
    }

    public function scopeApplyFilters($query, ?array $filters)
    {
        if (empty($filters)) {
            return $query;
        }

        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? '';

            if (!$field) continue;

            $fieldKey = 'custom_fields->' . $field;

            switch ($operator) {
                case '=':
                    $query->where($fieldKey, $value);
                    break;
                case '!=':
                    $query->where($fieldKey, '!=', $value);
                    break;
                case '>':
                    $query->where($fieldKey, '>', $value);
                    break;
                case '<':
                    $query->where($fieldKey, '<', $value);
                    break;
                case '>=':
                    $query->where($fieldKey, '>=', $value);
                    break;
                case '<=':
                    $query->where($fieldKey, '<=', $value);
                    break;
                case 'contains':
                    $query->where($fieldKey, 'LIKE', "%{$value}%");
                    break;
                case 'not_contains':
                    $query->where($fieldKey, 'NOT LIKE', "%{$value}%");
                    break;
                case 'starts_with':
                    $query->where($fieldKey, 'LIKE', "{$value}%");
                    break;
                case 'ends_with':
                    $query->where($fieldKey, 'LIKE', "%{$value}");
                    break;
                case 'is_empty':
                    $query->whereNull($fieldKey);
                    break;
                case 'is_not_empty':
                    $query->whereNotNull($fieldKey);
                    break;
            }
        }

        return $query;
    }
}
