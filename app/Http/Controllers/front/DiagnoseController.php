<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Services\Analytics\Tracker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * «شخّص مشروعك مجانًا» (client file «المربعات الخمسة», user decision 2026-10-16): one page
 * that joins both paths — a visitor with a website goes to the free site check; one who is
 * not sure which service he needs answers a few questions (goal = service group, closest
 * service, a short description), sees a summary, and continues to a ready request, the
 * direct publish form or «ساعدوني أختار». Nothing is stored but the summary in the session.
 */
class DiagnoseController extends Controller
{
    public const SESSION_KEY = 'diagnosis';

    public const URGENCY = ['now' => 'في أقرب وقت', 'month' => 'خلال شهر', 'later' => 'أستكشف الآن وأقرر لاحقًا'];

    public function show()
    {
        return view('website.diagnose.show', ['groups' => $this->groups(), 'urgency' => self::URGENCY]);
    }

    public function store(Request $request, Tracker $tracker)
    {
        $data = $request->validate([
            'group' => ['required', 'integer', Rule::exists('categories', 'id')->where('status', 1)],
            'service' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')->where('status', 1)],
            'description' => 'nullable|string|max:2000',
            'urgency' => ['nullable', Rule::in(array_keys(self::URGENCY))],
        ], ['group.required' => 'اختر هدفك الأساسي']);

        $service = isset($data['service']) ? SubCategory::where('parent_id', $data['group'])->find($data['service']) : null;
        $request->session()->put(self::SESSION_KEY, [
            'group' => (int) $data['group'],
            'service' => $service?->id,
            'description' => trim((string) ($data['description'] ?? '')),
            'urgency' => $data['urgency'] ?? null,
        ]);
        $tracker->record('diagnosis_completed', ['sub_category_id' => $service?->id, 'meta' => ['group' => (int) $data['group'], 'unsure' => ! $service]]);

        return redirect()->route('diagnose.result');
    }

    public function result(Request $request)
    {
        $diagnosis = $request->session()->get(self::SESSION_KEY);
        if (! $diagnosis) {
            return redirect()->route('diagnose');
        }
        $group = Category::find($diagnosis['group']);
        $service = $diagnosis['service'] ? SubCategory::find($diagnosis['service']) : null;
        $hasForm = $service && ServiceForm::published()->where('sub_category_id', $service->id)->exists();

        return view('website.diagnose.result', [
            'diagnosis' => $diagnosis,
            'group' => $group,
            'service' => $service,
            'requestUrl' => $service?->requestUrl($hasForm),
            'urgency' => self::URGENCY,
        ]);
    }

    /** The text a request or project starts with after a diagnosis (prefilled, editable). */
    public static function summaryText(array $diagnosis): string
    {
        $lines = [];
        if ($diagnosis['description'] ?? '') {
            $lines[] = $diagnosis['description'];
        }
        if (! empty($diagnosis['urgency'])) {
            $lines[] = 'التوقيت: ' . (self::URGENCY[$diagnosis['urgency']] ?? '');
        }

        return implode("\n", $lines);
    }

    private function groups()
    {
        return Category::where('status', 1)
            ->with(['subCategories' => fn ($q) => $q->where('status', 1)->orderBy('sort_order')->orderBy('name')])
            ->ordered()->get()
            ->filter(fn ($group) => $group->subCategories->isNotEmpty())->values();
    }
}
