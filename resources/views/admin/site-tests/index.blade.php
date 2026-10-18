@extends('admin.layouts.master')
@php use App\Http\Controllers\admin\SiteTestAdminController as C; @endphp
@section('title')
    اختبارات المواقع
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">اختبارات المواقع</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ الفحص المجاني والتقارير واختيار العميل</span>
            </div>
        </div>
        <div class="my-auto d-flex" style="gap: 6px">
            <a href="{{ route('admin.site-tests.settings') }}" class="btn btn-light"><i class="fas fa-cog"></i> إعداد الفحص وقواعد الربط</a>
            <a href="{{ route('site-tests.index') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt"></i> صفحة الفحص</a>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap mb-3" style="gap: 6px">
                <a href="{{ route('admin.site-tests.index') }}" class="btn btn-sm {{ $filter ? 'btn-light' : 'btn-primary' }}">الكل</a>
                @foreach (C::FILTERS as $key => $label)
                    <a href="{{ route('admin.site-tests.index', ['filter' => $key]) }}" class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                @if ($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="الرقم، الرابط، اسم أو هاتف العميل">
                <button class="btn btn-primary">بحث</button>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>الفحص</th><th>الموقع</th><th>النتيجة</th><th>العميل</th><th>الحالة</th><th>المتابعة</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($tests as $test)
                            @php $top = $test->clientIssues(1)->first(); @endphp
                            <tr>
                                <td><a href="{{ route('admin.site-tests.show', $test) }}" dir="ltr"><strong>{{ $test->number }}</strong></a></td>
                                <td><span dir="ltr">{{ $test->host }}</span><div class="text-muted small">{{ $test->created_at->format('Y-m-d H:i') }} · {{ \App\Models\SiteTest::DEVICES[$test->device] ?? '' }}</div></td>
                                <td>
                                    {{ $top?->title ?? ($test->status === 'failed' ? 'تعذر الفحص' : '—') }}
                                    @if ($test->suggestedService)<div class="small text-success">الخدمة المقترحة: {{ $test->suggestedService->name }}</div>@endif
                                </td>
                                <td>{{ $test->contact_name ?: '—' }}<div class="small text-muted" dir="ltr">{{ $test->contact_phone }}</div></td>
                                <td><span class="badge badge-light">{{ $test->stageLabel() }}</span></td>
                                <td>
                                    {{ $test->assignee?->name ?? '—' }}
                                    @if ($test->serviceRequest)<div class="small"><a href="{{ route('admin.requests.show', $test->serviceRequest) }}">{{ $test->serviceRequest->number }}</a></div>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا توجد فحوصات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $tests->links() }}</div>
        </div>
    </div>
@endsection
