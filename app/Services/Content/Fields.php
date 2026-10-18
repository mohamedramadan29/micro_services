<?php

namespace App\Services\Content;

use Illuminate\Validation\Rule;

/**
 * Validation and normalising of admin-edited fields, shared by the content
 * areas and the landing-page sections. Field types: text, textarea, link
 * (site path, in-page #anchor or http(s) URL), icon (Bootstrap icon name),
 * image / background (media library id), color, select.
 */
class Fields
{
    /**
     * @param  array<string, array>  $fields  definitions keyed by name
     * @return array{0: array, 1: array} rules and messages
     */
    public static function rules(array $fields, string $prefix = ''): array
    {
        $rules = [];
        $messages = [];
        foreach ($fields as $key => $field) {
            $name = $prefix . $key;
            $required = ! empty($field['required']) ? 'required' : 'nullable';
            $rules[$name] = match ($field['type']) {
                'text' => [$required, 'string', 'max:' . ($field['max'] ?? 255)],
                'textarea' => [$required, 'string', 'max:' . ($field['max'] ?? 2000)],
                // A site path, an anchor on the same page or an http(s) URL; nothing like javascript:.
                'link' => [$required, 'string', 'max:500', 'regex:#^(/(?!/)[^\s]*|\#[A-Za-z0-9_-]+|https?://[^\s]+)$#i'],
                'icon' => [$required, 'string', 'regex:/^bi-[a-z0-9-]+$/'],
                'image', 'background' => [$required, 'integer', Rule::exists('content_media', 'id')],
                'color' => [$required, 'regex:/^#[0-9a-fA-F]{6}$/'],
                'select' => [$required, Rule::in(array_keys($field['options']))],
            };
            $messages[$name . '.required'] = 'حقل «' . $field['label'] . '» مطلوب';
            $messages[$name . '.regex'] = 'قيمة «' . $field['label'] . '» غير صحيحة' . ($field['type'] === 'link' ? ' (ابدأ بـ / أو # أو https://)' : '');
            $messages[$name . '.max'] = '«' . $field['label'] . '» أطول من المسموح' . (isset($field['max']) ? ' (' . $field['max'] . ' حرفًا)' : '');
            $messages[$name . '.exists'] = 'اختر «' . $field['label'] . '» من مكتبة الصور';
        }

        return [$rules, $messages];
    }

    /** Every defined field present (empty string / null when unset), nothing else. */
    public static function normalize(array $fields, array $values): array
    {
        $out = [];
        foreach ($fields as $key => $field) {
            $value = $values[$key] ?? null;
            $out[$key] = in_array($field['type'], ['image', 'background'], true) ? ($value ? (int) $value : null) : (string) ($value ?? '');
        }

        return $out;
    }
}
