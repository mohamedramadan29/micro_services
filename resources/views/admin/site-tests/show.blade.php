@extends('admin.layouts.master')
@php
    use App\Models\SiteTest;
    use App\Services\SiteTests\SiteTestSettings;
    $clientLink = route('site-tests.show', ['siteTest' => $test->number, 't' => $test->token]);
@endphp
@section('title')
    {{ $test->number }} — اختبارات المواقع
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">اختبارات المواقع</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ <span dir="ltr">{{ $test->number }}</span> · {{ $test->stageLabel() }}</span>
            </div>
        </div>
        <div class="my-auto d-flex" style="gap: 6px">
            <a href="{{ $clientLink }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-eye"></i> كما يراها العميل</a>
            <a href="{{ route('admin.site-tests.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">الموقع والنتيجة</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">الرابط</dt><dd class="col-sm-9"><a href="{{ $test->url }}" target="_blank" rel="noopener nofollow" dir="ltr">{{ $test->url }}</a></dd>
                        <dt class="col-sm-3">وقت الفحص</dt><dd class="col-sm-9">{{ ($test->checked_at ?? $test->created_at)->format('Y-m-d H:i') }} · {{ SiteTest::DEVICES[$test->device] ?? '' }} · {{ $test->engine }}</dd>
                        @if ($test->failure_reason)<dt class="col-sm-3">سبب التعذر</dt><dd class="col-sm-9 text-danger">{{ $test->failure_reason }}</dd>@endif
                        @if ($test->parent)<dt class="col-sm-3">إعادة فحص لـ</dt><dd class="col-sm-9"><a href="{{ route('admin.site-tests.show', $test->parent) }}">{{ $test->parent->number }}</a></dd>@endif
                    </dl>
                    @foreach ((array) $test->scores as $device => $scores)
                        <div class="mt-2"><strong>{{ SiteTest::DEVICES[$device] ?? $device }}:</strong>
                            @foreach ($scores as $category => $score)
                                <span class="badge badge-light">{{ SiteTestSettings::CATEGORIES[$category]['label'] ?? $category }}: {{ $score ?? 'غير متاحة' }}</span>
                            @endforeach
                        </div>
                    @endforeach
                    @foreach ($test->results as $result)
                        <div class="small mt-1">{{ $result->device }} · {{ $result->ok ? 'نجح' : 'تعذر: ' . $result->error }}
                            @if ($result->raw) · <a href="{{ route('admin.site-tests.raw', [$test, $result->id]) }}">تنزيل النتيجة الخام</a>@endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">المشكلات والدليل ({{ $test->issues->count() }})</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead><tr><th>الأولوية</th><th>المشكلة</th><th>الدليل</th><th>الخدمة المرتبطة</th><th>للعميل</th></tr></thead>
                            <tbody>
                                @forelse ($test->issues->sortBy(fn ($i) => [\App\Models\SiteTestIssue::PRIORITY_ORDER[$i->priority] ?? 9, $i->sort_order]) as $issue)
                                    <tr class="{{ $issue->shown ? '' : 'text-muted' }}">
                                        <td>{{ $issue->priorityLabel() }}</td>
                                        <td>
                                            {{ $issue->title }}
                                            <div class="small text-muted" dir="ltr">{{ $issue->source === 'team' ? 'team note' : $issue->audit_code . ' · ' . $issue->category . ' · ' . $issue->device }}</div>
                                        </td>
                                        <td>{{ $issue->evidence }}</td>
                                        <td>{!! $issue->service ? e($issue->service->name) : '<span class="text-warning">بدون قاعدة — للمراجعة</span>' !!}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.site-tests.issues.toggle', [$test, $issue]) }}">@csrf<button class="btn btn-sm btn-light">{{ $issue->shown ? 'ظاهرة — إخفاء' : 'مخفية — إظهار' }}</button></form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">لا توجد مشكلات.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <form method="POST" action="{{ route('admin.site-tests.issues.store', $test) }}" class="row mt-3 align-items-end">
                        @csrf
                        <div class="form-group col-md-5"><label>ملاحظة الفريق (مراجعة بشرية)</label><input type="text" name="title" class="form-control" maxlength="250" placeholder="مثال: زر الإجراء الرئيسي غير بارز"></div>
                        <div class="form-group col-md-4"><label>الخدمة المرتبطة</label>
                            <select name="sub_category_id" class="form-control"><option value="">—</option>@foreach ($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach</select>
                        </div>
                        <div class="form-group col-md-3"><button class="btn btn-outline-primary btn-block">إضافة ملاحظة</button></div>
                        <div class="form-group col-12 mb-0"><input type="text" name="description" class="form-control" maxlength="1000" placeholder="ما أثرها؟ وما الخطوة التالية؟ (اختياري)"></div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">مراجعة الفريق قبل إرسال التقرير</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.site-tests.review', $test) }}">
                        @csrf
                        <div class="form-group">
                            <label>ملخص الموقع وأهم 3 أولويات (يظهر للعميل في التقرير)</label>
                            <textarea name="review_summary" rows="5" class="form-control" placeholder="الوضع العام بلغة بسيطة، ثم المشكلة والدليل وأثرها وخطوة الإصلاح لكل أولوية">{{ old('review_summary', $test->review_summary) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>ملاحظات داخلية (لا تظهر للعميل)</label>
                            <textarea name="team_notes" rows="2" class="form-control">{{ old('team_notes', $test->team_notes) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>الخدمة المقترحة في البطاقة</label>
                                <select name="suggested_sub_category_id" class="form-control" @disabled($test->service_request_id || $test->choice === 'yes')>
                                    <option value="">— بدون بطاقة عرض —</option>
                                    @foreach ($services as $service)<option value="{{ $service->id }}" @selected($test->suggested_sub_category_id === $service->id)>{{ $service->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>المسؤول</label>
                                <select name="assigned_to" class="form-control">
                                    <option value="">—</option>
                                    @foreach ($staff as $member)<option value="{{ $member->id }}" @selected($test->assigned_to === $member->id)>{{ $member->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>حالة المراجعة</label>
                                <select name="review_status" class="form-control">
                                    @foreach (SiteTest::REVIEW_STATUSES as $key => $label)
                                        @continue($key === SiteTest::REVIEW_SENT && $test->review_status !== SiteTest::REVIEW_SENT)
                                        <option value="{{ $key }}" @selected($test->review_status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary">حفظ المراجعة</button>
                    </form>
                    <hr>
                    <form method="POST" action="{{ route('admin.site-tests.send', $test) }}" class="d-inline" onsubmit="return confirm('إرسال التقرير للعميل الآن؟')">
                        @csrf
                        <button class="btn btn-success" @disabled(! $test->contact_phone && ! $test->contact_email)>{{ $test->review_status === SiteTest::REVIEW_SENT ? 'إعادة إرسال التقرير' : 'إرسال التقرير للعميل' }}</button>
                    </form>
                    @if ($test->contact_phone)
                        <a class="btn btn-outline-success" target="_blank" rel="noopener"
                            href="https://wa.me/{{ preg_replace('/\D/', '', $test->contact_phone) }}?text={{ rawurlencode('مرحبًا ' . $test->contact_name . '، تقرير فحص موقعك ' . $test->host . ' من نفّذها جاهز: ' . $clientLink) }}">
                            <i class="fab fa-whatsapp"></i> إرسال الرابط عبر واتساب
                        </a>
                    @endif
                    @if ($test->report_sent_at)<div class="small text-muted mt-2">أُرسل {{ $test->report_sent_at->format('Y-m-d H:i') }}</div>@endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">العميل</h6></div>
                <div class="card-body">
                    @if ($test->report_requested_at)
                        <p class="mb-1">{{ $test->contact_name }}</p>
                        <p class="mb-1" dir="ltr">{{ $test->contact_phone }}</p>
                        <p class="mb-1" dir="ltr">{{ $test->contact_email }}</p>
                        @forelse ((array) $test->answers as $answer)
                            <p class="mb-1">{{ $answer['label'] }}: <strong>{{ $answer['value'] }}</strong></p>
                        @empty
                            @if ($test->goal)<p class="mb-1">الهدف: {{ $test->goal }}</p>@endif
                        @endforelse
                        <p class="small text-muted mb-0">طلب التقرير {{ $test->report_requested_at->format('Y-m-d H:i') }}</p>
                    @else
                        <p class="text-muted mb-0">لم يطلب التقرير الكامل بعد (لا بيانات تواصل).</p>
                    @endif
                    @if ($test->user)<p class="mt-2 mb-0">الحساب: <a href="{{ route('admin.clients.show', $test->user) }}">{{ $test->user->name }}</a></p>@endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">اختيار العميل</h6></div>
                <div class="card-body">
                    @if ($test->choice === 'yes')
                        <p class="text-success mb-1"><strong>يريد التنفيذ</strong> · {{ $test->choice_at?->format('Y-m-d H:i') }}</p>
                    @elseif ($test->choice === 'no')
                        <p class="text-danger mb-1"><strong>لا يريد التعديل الآن</strong> · {{ $test->choice_at?->format('Y-m-d H:i') }}</p>
                    @else
                        <p class="text-muted mb-1">لم يختر بعد.</p>
                    @endif
                    @if ($test->serviceRequest)
                        <p class="mb-1">الطلب المنشور: <a href="{{ route('admin.requests.show', $test->serviceRequest) }}">{{ $test->serviceRequest->number }}</a> · {{ $test->serviceRequest->statusLabel() }}</p>
                        <a href="{{ url('/client-requests/' . $test->serviceRequest->number) }}" target="_blank" class="small">رابط صفحة المستقلين</a>
                    @elseif ($test->choice === 'yes')
                        <p class="small text-muted mb-0">بانتظار أن يكمل العميل بياناته وينشر الطلب.</p>
                    @endif
                </div>
            </div>

            @if ($history->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">فحوصات أخرى لنفس الموقع</h6></div>
                    <div class="card-body">
                        @foreach ($history as $other)
                            <div><a href="{{ route('admin.site-tests.show', $other) }}">{{ $other->number }}</a> · {{ $other->created_at->format('Y-m-d') }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
