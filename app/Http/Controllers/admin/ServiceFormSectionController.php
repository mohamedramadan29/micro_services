<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion;
use App\Models\ServiceFormSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceFormSectionController extends Controller
{
    private const RULES = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    private const MESSAGES = [
        'title.required' => 'من فضلك ادخل عنوان القسم',
        'title.max' => 'عنوان القسم يجب ألا يتجاوز 255 حرفًا',
        'description.max' => 'وصف القسم يجب ألا يتجاوز 1000 حرف',
    ];

    public function store(Request $request, ServiceForm $form)
    {
        $data = $request->validate(self::RULES, self::MESSAGES);

        $form->sections()->create($data + ['sort_order' => (int) $form->sections()->max('sort_order') + 1]);

        return back()->with('success', 'تمت إضافة القسم');
    }

    public function update(Request $request, ServiceFormSection $section)
    {
        $section->update($request->validate(self::RULES, self::MESSAGES));

        return back()->with('success', 'تم تعديل القسم');
    }

    public function duplicate(ServiceFormSection $section)
    {
        $section->duplicate();

        return back()->with('success', 'تم نسخ القسم بأسئلته');
    }

    public function destroy(ServiceFormSection $section)
    {
        $released = DB::transaction(function () use ($section) {
            $questionIds = $section->questions()->pluck('id')->all();
            $released = $this->releaseDependents($section->service_form_id, $questionIds);
            $section->delete();

            return $released;
        });

        $message = 'تم حذف القسم وأسئلته';
        if ($released) {
            $message .= '، وأُلغي شرط الظهور من: ' . implode('، ', $released);
        }

        return back()->with('success', $message);
    }

    /** Clear conditions in other questions that point at the deleted ones. */
    private function releaseDependents(int $formId, array $deletedIds): array
    {
        $released = [];
        $others = ServiceFormQuestion::where('service_form_id', $formId)->whereNotIn('id', $deletedIds)->get();
        foreach ($others as $question) {
            if (in_array($question->condition['question_id'] ?? null, $deletedIds)) {
                $question->update(['condition' => null]);
                $released[] = '«' . $question->label . '»';
            }
        }

        return $released;
    }
}
