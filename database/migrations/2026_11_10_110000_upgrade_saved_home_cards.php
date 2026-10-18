<?php

use App\Models\ContentBlock;
use App\Services\Content\ContentAreas;
use App\Services\Content\ContentManager;
use Illuminate\Database\Migrations\Migration;

/**
 * Homepage cards saved before the five-card layout (client file «المربعات الخمسة»): the typed
 * link becomes an approved destination, every card joins the first group, and the two new
 * cards are added once. A site that never saved the cards uses the new defaults already.
 */
return new class extends Migration
{
    private const LINKS = ['/request' => 'request', '/site-test' => 'diagnose', '/projects/diagnose' => 'diagnose', '/staged-project' => 'staged', '/projects/new' => 'post_project', '/freelancers/join' => 'freelancer_join', '/services' => 'services'];

    public function up(): void
    {
        $blocks = ContentBlock::where('area', 'home.cards')->get();
        if ($blocks->isEmpty()) {
            return;
        }
        $upgrade = function (?array $fields): ?array {
            if ($fields === null || isset($fields['action']) || ! empty($fields['_deleted'])) {
                return $fields;
            }
            $link = rtrim((string) ($fields['link'] ?? ''), '/') ?: (($fields['link'] ?? '') === '/' ? '/' : '');

            return $fields + ['action' => self::LINKS[$link] ?? 'none', 'group' => 'primary', 'audience' => 'all'];
        };
        foreach ($blocks as $block) {
            $block->update(['data' => $upgrade($block->data), 'draft' => $upgrade($block->draft)]);
        }

        $actions = $blocks->map(fn ($b) => $b->fresh()->working()['action'] ?? null)->all();
        $order = (int) $blocks->max(fn ($b) => $b->working()['_order'] ?? 0);
        foreach (ContentAreas::get('home.cards')['defaults'] as $default) {
            if (in_array($default['action'], ['post_project', 'freelancer_join'], true) && ! in_array($default['action'], $actions, true)) {
                $fields = array_map(fn () => '', ContentAreas::get('home.cards')['fields']);
                ContentBlock::create(['area' => 'home.cards', 'data' => array_merge($fields, ['image' => null, 'background' => null], $default, ['_visible' => true, '_order' => ++$order])]);
            }
        }
        app(ContentManager::class)->forget('home.cards');
    }

    public function down(): void
    {
        // Data upgrade only.
    }
};
