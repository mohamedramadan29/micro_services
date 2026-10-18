@extends('website.layouts.master')
@php
    // Service 3 «مشروعك ما بيوقف» uses this page in «staged» mode (texts from the content admin).
    $staged = $staged ?? false;
    $stagedPage = $staged ? \App\Support\SiteContent::first('staged.page') : null;
@endphp
@section('title')
    {{ $staged ? $stagedPage['title'] : 'اطلب عرضًا لمشروعك' }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <div class="rf-card">
                @if ($staged)
                    <div class="rf-card-header">
                        @if ($stagedPage['badge'])<span class="rf-badge">{{ $stagedPage['badge'] }}</span>@endif
                        <h1 class="rf-title">{{ $stagedPage['title'] }}</h1>
                        @if ($stagedPage['subtitle'])<p class="rf-subtitle">{{ $stagedPage['subtitle'] }}</p>@endif
                    </div>
                    @php $stagedSteps = \App\Support\SiteContent::items('staged.steps'); @endphp
                    @if ($stagedSteps->isNotEmpty())
                        <div class="rf-staged-how">
                            @if ($stagedPage['steps_title'])<h2 class="rf-section-title">{{ $stagedPage['steps_title'] }}</h2>@endif
                            <ol>
                                @foreach ($stagedSteps as $step)
                                    <li><strong>{{ $step['title'] }}</strong>@if ($step['description'])<span>{{ $step['description'] }}</span>@endif</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                @else
                <div class="rf-card-header">
                    <span class="rf-badge">اطلب عرضًا لمشروعك</span>
                    <h1 class="rf-title">أخبرنا ماذا تحتاج</h1>
                    <p class="rf-subtitle">اختر الخدمة وأجب عن أسئلتها. بياناتك تُحفظ كاملة، وأنت تقرر في النهاية: تنفيذ المشروع، أو معرفة الأسعار أولًا.</p>
                </div>
                @endif

                @if (! $client->isClient())
                    <div class="rf-notice">
                        <strong>إرسال الطلبات متاح لحسابات العملاء.</strong>
                        @if ($client->isFreelancer())
                            حسابك مسجّل كمستقل، ويمكنك <a href="{{ route('freelancer.requests.index') }}">متابعة طلبات العملاء</a> وتقديم عروضك عليها.
                        @else
                            لإرسال طلب غيّر نوع حسابك إلى «عميل» من <a href="{{ url('update-account') }}">تعديل الملف الشخصي</a>.
                        @endif
                    </div>
                @elseif ($categories->isEmpty())
                    <div class="rf-notice">لا توجد خدمات متاحة للطلب حاليًا، حاول مرة أخرى قريبًا.</div>
                @else
                    @if ($errors->any())
                        <div class="rf-notice rf-notice-error" role="alert">
                            <strong>راجع الحقول المظللة بالأسفل.</strong>
                            @if (! empty(old('sub_category_id')))
                                إذا كنت أرفقت ملفات، أعد إرفاقها قبل الإرسال.
                            @endif
                        </div>
                    @endif

                    @if (! empty($unavailable))
                        <div class="rf-notice rf-notice-warning" role="status">
                            خدمة «{{ $unavailable->name }}» لا تُطلب من هذه الصفحة حاليًا.
                            <a href="{{ route('services.index', ['help' => 1, 'service' => $unavailable->id]) }}">اطلب المساعدة وسيتواصل معك فريقنا</a>، أو اختر خدمة أخرى بالأسفل.
                        </div>
                    @endif
                    <div class="rf-notice rf-draft-notice" id="rf-draft-notice" hidden role="status">
                        استعدنا ما كتبته سابقًا في هذا الطلب. الملفات المرفقة لا تُحفظ، أعد إرفاقها قبل الإرسال.
                        <button type="button" class="btn rf-btn-outline btn-sm mt-1" id="rf-draft-reset">ابدأ من جديد</button>
                    </div>

                    <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" id="request-form" data-track-start="{{ $staged ? 'staged_project' : 'request' }}"
                        data-draft-key="{{ $staged ? 'rf-draft-staged' : 'rf-draft' }}:{{ $client->id }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
                        @csrf
                        @if ($staged)
                            @include('website.service-requests.partials.staged-fields', ['page' => $stagedPage])
                        @endif
                        <div class="rf-field rf-service-picker">
                            <label class="rf-field-label" for="rf-service">الخدمة <span class="text-danger">*</span></label>
                            <select class="form-control" id="rf-service" name="sub_category_id" required
                                data-fields-url="{{ route('requests.fields', ['subCategory' => '__ID__']) }}">
                                <option value="">-- اختر الخدمة التي تحتاجها --</option>
                                @foreach ($categories as $category)
                                    <optgroup label="{{ $category->name }}">
                                        @foreach ($category->subCategories as $subCategory)
                                            <option value="{{ $subCategory->id }}" @selected($form && $form->sub_category_id === $subCategory->id)>{{ $subCategory->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('sub_category_id')
                                <div class="rf-field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rf-form" data-request-form>
                            <div data-service-questions aria-live="polite">
                                @if ($form)
                                    @include('website.service-requests.partials.service-questions', ['form' => $form])
                                @else
                                    <p class="rf-placeholder">اختر الخدمة لتظهر أسئلتها.</p>
                                @endif
                            </div>
                            @include('website.service-requests.partials.shared-fields', ['client' => $client])
                        </div>

                        @if ($staged)
                        <div class="rf-submit">
                            <div class="rf-submit-actions">
                                <button type="submit" name="action" value="{{ \App\Models\ServiceRequest::PATH_EXECUTE }}" class="btn rf-btn-primary">{{ $stagedPage['submit'] }}</button>
                            </div>
                            <p class="rf-muted mt-2 mb-0">يُنشر مشروعك للمستقلين المختصين دون بيانات تواصلك. لا تدفع شيئًا الآن: بعد اختيار المستقل والاتفاق على المراحل تموّل المرحلة الأولى فقط.</p>
                        </div>
                        @else
                        <div class="rf-submit">
                            <p class="rf-submit-title">ماذا تريد الآن؟</p>
                            <div class="rf-submit-actions">
                                <button type="submit" name="action" value="{{ \App\Models\ServiceRequest::PATH_EXECUTE }}" class="btn rf-btn-primary">
                                    أريد تنفيذ المشروع
                                </button>
                                <button type="submit" name="action" value="{{ \App\Models\ServiceRequest::PATH_EXPLORE }}" class="btn rf-btn-secondary">
                                    ما بدي أنفذ هلأ، بدي أشوف الأسعار
                                </button>
                            </div>
                            <ul class="rf-path-notes">
                                <li><strong>أريد تنفيذ المشروع:</strong> يُنشر طلبك للمستقلين المختصين دون بيانات تواصلك، وتصلك عروضهم لتقارن بينها.</li>
                                <li><strong>أشوف الأسعار:</strong> يُحفظ طلبك كاستفسار خاص لدى فريقنا فقط، ولا يُنشر إلا بموافقتك.</li>
                            </ul>
                        </div>
                        @endif
                    </form>

                    {{-- «اتعثرت؟»: a help request to the team with the service and what was written so far (client file «المربعات الخمسة»). --}}
                    <details class="rf-stuck" id="rf-stuck" @if ($errors->stuck->any()) open @endif>
                        <summary><i class="bi bi-life-preserver"></i> اتعثرت أو مش متأكد تكمل إزاي؟ اطلب مساعدة فريق نفّذها</summary>
                        <form method="POST" action="{{ route('services.help.store') }}" class="mt-2" id="rf-stuck-form">
                            @csrf
                            <input type="hidden" name="source" value="{{ $staged ? 'staged_form' : 'request_form' }}">
                            <input type="hidden" name="service" value="">
                            <input type="hidden" name="context" value="">
                            <div class="row">
                                <div class="col-md-4 rf-field">
                                    <label class="rf-field-label" for="rf-stuck-wa">رقم واتساب</label>
                                    <input type="tel" id="rf-stuck-wa" name="whatsapp" data-country-picker dir="ltr" maxlength="30" class="form-control" value="{{ old('whatsapp', $client->whatsapp ?: $client->phone) }}" placeholder="+ رمز الدولة">
                                    @error('whatsapp', 'stuck')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 rf-field">
                                    <label class="rf-field-label" for="rf-stuck-msg">أين توقفت؟</label>
                                    <input type="text" id="rf-stuck-msg" name="message" maxlength="2000" class="form-control" value="{{ old('message') }}" placeholder="مثال: مش عارف أختار بين خدمتين، أو سؤال مش واضح">
                                    @error('message', 'stuck')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <button class="btn rf-btn-secondary">أرسل طلب المساعدة</button>
                            <small class="rf-muted d-block mt-1">نرسل للفريق الخدمة المختارة وما كتبته حتى الآن، ولا يُنشر شيء للمستقلين.</small>
                        </form>
                    </details>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="{{ \App\Support\AssetVersion::url('assets/website/js/service-request-form.js') }}"></script>
    <script>
        // «اتعثرت؟»: send the chosen service and what the client wrote with the help request.
        (function () {
            var help = document.getElementById('rf-stuck-form'), form = document.getElementById('request-form');
            if (!help || !form) return;
            help.addEventListener('submit', function () {
                var service = form.querySelector('[name=sub_category_id]');
                var problem = form.querySelector('[name=problem]');
                var title = form.querySelector('[name=project_title]');
                var parts = [];
                if (service && service.selectedIndex > 0) parts.push('الخدمة: ' + service.options[service.selectedIndex].text);
                if (title && title.value) parts.push('عنوان المشروع: ' + title.value);
                if (problem && problem.value) parts.push('ما كتبه: ' + problem.value.slice(0, 1500));
                help.elements.service.value = service ? service.value : '';
                help.elements.context.value = parts.join('\n');
            });
        })();
    </script>
    <script>
        (function () {
            var select = document.getElementById('rf-service');
            if (!select) { return; }
            var root = document.querySelector('[data-request-form]');
            var box = root.querySelector('[data-service-questions]');
            var cache = {};        // service id -> detached questions (keeps typed answers)
            var current = select.value;
            var loading = 0;

            function detach() {
                var fragment = document.createDocumentFragment();
                while (box.firstChild) { fragment.appendChild(box.firstChild); }
                return fragment;
            }

            function show(fragmentOrHtml) {
                if (typeof fragmentOrHtml === 'string') {
                    box.innerHTML = fragmentOrHtml;
                } else {
                    box.appendChild(fragmentOrHtml);
                }
                window.ServiceRequestForm.prepare(root);
                if (window.RequestDraft) { window.RequestDraft.restore(box); }
            }

            select.addEventListener('change', function () {
                var id = select.value;
                var previous = detach();
                if (current && previous.querySelector('.rf-field')) { cache[current] = previous; }
                current = id;
                if (!id) {
                    show('<p class="rf-placeholder">اختر الخدمة لتظهر أسئلتها.</p>');
                    return;
                }
                if (cache[id]) {
                    show(cache[id]);
                    delete cache[id];
                    return;
                }
                var ticket = ++loading;
                show('<p class="rf-placeholder">جارٍ تحميل أسئلة الخدمة…</p>');
                fetch(select.dataset.fieldsUrl.replace('__ID__', encodeURIComponent(id)), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (response) {
                        if (!response.ok) { throw new Error(response.status); }
                        return response.text();
                    })
                    .then(function (html) {
                        if (ticket === loading) { detach(); show(html); }
                    })
                    .catch(function () {
                        if (ticket === loading) {
                            detach();
                            show('<p class="rf-placeholder rf-field-error">تعذّر تحميل أسئلة هذه الخدمة، حاول مرة أخرى.</p>');
                        }
                    });
            });
        })();

        // Draft: keep what the client typed (not files) in this browser until the
        // request is sent, so going back or a temporary error loses nothing.
        (function () {
            var form = document.getElementById('request-form');
            if (!form) { return; }
            var key = form.dataset.draftKey;
            var maxAgeMs = 7 * 24 * 60 * 60 * 1000;
            var select = document.getElementById('rf-service');
            var root = form.querySelector('[data-request-form]');
            var timer = null;

            function read() {
                try {
                    var draft = JSON.parse(localStorage.getItem(key) || 'null');
                    return draft && Date.now() - draft.at < maxAgeMs ? draft : null;
                } catch (e) { return null; }
            }
            function write(draft) {
                try { localStorage.setItem(key, JSON.stringify(draft)); } catch (e) { /* storage unavailable */ }
            }
            function clear() {
                try { localStorage.removeItem(key); } catch (e) { /* storage unavailable */ }
            }
            function skip(el) {
                return !el.name || el.disabled || el.type === 'file' || el.type === 'submit' || el.type === 'button' || el.name === '_token';
            }

            function collect() {
                var values = {};
                Array.prototype.forEach.call(form.elements, function (el) {
                    if (skip(el)) { return; }
                    if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) { return; }
                    if (el.name.slice(-2) === '[]') {
                        (values[el.name] = values[el.name] || []).push(el.value);
                    } else if (el.value !== '') {
                        values[el.name] = el.value;
                    }
                });
                return values;
            }

            function save() {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    write({ at: Date.now(), service: select ? select.value : '', values: collect() });
                }, 400);
            }

            // Fill empty fields inside scope from the draft; never overwrite what is there.
            function restore(scope) {
                var draft = read();
                if (!draft || form.dataset.hasErrors === '1') { return false; }
                var restored = false;
                scope.querySelectorAll('input, select, textarea').forEach(function (el) {
                    if (!el.name || el.type === 'file' || el.name === '_token' || !(el.name in draft.values)) { return; }
                    var saved = draft.values[el.name];
                    if (el.type === 'radio') {
                        if (!form.querySelector('[name="' + CSS.escape(el.name) + '"]:checked') && el.value === saved) { el.checked = true; restored = true; }
                    } else if (el.type === 'checkbox') {
                        if ((Array.isArray(saved) ? saved.indexOf(el.value) !== -1 : saved === el.value) && !el.checked) { el.checked = true; restored = true; }
                    } else if (el !== select && el.value === '') {
                        el.value = saved;
                        restored = true;
                    }
                });
                if (restored) {
                    root.dispatchEvent(new Event('change', { bubbles: true }));
                    document.getElementById('rf-draft-notice').hidden = false;
                }
                return restored;
            }

            window.RequestDraft = { restore: restore, clear: clear };

            var draft = read();
            if (draft && form.dataset.hasErrors !== '1') {
                restore(form);
                if (select && !select.value && draft.service && select.querySelector('option[value="' + CSS.escape(draft.service) + '"]')) {
                    select.value = draft.service;
                    select.dispatchEvent(new Event('change'));
                }
            }

            form.addEventListener('input', save);
            form.addEventListener('change', save);

            document.getElementById('rf-draft-reset').addEventListener('click', function () {
                clear();
                window.location.href = window.location.pathname;
            });

            // Coming back to this page from the browser cache (Back button): undo the "sending" state.
            window.addEventListener('pageshow', function (event) {
                if (!event.persisted) { return; }
                delete form.dataset.submitting;
                form.removeAttribute('aria-busy');
                form.querySelectorAll('input[type="hidden"][data-submitter]').forEach(function (input) { input.remove(); });
                form.querySelectorAll('.rf-submit-actions button').forEach(function (button) {
                    button.disabled = false;
                    if (button.dataset.label) { button.textContent = button.dataset.label; }
                });
            });

            // Progress while sending, and no double submit. The submit button's
            // name/value is copied into a hidden field because disabled buttons are not sent.
            form.addEventListener('submit', function (event) {
                if (event.defaultPrevented) { return; }
                var submitter = event.submitter;
                if (submitter && submitter.name) {
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.setAttribute('data-submitter', '1');
                    hidden.name = submitter.name;
                    hidden.value = submitter.value;
                    form.appendChild(hidden);
                }
                form.querySelectorAll('.rf-submit-actions button').forEach(function (button) {
                    button.disabled = true;
                    if (button === submitter) {
                        button.dataset.label = button.textContent;
                        button.textContent = 'جارٍ الإرسال…';
                    }
                });
            });
        })();
    </script>
@endsection
