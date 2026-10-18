{{-- One stage of the agreement form; $i is its key (stages[$i][…]). --}}
@php $unlimited = ! empty($stage['unlimited_revisions']); @endphp
<div class="ag-stage">
    <div class="ag-stage-head">
        <strong>المرحلة <span class="ag-num">{{ is_int($i) ? $i + 1 : '' }}</span></strong>
        <button type="button" class="rf-btn-link ag-remove">حذف</button>
    </div>
    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="rf-field-label">العنوان</label>
            <input type="text" name="stages[{{ $i }}][title]" maxlength="150" class="form-control" value="{{ $stage['title'] ?? '' }}" placeholder="مثال: التصميم">
            @error("stages.$i.title")<div class="rf-field-error">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 col-6 mb-2">
            <label class="rf-field-label">المدة (أيام)</label>
            <input type="number" name="stages[{{ $i }}][duration_days]" min="1" max="365" class="form-control" value="{{ $stage['duration_days'] ?? '' }}">
            @error("stages.$i.duration_days")<div class="rf-field-error">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 col-6 mb-2">
            <label class="rf-field-label">المبلغ (ج.م)</label>
            <input type="number" name="stages[{{ $i }}][amount]" min="1" step="0.01" class="form-control" value="{{ $stage['amount'] ?? '' }}">
            @error("stages.$i.amount")<div class="rf-field-error">{{ $message }}</div>@enderror
        </div>
    </div>
    <label class="rf-field-label">ما يُسلَّم في هذه المرحلة</label>
    <textarea name="stages[{{ $i }}][deliverable]" rows="2" maxlength="2000" class="form-control" placeholder="مثال: خريطة الصفحات + نموذج أولي">{{ $stage['deliverable'] ?? '' }}</textarea>
    @error("stages.$i.deliverable")<div class="rf-field-error">{{ $message }}</div>@enderror
    <label class="rf-field-label mt-2">معيار القبول <span class="rf-muted">(اختياري — متى تُعتبر المرحلة مكتملة؟)</span></label>
    <textarea name="stages[{{ $i }}][acceptance]" rows="2" maxlength="1000" class="form-control" placeholder="مثال: الصفحات تعمل على الهاتف والكمبيوتر، والنصوص مطابقة للمحتوى المرسل">{{ $stage['acceptance'] ?? '' }}</textarea>
    @error("stages.$i.acceptance")<div class="rf-field-error">{{ $message }}</div>@enderror
    <div class="ag-revisions">
        <label class="rf-field-label mb-0">عدد التعديلات</label>
        <input type="number" name="stages[{{ $i }}][revisions]" min="0" max="20" class="form-control" value="{{ $stage['revisions'] ?? \App\Services\Contracts\ContractPolicy::number('default_revisions') }}" @disabled($unlimited)>
        <label class="ag-unlimited"><input type="checkbox" name="stages[{{ $i }}][unlimited_revisions]" value="1" @checked($unlimited)> غير محدود</label>
    </div>
    @error("stages.$i.revisions")<div class="rf-field-error">{{ $message }}</div>@enderror
</div>
