<?php

namespace App\Services\Landing;

use App\Models\LandingPage;
use App\Services\Content\Fields;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Edits the sections of a landing page. Every change goes to the draft;
 * visitors keep seeing `content` until the admin publishes.
 * A section = {id, type, visible, fields: {...}, items: [{...}]}.
 */
class LandingBuilder
{
    public const MAX_SECTIONS = 30;

    /** A new page, from the starter template or empty, unpublished. */
    public function create(array $attributes, bool $starter): LandingPage
    {
        $sections = $starter ? array_map(fn ($s) => $this->make($s['type'], $s['fields'] ?? [], $s['items'] ?? null), LandingSections::starter()) : [];

        return LandingPage::create($attributes + ['draft' => $sections, 'updated_by' => Auth::id()]);
    }

    public function add(LandingPage $page, string $type): array
    {
        if (! LandingSections::exists($type)) {
            throw new RuntimeException('نوع القسم غير معروف');
        }
        $sections = $page->working();
        if (count($sections) >= self::MAX_SECTIONS) {
            throw new RuntimeException('وصلت للحد الأقصى من الأقسام (' . self::MAX_SECTIONS . ')');
        }
        $section = $this->make($type);
        $sections[] = $section;
        $this->save($page, $sections);

        return $section;
    }

    /** Validate and store the admin's edits of one section. */
    public function update(LandingPage $page, string $id, array $input): void
    {
        $sections = $page->working();
        $index = $this->find($sections, $id);
        $type = $sections[$index]['type'];
        $definition = LandingSections::get($type);
        $fields = LandingSections::fields($type);

        [$rules, $messages] = Fields::rules($fields, 'fields.');
        $rules['fields.anchor'][] = 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/';
        $messages['fields.anchor.regex'] = 'معرّف القسم يكون بالإنجليزية بدون مسافات، مثل steps';
        $items = isset($definition['items']) ? array_values(array_filter((array) ($input['items'] ?? []), 'is_array')) : [];
        if (isset($definition['items'])) {
            $rules['items'] = ['array', 'max:' . $definition['items']['max']];
            $messages['items.max'] = 'الحد الأقصى ' . $definition['items']['max'] . ' عنصر في هذا القسم';
            [$itemRules, $itemMessages] = Fields::rules($definition['items']['fields'], 'items.*.');
            $rules += $itemRules;
            $messages += $itemMessages;
        }
        $data = Validator::make(['fields' => (array) ($input['fields'] ?? []), 'items' => $items], $rules, $messages)->validate();

        $sections[$index]['fields'] = Fields::normalize($fields, $data['fields'] ?? []);
        if (isset($definition['items'])) {
            $sections[$index]['items'] = array_map(fn ($item) => Fields::normalize($definition['items']['fields'], $item), $data['items'] ?? []);
        }
        $this->save($page, $sections);
    }

    public function move(LandingPage $page, string $id, int $direction): void
    {
        $sections = $page->working();
        $index = $this->find($sections, $id);
        $target = $index + ($direction < 0 ? -1 : 1);
        if (! isset($sections[$target])) {
            return;
        }
        [$sections[$index], $sections[$target]] = [$sections[$target], $sections[$index]];
        $this->save($page, $sections);
    }

    public function toggle(LandingPage $page, string $id): void
    {
        $sections = $page->working();
        $index = $this->find($sections, $id);
        $sections[$index]['visible'] = ! ($sections[$index]['visible'] ?? true);
        $this->save($page, $sections);
    }

    public function duplicate(LandingPage $page, string $id): void
    {
        $sections = $page->working();
        if (count($sections) >= self::MAX_SECTIONS) {
            throw new RuntimeException('وصلت للحد الأقصى من الأقسام (' . self::MAX_SECTIONS . ')');
        }
        $index = $this->find($sections, $id);
        $copy = ['id' => $this->newId()] + $sections[$index];
        // An anchor must stay unique on the page.
        $copy['fields']['anchor'] = '';
        array_splice($sections, $index + 1, 0, [$copy]);
        $this->save($page, $sections);
    }

    public function remove(LandingPage $page, string $id): void
    {
        $sections = $page->working();
        array_splice($sections, $this->find($sections, $id), 1);
        $this->save($page, $sections);
    }

    /** The draft becomes what visitors see; the page goes live. */
    public function publish(LandingPage $page): void
    {
        $page->update([
            'content' => $page->working(),
            'draft' => null,
            'is_published' => true,
            'published_at' => $page->published_at ?? now(),
            'updated_by' => Auth::id(),
        ]);
    }

    /** Back to what is published. */
    public function discard(LandingPage $page): void
    {
        if ($page->content === null) {
            throw new RuntimeException('الصفحة لم تُنشر بعد، فلا توجد نسخة منشورة للرجوع إليها.');
        }
        $page->update(['draft' => null]);
    }

    /** Sections visitors (or a previewing admin) see: visible ones only, every field present. */
    public static function visible(array $sections): array
    {
        return array_values(array_filter(array_map(function ($section) {
            if (! LandingSections::exists($section['type'] ?? '') || ! ($section['visible'] ?? true)) {
                return null;
            }
            $definition = LandingSections::get($section['type']);
            $section['fields'] = Fields::normalize(LandingSections::fields($section['type']), $section['fields'] ?? []);
            $section['items'] = isset($definition['items'])
                ? array_map(fn ($item) => Fields::normalize($definition['items']['fields'], $item), $section['items'] ?? [])
                : [];

            return $section;
        }, $sections)));
    }

    private function make(string $type, array $fields = [], ?array $items = null): array
    {
        $definition = LandingSections::get($type);

        return [
            'id' => $this->newId(),
            'type' => $type,
            'visible' => true,
            'fields' => Fields::normalize(LandingSections::fields($type), $fields + ($definition['defaults'] ?? [])),
            'items' => isset($definition['items'])
                ? array_map(fn ($item) => Fields::normalize($definition['items']['fields'], $item), $items ?? $definition['default_items'] ?? [])
                : [],
        ];
    }

    private function find(array $sections, string $id): int
    {
        foreach ($sections as $index => $section) {
            if (($section['id'] ?? null) === $id) {
                return $index;
            }
        }

        throw new RuntimeException('القسم غير موجود، ربما حُذف. حدّث الصفحة.');
    }

    private function save(LandingPage $page, array $sections): void
    {
        $page->update(['draft' => array_values($sections), 'updated_by' => Auth::id()]);
    }

    private function newId(): string
    {
        return Str::lower(Str::random(10));
    }
}
