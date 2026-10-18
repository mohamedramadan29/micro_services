<?php

namespace App\Models;

use App\Models\ServiceFormQuestion as Q;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestAnswer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'value' => 'array',
    ];

    /** Keep Arabic readable in the stored JSON so keyword search can match it. */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    /** The answer as one line of text (files are listed separately). */
    public function display(): string
    {
        $value = $this->value;

        return match ($this->type) {
            Q::TYPE_MULTI_CHOICE => implode('، ', (array) $value),
            Q::TYPE_NUMBER => is_array($value) ? $this->rangeText($value) : (string) $value,
            Q::TYPE_RATING => is_array($value) ? ($value['value'] . ' من ' . $value['scale']) : (string) $value,
            Q::TYPE_FILE => '',
            default => (string) (is_array($value) ? implode('، ', $value) : $value),
        };
    }

    private function rangeText(array $value): string
    {
        $unit = ! empty($value['unit']) ? ' ' . $value['unit'] : '';
        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        return match (true) {
            $min !== null && $max !== null => "من {$min} إلى {$max}{$unit}",
            $min !== null => "من {$min}{$unit}",
            default => "حتى {$max}{$unit}",
        };
    }
}
