<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\OfferMessage;
use App\Models\RequestOffer;
use App\Models\RequestOfferFile;
use App\Notifications\OfferMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/** Things both parties of an offer share: its conversation and files. */
class OfferController extends Controller
{
    public function chat(RequestOffer $offer)
    {
        $this->authorizeParty($offer);
        $offer->load(['request', 'freelancer', 'files']);

        $isClient = (int) Auth::id() === (int) $offer->request->user_id;
        if ($isClient) {
            $offer->markViewed();
        }
        $offer->messages()->whereNull('read_at')->where('sender_id', '!=', Auth::id())->update(['read_at' => now()]);
        $messages = $offer->messages()->with('sender')->get();

        return view('website.offers.chat', compact('offer', 'messages', 'isClient'));
    }

    public function sendMessage(Request $request, RequestOffer $offer)
    {
        $this->authorizeParty($offer);
        if (! $offer->isActive() && $offer->status !== RequestOffer::STATUS_ACCEPTED) {
            return back()->withErrors('هذه المحادثة مغلقة لأن العرض ' . $offer->statusLabel());
        }

        $data = $request->validate(
            ['body' => 'required|string|max:3000'],
            ['body.required' => 'اكتب رسالتك أولًا', 'body.max' => 'الرسالة يجب ألا تتجاوز 3000 حرف']
        );

        $message = $offer->messages()->create([
            'service_request_id' => $offer->service_request_id,
            'sender_id' => Auth::id(),
            'body' => $data['body'],
        ]);
        if (in_array($offer->status, [RequestOffer::STATUS_NEW, RequestOffer::STATUS_VIEWED], true)) {
            $offer->update(['status' => RequestOffer::STATUS_IN_DISCUSSION, 'viewed_at' => $offer->viewed_at ?? now()]);
        }

        $recipient = (int) Auth::id() === (int) $offer->freelancer_id ? $offer->request->user : $offer->freelancer;
        $recipient?->notify(new OfferMessageNotification($message));

        return redirect()->route('offers.chat', $offer)->withFragment('message-' . $message->id);
    }

    /** The client keeps an offer aside to compare later. */
    public function toggleSave(RequestOffer $offer)
    {
        abort_unless((int) Auth::id() === (int) $offer->request->user_id, 403);
        if (! $offer->isActive()) {
            return back();
        }

        $offer->update(['is_saved' => ! $offer->is_saved]);

        return back()->with('Success_message', $offer->is_saved ? 'تم حفظ العرض في قائمة «محفوظة»' : 'تمت إزالة العرض من المحفوظة');
    }

    public function file(RequestOffer $offer, RequestOfferFile $file)
    {
        $this->authorizeParty($offer);
        abort_unless((int) $file->request_offer_id === (int) $offer->id, 404);
        abort_unless(Storage::disk(RequestOfferFile::DISK)->exists($file->path), 404);

        return Storage::disk(RequestOfferFile::DISK)->download($file->path, $file->original_name);
    }

    private function authorizeParty(RequestOffer $offer): void
    {
        abort_unless($offer->involves(Auth::user()), 403);
        // A client may only reach offers once the freelancer actually sent them.
        abort_if((int) Auth::id() !== (int) $offer->freelancer_id && $offer->status === RequestOffer::STATUS_WITHDRAWN, 404);
    }
}
