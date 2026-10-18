<?php

namespace App\Models;

use App\Models\admin\SubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceForm extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PAUSED = 'paused';

    public const STATUSES = [
        self::STATUS_DRAFT => 'مسودة',
        self::STATUS_PUBLISHED => 'منشور',
        self::STATUS_PAUSED => 'متوقف',
    ];

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function sections()
    {
        return $this->hasMany(ServiceFormSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function questions()
    {
        return $this->hasMany(ServiceFormQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)->whereNull('revision_of_id');
    }

    /** The service's own form, not a draft revision of it. */
    public function scopeMain($query)
    {
        return $query->whereNull('revision_of_id');
    }

    /** The published form a draft revision will replace. */
    public function revisionOf()
    {
        return $this->belongsTo(self::class, 'revision_of_id');
    }

    /** The draft revision being prepared for this form, if any. */
    public function revision()
    {
        return $this->hasOne(self::class, 'revision_of_id');
    }

    public function isRevision(): bool
    {
        return $this->revision_of_id !== null;
    }

    /** Start (or return) the draft revision: a full copy clients don't see. */
    public function createRevision(): self
    {
        return $this->revision ?? DB::transaction(function () {
            $revision = self::create([
                'sub_category_id' => $this->sub_category_id,
                'revision_of_id' => $this->id,
                'status' => self::STATUS_DRAFT,
            ]);
            $revision->copyFrom($this);

            return $revision;
        });
    }

    /**
     * Replace the published form's sections and questions with the
     * revision's. Saved requests keep their answers (each answer stores a copy
     * of its question), and the form row itself stays, so requests still
     * point at it.
     */
    public function publishRevision(): self
    {
        if (! $this->isRevision()) {
            throw new \RuntimeException('هذا ليس مسودة تعديل');
        }
        if (! $this->questions()->exists()) {
            throw new \RuntimeException('لا يمكن نشر نموذج بدون أسئلة');
        }

        return DB::transaction(function () {
            $main = $this->revisionOf;
            $main->sections()->delete(); // their questions go with them (cascade)
            ServiceFormSection::where('service_form_id', $this->id)->update(['service_form_id' => $main->id]);
            ServiceFormQuestion::where('service_form_id', $this->id)->update(['service_form_id' => $main->id]);
            $this->delete();
            $main->touch();

            return $main;
        });
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Questions in display order: by section order, then question order. */
    public function orderedQuestions()
    {
        return $this->sections()->with('questions')->get()->flatMap->questions->values();
    }

    /**
     * Copy every section and question of another form into this one,
     * remapping visibility conditions to the copied questions.
     */
    public function copyFrom(ServiceForm $source): void
    {
        DB::transaction(function () use ($source) {
            $map = [];
            $copies = [];
            $sectionOrder = (int) $this->sections()->max('sort_order');

            foreach ($source->sections()->with('questions')->get() as $section) {
                $newSection = $this->sections()->create([
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => ++$sectionOrder,
                ]);
                foreach ($section->questions as $question) {
                    $copy = $question->replicate(['service_form_id', 'service_form_section_id']);
                    $copy->service_form_id = $this->id;
                    $copy->service_form_section_id = $newSection->id;
                    $copy->save();
                    $map[$question->id] = $copy->id;
                    $copies[] = $copy;
                }
            }

            foreach ($copies as $copy) {
                if ($copy->condition && isset($map[$copy->condition['question_id'] ?? null])) {
                    $copy->update(['condition' => array_merge($copy->condition, ['question_id' => $map[$copy->condition['question_id']]])]);
                } elseif ($copy->condition) {
                    $copy->update(['condition' => null]);
                }
            }
        });
    }
}
