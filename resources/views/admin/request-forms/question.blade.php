@extends('admin.layouts.master')
@php
    use App\Models\ServiceFormQuestion as Q;
    $isNew = ! $question->exists;
    $type = old('type', $question->type);
    $setting = fn ($key, $default = null) => old('settings.' . $key, $question->type === $type ? $question->setting($key, $default) : (Q::DEFAULT_SETTINGS[$type][$key] ?? $default));
    $condition = $question->condition ?? [];
    $conditionTarget = old('condition_question_id', $condition['question_id'] ?? '');
    $conditionValues = old('condition_values', $condition['values'] ?? []);
@endphp
@section('title')
    {{ $isNew ? 'سؤال جديد' : 'تعديل سؤال' }} — {{ $form->subCategory->name }}
@endsection
@section('css')
    <style>
        .rf-types { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; }
        .rf-type input { position: absolute; opacity: 0; pointer-events: none; }
        .rf-type label { display: block; margin: 0; padding: 10px; border: 1px solid #e3e8ef; border-radius: 8px; cursor: pointer; height: 100%; }
        .rf-type label i { color: #3fb697; margin-left: 4px; }
        .rf-type label small { display: block; color: #8a94a3; margin-top: 2px; }
        .rf-type input:checked + label { border-color: #3fb697; box-shadow: 0 0 0 1px #3fb697; background: rgba(63, 182, 151, .08); }
        .rf-type input:focus-visible + label { outline: 2px solid #3fb697; outline-offset: 2px; }
        .rf-block { border-top: 1px solid #eef1f5; padding-top: 16px; margin-top: 8px; }
        .rf-condition-values label { margin-left: 14px; font-weight: 400; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">نماذج الخدمات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                    {{ $form->subCategory->name }} / {{ $isNew ? 'سؤال جديد' : 'تعديل سؤال' }}</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.request-forms.builder', $form) }}" class="btn btn-light">
                <i class="fas fa-arrow-right"></i> رجوع للنموذج
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" id="question-form"
        action="{{ $isNew ? route('admin.request-forms.questions.store', $form) : route('admin.request-forms.questions.update', $question) }}">
        @csrf
        <div class="row row-sm">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>نوع الحقل</label>
                            <div class="rf-types">
                                @foreach (Q::TYPES as $key => $meta)
                                    <div class="rf-type">
                                        <input type="radio" name="type" id="type-{{ $key }}" value="{{ $key }}" @checked($type === $key)>
                                        <label for="type-{{ $key }}">
                                            <strong><i class="{{ $meta['icon'] }}"></i> {{ $meta['label'] }}</strong>
                                            <small>{{ $meta['hint'] }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rf-block">
                            <div class="form-group">
                                <label for="label">نص السؤال <span class="text-danger">*</span></label>
                                <input type="text" id="label" name="label" class="form-control" required maxlength="255"
                                    value="{{ old('label', $question->label) }}" placeholder="مثال: ما الهدف الرئيسي من الإعلان؟">
                            </div>
                            <div class="form-group">
                                <label for="help_text">نص المساعدة للعميل (اختياري)</label>
                                <input type="text" id="help_text" name="help_text" class="form-control" maxlength="1000"
                                    value="{{ old('help_text', $question->help_text) }}" placeholder="توضيح يظهر تحت السؤال">
                            </div>
                            <div class="form-group" data-types="short_text,long_text,number,url,location">
                                <label for="placeholder">مثال داخل الحقل (اختياري)</label>
                                <input type="text" id="placeholder" name="placeholder" class="form-control" maxlength="255"
                                    value="{{ old('placeholder', $question->placeholder) }}" placeholder="مثال: اسم المنتج أو الخدمة">
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_required" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_required" name="is_required" value="1"
                                    @checked(old('is_required', $question->is_required))>
                                <label class="custom-control-label" for="is_required">سؤال إلزامي</label>
                            </div>
                        </div>

                        {{-- Choices --}}
                        <div class="rf-block" data-types="single_choice,multi_choice,dropdown">
                            <div class="form-group">
                                <label for="options_text">الخيارات <span class="text-danger">*</span></label>
                                <textarea id="options_text" name="options_text" class="form-control" rows="6"
                                    placeholder="كل خيار في سطر&#10;زيادة المبيعات&#10;جذب عملاء">{{ old('options_text', implode("\n", $question->options ?? [])) }}</textarea>
                                <small class="text-muted">كل خيار في سطر منفصل، خياران على الأقل.</small>
                            </div>
                        </div>

                        {{-- Per-type settings --}}
                        <div class="rf-block" data-types="short_text,long_text">
                            <h6>حدود الإجابة</h6>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>أقل عدد أحرف</label>
                                    <input type="number" min="0" name="settings[min_length]" class="form-control" value="{{ $setting('min_length') }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>أقصى عدد أحرف</label>
                                    <input type="number" min="1" name="settings[max_length]" class="form-control"
                                        value="{{ $setting('max_length', $type === Q::TYPE_LONG_TEXT ? 5000 : 255) }}">
                                </div>
                            </div>
                        </div>

                        <div class="rf-block" data-types="number">
                            <h6>إعدادات الرقم</h6>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>نوع الإجابة</label>
                                    <select name="settings[mode]" class="form-control">
                                        <option value="single" @selected($setting('mode', 'single') === 'single')>رقم واحد</option>
                                        <option value="range" @selected($setting('mode') === 'range')>نطاق (من / إلى) — مثل الميزانية</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>الوحدة (اختياري)</label>
                                    <input type="text" name="settings[unit]" maxlength="20" class="form-control" value="{{ $setting('unit') }}" placeholder="مثال: $ أو يوم">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>أقل قيمة (اختياري)</label>
                                    <input type="number" step="any" name="settings[min]" class="form-control" value="{{ $setting('min') }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>أعلى قيمة (اختياري)</label>
                                    <input type="number" step="any" name="settings[max]" class="form-control" value="{{ $setting('max') }}">
                                </div>
                            </div>
                        </div>

                        <div class="rf-block" data-types="rating">
                            <h6>إعدادات المقياس</h6>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>عدد الدرجات</label>
                                    <input type="number" min="3" max="10" name="settings[scale]" class="form-control" value="{{ $setting('scale', 5) }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>وصف أقل درجة</label>
                                    <input type="text" maxlength="50" name="settings[min_label]" class="form-control" value="{{ $setting('min_label') }}" placeholder="منخفضة">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>وصف أعلى درجة</label>
                                    <input type="text" maxlength="50" name="settings[max_label]" class="form-control" value="{{ $setting('max_label') }}" placeholder="عالية">
                                </div>
                            </div>
                        </div>

                        <div class="rf-block" data-types="file">
                            <h6>إعدادات الملف</h6>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>أقصى عدد ملفات</label>
                                    <input type="number" min="1" max="10" name="settings[max_files]" class="form-control" value="{{ $setting('max_files', 3) }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>أقصى حجم للملف (ميجابايت)</label>
                                    <input type="number" min="1" max="20" name="settings[max_size_mb]" class="form-control" value="{{ $setting('max_size_mb', 5) }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>الأنواع المسموحة</label>
                                    <input type="text" name="settings[extensions]" class="form-control" dir="ltr"
                                        value="{{ $setting('extensions', Q::DEFAULT_SETTINGS[Q::TYPE_FILE]['extensions']) }}">
                                </div>
                            </div>
                            <small class="text-muted">المسموح: {{ implode(', ', \App\Http\Controllers\admin\ServiceFormQuestionController::ALLOWED_EXTENSIONS) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">مكان السؤال</h6></div>
                    <div class="card-body">
                        <label for="section">القسم</label>
                        <select id="section" name="service_form_section_id" class="form-control">
                            @foreach ($form->sections as $section)
                                <option value="{{ $section->id }}" @selected(old('service_form_section_id', $question->service_form_section_id) == $section->id)>{{ $section->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">شرط الظهور</h6></div>
                    <div class="card-body">
                        @if ($conditionSources->isEmpty())
                            <p class="text-muted tx-13 mb-0">لإضافة شرط، أضف أولًا سؤالًا من نوع اختيار أو نعم/لا في هذا النموذج.</p>
                        @else
                            <div class="form-group">
                                <label for="condition_question_id">أظهر هذا السؤال فقط عندما…</label>
                                <select id="condition_question_id" name="condition_question_id" class="form-control">
                                    <option value="">يظهر دائمًا</option>
                                    @foreach ($conditionSources as $source)
                                        <option value="{{ $source['id'] }}" @selected($conditionTarget == $source['id'])>{{ $source['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="condition-details">
                                <div class="form-group">
                                    <select name="condition_negate" class="form-control">
                                        <option value="0" @selected(! old('condition_negate', $condition['negate'] ?? false))>تكون الإجابة</option>
                                        <option value="1" @selected(old('condition_negate', $condition['negate'] ?? false))>لا تكون الإجابة</option>
                                    </select>
                                </div>
                                <div class="rf-condition-values" id="condition-values"></div>
                                <small class="text-muted">إذا كان السؤال إلزاميًا فهو مطلوب فقط عندما يظهر.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0">ربط بفلاتر الطلبات</h6></div>
                    <div class="card-body">
                        <select name="filter_key" id="filter_key" class="form-control">
                            <option value="">بدون</option>
                            @foreach (Q::FILTER_KEYS as $key => $meta)
                                <option value="{{ $key }}" data-types="{{ implode(',', $meta['types']) }}"
                                    @disabled(in_array($key, $usedFilterKeys)) @selected(old('filter_key', $question->filter_key) === $key)>
                                    {{ $meta['label'] }}{{ in_array($key, $usedFilterKeys) ? ' (مستخدم)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">إجابة هذا السؤال تُستخدم في فلترة الطلبات عند المستقلين.</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> {{ $isNew ? 'إضافة السؤال' : 'حفظ التعديلات' }}
                </button>
            </div>
        </div>
    </form>
@endsection
@section('js')
    <script>
        (function () {
            var form = document.getElementById('question-form');
            var sources = @json($conditionSources);
            var selectedValues = @json(array_values((array) $conditionValues));

            function currentType() {
                var checked = form.querySelector('input[name="type"]:checked');
                return checked ? checked.value : '';
            }

            // Show only the blocks that apply to the chosen type; hidden inputs are not sent.
            function applyType() {
                var type = currentType();
                form.querySelectorAll('[data-types]').forEach(function (el) {
                    if (el.tagName === 'OPTION') {
                        var fits = el.dataset.types.split(',').indexOf(type) !== -1;
                        el.hidden = !fits;
                        if (!fits && el.selected) { el.parentNode.value = ''; }
                        return;
                    }
                    var show = el.dataset.types.split(',').indexOf(type) !== -1;
                    el.style.display = show ? '' : 'none';
                    el.querySelectorAll('input, select, textarea').forEach(function (input) { input.disabled = !show; });
                });
            }

            function renderConditionValues() {
                var select = document.getElementById('condition_question_id');
                if (!select) { return; }
                var details = document.getElementById('condition-details');
                var box = document.getElementById('condition-values');
                var source = sources.find(function (s) { return String(s.id) === select.value; });
                details.style.display = source ? '' : 'none';
                box.innerHTML = '';
                if (!source) { return; }
                source.values.forEach(function (value, index) {
                    var id = 'cv-' + index;
                    var wrap = document.createElement('div');
                    wrap.className = 'custom-control custom-checkbox';
                    var input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'custom-control-input';
                    input.name = 'condition_values[]';
                    input.id = id;
                    input.value = value;
                    input.checked = selectedValues.indexOf(value) !== -1;
                    var label = document.createElement('label');
                    label.className = 'custom-control-label';
                    label.htmlFor = id;
                    label.textContent = value;
                    wrap.appendChild(input);
                    wrap.appendChild(label);
                    box.appendChild(wrap);
                });
            }

            form.querySelectorAll('input[name="type"]').forEach(function (radio) {
                radio.addEventListener('change', applyType);
            });
            var conditionSelect = document.getElementById('condition_question_id');
            if (conditionSelect) {
                conditionSelect.addEventListener('change', function () { selectedValues = []; renderConditionValues(); });
            }
            applyType();
            renderConditionValues();
        })();
    </script>
@endsection
