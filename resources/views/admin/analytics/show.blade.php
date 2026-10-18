@extends('admin.layouts.master')
@php use App\Http\Controllers\admin\AnalyticsAdminController as C; @endphp
@section('title')
    تفاصيل زيارة
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto"><a href="{{ route('admin.analytics.index') }}">إحصائيات رحلة الزوار</a></h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ زيارة #{{ $visit->id }}</span>
            </div>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-4">
            <div class="card"><div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>المصدر</th><td>{{ $visit->sourceLabel() }}</td></tr>
                    @if ($visit->referrer_host)<tr><th>جاء من</th><td dir="ltr">{{ $visit->referrer_host }}</td></tr>@endif
                    @if ($visit->medium)<tr><th>الوسيط</th><td>{{ $visit->medium }}</td></tr>@endif
                    @if ($visit->campaign)<tr><th>الحملة</th><td>{{ $visit->campaign }}</td></tr>@endif
                    <tr><th>صفحة الدخول</th><td dir="ltr">{{ $visit->landing_path }}</td></tr>
                    <tr><th>البداية</th><td>{{ $visit->started_at->format('Y-m-d H:i') }}</td></tr>
                    <tr><th>آخر نشاط</th><td>{{ $visit->last_seen_at->format('Y-m-d H:i') }}</td></tr>
                    <tr><th>آخر خطوة</th><td>{{ $visit->stepLabel() }}</td></tr>
                    <tr><th>الحالة</th><td>{{ C::STATUSES[$status] }}</td></tr>
                    <tr><th>زائر</th><td>{{ $visit->user_id ? 'مستخدم مسجّل' : 'زائر مجهول' }}</td></tr>
                </table>
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">المسار خطوة بخطوة</h6></div>
                <div class="card-body">
                    <ol class="mb-0">
                        @foreach ($visit->events as $event)
                            <li class="mb-2">
                                <strong>{{ $event->label() }}</strong>
                                <span class="text-muted small">· {{ $event->created_at->format('H:i:s') }}</span>
                                @if ($event->service)<span class="badge badge-light">{{ $event->service->name }}</span>@endif
                                @if ($event->path)<div class="small text-muted" dir="ltr" style="text-align: right">{{ $event->path }}</div>@endif
                                @if ($event->type === 'abandon_reason')
                                    <div class="small">السبب: {{ $reasons[$event->meta['reason'] ?? ''] ?? 'السبب غير معروف' }}@if (! empty($event->meta['note'])) — {{ $event->meta['note'] }}@endif</div>
                                @elseif (isset($event->meta['amount']))
                                    <div class="small text-muted">{{ \App\Support\Money::format($event->meta['amount']) }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($status === 'abandoned' && ! $visit->events->contains('type', 'abandon_reason'))
                        <p class="text-muted small mt-2 mb-0">سبب عدم الإكمال: السبب غير معروف.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
