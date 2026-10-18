<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceFormQuestion extends Model
{
    public const TYPE_SHORT_TEXT = 'short_text';
    public const TYPE_LONG_TEXT = 'long_text';
    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_MULTI_CHOICE = 'multi_choice';
    public const TYPE_DROPDOWN = 'dropdown';
    public const TYPE_YES_NO = 'yes_no';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATE = 'date';
    public const TYPE_URL = 'url';
    public const TYPE_LOCATION = 'location';
    public const TYPE_RATING = 'rating';
    public const TYPE_FILE = 'file';

    /** Field library shown to the admin: label + example + icon. */
    public const TYPES = [
        self::TYPE_SHORT_TEXT => ['label' => 'إجابة قصيرة', 'hint' => 'اسم المشروع أو المنتج', 'icon' => 'fas fa-font'],
        self::TYPE_LONG_TEXT => ['label' => 'إجابة طويلة', 'hint' => 'شرح الاحتياج أو المشكلة', 'icon' => 'fas fa-align-right'],
        self::TYPE_SINGLE_CHOICE => ['label' => 'اختيار واحد', 'hint' => 'هدف واحد من قائمة', 'icon' => 'far fa-dot-circle'],
        self::TYPE_MULTI_CHOICE => ['label' => 'اختيارات متعددة', 'hint' => 'الجمهور أو المنصات', 'icon' => 'far fa-check-square'],
        self::TYPE_DROPDOWN => ['label' => 'قائمة منسدلة', 'hint' => 'الخدمة أو الدولة', 'icon' => 'fas fa-caret-square-down'],
        self::TYPE_YES_NO => ['label' => 'نعم / لا', 'hint' => 'هل لديك موقع؟', 'icon' => 'fas fa-toggle-on'],
        self::TYPE_NUMBER => ['label' => 'رقم أو ميزانية', 'hint' => 'ميزانية تقريبية', 'icon' => 'fas fa-hashtag'],
        self::TYPE_DATE => ['label' => 'تاريخ', 'hint' => 'موعد البدء أو التسليم', 'icon' => 'far fa-calendar-alt'],
        self::TYPE_URL => ['label' => 'رابط إلكتروني', 'hint' => 'رابط الموقع أو العمل', 'icon' => 'fas fa-link'],
        self::TYPE_LOCATION => ['label' => 'موقع جغرافي', 'hint' => 'مدينة أو منطقة', 'icon' => 'fas fa-map-marker-alt'],
        self::TYPE_RATING => ['label' => 'تقييم أو مقياس', 'hint' => 'الأولوية أو مستوى الجاهزية', 'icon' => 'far fa-star'],
        self::TYPE_FILE => ['label' => 'رفع ملف', 'hint' => 'صور أو ملفات مرجعية', 'icon' => 'fas fa-paperclip'],
    ];

    /** Types whose answer is picked from `options`. */
    public const OPTION_TYPES = [self::TYPE_SINGLE_CHOICE, self::TYPE_MULTI_CHOICE, self::TYPE_DROPDOWN];

    /** Types another question can depend on (show when the answer is …). */
    public const CONDITION_SOURCE_TYPES = [self::TYPE_SINGLE_CHOICE, self::TYPE_MULTI_CHOICE, self::TYPE_DROPDOWN, self::TYPE_YES_NO];

    public const YES = 'نعم';
    public const NO = 'لا';

    /**
     * Request filters a question's answer can feed (used by the freelancer
     * requests page). Each key is used at most once per form.
     */
    public const FILTER_KEYS = [
        'budget' => ['label' => 'الميزانية', 'types' => [self::TYPE_NUMBER]],
        'deadline' => ['label' => 'موعد التسليم', 'types' => [self::TYPE_DATE]],
        'project_type' => ['label' => 'نوع المشروع (مرة واحدة / مستمر)', 'types' => [self::TYPE_SINGLE_CHOICE, self::TYPE_DROPDOWN]],
        'work_mode' => ['label' => 'مكان التنفيذ (عن بُعد / حضوري)', 'types' => [self::TYPE_SINGLE_CHOICE, self::TYPE_DROPDOWN]],
        'city' => ['label' => 'المدينة أو الدولة', 'types' => [self::TYPE_LOCATION, self::TYPE_SHORT_TEXT, self::TYPE_DROPDOWN]],
        'urgency' => ['label' => 'مستوى الاستعجال', 'types' => [self::TYPE_SINGLE_CHOICE, self::TYPE_DROPDOWN, self::TYPE_YES_NO]],
        'experience' => ['label' => 'الخبرة المطلوبة', 'types' => [self::TYPE_SINGLE_CHOICE, self::TYPE_DROPDOWN]],
    ];

    /** Default limits per type, merged under the saved settings. */
    public const DEFAULT_SETTINGS = [
        self::TYPE_SHORT_TEXT => ['max_length' => 255],
        self::TYPE_LONG_TEXT => ['max_length' => 5000],
        self::TYPE_NUMBER => ['mode' => 'single', 'min' => null, 'max' => null, 'unit' => ''],
        self::TYPE_RATING => ['scale' => 5, 'min_label' => '', 'max_label' => ''],
        self::TYPE_FILE => ['max_files' => 3, 'max_size_mb' => 5, 'extensions' => 'jpg,jpeg,png,pdf,doc,docx,zip'],
    ];

    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'settings' => 'array',
        'condition' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(ServiceForm::class, 'service_form_id');
    }

    public function section()
    {
        return $this->belongsTo(ServiceFormSection::class, 'service_form_section_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, self::OPTION_TYPES, true);
    }

    public function canBeConditionSource(): bool
    {
        return in_array($this->type, self::CONDITION_SOURCE_TYPES, true);
    }

    /** The values a dependent question can match on. */
    public function choiceValues(): array
    {
        if ($this->type === self::TYPE_YES_NO) {
            return [self::YES, self::NO];
        }

        return $this->hasOptions() ? array_values($this->options ?? []) : [];
    }

    public function setting(string $key, $default = null)
    {
        $settings = array_merge(self::DEFAULT_SETTINGS[$this->type] ?? [], $this->settings ?? []);

        return $settings[$key] ?? $default;
    }

    /** Questions in the same form whose visibility depends on this one. */
    public function dependents()
    {
        return self::where('service_form_id', $this->service_form_id)
            ->where('id', '!=', $this->id)
            ->get()
            ->filter(fn ($question) => ($question->condition['question_id'] ?? null) == $this->id)
            ->values();
    }

    /**
     * Whether making this question depend on $targetId would create a loop
     * (the target, or something it depends on, already depends on this one).
     */
    public function wouldCreateConditionLoop(int $targetId): bool
    {
        $questions = self::where('service_form_id', $this->service_form_id)->get()->keyBy('id');
        $seen = [];
        $current = $targetId;

        while ($current && ! isset($seen[$current])) {
            if ($current == $this->id) {
                return true;
            }
            $seen[$current] = true;
            $current = $questions[$current]->condition['question_id'] ?? null;
        }

        return false;
    }

    /** Duplicate right after itself in the same section (filter link is not copied). */
    public function duplicate(): self
    {
        return DB::transaction(function () {
            self::where('service_form_section_id', $this->service_form_section_id)
                ->where('sort_order', '>', $this->sort_order)
                ->increment('sort_order');

            $copy = $this->replicate(['filter_key']);
            $copy->label = $this->label . ' (نسخة)';
            $copy->sort_order = $this->sort_order + 1;
            $copy->save();

            return $copy;
        });
    }
}
