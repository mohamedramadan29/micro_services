<?php

namespace App\Services\ServiceRequests;

use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Validates and stores a client's request against a service form.
 *
 * Visibility follows the same rule as public/assets/website/js/service-request-form.js:
 * a conditional question is shown only when its source question is shown,
 * answered, and the answer matches one of the condition values (or none, when
 * negated). Hidden questions are ignored entirely: not validated, not stored.
 */
class RequestSubmission
{
    /** Shared attachments: count, size and types. */
    public const ATTACHMENTS_MAX = 5;
    public const ATTACHMENT_MAX_MB = 10;
    public const ATTACHMENT_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];

    private Collection $questions;

    /** When editing: files already on the request that the client keeps (counted for limits and required file questions). */
    private ?ServiceRequest $editing = null;

    private array $keptFiles = [];

    public function __construct(private ServiceForm $form)
    {
        $this->questions = $form->orderedQuestions()->keyBy('id');
    }

    /** Ids of the questions shown for these answers. */
    public function visibleQuestionIds(array $answers): array
    {
        $visible = [];
        foreach ($this->questions as $question) {
            $visible[$question->id] = ! $question->condition;
        }

        // Repeat so chains settle in any order (same as the browser).
        for ($pass = 0; $pass <= $this->questions->count(); $pass++) {
            $changed = false;
            foreach ($this->questions as $question) {
                if (! $question->condition) {
                    continue;
                }
                $sourceId = $question->condition['question_id'] ?? null;
                $answer = ($visible[$sourceId] ?? false) ? $this->choiceAnswer($answers[$sourceId] ?? null) : [];
                $matches = (bool) array_intersect($answer, $question->condition['values'] ?? []);
                $shown = $answer && (! empty($question->condition['negate']) ? ! $matches : $matches);
                if ($shown !== $visible[$question->id]) {
                    $visible[$question->id] = $shown;
                    $changed = true;
                }
            }
            if (! $changed) {
                break;
            }
        }

        return array_keys(array_filter($visible));
    }

    /** Validate the whole submission; returns the validated data. */
    public function validate(Request $request): array
    {
        if ($this->editing) {
            // The path is not edited; files the client keeps count as already attached.
            $request->merge(['action' => $this->editing->path, 'kind' => $this->editing->kind]);
            $removed = array_map('intval', (array) $request->input('remove_files', []));
            $this->keptFiles = $this->editing->files->reject(fn ($file) => in_array($file->id, $removed, true))
                ->groupBy(fn ($file) => $file->question_id ?? 'attachments')->map->count()->all();
        }
        $answers = (array) $request->input('answers', []);
        $visible = $this->visibleQuestionIds($answers);

        $rules = [
            'action' => ['required', Rule::in([ServiceRequest::PATH_EXECUTE, ServiceRequest::PATH_EXPLORE])],
            'problem' => 'required|string|min:10|max:5000',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s\-()]{6,30}$/'],
            'contact_email' => 'required|email|max:255',
            'contact_method' => ['nullable', Rule::in(array_keys(ServiceRequest::CONTACT_METHODS))],
            'attachments' => 'nullable|array|max:' . max(0, self::ATTACHMENTS_MAX - ($this->keptFiles['attachments'] ?? 0)),
            // Service 3 «مشروع بمراحل».
            'kind' => ['nullable', Rule::in([ServiceRequest::KIND_STANDARD, ServiceRequest::KIND_STAGED])],
            'project_title' => ['required_if:kind,' . ServiceRequest::KIND_STAGED, 'nullable', 'string', 'max:150'],
            'stopped_project' => 'nullable|boolean',
            'done_before' => ['required_if:stopped_project,1', 'nullable', 'string', 'max:5000'],
            'attachments.*' => 'file|max:' . (self::ATTACHMENT_MAX_MB * 1024) . '|mimes:' . implode(',', self::ATTACHMENT_EXTENSIONS),
        ];
        $messages = [
            'action.required' => 'اختر: أريد تنفيذ المشروع، أو أريد معرفة الأسعار أولًا',
            'problem.required' => 'من فضلك اشرح المشكلة أو ما ترغب بتنفيذه',
            'problem.min' => 'اكتب شرحًا أوضح (10 أحرف على الأقل)',
            'problem.max' => 'الشرح يجب ألا يتجاوز 5000 حرف',
            'contact_name.required' => 'من فضلك ادخل الاسم الكامل',
            'contact_phone.required' => 'من فضلك ادخل رقم التواصل',
            'contact_phone.regex' => 'رقم التواصل غير صحيح',
            'contact_email.required' => 'من فضلك ادخل البريد الإلكتروني',
            'contact_email.email' => 'البريد الإلكتروني غير صحيح',
            'contact_method.in' => 'طريقة التواصل غير صحيحة',
            'attachments.max' => 'الحد الأقصى ' . self::ATTACHMENTS_MAX . ' مرفقات',
            'attachments.*.max' => 'حجم المرفق يجب ألا يتجاوز ' . self::ATTACHMENT_MAX_MB . ' ميجابايت',
            'attachments.*.mimes' => 'نوع المرفق غير مسموح',
            'project_title.required_if' => 'اكتب عنوانًا للمشروع',
            'project_title.max' => 'عنوان المشروع طويل (150 حرفًا كحد أقصى)',
            'done_before.required_if' => 'اكتب ما أُنجز في المشروع قبل توقفه وما تبقّى',
        ];
        $data = $this->answersValidator($request, $visible, $rules, $messages)->validate();
        $data['visible'] = $visible;

        return $data;
    }

    /**
     * Only the form's answers (questions + their files), for other flows that reuse a form built in
     * «نماذج الخدمات» (the companies' guided request). Returns `answers`, `answer_files`, `visible`.
     */
    public function validateAnswers(Request $request): array
    {
        $visible = $this->visibleQuestionIds((array) $request->input('answers', []));
        $data = $this->answersValidator($request, $visible, [], [])->validate();
        $data['visible'] = $visible;

        return $data;
    }

    /**
     * The validated answers as a snapshot list (section, label, type, value) — the question may change
     * later, the answer keeps what the client saw. File questions carry the uploaded files under `files`.
     */
    public function answerRows(array $data, Request $request): array
    {
        $answers = (array) ($data['answers'] ?? []);
        $rows = [];
        foreach ($data['visible'] as $id) {
            $question = $this->questions[$id];
            $value = $this->normalize($question, $answers[$id] ?? null);
            $files = $question->type === Q::TYPE_FILE ? (array) $request->file("answer_files.$id", []) : [];
            if ($value === null && ! $files) {
                continue;
            }
            $rows[] = ['question_id' => $question->id, 'section' => $question->section?->title, 'label' => $question->label,
                'type' => $question->type, 'value' => $value, 'files' => $files];
        }

        return $rows;
    }

    private function answersValidator(Request $request, array $visible, array $rules, array $messages): \Illuminate\Validation\Validator
    {
        $attributes = [];

        foreach ($visible as $id) {
            $question = $this->questions[$id];
            $this->addQuestionRules($question, $rules, $messages);
            $attributes["answers.$id"] = '«' . $question->label . '»';
            $attributes["answers.$id.min"] = '«' . $question->label . '»';
            $attributes["answers.$id.max"] = '«' . $question->label . '»';
            $attributes["answer_files.$id"] = '«' . $question->label . '»';
        }

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        $validator->after(function ($validator) use ($request, $visible) {
            foreach ($visible as $id) {
                $question = $this->questions[$id];
                if ($question->type !== Q::TYPE_NUMBER || $question->setting('mode') !== 'range') {
                    continue;
                }
                $min = $request->input("answers.$id.min");
                $max = $request->input("answers.$id.max");
                if (is_numeric($min) && is_numeric($max) && $min > $max) {
                    $validator->errors()->add("answers.$id", 'في «' . $question->label . '» القيمة «من» يجب ألا تكون أكبر من «إلى»');
                }
            }
        });

        return $validator;
    }

    private function addQuestionRules(Q $question, array &$rules, array &$messages): void
    {
        $key = 'answers.' . $question->id;
        $required = $question->is_required ? 'required' : 'nullable';
        $label = '«' . $question->label . '»';

        switch ($question->type) {
            case Q::TYPE_SHORT_TEXT:
            case Q::TYPE_LOCATION:
            case Q::TYPE_LONG_TEXT:
                $max = $question->setting('max_length', $question->type === Q::TYPE_LONG_TEXT ? 5000 : 255);
                $rules[$key] = array_filter([$required, 'string', 'max:' . $max, $question->setting('min_length') ? 'min:' . $question->setting('min_length') : null]);
                break;
            case Q::TYPE_SINGLE_CHOICE:
            case Q::TYPE_DROPDOWN:
            case Q::TYPE_YES_NO:
                $rules[$key] = [$required, 'string', Rule::in($question->choiceValues())];
                break;
            case Q::TYPE_MULTI_CHOICE:
                $rules[$key] = [$required, 'array', 'min:1'];
                $rules["$key.*"] = ['string', Rule::in($question->choiceValues())];
                break;
            case Q::TYPE_NUMBER:
                $bounds = array_filter([
                    is_numeric($question->setting('min')) ? 'min:' . $question->setting('min') : null,
                    is_numeric($question->setting('max')) ? 'max:' . $question->setting('max') : null,
                ]);
                if ($question->setting('mode') === 'range') {
                    $rules[$key] = [$required, 'array'];
                    $rules["$key.min"] = array_merge([$question->is_required ? 'required' : 'nullable', 'numeric'], $bounds);
                    $rules["$key.max"] = array_merge([$question->is_required ? 'required' : 'nullable', 'numeric'], $bounds);
                } else {
                    $rules[$key] = array_merge([$required, 'numeric'], $bounds);
                }
                break;
            case Q::TYPE_DATE:
                $rules[$key] = [$required, 'date_format:Y-m-d'];
                break;
            case Q::TYPE_URL:
                $rules[$key] = [$required, 'url', 'max:2048'];
                break;
            case Q::TYPE_RATING:
                $rules[$key] = [$required, 'integer', 'between:1,' . $question->setting('scale', 5)];
                break;
            case Q::TYPE_FILE:
                $fileKey = 'answer_files.' . $question->id;
                $kept = $this->keptFiles[$question->id] ?? 0;
                $rules[$fileKey] = [$kept ? 'nullable' : $required, 'array', 'max:' . max(0, $question->setting('max_files', 1) - $kept)];
                $rules["$fileKey.*"] = ['file', 'max:' . ($question->setting('max_size_mb', 5) * 1024), 'mimes:' . $question->setting('extensions', 'pdf')];
                $messages["$fileKey.*.max"] = "حجم الملف في $label يجب ألا يتجاوز " . $question->setting('max_size_mb', 5) . ' ميجابايت';
                $messages["$fileKey.*.mimes"] = "نوع الملف في $label غير مسموح";
                $messages["$fileKey.max"] = "الحد الأقصى في $label هو " . $question->setting('max_files', 1) . ' ملفات';
                $messages["$fileKey.required"] = "من فضلك أرفق ملفًا في $label";
                break;
        }

        $messages["$key.required"] = "من فضلك أجب عن $label";
        $messages["$key.min.required"] = "من فضلك أدخل القيمتين في $label";
        $messages["$key.max.required"] = "من فضلك أدخل القيمتين في $label";
        $messages["$key.in"] = "اختر إجابة من الخيارات في $label";
        $messages["$key.*.in"] = "اختر إجابة من الخيارات في $label";
        $messages["$key.url"] = "الرابط في $label غير صحيح";
        $messages["$key.date_format"] = "التاريخ في $label غير صحيح";
        $messages["$key.numeric"] = "أدخل رقمًا في $label";
    }

    /** Store a validated submission as one request record. */
    public function store(User $client, array $data, Request $request): ServiceRequest
    {
        $answers = (array) ($data['answers'] ?? []);
        $form = $this->form->loadMissing('subCategory.category');
        $paths = [];

        try {
            return DB::transaction(function () use ($client, $data, $answers, $form, $request, &$paths) {
                $staged = ($data['kind'] ?? null) === ServiceRequest::KIND_STAGED;
                // A staged project is always published for offers (the card's path has no price inquiry).
                $execute = $staged || $data['action'] === ServiceRequest::PATH_EXECUTE;
                $serviceRequest = ServiceRequest::create([
                    'user_id' => $client->id,
                    'service_form_id' => $form->id,
                    'category_id' => $form->subCategory->category?->id,
                    'sub_category_id' => $form->sub_category_id,
                    'category_name' => $form->subCategory->category?->name,
                    'service_name' => $form->subCategory->name,
                    'path' => $execute ? ServiceRequest::PATH_EXECUTE : $data['action'],
                    'kind' => $staged ? ServiceRequest::KIND_STAGED : ServiceRequest::KIND_STANDARD,
                    'project_title' => $staged ? $data['project_title'] : null,
                    'stopped_project' => $staged && ! empty($data['stopped_project']),
                    'done_before' => $staged && ! empty($data['stopped_project']) ? $data['done_before'] : null,
                    'status' => $execute ? ServiceRequest::STATUS_OPEN : ServiceRequest::STATUS_INQUIRY,
                    'problem' => $data['problem'],
                    'contact_name' => $data['contact_name'],
                    'contact_phone' => $data['contact_phone'],
                    'contact_email' => $data['contact_email'],
                    'contact_method' => $data['contact_method'] ?? null,
                    'published_at' => $execute ? now() : null,
                ] + $this->filterValues($data['visible'], $answers));

                $serviceRequest->update(['number' => 'REQ-' . (1000 + $serviceRequest->id)]);

                $order = 0;
                foreach ($data['visible'] as $id) {
                    $question = $this->questions[$id];
                    $value = $this->normalize($question, $answers[$id] ?? null);
                    $files = $question->type === Q::TYPE_FILE ? (array) $request->file("answer_files.$id", []) : [];
                    if ($value === null && ! $files) {
                        continue;
                    }
                    $serviceRequest->answers()->create([
                        'question_id' => $question->id,
                        'section_title' => $question->section?->title,
                        'label' => $question->label,
                        'type' => $question->type,
                        'value' => $question->type === Q::TYPE_FILE ? null : $value,
                        'sort_order' => ++$order,
                    ]);
                    foreach ($files as $file) {
                        $paths[] = $this->storeFile($serviceRequest, $file, $question->id);
                    }
                }

                foreach ((array) $request->file('attachments', []) as $file) {
                    $paths[] = $this->storeFile($serviceRequest, $file, null);
                }

                return $serviceRequest;
            });
        } catch (\Throwable $e) {
            // The database rolled back; remove files written before the failure.
            Storage::disk(ServiceRequestFile::DISK)->delete(array_filter($paths));
            throw $e;
        }
    }

    /** Edit mode: validate() keeps the request's path and its files. */
    public function editing(ServiceRequest $serviceRequest): static
    {
        $this->editing = $serviceRequest->loadMissing('files');

        return $this;
    }

    /**
     * Save an edit made before an offer was chosen: same rules as a new request; answers are
     * replaced, removed files deleted, new ones added. Returns the labels of what changed.
     */
    public function update(ServiceRequest $serviceRequest, array $data, Request $request): array
    {
        $answers = (array) ($data['answers'] ?? []);
        $removed = $serviceRequest->files()->whereIn('id', array_map('intval', (array) $request->input('remove_files', [])))->get();
        $before = $this->snapshot($serviceRequest);
        $paths = [];

        try {
            DB::transaction(function () use ($serviceRequest, $data, $answers, $request, $removed, &$paths) {
                $serviceRequest->update([
                    'problem' => $data['problem'],
                    'contact_name' => $data['contact_name'],
                    'contact_phone' => $data['contact_phone'],
                    'contact_email' => $data['contact_email'],
                    'contact_method' => $data['contact_method'] ?? null,
                    'edited_at' => now(),
                ] + ($serviceRequest->isStaged() ? [
                    'project_title' => $data['project_title'],
                    'stopped_project' => ! empty($data['stopped_project']),
                    'done_before' => ! empty($data['stopped_project']) ? $data['done_before'] : null,
                ] : []) + $this->filterValues($data['visible'], $answers) + array_fill_keys(self::FILTER_COLUMNS, null));

                $serviceRequest->answers()->delete();
                $order = 0;
                foreach ($data['visible'] as $id) {
                    $question = $this->questions[$id];
                    $value = $this->normalize($question, $answers[$id] ?? null);
                    $files = $question->type === Q::TYPE_FILE ? (array) $request->file("answer_files.$id", []) : [];
                    $hasKept = $question->type === Q::TYPE_FILE && ($this->keptFiles[$id] ?? 0) > 0;
                    if ($value === null && ! $files && ! $hasKept) {
                        continue;
                    }
                    $serviceRequest->answers()->create([
                        'question_id' => $question->id,
                        'section_title' => $question->section?->title,
                        'label' => $question->label,
                        'type' => $question->type,
                        'value' => $question->type === Q::TYPE_FILE ? null : $value,
                        'sort_order' => ++$order,
                    ]);
                    foreach ($files as $file) {
                        $paths[] = $this->storeFile($serviceRequest, $file, $question->id);
                    }
                }
                // Files of questions that are now hidden go too.
                $serviceRequest->files()->whereNotNull('question_id')->whereNotIn('question_id', $data['visible'])->get()
                    ->each(fn ($file) => $removed->push($file));
                foreach ((array) $request->file('attachments', []) as $file) {
                    $paths[] = $this->storeFile($serviceRequest, $file, null);
                }
                $serviceRequest->files()->whereIn('id', $removed->pluck('id'))->delete();
            });
        } catch (\Throwable $e) {
            Storage::disk(ServiceRequestFile::DISK)->delete(array_filter($paths));
            throw $e;
        }
        Storage::disk(ServiceRequestFile::DISK)->delete($removed->pluck('path')->filter()->all());

        return $this->changedLabels($before, $this->snapshot($serviceRequest->fresh(['answers', 'files'])));
    }

    /** Filter columns a request may carry (reset on edit, then refilled from the answers). */
    public const FILTER_COLUMNS = ['budget_min', 'budget_max', 'deadline', 'project_type', 'work_mode', 'city', 'urgency', 'experience'];

    private function snapshot(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing(['answers', 'files']);
        $out = ['عنوان المشروع' => $serviceRequest->project_title, 'ما أُنجز قبل التوقف' => [(bool) $serviceRequest->stopped_project, $serviceRequest->done_before], 'الوصف' => $serviceRequest->problem, 'بيانات التواصل' => [$serviceRequest->contact_name, $serviceRequest->contact_phone, $serviceRequest->contact_email, $serviceRequest->contact_method]];
        foreach ($serviceRequest->answers as $answer) {
            $out[$answer->label] = $answer->value;
        }
        $out['الملفات'] = $serviceRequest->files->pluck('id')->sort()->values()->all();

        return $out;
    }

    private function changedLabels(array $before, array $after): array
    {
        $labels = [];
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $label) {
            if (json_encode($before[$label] ?? null) !== json_encode($after[$label] ?? null)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    private function storeFile(ServiceRequest $serviceRequest, UploadedFile $file, ?int $questionId): string
    {
        $path = $file->store('service-requests/' . $serviceRequest->id, ServiceRequestFile::DISK);
        $serviceRequest->files()->create([
            'question_id' => $questionId,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return $path;
    }

    /** The stored form of an answer, or null when it was left empty. */
    private function normalize(Q $question, $value)
    {
        switch ($question->type) {
            case Q::TYPE_MULTI_CHOICE:
                $value = array_values(array_filter((array) $value, fn ($item) => $item !== null && $item !== ''));

                return $value ?: null;
            case Q::TYPE_NUMBER:
                if ($question->setting('mode') === 'range') {
                    $min = $value['min'] ?? null;
                    $max = $value['max'] ?? null;
                    if (! is_numeric($min) && ! is_numeric($max)) {
                        return null;
                    }

                    return ['min' => is_numeric($min) ? $min + 0 : null, 'max' => is_numeric($max) ? $max + 0 : null, 'unit' => $question->setting('unit')];
                }

                return is_numeric($value) ? $value + 0 : null;
            case Q::TYPE_RATING:
                return is_numeric($value) ? ['value' => (int) $value, 'scale' => (int) $question->setting('scale', 5)] : null;
            case Q::TYPE_FILE:
                return null;
            default:
                return ($value === null || $value === '') ? null : (string) $value;
        }
    }

    /** Columns used by the freelancer filters, from questions linked to a filter. */
    private function filterValues(array $visible, array $answers): array
    {
        $values = [];
        foreach ($visible as $id) {
            $question = $this->questions[$id];
            $answer = $answers[$id] ?? null;
            if (! $question->filter_key || $answer === null || $answer === '') {
                continue;
            }
            if ($question->filter_key === 'budget') {
                if (is_array($answer)) {
                    $values['budget_min'] = is_numeric($answer['min'] ?? null) ? $answer['min'] : null;
                    $values['budget_max'] = is_numeric($answer['max'] ?? null) ? $answer['max'] : null;
                } elseif (is_numeric($answer)) {
                    $values['budget_min'] = $values['budget_max'] = $answer;
                }
            } elseif ($question->filter_key === 'deadline') {
                $values['deadline'] = $answer;
            } elseif (is_string($answer)) {
                $values[$question->filter_key] = mb_substr($answer, 0, 255);
            }
        }

        return $values;
    }

    /** A choice answer as a list of values. */
    private function choiceAnswer($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => is_string($item) && $item !== ''));
        }

        return is_string($value) && $value !== '' ? [$value] : [];
    }
}
