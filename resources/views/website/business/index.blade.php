@extends('website.layouts.master')
@php
    // «نفّذها للأعمال» (client file «قسم الشركات» pages 4–5): every text from the content admin (areas business.*).
    $page = \App\Support\SiteContent::first('business.page');
    $sizes = \App\Support\SiteContent::items('business.sizes');
    $after = \App\Support\SiteContent::items('business.after');
    $created = session('business_created');
    $user = auth()->user();
    $draftKey = 'rf-business-draft:' . ($user?->id ?? 'guest');
@endphp
@section('title')
    {{ $page['badge'] ?: 'نفّذها للأعمال' }}
@endsection
@section('content')
    <section class="bz-hero" dir="rtl">
        <div class="container">
            @if ($page['badge'])<span class="bz-badge">{{ $page['badge'] }}</span>@endif
            <h1>{{ $page['title'] }}@if ($page['title_accent'])<span>{{ $page['title_accent'] }}</span>@endif</h1>
            @if ($page['services_line'])<p>{{ $page['services_line'] }}</p>@endif
        </div>
    </section>

    <section class="bz-page gray-bg" dir="rtl">
        <div class="container">
            @if ($created)
                <div class="rf-notice rf-notice-success bz-done" role="status" id="bz-created" data-draft-key="{{ $draftKey }}">
                    <strong>{!! str_replace(':number', '<bdi dir="ltr" class="text-nowrap">' . e($created) . '</bdi>', e($page['success'] ?: 'وصل طلبك بنجاح، رقم الطلب :number')) !!}</strong>
                    @if ($user)<div class="mt-2"><a href="{{ route('business.mine') }}" class="rf-btn-link">تابع طلباتك ومشاريعك</a></div>@endif
                </div>
            @endif

            @if ($sizes)
                <div class="bz-sizes">
                    @foreach ($sizes as $size)
                        <div class="bz-size">
                            <span class="bz-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h2>{{ $size['title'] }}</h2>
                            @if ($size['text'])<p>{{ $size['text'] }}</p>@endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bz-paths">
                <div class="bz-path bz-path-free">
                    <h2><i class="bi bi-pencil-square" aria-hidden="true"></i> {{ $page['free_title'] }}</h2>
                    <p>{{ $page['free_text'] }}</p>
                    <a href="#bz-free" class="btn rf-btn-primary">{{ $page['free_button'] ?: 'اكتب طلبك بحرية' }} <span aria-hidden="true">←</span></a>
                </div>
                <div class="bz-path">
                    <h2><i class="bi bi-ui-checks" aria-hidden="true"></i> {{ $page['guided_title'] }}</h2>
                    <p>{{ $page['guided_text'] }}</p>
                    @if ($guidedReady)
                        <a href="{{ route('business.guided') }}" class="btn bz-btn-dark">{{ $page['guided_button'] ?: 'حدّد طلبك خطوة بخطوة' }} <span aria-hidden="true">←</span></a>
                        @guest<small class="d-block mt-2 rf-muted">يحتاج تسجيل الدخول بحساب عميل لتتابع مشروعك بعد ذلك.</small>@endguest
                    @else
                        <small class="rf-muted">النموذج الموجّه غير متاح الآن؛ استخدم الطلب الحر.</small>
                    @endif
                </div>
            </div>

            <div class="bz-free" id="bz-free">
                <div class="rf-card bz-free-card">
                    <h2 class="rf-title">{{ $page['free_form_title'] ?: 'أخبرنا عن مشروعك بالتفصيل' }}</h2>
                    <p class="rf-muted">لا تحتاج اختيار خدمة أو تسجيل حساب. اكتب طلبك بحرية كما تحب.</p>
                    @if ($errors->any())
                        <div class="rf-notice rf-notice-error" role="alert"><strong>راجع الحقول المظللة بالأسفل.</strong> ما كتبته ما زال هنا.</div>
                    @endif
                    <div class="rf-notice rf-draft-notice" id="bz-draft-notice" hidden role="status">استعدنا ما كتبته سابقًا.</div>
                    <form method="POST" action="{{ route('business.free') }}" enctype="multipart/form-data" id="bz-free-form" novalidate
                          data-draft-key="{{ $draftKey }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}" data-track-start="business">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
                        <div class="rf-sg-trap" aria-hidden="true"><label for="bz-extra">اتركه فارغًا</label><input type="text" id="bz-extra" name="nf_extra" tabindex="-1" autocomplete="off"></div>
                        <div class="rf-field">
                            <label class="rf-field-label" for="bz-details">طلبك بالتفصيل <span class="text-danger">*</span></label>
                            <textarea id="bz-details" name="details" rows="9" class="form-control" maxlength="{{ $freeMax }}" required
                                      placeholder="{{ $page['free_placeholder'] }}" aria-describedby="bz-details-count">{{ old('details') }}</textarea>
                            <small class="rf-field-help" id="bz-details-count" aria-live="polite">اكتب حتى {{ number_format($freeMax) }} حرف.</small>
                            @error('details')<div class="rf-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 rf-field">
                                <label class="rf-field-label" for="bz-phone">رقم الهاتف / واتساب <span class="text-danger">*</span></label>
                                <input type="tel" id="bz-phone" name="phone" class="form-control" data-country-picker dir="ltr" maxlength="30" required autocomplete="tel"
                                       value="{{ old('phone', $user?->whatsapp ?: $user?->phone) }}">
                                @error('phone')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 rf-field">
                                <label class="rf-field-label" for="bz-company">اسم الشركة (اختياري)</label>
                                <input type="text" id="bz-company" name="company_name" class="form-control" maxlength="150" value="{{ old('company_name') }}" autocomplete="organization">
                                @error('company_name')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 rf-field">
                                <label class="rf-field-label" for="bz-email">البريد الإلكتروني (اختياري)</label>
                                <input type="email" id="bz-email" name="email" class="form-control" dir="ltr" maxlength="150" value="{{ old('email', $user?->email) }}" autocomplete="email">
                                @error('email')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="rf-field">
                            <label class="rf-field-label" for="bz-files">مرفقات (اختياري)</label>
                            <input type="file" id="bz-files" name="attachments[]" class="form-control" multiple>
                            <small class="rf-field-help">حتى 3 ملفات، 20 ميجابايت للملف. للفيديوهات الكبيرة ضع رابطًا داخل الطلب.</small>
                            @error('attachments')<div class="rf-field-error">{{ $message }}</div>@enderror
                            @error('attachments.*')<div class="rf-field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="rf-field rf-sg-updates">
                            <input type="checkbox" id="bz-consent" name="consent" value="1" @checked(old('consent')) required>
                            <label for="bz-consent">{{ $page['consent'] }}</label>
                        </div>
                        @error('consent')<div class="rf-field-error">{{ $message }}</div>@enderror
                        <div class="rf-submit"><div class="rf-submit-actions">
                            <button type="submit" class="btn rf-btn-primary">{{ $page['submit'] }} <span aria-hidden="true">←</span></button>
                        </div></div>
                    </form>
                </div>

                @if ($after)
                    <aside class="rf-sg-aside bz-after" aria-labelledby="bz-after-title">
                        <h2 class="rf-section-title" id="bz-after-title">ماذا يحدث بعد الإرسال؟</h2>
                        <ol class="rf-join-steps mb-0">
                            @foreach ($after as $step)
                                <li><span><strong>{{ $step['title'] }}</strong>@if ($step['text'])<br><small class="rf-muted">{{ $step['text'] }}</small>@endif</span></li>
                            @endforeach
                        </ol>
                    </aside>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        // The free text is kept in this browser until it is sent (reload, expired page, network error).
        (function () {
            var done = document.getElementById('bz-created');
            if (done) { try { localStorage.removeItem(done.dataset.draftKey); } catch (e) {} }
            var form = document.getElementById('bz-free-form');
            if (!form) return;
            var key = form.dataset.draftKey, fields = ['details', 'company_name', 'email'];
            var saved = null;
            try { saved = done ? null : JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) {}
            if (saved && !form.elements.details.value && form.dataset.hasErrors !== '1') {
                fields.forEach(function (f) { if (saved[f] && !form.elements[f].value) form.elements[f].value = saved[f]; });
                document.getElementById('bz-draft-notice').hidden = false;
            }
            var save = function () {
                var data = {};
                fields.forEach(function (f) { data[f] = form.elements[f].value; });
                try { localStorage.setItem(key, JSON.stringify(data)); } catch (e) {}
            };
            form.addEventListener('input', save);
            var details = form.elements.details, counter = document.getElementById('bz-details-count');
            details.addEventListener('input', function () { counter.textContent = details.value.length.toLocaleString('ar-EG') + ' / ' + Number(details.maxLength).toLocaleString('ar-EG') + ' حرف'; });
            form.addEventListener('submit', function (e) {
                if (form.dataset.submitting === '1') { e.preventDefault(); return; }
                if (!navigator.onLine) { e.preventDefault(); alert('لا يوجد اتصال بالإنترنت الآن. ما كتبته محفوظ، أعد المحاولة عند عودة الاتصال.'); return; }
                form.dataset.submitting = '1';
                form.querySelector('[type=submit]').disabled = true;
            });
            window.addEventListener('pageshow', function (e) { if (e.persisted) { delete form.dataset.submitting; form.querySelector('[type=submit]').disabled = false; } });
        })();
    </script>
@endsection
