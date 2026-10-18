@extends('website.layouts.master')
@php
    use App\Models\SiteTest;
    $page = \App\Support\SiteContent::first('sitetest.page');
    $result = \App\Support\SiteContent::first('sitetest.result');
    $issues = $test->clientIssues($maxIssues);
    // The offer names the issue its service solves (never another, unlinked problem);
    // a service the team picked by hand falls back to the top issue.
    $topIssue = $test->suggested_sub_category_id
        ? ($test->clientIssues()->firstWhere('sub_category_id', $test->suggested_sub_category_id) ?? $issues->first())
        : null;
    $service = $test->suggestedService;
    $fill = fn (string $text) => strtr($text, [
        '{name}' => $test->contact_name ?: auth()->user()?->name ?: '',
        '{issue}' => $topIssue ? $topIssue->title . ($topIssue->evidence ? ' (' . $topIssue->evidence . ')' : '') : '',
        '{service}' => $service?->name ?? '',
        '{url}' => $test->host,
    ]);
    $hasName = $test->contact_name || auth()->check();
    $deviceLabels = SiteTest::DEVICES;
@endphp
@section('title')
    {{ $test->host }} — {{ $page['title'] }}
@endsection
@section('content')
    <section class="rf-st-hero rf-st-hero-small" dir="rtl">
        <div class="container">
            <span class="rf-sv-label" dir="ltr">{{ $test->number }}</span>
            <h1 dir="ltr">{{ $test->host }}</h1>
            <p>{{ $test->engine ?: 'PageSpeed Insights' }} · {{ $deviceLabels[$test->device] ?? $test->device }} · {{ ($test->checked_at ?? $test->created_at)->format('Y-m-d H:i') }}</p>
        </div>
    </section>

    <section class="rf-st" dir="rtl">
        <div class="container">
            @if (! $test->isFinished())
                {{-- Status while the check runs; the page reloads itself when it is done. --}}
                <div class="rf-st-card" id="rf-st-status" data-url="{{ route('site-tests.status', ['siteTest' => $test->number, 't' => $test->token]) }}">
                    <h2 class="rf-st-h2">حالة الفحص</h2>
                    <p>نفحص الرابط العام الآن · {{ $deviceLabels[$test->device] ?? '' }} · {{ collect($test->categories)->map(fn ($c) => $categories[$c]['label'] ?? $c)->implode('، ') }}</p>
                    @php $progress = $test->progress(); @endphp
                    <div class="rf-st-progress rf-st-progress-live"><span data-progress-bar style="width: {{ $progress['percent'] }}%"></span></div>
                    <div class="rf-st-stages">
                        <div class="rf-st-stage done" data-stage="1"><strong>التحقق من الرابط</strong><span>تم</span></div>
                        <div class="rf-st-stage" data-stage="2"><strong>جمع المؤشرات</strong><span data-stage-text>بالانتظار</span></div>
                        <div class="rf-st-stage" data-stage="3"><strong>إعداد الملخص</strong><span data-stage-text>بانتظار الفحص</span></div>
                    </div>
                    <script type="application/json" id="rf-st-progress-initial">@json($progress)</script>
                    <p class="rf-st-note">قد يستغرق الفحص حتى دقيقة. ستتحدث الصفحة تلقائيًا.</p>
                </div>
            @elseif ($test->status === SiteTest::STATUS_FAILED)
                <div class="rf-st-card rf-st-failed">
                    <h2 class="rf-st-h2">{{ $result['failed_title'] }}</h2>
                    <p>{{ \App\Services\SiteTests\SiteTestSettings::get('failure_message') }}</p>
                    @if ($test->failure_reason)
                        <p class="rf-st-failure-reason"><strong>السبب:</strong> {{ $test->failure_reason }}</p>
                    @endif
                    <a href="{{ route('site-tests.index') }}" class="rf-sv-btn rf-sv-btn-primary">فحص رابط آخر</a>
                </div>
            @else
                <div class="rf-st-card">
                    <h2 class="rf-st-h2">{{ $result['summary_title'] }}</h2>
                    @foreach ($test->devices() as $device)
                        @if (count($test->devices()) > 1)<h3 class="rf-st-h3">{{ $deviceLabels[$device] }}</h3>@endif
                        <div class="rf-st-scores">
                            @foreach ($test->categories as $category)
                                @php $score = $test->scores[$device][$category] ?? null; @endphp
                                <div class="rf-st-score {{ $score === null ? 'na' : ($score >= 90 ? 'good' : ($score >= 50 ? 'mid' : 'bad')) }}">
                                    <span>{{ $categories[$category]['label'] ?? $category }}</span>
                                    <strong>{{ $score === null ? 'غير متاحة' : $score }}</strong>
                                    <small>{{ $score === null ? 'لم يقسها المصدر لهذا الجهاز' : 'من 100 — نتيجة المصدر' }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <h3 class="rf-st-h3">المشكلات حسب الأولوية</h3>
                    @forelse ($issues as $issue)
                        <div class="rf-st-issue">
                            <span class="rf-st-priority p-{{ $issue->priority }}">{{ $issue->priorityLabel() }}</span>
                            <div>
                                <strong>{{ $issue->title }}</strong>
                                <small><span dir="ltr">{{ $test->url }}</span> • <span dir="ltr">{{ $issue->audit_code }}</span> • دليل: {{ $issue->evidence ?: 'نتيجة الفحص' }}</small>
                                @if ($issue->description)<p>{{ $issue->description }}</p>@endif
                            </div>
                        </div>
                    @empty
                        <p class="rf-st-note">لم يجد الفحص مشكلات واضحة في المجالات التي اخترتها. أحسنت!</p>
                    @endforelse

                    @if ($test->teamNotes()->isNotEmpty())
                        <h3 class="rf-st-h3">مراجعة الفريق</h3>
                        @foreach ($test->teamNotes() as $note)
                            <div class="rf-st-issue">
                                <span class="rf-st-priority p-team">{{ $note->priorityLabel() }}</span>
                                <div>
                                    <strong>{{ $note->title }}</strong>
                                    <small>ملاحظة بشرية • ليست درجة آلية</small>
                                    @if ($note->description)<p>{{ $note->description }}</p>@endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                @if ($showOffer && $topIssue)
                    {{-- The automatic thank-you and offer card (spec page 11). --}}
                    <div class="rf-st-card rf-st-offer" id="offer">
                        @if ($test->serviceRequest)
                            <p><strong>نُشر طلبك {{ $test->serviceRequest->number }} للمستقلين.</strong></p>
                            <a class="rf-sv-btn rf-sv-btn-primary" href="{{ route('requests.show', $test->serviceRequest) }}">متابعة الطلب والعروض</a>
                        @else
                            <p><strong>{{ $hasName ? $fill($result['offer_greeting_named']) : $result['offer_greeting'] }}</strong> {{ $fill($result['offer_body']) }}</p>
                            @if (session('site_test_declined') || $test->choice === 'no')
                                <p class="rf-st-note">{{ $result['offer_no_done'] }}</p>
                            @endif
                            <div class="rf-st-actions">
                                <form method="POST" action="{{ route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'yes']) }}">
                                    @csrf
                                    <input type="hidden" name="t" value="{{ $test->token }}">
                                    <button class="rf-sv-btn rf-sv-btn-primary">{{ $fill($result['offer_yes']) }}</button>
                                </form>
                                @if ($test->choice !== 'no')
                                    <form method="POST" action="{{ route('site-tests.choose', ['siteTest' => $test->number, 'choice' => 'no']) }}">
                                        @csrf
                                        <input type="hidden" name="t" value="{{ $test->token }}">
                                        <button class="rf-sv-btn rf-st-btn-no">{{ $result['offer_no'] }}</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="rf-st-card" id="report">
                    <h2 class="rf-st-h2">{{ $result['report_title'] }}</h2>
                    @if ($test->review_status !== SiteTest::REVIEW_NONE)
                        <p>{{ $result['report_done'] }}</p>
                        @if ($test->review_status === SiteTest::REVIEW_SENT && $test->review_summary)
                            <div class="rf-st-review">
                                <h3 class="rf-st-h3">مراجعة الفريق</h3>
                                <p>{{ $test->review_summary }}</p>
                            </div>
                        @endif
                    @else
                        @if ($result['report_note'])<p class="rf-st-note">{{ $result['report_note'] }}</p>@endif
                        <form method="POST" action="{{ route('site-tests.report', $test->number) }}" class="row" novalidate>
                            @csrf
                            <input type="hidden" name="t" value="{{ $test->token }}">
                            <div class="col-md-6 mb-3">
                                <label class="rf-st-label" for="rf-st-name">الاسم الكامل</label>
                                <input type="text" id="rf-st-name" name="contact_name" class="form-control" maxlength="255" placeholder="اكتب اسمك" value="{{ old('contact_name', auth()->user()?->name) }}">
                                @error('contact_name')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="rf-st-label" for="rf-st-phone">رقم الهاتف أو واتساب <span class="rf-st-tag">مطلوب</span></label>
                                <input type="tel" id="rf-st-phone" name="contact_phone" class="form-control" dir="ltr" maxlength="30" placeholder="+ رمز الدولة" value="{{ old('contact_phone', auth()->user()?->phone) }}">
                                @error('contact_phone')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="rf-st-label" for="rf-st-email">البريد الإلكتروني <span class="rf-st-tag rf-st-tag-muted">اختياري</span></label>
                                <input type="email" id="rf-st-email" name="contact_email" class="form-control" dir="ltr" maxlength="255" placeholder="name@example.com" value="{{ old('contact_email', auth()->user()?->email) }}">
                                @error('contact_email')<div class="rf-field-error">{{ $message }}</div>@enderror
                            </div>
                            @foreach (\App\Services\SiteTests\SiteTestSettings::questions() as $question)
                                <div class="{{ $question['type'] === 'textarea' ? 'col-12' : 'col-md-6' }} mb-3"
                                     @if (! empty($question['show_if'])) data-show-if="{{ $question['show_if'] }}" data-show-value="{{ $question['show_value'] }}" @endif>
                                    <label class="rf-st-label" for="rf-st-q-{{ $question['key'] }}">{{ $question['label'] }}
                                        <span class="rf-st-tag {{ empty($question['required']) ? 'rf-st-tag-muted' : '' }}">{{ empty($question['required']) ? 'اختياري' : 'مطلوب' }}</span></label>
                                    @if ($question['type'] === 'select')
                                        <select id="rf-st-q-{{ $question['key'] }}" name="answers[{{ $question['key'] }}]" class="form-control">
                                            <option value="">— اختر —</option>
                                            @foreach ($question['options'] as $option)<option value="{{ $option }}" @selected(old('answers.' . $question['key']) === $option)>{{ $option }}</option>@endforeach
                                        </select>
                                    @elseif ($question['type'] === 'textarea')
                                        <textarea id="rf-st-q-{{ $question['key'] }}" name="answers[{{ $question['key'] }}]" rows="3" class="form-control" maxlength="2000">{{ old('answers.' . $question['key']) }}</textarea>
                                    @else
                                        <input type="text" id="rf-st-q-{{ $question['key'] }}" name="answers[{{ $question['key'] }}]" class="form-control" maxlength="500" value="{{ old('answers.' . $question['key']) }}">
                                    @endif
                                    @error('answers.' . $question['key'])<div class="rf-field-error">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                            <div class="col-12">
                                <button class="rf-sv-btn rf-sv-btn-primary">أرسل لي التقرير الكامل</button>
                            </div>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('site-tests.store') }}" class="rf-st-recheck">
                    @csrf
                    <input type="hidden" name="url" value="{{ $test->url }}">
                    <input type="hidden" name="device" value="{{ $test->device }}">
                    <input type="hidden" name="recheck" value="{{ $test->number }}">
                    <input type="hidden" name="t" value="{{ $test->token }}">
                    @foreach ($test->categories as $category)<input type="hidden" name="categories[]" value="{{ $category }}">@endforeach
                    <button class="rf-sv-btn rf-sv-btn-outline"><i class="bi bi-arrow-repeat"></i> إعادة الفحص (تُحفظ النتيجة الحالية)</button>
                </form>
            @endif
        </div>
    </section>
