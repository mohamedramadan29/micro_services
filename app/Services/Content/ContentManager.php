<?php

namespace App\Services\Content;

use App\Models\ContentBlock;
use App\Models\ContentMedia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Reads and edits the admin-managed content (areas in ContentAreas).
 * Visitors see published data (cached); an admin in preview mode sees drafts.
 * Edits never reach visitors until the admin publishes the area.
 */
class ContentManager
{
    public const PREVIEW_SESSION = 'content_preview';

    /** @var array<int, ?ContentMedia> */
    private array $media = [];

    public function previewing(): bool
    {
        return (bool) session(self::PREVIEW_SESSION) && Auth::user()?->account_type === User::TYPE_ADMIN;
    }

    /**
     * Visible items of an area, in order, each with every field of the area
     * (missing ones empty). Before the admin saves anything: the defaults.
     *
     * @return Collection<int, array>
     */
    public function items(string $area): Collection
    {
        $preview = $this->previewing();
        $items = $preview ? $this->load($area, true) : Cache::rememberForever($this->cacheKey($area), fn () => $this->load($area, false)->all());

        return collect($items)->values();
    }

    /** The single block of a non-repeatable area. */
    public function first(string $area): array
    {
        return $this->items($area)->first() ?? $this->blank($area);
    }

    public function mediaUrl($id): ?string
    {
        return $this->mediaItem($id)?->url();
    }

    public function mediaItem($id): ?ContentMedia
    {
        if (! $id) {
            return null;
        }

        return $this->media[(int) $id] ??= ContentMedia::find((int) $id);
    }

    /** Admin: the blocks of an area, creating them from the defaults the first time. */
    public function blocks(string $area): Collection
    {
        $this->ensureSeeded($area);

        return ContentBlock::where('area', $area)->get()
            ->sortBy(fn (ContentBlock $block) => [(int) ($block->working()['_order'] ?? 0), $block->id])
            ->values();
    }

    public function ensureSeeded(string $area): void
    {
        if (ContentBlock::where('area', $area)->exists()) {
            return;
        }
        foreach (ContentAreas::get($area)['defaults'] as $index => $fields) {
            ContentBlock::create(['area' => $area, 'data' => $this->normalize($area, $fields) + ['_visible' => true, '_order' => $index + 1]]);
        }
        $this->forget($area);
    }

    /** Validate and save the admin's edits of one block as a draft. */
    public function saveDraft(ContentBlock $block, array $input): void
    {
        if ($block->isDeletedInDraft()) {
            throw new RuntimeException('هذا العنصر محدد للحذف عند النشر؛ تجاهل التعديلات أولًا لتعديله.');
        }
        $fields = $this->validate($block->area, $input);
        $working = $block->working();
        $block->update([
            'draft' => $fields + ['_visible' => $working['_visible'] ?? true, '_order' => $working['_order'] ?? $this->nextOrder($block->area)],
            'updated_by' => Auth::id(),
        ]);
    }

    public function add(string $area): ContentBlock
    {
        $definition = ContentAreas::get($area);
        if (empty($definition['repeatable'])) {
            throw new RuntimeException('لا يمكن إضافة عناصر لهذا القسم');
        }
        $count = $this->blocks($area)->reject(fn (ContentBlock $block) => $block->isDeletedInDraft())->count();
        if ($count >= ($definition['max'] ?? PHP_INT_MAX)) {
            throw new RuntimeException('وصلت للحد الأقصى من العناصر في هذا القسم (' . $definition['max'] . ')');
        }

        // New items start as a hidden draft: nothing reaches visitors before publishing.
        return ContentBlock::create([
            'area' => $area,
            'draft' => $this->blank($area) + ['_visible' => true, '_order' => $this->nextOrder($area)],
            'updated_by' => Auth::id(),
        ]);
    }

    /** Delete on publish (an item never published is removed at once). */
    public function remove(ContentBlock $block): void
    {
        if (empty(ContentAreas::get($block->area)['repeatable'])) {
            throw new RuntimeException('هذا القسم لا يُحذف، يمكنك إخفاؤه فقط');
        }
        $block->isNew() ? $block->delete() : $block->update(['draft' => ['_deleted' => true], 'updated_by' => Auth::id()]);
    }

    public function toggle(ContentBlock $block): void
    {
        $working = $block->working();
        $block->update(['draft' => ['_visible' => ! ($working['_visible'] ?? true)] + $working, 'updated_by' => Auth::id()]);
    }

    /** Swap with the previous (-1) or next (+1) item. */
    public function move(ContentBlock $block, int $direction): void
    {
        $blocks = $this->blocks($block->area)->reject(fn (ContentBlock $b) => $b->isDeletedInDraft())->values()->all();
        $index = collect($blocks)->search(fn (ContentBlock $b) => $b->id === $block->id);
        if ($index === false) {
            return;
        }
        // Items shown in groups (homepage cards) move among their own group only.
        $grouped = array_key_exists('group', ContentAreas::get($block->area)['fields']);
        $group = $block->working()['group'] ?? null;
        $target = $index;
        do {
            $target += $direction < 0 ? -1 : 1;
        } while ($grouped && isset($blocks[$target]) && ($blocks[$target]->working()['group'] ?? null) !== $group);
        if (! isset($blocks[$target])) {
            return;
        }

        [$blocks[$index], $blocks[$target]] = [$blocks[$target], $blocks[$index]];
        // Renumber the whole list so equal or missing orders cannot get stuck.
        foreach ($blocks as $position => $b) {
            $this->setOrder($b, $position + 1);
        }
    }

