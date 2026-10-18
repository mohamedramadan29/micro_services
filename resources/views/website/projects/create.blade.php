@extends('website.layouts.master')
@php
    // «انشر مشروعك» (client file «المربعات الخمسة»): texts from the content admin (area projects.page).
    $page = \App\Support\SiteContent::first('projects.page');
    $editing = isset($project);
    $draftKey = 'rf-project-draft:' . ($user?->id ?? 'guest');
    $selectedService = old('sub_category_id', $selected);
@endphp
@section('title')
    {{ $editing ? 'تعديل المشروع ' . $project->number : $page['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card">
                <div class="rf-card-header">
                    @if ($page['badge'])<span class="rf-badge">{{ $page['badge'] }}</span>@endif
                    <h1 class="rf-title">{{ $editing ? 'تعديل المشروع ' . $project->number : $page['title'] }}</h1>
                    @if ($page['subtitle'] && ! $editing)<p class="rf-subtitle">{{ $page['subtitle'] }}</p>@endif
                </div>

                @if (! $canPost)
                    <div class="rf-notice">
                        <strong>نشر المشاريع متاح لحسابات العملاء.</strong>
                        @if ($user->isFreelancer())
                            حسابك مسجّل كمستقل؛ تابع <a href="{{ route('freelancer.requests.index') }}">طلبات العملاء</a> وقدّم عروضك عليها.
                        @endif
                    </div>
                @else
                    @if (! $editing)
                        <p class="rf-muted mb-3">{{ $page['other_paths'] }}
                            <a href="{{ route('requests.create') }}">اطلب عرضًا بالأسئلة الموجّهة</a> ·
                            <a href="{{ route('diagnose') }}">شخّص مشروعك</a>
                        </p>
                    @endif
                    @if ($restored === 'login')
                        <div class="rf-notice rf-notice-success" role="status">استعدنا مشروعك كما كتبته قبل تسجيل الدخول. أضف المرفقات إن أردت ثم انشره.</div>
                    @elseif ($restored === 'diagnosis')
                        <div class="rf-notice" role="status">بدأنا مشروعك مما كتبته في التشخيص؛ أكمل التفاصيل.</div>
                    @endif
                    @if ($errors->any())
                        <div class="rf-notice rf-notice-error" role="alert"><strong>راجع الحقول المظللة بالأسفل.</strong> إذا كنت أرفقت ملفات، أعد إرفاقها.</div>
                    @endif
                    <div class="rf-notice rf-draft-notice" id="rf-project-draft-notice" hidden role="status">استعدنا ما كتبته سابقًا في هذا المشروع.</div>

                    <form method="POST" action="{{ $editing ? route('projects.update', $project) : route('projects.store') }}" enctype="multipart/form-data"
                          id="project-form" data-track-start="project" data-draft-key="{{ $editing ? '' : $draftKey }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
                        @csrf
                        <fieldset class="rf-section rf-section-shared">
                            <div class="rf-field">
                                <label class="rf-field-label" for="pj-title">عنوان المشروع <span class="text-danger">*</span></label>
                                <input type="text" id="pj-title" name="title" class="form-control" maxlength="150" value="{{ old('title') }}" placeholder="مثال: تصميم متجر إلكتروني لبيع الملابس">
                                @error('title')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="rf-field">
                                <label class="rf-field-label" for="pj-service">مجال المشروع <span class="text-danger">*</span></label>
                                <select id="pj-service" name="sub_category_id" class="form-control">
                                    <option value="">-- اختر الخدمة الأقرب لمشروعك --</option>
                                    @foreach ($groups as $group)
                                        <optgroup label="{{ $group->name }}">
                                            @foreach ($group->subCategories as $service)
                                                <option value="{{ $service->id }}" @selected((string) $selectedService === (string) $service->id)>{{ $service->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small class="rf-field-help">يصل مشروعك للمستقلين المختصين في هذه الخدمة.</small>
                                @error('sub_category_id')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="rf-field">
                                <label class="rf-field-label" for="pj-description">وصف المشروع <span class="text-danger">*</span></label>
                                <textarea id="pj-description" name="description" rows="7" maxlength="5000" class="form-control" placeholder="{{ $page['description_hint'] }}">{{ old('description') }}</textarea>
                                @error('description')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-6 rf-field">
                                    <label class="rf-field-label" for="pj-min">الميزانية من (ج.م)</label>
                                    <input type="number" id="pj-min" name="budget_min" min="1" step="1" class="form-control" value="{{ old('budget_min') }}">
                                    @error('budget_min')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 col-6 rf-field">
                                    <label class="rf-field-label" for="pj-max">إلى (ج.م)</label>
                                    <input type="number" id="pj-max" name="budget_max" min="1" step="1" class="form-control" value="{{ old('budget_max') }}">
                                    @error('budget_max')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 rf-field">
                                    <label class="rf-field-label" for="pj-deadline">الموعد المطلوب (اختياري)</label>
                                    <input type="date" id="pj-deadline" name="deadline" min="{{ now()->addDay()->format('Y-m-d') }}" class="form-control" value="{{ old('deadline') }}">
                                    @error('deadline')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="rf-field">
                                <label class="rf-field-label" for="pj-files">مرفقات (اختياري)</label>
                                @if ($editing && $project->files->isNotEmpty())
                                    <ul class="rf-files mb-2">
                                        @foreach ($project->files as $file)
                                            <li><label class="rf-remove-file"><input type="checkbox" name="remove_files[]" value="{{ $file->id }}"> حذف</label> {{ $file->original_name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if ($user)
                                    <input type="file" id="pj-files" name="attachments[]" multiple class="form-control"
                                        accept="{{ collect(\App\Services\ServiceRequests\RequestSubmission::ATTACHMENT_EXTENSIONS)->map(fn ($ext) => '.' . $ext)->implode(',') }}">
                                    <small class="rf-field-help">حتى {{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENTS_MAX }} ملفات، {{ \App\Services\ServiceRequests\RequestSubmission::ATTACHMENT_MAX_MB }} ميجابايت لكل ملف.</small>
                                @else
                                    <p class="rf-muted mb-0">تضيف المرفقات بعد تسجيل الدخول؛ ما تكتبه الآن يُحفظ ويعود إليك.</p>
                                @endif
                                @error('attachments')<div class="rf-field-error">{{ $message }}</div>@enderror
                                @error('attachments.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>

                            @if ($user && empty($user->phone) && ! $editing)
                                <div class="rf-field">
                                    <label class="rf-field-label" for="pj-phone">رقم التواصل <span class="text-danger">*</span></label>
                                    <input type="tel" id="pj-phone" name="contact_phone" dir="ltr" maxlength="30" class="form-control" value="{{ old('contact_phone') }}" placeholder="+ رمز الدولة">
                                    <small class="rf-field-help">لفريق نفّذها فقط، لا يظهر للمستقلين.</small>
                                    @error('contact_phone')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                            @endif
                        </fieldset>

                        <div class="rf-submit">
                            <div class="rf-submit-actions">
                                <button type="submit" class="btn rf-btn-primary">{{ $editing ? 'حفظ التعديلات' : ($user ? $page['submit'] : $page['guest_submit']) }}</button>
                                @if ($editing)<a href="{{ route('requests.show', $project) }}" class="btn rf-btn-outline">إلغاء</a>@endif
                            </div>
                            @if (! $editing)<p class="rf-muted mt-2 mb-0">{{ $page['note'] }}</p>@endif
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        // The text of the project is kept in this browser until it is published (reload, login…); never the files.
        (function () {
            var form = document.getElementById('project-form');
            if (!form || !form.dataset.draftKey) return;
            var key = form.dataset.draftKey, fields = ['title', 'description', 'sub_category_id', 'budget_min', 'budget_max', 'deadline'];
            var read = function (k) { try { return JSON.parse(localStorage.getItem(k) || 'null'); } catch (e) { return null; } };
            var saved = read(key);
            // A visitor's draft moves once to the account that logs in on this browser.
            if (!saved && key !== 'rf-project-draft:guest') {
                saved = read('rf-project-draft:guest');
                try { localStorage.removeItem('rf-project-draft:guest'); } catch (e) {}
            }
            var empty = fields.every(function (f) { return !form.elements[f] || !form.elements[f].value; });
            if (saved && empty && form.dataset.hasErrors !== '1') {
                fields.forEach(function (f) { if (form.elements[f] && saved[f]) form.elements[f].value = saved[f]; });
                document.getElementById('rf-project-draft-notice').hidden = false;
            }
            var save = function () {
                var data = {};
                fields.forEach(function (f) { if (form.elements[f]) data[f] = form.elements[f].value; });
                try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
            };
            form.addEventListener('input', save);
            form.addEventListener('change', save);
            form.addEventListener('submit', function (e) {
                if (form.dataset.submitting === '1') { e.preventDefault(); return; }
                form.dataset.submitting = '1';
            });
            window.addEventListener('pageshow', function (e) { if (e.persisted) { delete form.dataset.submitting; } });
        })();
    </script>
@endsection
