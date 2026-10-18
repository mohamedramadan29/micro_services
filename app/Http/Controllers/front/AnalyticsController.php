<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsVisit;
use App\Models\OnlinePayment;
use App\Services\Analytics\Tracker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Consent, the «بدء النموذج» beacon and the optional «why didn't you finish?» answer. */
class AnalyticsController extends Controller
{
    public const ABANDON_REASONS = [
        'price' => 'السعر أعلى من المتوقع',
        'payment_method' => 'طريقة الدفع غير مناسبة',
        'technical' => 'مشكلة تقنية في الدفع',
        'later' => 'أريد التفكير أو المقارنة أولًا',
        'other' => 'سبب آخر',
    ];

    public function __construct(private Tracker $tracker)
    {
    }

    public function consent(Request $request)
    {
        $accepted = $request->boolean('accept');
        $response = back(302, [], url('/'))->withCookie(cookie()->forever(Tracker::CONSENT_COOKIE, $accepted ? '1' : '0'));
        if (! $accepted) {
            $request->session()->forget([Tracker::SESSION_KEY, Tracker::ENTRY_KEY]);
            $response->withCookie(cookie()->forget(Tracker::VISITOR_COOKIE));
        }

        return $response;
    }

    /** Sent by the page once the visitor starts filling a request/check/order form. */
    public function formStart(Request $request)
    {
        $data = $request->validate([
            'service' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')],
            'form' => 'nullable|string|max:40',
        ]);
        $visit = $this->tracker->visit();
        // The direct «انشر مشروعك» form has its own event (client file «المربعات الخمسة»).
        $type = ($data['form'] ?? null) === 'project' ? 'project_form_start' : 'form_start';
        if ($visit && ! $visit->events()->where('type', $type)->where('sub_category_id', $data['service'] ?? null)->exists()) {
            Tracker::recordOn($visit, $type, [
                'path' => mb_substr((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), 0, 500) ?: null,
                'sub_category_id' => $data['service'] ?? null,
                'meta' => array_filter(['form' => $data['form'] ?? null]),
            ]);
        }

        return response()->noContent();
    }

    /** Optional answer after an unfinished payment; shown in the admin only as written (else «السبب غير معروف»). */
    public function abandonReason(Request $request)
    {
        $data = $request->validate([
            'payment' => 'required|integer',
            'reason' => ['required', Rule::in(array_keys(self::ABANDON_REASONS))],
            'note' => 'nullable|string|max:500',
        ]);
        $payment = OnlinePayment::find($data['payment']);
        $visit = $payment?->visit_id ? AnalyticsVisit::find($payment->visit_id) : null;
        // Only the visitor who started that payment can answer for it.
        if ($visit && $visit->visitor_id === $request->cookie(Tracker::VISITOR_COOKIE)
            && ! $visit->events()->where('type', 'abandon_reason')->where('meta->payment', $payment->id)->exists()) {
            Tracker::recordOn($visit, 'abandon_reason', ['meta' => ['payment' => $payment->id, 'reason' => $data['reason'], 'note' => $data['note'] ?? null]]);
        }

        return back(302, [], url('/'))->with('Success_message', 'شكرًا لك، رأيك يساعدنا على تحسين الموقع.');
    }
}
