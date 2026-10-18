@extends('admin.layouts.master')
@php
    use App\Services\Analytics\Tracker;
    use App\Http\Controllers\admin\AnalyticsAdminController as C;
    $rate = fn ($n) => $total ? round(100 * $n / $total) . '%' : '—';
    $maxSource = max(1, $sources->max('visits') ?? 1);
    $maxFunnel = max(1, collect($funnel)->max('count'));
@endphp
@section('title')
    إحصائيات رحلة الزوار
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">إحصائيات رحلة الزوار</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ من أين يأتون، وأين يتوقفون</span>
            </div>
        </div>
    </div>

    <div class="alert alert-light border small">
        <i class="fas fa-info-circle"></i> الأرقام حقيقية من الموقع نفسه، وتشمل فقط الزوار الذين وافقوا على الإحصائيات (لا تُسجَّل أي زيارة قبل الموافقة، ولا يُحفظ اسم أو رقم هاتف).
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row" style="row-gap: 8px">
                <div class="col-md-2 col-6"><label class="small mb-1">من</label><input type="date" name="from" class="form-control" value="{{ $filters['from']->format('Y-m-d') }}"></div>
                <div class="col-md-2 col-6"><label class="small mb-1">إلى</label><input type="date" name="to" class="form-control" value="{{ $filters['to']->format('Y-m-d') }}"></div>
                <div class="col-md-2 col-6"><label class="small mb-1">المصدر</label>
                    <select name="source" class="form-control"><option value="">الكل</option>
                        @foreach (Tracker::SOURCES as $key => $label)<option value="{{ $key }}" @selected($filters['source'] === $key)>{{ $label }}</option>@endforeach
                    </select></div>
                <div class="col-md-2 col-6"><label class="small mb-1">الخدمة</label>
                    <select name="service" class="form-control"><option value="">الكل</option>
                        @foreach ($services as $service)<option value="{{ $service->id }}" @selected($filters['service'] === $service->id)>{{ $service->name }}</option>@endforeach
                    </select></div>
                <div class="col-md-2 col-6"><label class="small mb-1">حالة الطلب</label>
                    <select name="status" class="form-control"><option value="">الكل</option>
                        @foreach (C::STATUSES as $key => $label)<option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>@endforeach
                    </select></div>
                <div class="col-md-2 col-6 d-flex align-items-end"><button class="btn btn-primary btn-block">عرض</button></div>
            </form>
        </div>
    </div>

    <div class="row row-sm">
        @foreach ([
            ['الزيارات', number_format($total), 'fa-users', 'text-primary'],
            ['أتمّوا طلبًا', number_format($completed) . ' (' . $rate($completed) . ')', 'fa-check-circle', 'text-success'],
            ['بدأوا ولم يكملوا', number_format($abandoned) . ' (' . $rate($abandoned) . ')', 'fa-door-open', 'text-danger'],
            ['المصادر', $sources->count(), 'fa-random', 'text-info'],
            ['الفترة', $filters['from']->format('d/m') . ' – ' . $filters['to']->format('d/m/Y'), 'fa-calendar', 'text-muted'],
        ] as [$label, $value, $icon, $color])
            <div class="col-lg col-md-4 col-6">
                <div class="card"><div class="card-body">
                    <div class="text-muted small"><i class="fas {{ $icon }} {{ $color }}"></i> {{ $label }}</div>
                    <h5 class="mb-0 mt-1" dir="auto" style="white-space: nowrap">{{ $value }}</h5>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row row-sm">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">مسار الطلب</h6></div>
                <div class="card-body">
                    @foreach ($funnel as $step)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small">
                                <strong>{{ $loop->iteration }}. {{ $step['label'] }}</strong>
                                <span>{{ number_format($step['count']) }} زيارة
                                    @if ($step['drop'] !== null && $step['drop'] > 0)<span class="text-danger">· خسارة {{ $step['drop'] }}%</span>@endif
                                </span>
                            </div>
                            <div class="progress" style="height: 10px"><div class="progress-bar bg-primary" style="width: {{ round(100 * $step['count'] / $maxFunnel) }}%"></div></div>
                        </div>
                    @endforeach
                    <small class="text-muted">الخسارة = نسبة من وصلوا للخطوة السابقة ولم يصلوا لهذه.</small>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">مصادر الزيارات</h6></div>
                <div class="card-body">
                    @forelse ($sources as $source)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small">
                                <strong>{{ $source['label'] }}</strong>
                                <span>{{ number_format($source['visits']) }} زيارة · {{ number_format($source['completed']) }} أتمّوا</span>
                            </div>
                            <div class="progress" style="height: 10px"><div class="progress-bar bg-info" style="width: {{ round(100 * $source['visits'] / $maxSource) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">لا توجد زيارات في هذه الفترة.</p>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between"><h6 class="mb-0">مداخل الصفحة الرئيسية</h6><a href="{{ route('admin.home-cards.index') }}" class="small">نتائج كل مربع</a></div>
                <div class="card-body">
                    @foreach ($entries as $entry)
                        <div class="d-flex justify-content-between border-bottom py-1"><span>{{ $entry['label'] }}</span><strong>{{ number_format($entry['count']) }}</strong></div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h6 class="mb-0">أسباب عدم الإكمال</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        @foreach ($abandonReasons as $row)
                            <tr><td>{{ $row['label'] }}</td><td class="text-left">{{ number_format($row['count']) }}</td></tr>
                        @endforeach
                    </table>
                    <small class="text-muted">يظهر السبب فقط إذا كتبه الزائر بنفسه بعد دفع لم يكتمل.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">الزيارات ({{ number_format($visits->total()) }})</h6></div>
        <div class="card-body table-responsive">
            <table class="table table-hover text-nowrap mb-0">
                <thead><tr><th>البداية</th><th>المصدر</th><th>صفحة الدخول</th><th>المسار</th><th>آخر خطوة</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                    @forelse ($visits as $visit)
                        @php $status = C::statusOf($visit); @endphp
                        <tr>
                            <td>{{ $visit->started_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $visit->sourceLabel() }}@if ($visit->campaign)<div class="small text-muted">{{ $visit->campaign }}</div>@endif</td>
                            <td dir="ltr" class="text-right"><small>{{ \Illuminate\Support\Str::limit($visit->landing_path, 40) }}</small></td>
                            <td class="small">{{ $visit->events->whereIn('type', array_keys(Tracker::FUNNEL))->map->label()->unique()->implode(' ← ') ?: '—' }}</td>
                            <td>{{ $visit->stepLabel() }}</td>
                            <td><span class="badge badge-{{ ['completed' => 'success', 'abandoned' => 'danger', 'browsing' => 'light'][$status] }}">{{ C::STATUSES[$status] }}</span></td>
                            <td><a href="{{ route('admin.analytics.show', $visit) }}" class="btn btn-sm btn-light">التفاصيل</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">لا توجد زيارات تطابق الفلاتر.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $visits->links() }}</div>
        </div>
    </div>
@endsection
