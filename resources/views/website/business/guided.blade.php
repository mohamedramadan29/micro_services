@extends('website.layouts.master')
@php
    // The guided request (client file «قسم الشركات» page 7): step 1 = the company (fixed fields), then one step per
    // section of the form the admin builds in «نماذج الخدمات» (questions, conditions, required fields).
    $page = \App\Support\SiteContent::first('business.page');
    $user = auth()->user();
    $sections = $form->sections->filter(fn ($s) => $s->questions->isNotEmpty())->values();
    $stepTitles = array_merge(['تعريف الشركة'], $sections->pluck('title')->all());
@endphp
@section('title')
    {{ $page['guided_button'] ?: 'حدّد طلبك خطوة بخطوة' }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <nav aria-label="breadcrumb"><ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('business.index') }}">نفّذها للأعمال</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $page['guided_button'] ?: 'حدّد طلبك خطوة بخطوة' }}</li>
            </ol></nav>
            <div class="rf-card">
                <div class="rf-card-header">
                    <h1 class="rf-title">{{ $page['guided_title'] ?: 'حدّد طلبك خطوة بخطوة' }}</h1>
                    <p class="rf-subtitle">الأسئلة تتغيّر حسب الخدمات التي تختارها. الميزانية تقديرية؛ نرسل لك عرض سعر بعد مراجعة النطاق.</p>
                </div>

                @if (! $canSend)
                    <div class="rf-notice"><strong>طلبات الشركات لحسابات العملاء.</strong> حسابك ليس حساب عميل؛ يمكنك استخدام <a href="{{ route('business.index') }}#bz-free">الطلب الحر</a>.</div>
                @else
                    @if ($errors->any())
                        <div class="rf-notice rf-notice-error" role="alert"><strong>راجع الحقول المظللة.</strong> إن كنت أرفقت ملفات، أعد إرفاقها.</div>
                    @endif
                    <ol class="rf-steps bz-stepper" aria-label="خطوات الطلب" hidden>
                        @foreach ($stepTitles as $title)
                            <li data-step-dot="{{ $loop->index }}"><span class="sr-only">الخطوة {{ $loop->iteration }}: </span>{{ $title }}</li>
                        @endforeach
                    </ol>

                    <form method="POST" action="{{ route('business.guided.store') }}" enctype="multipart/form-data" id="bz-guided" novalidate>
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
                        <div data-request-form>
                            <fieldset class="rf-section" data-step="0">
                                <legend class="rf-section-title">تعريف الشركة</legend>
                                <div class="row">
                                    <div class="col-md-6 rf-field">
                                        <label class="rf-field-label" for="bz-g-name">اسم الشركة <span class="text-danger">*</span></label>
                                        <input type="text" id="bz-g-name" name="company_name" class="form-control" maxlength="150" required autocomplete="organization"
                                               value="{{ old('company_name', $company?->name) }}">
                                        @error('company_name')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 rf-field">
                                        <label class="rf-field-label" for="bz-g-size">حجم الشركة <span class="text-danger">*</span></label>
                                        <select id="bz-g-size" name="company_size" class="form-control" required>
                                            <option value="">-- اختر --</option>
                                            @foreach (\App\Models\BusinessCompany::SIZES as $key => $label)
                                                <option value="{{ $key }}" @selected(old('company_size', $company?->size) === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('company_size')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 rf-field">
                                        <label class="rf-field-label" for="bz-g-phone">رقم الهاتف / واتساب <span class="text-danger">*</span></label>
                                        <input type="tel" id="bz-g-phone" name="phone" class="form-control" data-country-picker dir="ltr" maxlength="30" required autocomplete="tel"
                                               value="{{ old('phone', $company?->phone ?: ($user->whatsapp ?: $user->phone)) }}">
                                        @error('phone')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 rf-field">
                                        <label class="rf-field-label" for="bz-g-email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                        <input type="email" id="bz-g-email" name="email" class="form-control" dir="ltr" maxlength="150" required autocomplete="email"
                                               value="{{ old('email', $user->email) }}">
                                        @error('email')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </fieldset>

                            @foreach ($sections as $section)
                                <fieldset class="rf-section" data-step="{{ $loop->iteration }}">
                                    <legend class="rf-section-title">{{ $section->title }}</legend>
                                    @if ($section->description)<p class="rf-section-desc">{{ $section->description }}</p>@endif
                                    @foreach ($section->questions as $question)
                                        @include('website.service-requests.partials.question', ['question' => $question])
                                    @endforeach
                                    @if ($loop->last)
                                        <div class="rf-field rf-sg-updates mt-3">
                                            <input type="checkbox" id="bz-g-consent" name="consent" value="1" @checked(old('consent')) required>
                                            <label for="bz-g-consent">{{ $page['consent'] }}</label>
                                        </div>
                                        @error('consent')<div class="rf-field-error">{{ $message }}</div>@enderror
                                    @endif
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="rf-submit bz-step-nav">
                            <div class="rf-submit-actions">
                                <button type="button" class="btn rf-btn-outline" data-step-back hidden>رجوع</button>
                                <button type="button" class="btn rf-btn-primary" data-step-next hidden>متابعة <span aria-hidden="true">←</span></button>
                                <button type="submit" class="btn rf-btn-primary" data-step-submit>{{ $page['submit'] }} <span aria-hidden="true">←</span></button>
                            </div>
                            <p class="rf-muted mt-2 mb-0">الميزانية تقديرية وليست موافقة على السعر؛ يبدأ المشروع بعد عرض السعر واعتمادك له.</p>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="{{ \App\Support\AssetVersion::url('assets/website/js/service-request-form.js') }}"></script>
    <script>
        // One step at a time (without JS every step shows). «متابعة» checks the required fields of the step first.
        (function () {
            var form = document.getElementById('bz-guided');
            if (!form) return;
            var steps = Array.prototype.slice.call(form.querySelectorAll('[data-step]'));
            if (steps.length < 2) return;
            var dots = document.querySelectorAll('[data-step-dot]'), back = form.querySelector('[data-step-back]'),
                next = form.querySelector('[data-step-next]'), submit = form.querySelector('[data-step-submit]');
            document.querySelector('.bz-stepper').hidden = false;
            // Back from an error: open the first step that has one.
            var current = Math.max(0, steps.findIndex(function (s) { return s.querySelector('.rf-field-error'); }));
            var show = function (i) {
                current = i;
                steps.forEach(function (s, k) { s.hidden = k !== i; });
                dots.forEach(function (d, k) { d.classList.toggle('done', k <= i); if (k === i) d.setAttribute('aria-current', 'step'); else d.removeAttribute('aria-current'); });
                back.hidden = i === 0;
                next.hidden = i === steps.length - 1;
                submit.hidden = i !== steps.length - 1;
                var title = steps[i].querySelector('legend');
                if (title) { title.setAttribute('tabindex', '-1'); title.focus({ preventScroll: true }); }
                steps[i].scrollIntoView({ block: 'start', behavior: 'auto' });
            };
            var stepValid = function (step) {
                // A required multi-choice question (checkboxes) has no native «required».
                var multi = step.querySelectorAll('.rf-field[data-type="multi_choice"][data-required="1"]');
                for (var m = 0; m < multi.length; m++) {
                    if (multi[m].hidden || multi[m].querySelector(':checked')) continue;
                    var box = multi[m].querySelector('input[type=checkbox]');
                    if (box) { box.setCustomValidity('من فضلك اختر إجابة واحدة على الأقل'); box.reportValidity(); box.addEventListener('change', function () { this.setCustomValidity(''); }, { once: true }); }
                    return false;
                }
                var inputs = step.querySelectorAll('input, select, textarea');
                for (var k = 0; k < inputs.length; k++) {
                    var input = inputs[k];
                    if (input.disabled || input.type === 'hidden' || input.closest('[hidden]')) continue;
                    if (!input.checkValidity()) { input.reportValidity(); return false; }
                }
                return true;
            };
            next.addEventListener('click', function () { if (stepValid(steps[current])) show(current + 1); });
            back.addEventListener('click', function () { show(current - 1); });
            // The final check of the form script may flag a field of an earlier step: open that step.
            form.addEventListener('submit', function () {
                setTimeout(function () {
                    var error = form.querySelector('.rf-field-error[data-client]');
                    var step = error && error.closest('[data-step]');
                    if (step && step.hidden) show(steps.indexOf(step));
                }, 0);
            });
            show(current);
        })();
    </script>
@endsection