    public function hasDrafts(string $area): bool
    {
        return ContentBlock::where('area', $area)->whereNotNull('draft')->exists();
    }

    /**
     * Snapshot of what visitors see on a page (every published block, hidden ones too),
     * kept after each publish for the change log and rollback.
     */
    public function snapshot(string $page): array
    {
        return collect(ContentAreas::PAGES[$page]['areas'])->mapWithKeys(fn ($area) => [$area => ContentBlock::where('area', $area)->whereNotNull('data')->orderBy('id')->get()
            ->map(fn (ContentBlock $block) => ['id' => $block->id, 'data' => $block->data])->all()])->all();
    }

    public function record(string $page, string $action = 'publish'): \App\Models\ContentRevision
    {
        return \App\Models\ContentRevision::create(['page' => $page, 'snapshot' => $this->snapshot($page), 'user_id' => Auth::id(), 'action' => $action]);
    }

    /** Put a page back as it was in a revision (published at once; current drafts are dropped). Block ids are kept where they still exist. */
    public function rollback(\App\Models\ContentRevision $revision): void
    {
        DB::transaction(function () use ($revision) {
            foreach ($revision->snapshot as $area => $items) {
                if (! array_key_exists($area, ContentAreas::all())) {
                    continue;
                }
                $keep = [];
                foreach ($items as $item) {
                    $block = ContentBlock::where('area', $area)->find($item['id']);
                    $block ? $block->update(['data' => $item['data'], 'draft' => null, 'updated_by' => Auth::id()])
                        : $block = ContentBlock::create(['area' => $area, 'data' => $item['data'], 'updated_by' => Auth::id()]);
                    $keep[] = $block->id;
                }
                ContentBlock::where('area', $area)->whereNotIn('id', $keep)->delete();
                $this->forget($area);
            }
        });
        $this->record($revision->page, 'rollback');
    }

    /** Make the drafts of the given areas what visitors see. */
    public function publish(array $areas): int
    {
        $count = 0;
        DB::transaction(function () use ($areas, &$count) {
            foreach (ContentBlock::whereIn('area', $areas)->whereNotNull('draft')->lockForUpdate()->get() as $block) {
                $block->isDeletedInDraft()
                    ? $block->delete()
                    : $block->update(['data' => $block->draft, 'draft' => null]);
                $count++;
            }
        });
        array_map(fn ($area) => $this->forget($area), $areas);

        return $count;
    }

    /** Throw away unpublished edits (and never-published items). */
    public function discard(array $areas): void
    {
        ContentBlock::whereIn('area', $areas)->whereNull('data')->delete();
        ContentBlock::whereIn('area', $areas)->update(['draft' => null]);
    }

    public function forget(string $area): void
    {
        Cache::forget($this->cacheKey($area));
    }

    private function load(string $area, bool $drafts): Collection
    {
        $blocks = ContentBlock::where('area', $area)->get();
        if ($blocks->isEmpty()) {
            return collect(ContentAreas::get($area)['defaults'])->map(fn ($fields) => $this->normalize($area, $fields) + ['_id' => null]);
        }

        // `_id` = the block id, a stable key for a repeatable item (e.g. homepage card click tracking).
        return $blocks
            ->map(fn (ContentBlock $block) => ($fields = $drafts ? ($block->isDeletedInDraft() ? null : $block->working()) : $block->data) ? $fields + ['_id' => $block->id] : null)
            ->filter(fn ($fields) => $fields && ($fields['_visible'] ?? true))
            ->sortBy(fn ($fields) => (int) ($fields['_order'] ?? 0))
            ->map(fn ($fields) => $this->normalize($area, $fields) + ['_id' => $fields['_id']])
            ->values();
    }

    private function validate(string $area, array $input): array
    {
        [$rules, $messages] = Fields::rules(ContentAreas::get($area)['fields']);

        return $this->normalize($area, Validator::make($input, $rules, $messages)->validate());
    }

    /** Every field of the area present (empty string / null when unset). */
    private function normalize(string $area, array $fields): array
    {
        // A field added to a single-block area after the admin saved it takes its default
        // text instead of showing empty (an empty value the admin saved stays empty).
        $definition = ContentAreas::get($area);
        if (empty($definition['repeatable']) && $fields && ($defaults = $definition['defaults'][0] ?? null)) {
            $fields += array_intersect_key($defaults, array_diff_key($definition['fields'], $fields));
        }

        return Fields::normalize($definition['fields'], $fields) + array_intersect_key($fields, ['_visible' => 1, '_order' => 1]);
    }

    private function blank(string $area): array
    {
        return $this->normalize($area, []);
    }

    private function nextOrder(string $area): int
    {
        return (int) ContentBlock::where('area', $area)->get()->max(fn (ContentBlock $b) => (int) ($b->working()['_order'] ?? 0)) + 1;
    }

    private function setOrder(ContentBlock $block, int $order): void
    {
        $working = $block->working();
        if ((int) ($working['_order'] ?? 0) !== $order) {
            $block->update(['draft' => ['_order' => $order] + $working]);
        }
    }

    private function cacheKey(string $area): string
    {
        // The field list is part of the key: a field added in a release never meets an old cached copy.
        return 'content.' . $area . '.' . substr(md5(implode(',', array_keys(ContentAreas::get($area)['fields']))), 0, 8);
    }
}
