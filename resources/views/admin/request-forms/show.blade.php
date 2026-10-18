@extends('admin.layouts.master')
@section('title')
    نموذج {{ $subCategory->name }}
@endsection
@section('css')
    <style>
        .rf-section { border: 1px solid #e3e8ef; border-radius: 8px; margin-bottom: 16px; background: #fff; }
        .rf-section-header { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-bottom: 1px solid #eef1f5; }
        .rf-section-header h6 { margin: 0; font-size: 15px; }
        .rf-questions { list-style: none; margin: 0; padding: 8px 10px; min-height: 44px; }
        .rf-question { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid #eef1f5; border-radius: 6px; margin-bottom: 8px; background: #fbfcfd; }
        .rf-question:last-child { margin-bottom: 0; }
        .rf-handle { cursor: grab; color: #9aa5b1; padding: 2px 4px; }
        .rf-handle:active { cursor: grabbing; }
        .rf-number { min-width: 24px; color: #6b7785; font-weight: 600; }
        .rf-label { flex: 1; min-width: 0; }
        .rf-label strong { display: block; font-weight: 600; }
        .rf-meta { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
        .rf-tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; line-height: 1.6; background: #f1f3f6; color: #4a5562; }
        .rf-tag-required { background: #fdecea; color: #b42318; }
        .rf-tag-condition { background: #e8f3fd; color: #175cd3; }
        .rf-tag-filter { background: #e7f6f1; color: #1f7a62; }
        .rf-actions { display: flex; gap: 4px; flex-shrink: 0; }
        .rf-actions form { margin: 0; }
        .rf-empty { color: #9aa5b1; font-size: 13px; padding: 6px 4px; }
        .sortable-ghost { opacity: .4; }
        .rf-save-state { font-size: 13px; }
    </style>
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">نماذج الخدمات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/
                    {{ $subCategory->category?->name }} / {{ $subCategory->name }}</span>
            </div>
        </div>
        <div class="my-auto">
            <a href="{{ route('admin.request-forms.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-right"></i> كل النماذج
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $form)
        {{-- No form yet: start empty or copy another service's form --}}
        <div class="card">
            <div class="card-body">
                <h5>لا يوجد نموذج لخدمة «{{ $subCategory->name }}» بعد</h5>
                <p class="text-muted">ابدأ بنموذج فارغ، أو انسخ أسئلة خدمة مشابهة ثم عدّلها.</p>
                <form method="POST" action="{{ route('admin.request-forms.store', $subCategory) }}" class="form-inline">
                    @csrf
                    <select name="copy_from" class="form-control ml-2 mb-2" style="min-width: 260px">
                        <option value="">نموذج فارغ</option>
                        @foreach ($copySources as $source)
                            <option value="{{ $source->id }}">نسخ من: {{ $source->subCategory->name }} ({{ $source->questions_count }} سؤال)</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-plus"></i> إنشاء النموذج</button>
                </form>
            </div>
        </div>
    @else
        {{-- Form header: status and actions --}}
        @if ($form->isRevision())
            <div class="card border-warning">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px">
                    <div>
                        <h5 class="mb-1">مسودة تعديل على نموذج «{{ $subCategory->name }}»</h5>
                        <span class="text-muted tx-13">
                            <span class="badge badge-warning">مسودة تعديل</span>
                            العملاء يرون <a href="{{ route('admin.request-forms.builder', $form->revisionOf) }}">النموذج المنشور</a> حتى تنشر التعديلات · {{ $questionsById->count() }} سؤال
                        </span>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 6px">
                        <a href="{{ route('admin.request-forms.preview', $form) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> معاينة التعديلات
                        </a>
                        <form method="POST" action="{{ route('admin.request-forms.revision.publish', $form) }}"
                            onsubmit="return confirm(@js('نشر التعديلات؟ ستحل محل النموذج المنشور فورًا، والطلبات السابقة لن تتأثر.'))">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> نشر التعديلات</button>
                        </form>
                        <form method="POST" action="{{ route('admin.request-forms.revision.discard', $form) }}"
                            onsubmit="return confirm(@js('تجاهل كل تعديلات المسودة؟'))">
                            @csrf
                            <button type="submit" class="btn btn-light"><i class="fas fa-times"></i> تجاهل المسودة</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px">
                    <div>
                        <h5 class="mb-1">نموذج «{{ $subCategory->name }}»</h5>
                        <span class="text-muted tx-13">
                            الحالة:
                            @if ($form->status === \App\Models\ServiceForm::STATUS_PUBLISHED)
                                <span class="badge badge-success">منشور</span> يظهر للعملاء في صفحة الطلب
                            @elseif ($form->status === \App\Models\ServiceForm::STATUS_PAUSED)
                                <span class="badge badge-warning">متوقف</span> لا يظهر للعملاء
                            @else
                                <span class="badge badge-secondary">مسودة</span> لا يظهر للعملاء حتى تنشره
                            @endif
                            · {{ $questionsById->count() }} سؤال
                        </span>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 6px">
                        <a href="{{ route('admin.request-forms.preview', $form) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> معاينة كما يراها العميل
                        </a>
                        @foreach ([\App\Models\ServiceForm::STATUS_PUBLISHED => ['نشر النموذج', 'btn-success', 'fa-check'], \App\Models\ServiceForm::STATUS_PAUSED => ['إيقاف', 'btn-warning', 'fa-pause'], \App\Models\ServiceForm::STATUS_DRAFT => ['إرجاع لمسودة', 'btn-light', 'fa-pen']] as $status => [$text, $class, $icon])
                            @continue($form->status === $status || ($status === \App\Models\ServiceForm::STATUS_PAUSED && $form->status !== \App\Models\ServiceForm::STATUS_PUBLISHED))
                            <form method="POST" action="{{ route('admin.request-forms.status', $form) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="btn {{ $class }}"><i class="fas {{ $icon }}"></i> {{ $text }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($form->revision)
                <div class="alert alert-warning">
                    توجد مسودة تعديل على هذا النموذج لم تُنشر بعد.
                    <a href="{{ route('admin.request-forms.builder', $form->revision) }}" class="alert-link">افتح مسودة التعديل</a>
                </div>
            @elseif ($form->status === \App\Models\ServiceForm::STATUS_PUBLISHED)
                <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px">
                    <span>هذا النموذج منشور: أي تعديل هنا يظهر للعملاء فورًا. للتعديلات الكبيرة عدّل على مسودة أولًا.</span>
                    <form method="POST" action="{{ route('admin.request-forms.revision', $form) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-code-branch"></i> تعديل كمسودة</button>
                    </form>
                </div>
            @endif
        @endif

        <div class="row row-sm">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted tx-13"><i class="fas fa-grip-vertical"></i> اسحب الأقسام والأسئلة لترتيبها، ويمكن نقل سؤال من قسم لآخر.</span>
                    <span class="rf-save-state" id="rf-save-state"></span>
                </div>

                <div id="rf-sections">
                    @php($number = 0)
                    @foreach ($form->sections as $section)
                        <div class="rf-section" data-section-id="{{ $section->id }}">
                            <div class="rf-section-header">
                                <span class="rf-handle rf-section-handle" title="اسحب لترتيب الأقسام"><i class="fas fa-grip-vertical"></i></span>
                                <div class="flex-fill">
                                    <h6>{{ $section->title }}</h6>
                                    @if ($section->description)
                                        <small class="text-muted">{{ $section->description }}</small>
                                    @endif
                                </div>
                                <div class="rf-actions">
                                    <button type="button" class="btn btn-sm btn-light" data-toggle="collapse" data-target="#edit-section-{{ $section->id }}" title="تعديل القسم"><i class="fas fa-pen"></i></button>
                                    <form method="POST" action="{{ route('admin.request-forms.sections.duplicate', $section) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light" title="نسخ القسم بأسئلته"><i class="far fa-copy"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.request-forms.sections.destroy', $section) }}"
                                        onsubmit="return confirm(@js('حذف القسم «' . ($section->title) . '» وكل أسئلته (' . ($section->questions->count()) . ')؟ إجابات الطلبات السابقة لن تتأثر.'))">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="حذف القسم"><i class="far fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>

                            <div class="collapse" id="edit-section-{{ $section->id }}">
                                <form method="POST" action="{{ route('admin.request-forms.sections.update', $section) }}" class="p-3 border-bottom">
                                    @csrf
                                    <div class="form-group">
                                        <label>عنوان القسم</label>
                                        <input type="text" name="title" class="form-control" value="{{ $section->title }}" required maxlength="255">
                                    </div>
                                    <div class="form-group">
                                        <label>وصف قصير (اختياري)</label>
                                        <input type="text" name="description" class="form-control" value="{{ $section->description }}" maxlength="1000">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">حفظ</button>
                                </form>
                            </div>

                            <ul class="rf-questions" data-section-id="{{ $section->id }}">
                                @foreach ($section->questions as $question)
                                    @php($number++)
                                    <li class="rf-question" data-question-id="{{ $question->id }}">
                                        <span class="rf-handle" title="اسحب لترتيب الأسئلة"><i class="fas fa-grip-vertical"></i></span>
                                        <span class="rf-number">{{ $number }}.</span>
                                        <div class="rf-label">
                                            <strong>{{ $question->label }}</strong>
                                            <div class="rf-meta">
                                                <span class="rf-tag">
                                                    <i class="{{ \App\Models\ServiceFormQuestion::TYPES[$question->type]['icon'] ?? '' }}"></i>
                                                    {{ $question->typeLabel() }}
                                                </span>
                                                @if ($question->is_required)
                                                    <span class="rf-tag rf-tag-required">إلزامي</span>
                                                @else
                                                    <span class="rf-tag">اختياري</span>
                                                @endif
                                                @if ($question->condition && isset($questionsById[$question->condition['question_id']]))
                                                    <span class="rf-tag rf-tag-condition">
                                                        <i class="fas fa-code-branch"></i>
                                                        يظهر عند «{{ \Illuminate\Support\Str::limit($questionsById[$question->condition['question_id']]->label, 30) }}»
                                                        {{ ! empty($question->condition['negate']) ? '≠' : '=' }}
                                                        {{ implode(' أو ', $question->condition['values']) }}
                                                    </span>
                                                @endif
                                                @if ($question->filter_key)
                                                    <span class="rf-tag rf-tag-filter">
                                                        <i class="fas fa-filter"></i> {{ \App\Models\ServiceFormQuestion::FILTER_KEYS[$question->filter_key]['label'] ?? $question->filter_key }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rf-actions">
                                            <a href="{{ route('admin.request-forms.questions.edit', $question) }}" class="btn btn-sm btn-light" title="تعديل"><i class="fas fa-pen"></i></a>
                                            <form method="POST" action="{{ route('admin.request-forms.questions.duplicate', $question) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light" title="نسخ"><i class="far fa-copy"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.request-forms.questions.destroy', $question) }}"
                                                onsubmit="return confirm(@js('حذف السؤال «' . ($question->label) . '»؟ إجابات الطلبات السابقة لن تتأثر.'))">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="حذف"><i class="far fa-trash-alt"></i></button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($section->questions->isEmpty())
                                <div class="rf-empty px-3 pb-2">لا توجد أسئلة في هذا القسم. أضف سؤالًا أو اسحب سؤالًا إلى هنا.</div>
                            @endif
                            <div class="px-3 pb-3">
                                <a href="{{ route('admin.request-forms.questions.create', ['form' => $form, 'section' => $section->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> أضف سؤالًا
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-light btn-block" type="button" data-toggle="collapse" data-target="#new-section">
                            <i class="fas fa-plus"></i> أضف قسمًا
                        </button>
                        <div class="collapse mt-3" id="new-section">
                            <form method="POST" action="{{ route('admin.request-forms.sections.store', $form) }}">
                                @csrf
                                <div class="form-group">
                                    <label>عنوان القسم</label>
                                    <input type="text" name="title" class="form-control" required maxlength="255" placeholder="مثال: تفاصيل الحملة">
                                </div>
                                <div class="form-group">
                                    <label>وصف قصير (اختياري)</label>
                                    <input type="text" name="description" class="form-control" maxlength="1000">
                                </div>
                                <button type="submit" class="btn btn-primary">إضافة القسم</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">المعلومات المشتركة</h6></div>
                    <div class="card-body">
                        <p class="text-muted tx-13">تظهر تلقائيًا في نهاية كل نموذج، ولا تحتاج لإضافتها:</p>
                        <ul class="mb-0 tx-13">
                            <li>الاسم الكامل</li>
                            <li>رقم الهاتف</li>
                            <li>البريد الإلكتروني</li>
                            <li>اشرح المشكلة التي تواجهها أو ما ترغب بتنفيذه</li>
                            <li>المرفقات (اختياري)</li>
                        </ul>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">ملاحظات</h6></div>
                    <div class="card-body tx-13">
                        <ul class="mb-0 pr-3">
                            <li>تعديل النموذج أو حذف سؤال لا يغيّر إجابات الطلبات السابقة.</li>
                            <li>شرط الظهور يجعل السؤال يظهر فقط عند إجابة معينة على سؤال آخر، والسؤال الإلزامي يُطلب فقط عندما يظهر.</li>
                            <li>ربط السؤال بفلتر يجعل إجابته تُستخدم في فلترة الطلبات عند المستقلين.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('js')
    @if ($form)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
        <script>
            (function () {
                var state = document.getElementById('rf-save-state');
                var sectionsEl = document.getElementById('rf-sections');

                function collectOrder() {
                    return Array.prototype.map.call(sectionsEl.querySelectorAll('.rf-section'), function (section) {
                        return {
                            id: section.dataset.sectionId,
                            questions: Array.prototype.map.call(section.querySelectorAll('.rf-question'), function (q) {
                                return q.dataset.questionId;
                            })
                        };
                    });
                }

                function renumber() {
                    sectionsEl.querySelectorAll('.rf-number').forEach(function (el, index) {
                        el.textContent = (index + 1) + '.';
                    });
                }

                function save() {
                    renumber();
                    state.className = 'rf-save-state text-muted';
                    state.textContent = 'جارٍ حفظ الترتيب…';
                    $.ajax({
                        url: @json(route('admin.request-forms.reorder', $form)),
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ sections: collectOrder() }),
                        headers: { 'X-CSRF-TOKEN': @json(csrf_token()) }
                    }).done(function (response) {
                        state.className = 'rf-save-state text-success';
                        state.textContent = response.message;
                    }).fail(function (xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) || 'تعذّر حفظ الترتيب، سيتم إعادة تحميل الصفحة');
                        location.reload();
                    });
                }

                new Sortable(sectionsEl, { handle: '.rf-section-handle', animation: 150, onEnd: save });
                sectionsEl.querySelectorAll('.rf-questions').forEach(function (list) {
                    new Sortable(list, { group: 'questions', handle: '.rf-handle', animation: 150, onEnd: save });
                });
            })();
        </script>
    @endif
@endsection
