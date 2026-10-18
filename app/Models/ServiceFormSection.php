<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceFormSection extends Model
{
    protected $guarded = [];

    public function form()
    {
        return $this->belongsTo(ServiceForm::class, 'service_form_id');
    }

    public function questions()
    {
        return $this->hasMany(ServiceFormQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Duplicate this section with its questions right after it. Conditions that
     * point inside the section follow the copies; others keep their target.
     */
    public function duplicate(): self
    {
        return DB::transaction(function () {
            $this->form->sections()->where('sort_order', '>', $this->sort_order)->increment('sort_order');

            $copy = $this->replicate();
            $copy->title = $this->title . ' (نسخة)';
            $copy->sort_order = $this->sort_order + 1;
            $copy->save();

            $map = [];
            $copies = [];
            foreach ($this->questions as $question) {
                $newQuestion = $question->replicate(['service_form_section_id', 'filter_key']);
                $newQuestion->service_form_section_id = $copy->id;
                $newQuestion->save();
                $map[$question->id] = $newQuestion->id;
                $copies[] = $newQuestion;
            }
            foreach ($copies as $newQuestion) {
                $target = $newQuestion->condition['question_id'] ?? null;
                if ($target && isset($map[$target])) {
                    $newQuestion->update(['condition' => array_merge($newQuestion->condition, ['question_id' => $map[$target]])]);
                }
            }

            return $copy;
        });
    }
}
