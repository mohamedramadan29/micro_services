<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Services\Analytics\Tracker;
use App\Services\Content\ContentManager;
use App\Services\Content\HomeActions;

/** A homepage card click: counted (with consent), then sent to the card's approved destination. */
class HomeCardController extends Controller
{
    public function go(int $card, Tracker $tracker, ContentManager $content)
    {
        $published = $content->items('home.cards')->firstWhere('_id', $card);
        $target = $published ? HomeActions::url($published['action']) : null;
        if (! $target) {
            return redirect()->to(url('/'));
        }
        $tracker->record('home_card_click', ['meta' => ['card' => $card, 'action' => $published['action']]]);

        return redirect()->to($target);
    }
}
