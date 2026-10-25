<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;

/** One free website check (client file «اختبر موقعك مجاناً»). */
class SiteTest extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';

    public const REVIEW_NONE = 'none';
    public const REVIEW_REQUESTED = 'requested';
    public const REVIEW_IN_PROGRESS = 'in_review';
    public const REVIEW_SENT = 'sent';
    public const REVIEW_CLOSED = 'closed';

    public const REVIEW_STATUSES = [
        self::REVIEW_NONE => 'لم يطلب التقرير',
        self::REVIEW_REQUESTED => 'طلب التقرير',
        self::REVIEW_IN_PROGRESS => 'قيد المراجعة',
        self::REVIEW_SENT => 'أُرسل التقرير',
        self::REVIEW_CLOSED => 'مغلق',
    ];

    public const DEVICES = [
        'mobile' => 'الهاتف',
        'desktop' => 'الكمبيوتر',
        'both' => 'كلاهما',
    ];

    protected $guarded = [];

    protected $hidden = ['token'];

    protected $casts = [
        'categories' => 'array',
        'answers' => 'array',
        'scores' => 'array',
        'checked_at' => 'datetime',
        'report_requested_at' => 'datetime',
        'report_sent_at' => 'datetime',
        'choice_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function issues()
    {
        return $this->hasMany(SiteTestIssue::class)->orderBy('sort_order')->orderBy('id');
    }

    public function results()
    {
        return $this->hasMany(SiteTestResult::class);
    }

    public function suggestedService()
    {
        return $this->belongsTo(SubCategory::class, 'suggested_sub_category_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function devices(): array
    {
        return $this->device === 'both' ? ['mobile', 'desktop'] : [$this->device];
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_DONE, self::STATUS_FAILED], true);
    }

    /**
     * Live progress for the waiting page: devices done out of all, and the
     * current stage (1 = link checked, 2 = collecting, 3 = writing the summary).
     */
    public function progress(): array
    {
        $total = count($this->devices());
        $done = $this->status === self::STATUS_QUEUED ? 0 : min($total, $this->results()->count());
        $stage = match (true) {
            $this->isFinished() => 4,
            $this->status === self::STATUS_QUEUED => 1,
            $done >= $total => 3,
            default => 2,
        };

        return [
            'stage' => $stage,
            'devices_done' => $done,
            'devices_total' => $total,
            'percent' => $this->isFinished() ? 100 : (int) round(10 + 80 * ($done / max(1, $total)) + ($stage === 3 ? 5 : 0)),
            'running_device' => $stage === 2 ? (self::DEVICES[$this->devices()[$done] ?? ''] ?? null) : null,
        ];
    }

    /**
     * The engine's error in plain Arabic for the client and the admin (the
     * raw message stays on the result row).
     */
    public static function friendlyFailure(?string $error): string
    {
        $e = strtolower((string) $error);

        return match (true) {
            $e === '' => 'تعذّر الفحص لسبب غير معروف.',
            str_contains($e, 'timed out') || str_contains($e, 'timeout') => 'استغرق الموقع وقتًا طويلًا جدًا في الاستجابة.',
            str_contains($e, 'dns') || str_contains($e, 'resolve') || str_contains($e, 'name_not_resolved') => 'لم نتمكن من الوصول إلى النطاق (تحقق من كتابة الرابط).',
            str_contains($e, 'status code 4') || str_contains($e, 'status_code_4') || str_contains($e, '404') || str_contains($e, '403') || str_contains($e, '401') => 'الصفحة رفضت الفحص أو غير موجودة (خطأ 4xx)، أو تحتاج تسجيل دخول.',
            str_contains($e, 'status code 5') || str_contains($e, '500') || str_contains($e, '502') || str_contains($e, '503') => 'خادم الموقع أعاد خطأ (5xx) أثناء الفحص.',
            str_contains($e, 'no_fcp') || str_contains($e, 'no_lcp') || str_contains($e, 'paint') => 'الصفحة لم تعرض أي محتوى أثناء الفحص.',
            str_contains($e, 'document_request') || str_contains($e, 'failed_document') || str_contains($e, 'errored_document') => 'تعذّر تحميل الصفحة نفسها أثناء الفحص.',
            str_contains($e, 'quota') || str_contains($e, '429') || str_contains($e, 'rate limit') => 'خدمة الفحص مشغولة الآن، حاول بعد دقائق.',
            str_contains($e, 'api key') || str_contains($e, 'api_key') || str_contains($e, 'pagespeed') || str_contains($e, 'permission') || str_contains($e, 'forbidden') => 'خدمة الفحص غير متاحة مؤقتًا من جهتنا.',
            str_contains($e, 'redirect') => 'الرابط يعيد التوجيه كثيرًا ولم نصل إلى الصفحة.',
            // Already Arabic (ours) — keep it.
            (bool) preg_match('/\p{Arabic}/u', (string) $error) => mb_substr((string) $error, 0, 250),
            default => 'تعذّر الفحص بسبب خطأ تقني في تحميل الصفحة.',
        };
    }

    /** Tool results the client may see, with evidence, worst first. */
    public function clientIssues(?int $limit = null)
    {
        $issues = $this->issues->where('shown', true)->where('source', 'tool')->sortBy(fn (SiteTestIssue $i) => [SiteTestIssue::PRIORITY_ORDER[$i->priority] ?? 9, $i->sort_order])->values();

        return $limit ? $issues->take($limit) : $issues;
    }

    /** The team's own notes, shown apart from the tool's results (spec page 6). */
    public function teamNotes()
    {
        return $this->issues->where('shown', true)->where('source', 'team')->values();
    }

    /** One status label for lists (spec page 9). */
    public function stageLabel(): string
    {
        return match (true) {
            $this->review_status === self::REVIEW_CLOSED => 'مغلق',
            $this->choice === 'yes' && $this->service_request_id => 'تم النشر',
            $this->choice === 'yes' => 'مهتم بالتنفيذ',
            $this->choice === 'no' => 'لا يريد الآن',
            $this->review_status === self::REVIEW_IN_PROGRESS => 'قيد المراجعة',
            $this->review_status === self::REVIEW_REQUESTED => 'طلب التقرير',
            $this->review_status === self::REVIEW_SENT => 'أُرسل التقرير',
            $this->status === self::STATUS_FAILED => 'تعذر',
            $this->status === self::STATUS_DONE => 'جديد',
            default => 'قيد الفحص',
        };
    }
}
