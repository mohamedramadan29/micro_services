@extends('website.layouts.master')
@php
    // «شاركنا اقتراحك» (client file «نظام الاقتراحات»): texts from the content admin (area suggestions.page).
    $page = \App\Support\SiteContent::first('suggestions.page');
    $created = session('suggestion_created');
    $draftKey = 'rf-suggestion-draft:' . ($user?->id ?? 'guest');
    $steps = array_values(array_filter([$page['step1'], $page['step2'], $page['step3'], $page['step4']]));
@endphp
@section('title')
    {{ $page['title'] }}
@endsection
@section('content')
    <section class="gray-bg text-right rf-page" dir="rtl">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page['title'] }}</li>
                </ol>
            </nav>
            <div class="rf-card">
                <div class="rf-card-header">
                    <h1 class="rf-title">{{ $page['title'] }}</h1>
                    @if ($page['subtitle'])<p class="rf-subtitle">{{ $page['subtitle'] }}</p>@endif
                </div>

                <div class="rf-sg-layout">
                    <div class="rf-sg-main">
                        @if ($created)
                            <div class="rf-notice rf-notice-success" role="status" id="rf-suggestion-created" data-draft-key="{{ $draftKey }}">
                                {{-- The admin's text is escaped; only the number is wrapped so «SUG-1004» never breaks in RTL. --}}
                                <strong>{!! str_replace(':number', '<bdi dir="ltr" class="text-nowrap">' . e($created) . '</bdi>', e($page['success'] ?: 'شكرًا لمشاركتك. وصل اقتراحك بنجاح، رقم المتابعة :number')) !!}</strong>
                                <div class="mt-2">
                                    @if ($user)
                                        <a href="{{ route('suggestions.mine') }}" class="rf-btn-link">تابع حالته في «اقتراحاتي»</a> ·
                                    @endif
                                    <a href="{{ route('suggestions.create') }}" class="rf-btn-link">أرسل اقتراحًا آخر</a>
                                </div>
                            </div>
                        @else
                            @if ($page['support_note'])
                                <p class="rf-muted mb-3">{{ $page['support_note'] }} <a href="{{ url('tickets') }}">الدعم الفني</a></p>
                            @endif
                            @if ($errors->any())
                                <div class="rf-notice rf-notice-error" role="alert"><strong>راجع الحقول المظللة بالأسفل.</strong> ما كتبته ما زال هنا.</div>
                            @endif
                            <div class="rf-notice rf-draft-notice" id="rf-suggestion-draft-notice" hidden role="status">استعدنا ما كتبته سابقًا في هذا الاقتراح.</div>

                            <form method="POST" action="{{ route('suggestions.store') }}" id="suggestion-form" novalidate
                                  data-draft-key="{{ $draftKey }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
                                @csrf
                                <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
                                {{-- Left empty by people; bots fill it. --}}
                                <div class="rf-sg-trap" aria-hidden="true">
                                    <label for="sg-extra">اتركه فارغًا</label>
                                    <input type="text" id="sg-extra" name="nf_extra" tabindex="-1" autocomplete="off">
                                </div>

                                @if ($user)
                                    <p class="rf-muted">ترسل باسم <strong>{{ $user->name }}</strong> ({{ $user->typeLabel() }})، ونخبرك بتحديثات اقتراحك في حسابك.</p>
                                @endif

                                <div class="rf-field">
                                    <label class="rf-field-label" for="sg-category">نوع الاقتراح <span class="text-danger">*</span></label>
                                    <select id="sg-category" name="category_id" class="form-control" required aria-describedby="sg-category-error">
                                        <option value="">-- اختر النوع --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="rf-field-error" id="sg-category-error">{{ $message }}</div>@enderror
                                </div>

                                <div class="rf-field">
                                    <label class="rf-field-label" for="sg-title">عنوان الاقتراح <span class="text-danger">*</span></label>
                                    <input type="text" id="sg-title" name="title" class="form-control" required minlength="5"
                                           maxlength="{{ \App\Models\Suggestion::TITLE_MAX }}" value="{{ old('title') }}" placeholder="{{ $page['title_hint'] }}" aria-describedby="sg-title-help">
                                    <small class="rf-field-help" id="sg-title-help">عنوان قصير وواضح، حتى {{ \App\Models\Suggestion::TITLE_MAX }} حرفًا.</small>
                                    @error('title')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>

                                <div class="rf-field">
                                    <label class="rf-field-label" for="sg-description">اشرح لنا فكرتك <span class="text-danger">*</span></label>
                                    <textarea id="sg-description" name="description" rows="7" class="form-control" required
                                              minlength="{{ \App\Models\Suggestion::DESCRIPTION_MIN }}" maxlength="{{ \App\Models\Suggestion::DESCRIPTION_MAX }}"
                                              placeholder="{{ $page['description_hint'] }}" aria-describedby="sg-description-count">{{ old('description') }}</textarea>
                                    <small class="rf-field-help" id="sg-description-count" aria-live="polite">من {{ \App\Models\Suggestion::DESCRIPTION_MIN }} إلى {{ \App\Models\Suggestion::DESCRIPTION_MAX }} حرف.</small>
                                    @error('description')<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>

                                @guest
                                    <fieldset class="rf-section">
                                        <legend class="rf-section-title">بياناتك (اختيارية)</legend>
                                        <div class="rf-field">
                                            <label class="rf-field-label" for="sg-name">الاسم</label>
                                            <input type="text" id="sg-name" name="guest_name" class="form-control" maxlength="100" value="{{ old('guest_name') }}" autocomplete="name">
                                            @error('guest_name')<div class="rf-field-error">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="rf-field rf-sg-updates">
                                            <input type="checkbox" id="sg-updates" name="wants_updates" value="1" @checked(old('wants_updates'))>
                                            <label for="sg-updates">أرسلوا لي تحديثات الاقتراح على بريدي</label>
                                        </div>
                                        <div class="rf-field" id="sg-email-field" @if (! old('wants_updates')) hidden @endif>
                                            <label class="rf-field-label" for="sg-email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                            <input type="email" id="sg-email" name="guest_email" class="form-control" dir="ltr" maxlength="150" value="{{ old('guest_email') }}" autocomplete="email">
                                            @error('guest_email')<div class="rf-field-error">{{ $message }}</div>@enderror
                                        </div>
                                        <p class="rf-muted mb-0">لديك حساب؟ <a href="{{ route('user_login') }}">سجّل الدخول</a> لتتابع اقتراحاتك من «اقتراحاتي».</p>
                                    </fieldset>
                                @endguest

                                <div class="rf-submit">
                                    <div class="rf-submit-actions">
                                        <button type="submit" class="btn rf-btn-primary">{{ $page['submit'] ?: 'إرسال الاقتراح' }}</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>

                    @if ($steps)
                        <aside class="rf-sg-aside" aria-labelledby="sg-steps-title">
                            <h2 class="rf-section-title" id="sg-steps-title">{{ $page['steps_title'] ?: 'ماذا يحدث بعد الإرسال؟' }}</h2>
                            <ol class="rf-join-steps mb-2">
                                @foreach ($steps as $step)
                                    <li><span>{{ $step }}</span></li>
                                @endforeach
                            </ol>
                            @if ($page['note'])<p class="rf-muted mb-0">{{ $page['note'] }}</p>@endif
                        </aside>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        // What was written is kept in this browser until the suggestion is saved (expired session,
        // network error, reload) and cleared once the success message shows.
        (function () {
            var done = document.getElementById('rf-suggestion-created');
            if (done) { try { localStorage.removeItem(done.dataset.draftKey); } catch (e) {} return; }
            var form = document.getElementById('suggestion-form');
            if (!form) return;
            var key = form.dataset.draftKey, fields = ['category_id', 'title', 'description', 'guest_name', 'guest_email'];
            var saved = null;
            try { saved = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) {}
            var empty = fields.every(function (f) { return !form.elements[f] || !form.elements[f].value; });
            if (saved && empty && form.dataset.hasErrors !== '1') {
                fields.forEach(function (f) { if (form.elements[f] && saved[f]) form.elements[f].value = saved[f]; });
                document.getElementById('rf-suggestion-draft-notice').hidden = false;
            }
            var save = function () {
                var data = {};
                fields.forEach(function (f) { if (form.elements[f]) data[f] = form.elements[f].value; });
                try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
            };
            form.addEventListener('input', save);
            form.addEventListener('change', save);

            var desc = form.elements.description, counter = document.getElementById('sg-description-count');
            var count = function () { if (desc.value.length) counter.textContent = desc.value.length + ' / {{ \App\Models\Suggestion::DESCRIPTION_MAX }} حرف'; };
            desc.addEventListener('input', count);
            count();

            var updates = document.getElementById('sg-updates'), emailField = document.getElementById('sg-email-field');
            if (updates) updates.addEventListener('change', function () {
                emailField.hidden = !updates.checked;
                form.elements.guest_email.required = updates.checked;
            });

            // One submission per click; the server also refuses a repeat of the same form (submission_key).
            form.addEventListener('submit', function (e) {
                if (form.dataset.submitting === '1') { e.preventDefault(); return; }
                if (!navigator.onLine) { e.preventDefault(); alert('لا يوجد اتصال بالإنترنت الآن. ما كتبته محفوظ، أعد المحاولة عند عودة الاتصال.'); return; }
                form.dataset.submitting = '1';
                form.querySelector('[type=submit]').disabled = true;
            });
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) { delete form.dataset.submitting; form.querySelector('[type=submit]').disabled = false; }
            });
        })();
    </script>
@endsection
