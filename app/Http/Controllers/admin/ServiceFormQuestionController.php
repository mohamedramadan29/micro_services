<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceFormQuestionController extends Controller
{
    /** File extensions the admin may allow (no svg/html/executables). */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'mp4', 'mov', 'psd', 'ai'];

    public function create(Request $request, ServiceForm $form)
    {
        $question = new Question([
            'service_form_section_id' => $request->integer('section') ?: $form->sections()->value('id'),
            'type' => Question::TYPE_SHORT_TEXT,
        ]);

        return $this->editor($form, $question);
    }

    public function store(Request $request, ServiceForm $form)
    {
        $attributes = $this->validated($request, $form);
        $attributes['sort_order'] = (int) Question::where('service_form_section_id', $attributes['service_form_section_id'])->max('sort_order') + 1;

        $form->questions()->create($attributes);

        return redirect()->route('admin.request-forms.builder', $form)->with('success', 'تمت إضافة السؤال');
    }

    public function edit(Question $question)
    {
        return $this->editor($question->form, $question);
    }

    public function update(Request $request, Question $question)
    {
        $form = $question->form;
        $attributes = $this->validated($request, $form, $question);

        if ($attributes['service_form_section_id'] != $question->service_form_section_id) {
            $attributes['sort_order'] = (int) Question::where('service_form_section_id', $attributes['service_form_section_id'])->max('sort_order') + 1;
        }

        $question->update($attributes);

        return redirect()->route('admin.request-forms.builder', $form)->with('success', 'تم تعديل السؤال');
    }

    public function duplicate(Question $question)
    {
        $question->duplicate();

        return back()->with('success', 'تم نسخ السؤال');
    }

    public function destroy(Question $question)
    {
        $released = DB::transaction(function () use ($question) {
            $names = $question->dependents()->map(function ($dependent) {
                $dependent->update(['condition' => null]);

                return '«' . $dependent->label . '»';
            })->all();
            $question->delete();

            return $names;
        });

        $message = 'تم حذف السؤال';
        if ($released) {
            $message .= '، وأُلغي شرط الظهور من: ' . implode('، ', $released);
        }

        return back()->with('success', $message);
    }

    private function editor(ServiceForm $form, Question $question)
    {
        $form->load(['subCategory', 'sections.questions']);

        // Questions this one may depend on, with the values it can match.
        $conditionSources = $form->sections->flatMap->questions
            ->filter(fn ($candidate) => $candidate->canBeConditionSource() && $candidate->id !== $question->id)
            ->filter(fn ($candidate) => ! $question->exists || ! $question->wouldCreateConditionLoop($candidate->id))
            ->map(fn ($candidate) => ['id' => $candidate->id, 'label' => $candidate->label, 'values' => $candidate->choiceValues()])
            ->values();

        $usedFilterKeys = $form->sections->flatMap->questions
            ->where('id', '!=', $question->id)
            ->pluck('filter_key')->filter()->values()->all();

        return view('admin.request-forms.question', compact('form', 'question', 'conditionSources', 'usedFilterKeys'));
    }

    /** Validate the editor input and turn it into model attributes. */
    private function validated(Request $request, ServiceForm $form, ?Question $question = null): array
    {
        $type = $request->input('type');
        $options = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('options_text')))
            ->map(fn ($line) => trim($line))->filter(fn ($line) => $line !== '')->values();
        $request->merge(['options_list' => $options->all()]);

        $rules = [
            'service_form_section_id' => ['required', Rule::exists('service_form_sections', 'id')->where('service_form_id', $form->id)],
            'type' => ['required', Rule::in(array_keys(Question::TYPES))],
            'label' => 'required|string|max:255',
            'help_text' => 'nullable|string|max:1000',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'filter_key' => ['nullable', Rule::in(array_keys(Question::FILTER_KEYS))],
            'condition_question_id' => ['nullable', 'integer'],
            'condition_values' => 'nullable|array',
            'condition_values.*' => 'string',
            'condition_negate' => 'nullable|boolean',
        ];

        if (in_array($type, Question::OPTION_TYPES, true)) {
            $rules['options_list'] = 'array|min:2|max:50';
            $rules['options_list.*'] = 'string|max:150|distinct';
        }

        match ($type) {
            Question::TYPE_SHORT_TEXT, Question::TYPE_LONG_TEXT => $rules += [
                'settings.min_length' => 'nullable|integer|min:0|lte:settings.max_length',
                'settings.max_length' => 'required|integer|min:1|max:' . ($type === Question::TYPE_SHORT_TEXT ? 255 : 10000),
            ],
            Question::TYPE_NUMBER => $rules += [
                'settings.mode' => 'required|in:single,range',
                'settings.min' => 'nullable|numeric',
                'settings.max' => 'nullable|numeric|gte:settings.min',
                'settings.unit' => 'nullable|string|max:20',
            ],
            Question::TYPE_RATING => $rules += [
                'settings.scale' => 'required|integer|min:3|max:10',
                'settings.min_label' => 'nullable|string|max:50',
                'settings.max_label' => 'nullable|string|max:50',
            ],
            Question::TYPE_FILE => $rules += [
                'settings.max_files' => 'required|integer|min:1|max:10',
                'settings.max_size_mb' => 'required|integer|min:1|max:20',
                'settings.extensions' => 'required|string|max:255',
            ],
            default => null,
        };

        $messages = [
            'service_form_section_id.required' => 'من فضلك اختر القسم',
            'service_form_section_id.exists' => 'القسم المختار غير موجود في هذا النموذج',
            'type.required' => 'من فضلك اختر نوع الحقل',
            'type.in' => 'نوع الحقل غير صحيح',
            'label.required' => 'من فضلك ادخل نص السؤال',
            'label.max' => 'نص السؤال يجب ألا يتجاوز 255 حرفًا',
            'help_text.max' => 'نص المساعدة يجب ألا يتجاوز 1000 حرف',
            'placeholder.max' => 'النص التوضيحي داخل الحقل يجب ألا يتجاوز 255 حرفًا',
            'options_list.min' => 'أضف خيارين على الأقل، كل خيار في سطر',
            'options_list.max' => 'الحد الأقصى 50 خيارًا',
            'options_list.*.max' => 'كل خيار يجب ألا يتجاوز 150 حرفًا',
            'options_list.*.distinct' => 'يوجد خيار مكرر',
            'settings.max_length.required' => 'من فضلك حدد أقصى عدد للأحرف',
            'settings.max_length.max' => 'أقصى عدد للأحرف أكبر من المسموح',
            'settings.min_length.lte' => 'أقل عدد للأحرف يجب ألا يتجاوز الحد الأقصى',
            'settings.max.gte' => 'الحد الأعلى يجب أن يكون أكبر من أو يساوي الحد الأدنى',
            'settings.scale.min' => 'المقياس من 3 إلى 10',
            'settings.scale.max' => 'المقياس من 3 إلى 10',
            'settings.max_files.max' => 'الحد الأقصى 10 ملفات',
            'settings.max_size_mb.max' => 'الحد الأقصى لحجم الملف 20 ميجابايت',
            'settings.extensions.required' => 'من فضلك حدد أنواع الملفات المسموحة',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($request, $form, $question, $type, $options) {
            $this->checkExtensions($validator, $request, $type);
            $this->checkFilterKey($validator, $request, $form, $question, $type);
            $this->checkCondition($validator, $request, $form, $question);
            if ($question) {
                $this->checkDependents($validator, $question, $type, $options->all());
            }
        });

        $validator->validate();

        $number = fn ($key) => $request->filled("settings.$key") ? $request->input("settings.$key") + 0 : null;
        $settings = match ($type) {
            Question::TYPE_SHORT_TEXT, Question::TYPE_LONG_TEXT => [
                'min_length' => $number('min_length'),
                'max_length' => (int) $request->input('settings.max_length'),
            ],
            Question::TYPE_NUMBER => [
                'mode' => $request->input('settings.mode'),
                'min' => $number('min'),
                'max' => $number('max'),
                'unit' => (string) $request->input('settings.unit'),
            ],
            Question::TYPE_RATING => [
                'scale' => (int) $request->input('settings.scale'),
                'min_label' => (string) $request->input('settings.min_label'),
                'max_label' => (string) $request->input('settings.max_label'),
            ],
            Question::TYPE_FILE => [
                'max_files' => (int) $request->input('settings.max_files'),
                'max_size_mb' => (int) $request->input('settings.max_size_mb'),
                'extensions' => $this->normalizeExtensions($request->input('settings.extensions')),
            ],
            default => null,
        };

        $conditionTarget = $request->input('condition_question_id');

        return [
            'service_form_section_id' => (int) $request->input('service_form_section_id'),
            'type' => $type,
            'label' => $request->input('label'),
            'help_text' => $request->input('help_text'),
            'placeholder' => $request->input('placeholder'),
            'is_required' => $request->boolean('is_required'),
            'options' => in_array($type, Question::OPTION_TYPES, true) ? $options->all() : null,
            'settings' => $settings,
            'condition' => $conditionTarget ? [
                'question_id' => (int) $conditionTarget,
                'values' => array_values($request->input('condition_values', [])),
                'negate' => $request->boolean('condition_negate'),
            ] : null,
            'filter_key' => $request->input('filter_key') ?: null,
        ];
    }

    private function normalizeExtensions(?string $value): string
    {
        return collect(explode(',', strtolower((string) $value)))
            ->map(fn ($extension) => trim($extension, " .\t"))
            ->filter()->unique()->implode(',');
    }

    private function checkExtensions($validator, Request $request, ?string $type): void
    {
        if ($type !== Question::TYPE_FILE || ! $request->filled('settings.extensions')) {
            return;
        }
        $extensions = explode(',', $this->normalizeExtensions($request->input('settings.extensions')));
        $invalid = array_diff($extensions, self::ALLOWED_EXTENSIONS);
        if ($extensions === [''] || $invalid) {
            $validator->errors()->add('settings.extensions', 'أنواع ملفات غير مسموحة: ' . implode('، ', $invalid ?: ['فارغ']) . '. المسموح: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }
    }

    private function checkFilterKey($validator, Request $request, ServiceForm $form, ?Question $question, ?string $type): void
    {
        $key = $request->input('filter_key');
        if (! $key || ! isset(Question::FILTER_KEYS[$key])) {
            return;
        }
        if (! in_array($type, Question::FILTER_KEYS[$key]['types'], true)) {
            $validator->errors()->add('filter_key', 'نوع الحقل لا يناسب فلتر «' . Question::FILTER_KEYS[$key]['label'] . '»');
        }
        $taken = $form->questions()->where('filter_key', $key)
            ->when($question, fn ($query) => $query->where('id', '!=', $question->id))
            ->exists();
        if ($taken) {
            $validator->errors()->add('filter_key', 'فلتر «' . Question::FILTER_KEYS[$key]['label'] . '» مربوط بسؤال آخر في هذا النموذج');
        }
    }

    private function checkCondition($validator, Request $request, ServiceForm $form, ?Question $question): void
    {
        $targetId = $request->input('condition_question_id');
        if (! $targetId) {
            return;
        }
        $target = $form->questions()->find($targetId);
        if (! $target || ($question && $target->id === $question->id) || ! $target->canBeConditionSource()) {
            $validator->errors()->add('condition_question_id', 'سؤال الشرط غير صالح');

            return;
        }
        if ($question && $question->wouldCreateConditionLoop($target->id)) {
            $validator->errors()->add('condition_question_id', 'هذا الشرط يجعل السؤالين يعتمدان على بعضهما');

            return;
        }
        $values = (array) $request->input('condition_values', []);
        if (! $values) {
            $validator->errors()->add('condition_values', 'اختر إجابة واحدة على الأقل لشرط الظهور');
        } elseif (array_diff($values, $target->choiceValues())) {
            $validator->errors()->add('condition_values', 'إجابات الشرط غير موجودة في خيارات السؤال «' . $target->label . '»');
        }
    }

    /** Changing a question must not break conditions that depend on its answers. */
    private function checkDependents($validator, Question $question, ?string $type, array $options): void
    {
        $dependents = $question->dependents();
        if ($dependents->isEmpty()) {
            return;
        }
        $names = $dependents->map(fn ($dependent) => '«' . $dependent->label . '»')->implode('، ');

        if (! in_array($type, Question::CONDITION_SOURCE_TYPES, true)) {
            $validator->errors()->add('type', 'لا يمكن تغيير نوع الحقل لأن هذه الأسئلة تعتمد عليه في شرط الظهور: ' . $names);

            return;
        }

        $available = $type === Question::TYPE_YES_NO ? [Question::YES, Question::NO] : $options;
        foreach ($dependents as $dependent) {
            $missing = array_diff($dependent->condition['values'] ?? [], $available);
            if ($missing) {
                $validator->errors()->add('options_text', 'الخيار «' . implode('، ', $missing) . '» مستخدم في شرط ظهور السؤال «' . $dependent->label . '»، عدّل الشرط أولًا');
            }
        }
    }
}
