<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\User;
use App\Services\Landing\LandingBuilder;
use Illuminate\Http\Request;

/** Public landing pages (client file «تعديلات», item 07); `?preview=1` shows an admin the draft. */
class LandingPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $page = LandingPage::where('slug', $slug)->firstOrFail();
        $preview = $request->boolean('preview') && $request->user()?->account_type === User::TYPE_ADMIN;
        abort_unless($preview || ($page->is_published && $page->content !== null), 404);

        return view('website.landing.show', [
            'page' => $page,
            'preview' => $preview,
            'sections' => LandingBuilder::visible($preview ? $page->working() : $page->content),
        ]);
    }
}