@endsection
@section('js')
    <script>
        // Report questions shown only when another answer matches (admin-managed condition).
        (function () {
            var rows = document.querySelectorAll('[data-show-if]');
            if (!rows.length) return;
            var sync = function () {
                rows.forEach(function (row) {
                    var source = document.querySelector('[name="answers[' + row.dataset.showIf + ']"]');
                    row.hidden = !source || source.value.trim() !== row.dataset.showValue;
                });
            };
            document.addEventListener('input', sync);
            document.addEventListener('change', sync);
            sync();
        })();
    </script>
    @if (! $test->isFinished())
        <script>
            (function () {
                var box = document.getElementById('rf-st-status');
                var tries = 0;
                var bar = box.querySelector('[data-progress-bar]');
                var paint = function (p) {
                    if (bar) bar.style.width = p.percent + '%';
                    box.querySelectorAll('[data-stage]').forEach(function (el) {
                        var n = +el.dataset.stage, text = el.querySelector('[data-stage-text]');
                        el.classList.toggle('done', n < p.stage);
                        el.classList.toggle('running', n === p.stage);
                        if (!text) return;
                        if (n < p.stage) text.textContent = 'تم';
                        else if (n === p.stage && n === 2) text.textContent = 'جارٍ' + (p.devices_total > 1 ? ' (' + p.devices_done + ' من ' + p.devices_total + ')' : '') + (p.running_device ? ' · ' + p.running_device : '');
                        else if (n === p.stage) text.textContent = 'جارٍ';
                        else text.textContent = 'بالانتظار';
                    });
                };
                try { paint(JSON.parse(document.getElementById('rf-st-progress-initial').textContent)); } catch (e) {}
                var poll = function () {
                    fetch(box.dataset.url, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.finished) { paint(data); window.location.reload(); return; }
                            paint(data);
                            if (++tries < 90) setTimeout(poll, 3000);
                        })
                        .catch(function () { if (++tries < 90) setTimeout(poll, 5000); });
                };
                setTimeout(poll, 3000);
            })();
        </script>
    @endif
@endsection
