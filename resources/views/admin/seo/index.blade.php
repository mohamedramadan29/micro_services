@extends('admin.layouts.master')
@php
    use App\Models\SeoKeyword;
    use App\Http\Controllers\admin\SeoKeywordController as C;
    $q = fn ($key) => request($key, '');
@endphp
@section('title')
    SEO والكلمات المفتاحية
@endsection
@section('content')
    @include('admin.seo.partials.head')

    <div class="row row-sm">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">إضافة كلمات</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.seo.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>الكلمات (كل كلمة في سطر — يمكن لصق المئات)</label>
                            <textarea name="keywords" rows="6" class="form-control" required>{{ old('keywords') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>الصفحة المرتبطة (اختياري)</label>
                            <input type="text" name="page_url" class="form-control" dir="ltr" placeholder="/services أو https://…" value="{{ old('page_url') }}">
                        </div>
                        <div class="form-group">
                            <label>الخدمة المرتبطة (اختياري)</label>
                            <select name="sub_category_id" class="form-control">
                                <option value="">—</option>
                                @foreach ($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ملاحظة (اختياري)</label>
                            <input type="text" name="notes" class="form-control" maxlength="2000">
                        </div>
                        <button class="btn btn-primary btn-block">إضافة</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">استيراد وتصدير</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.seo.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".csv,text/csv" class="form-control mb-2" required>
                        <small class="text-muted d-block mb-2">CSV بأعمدة: {{ implode('، ', C::CSV_COLUMNS) }} (أو عمود الكلمات فقط). الكلمات الموجودة تتحدث ولا تتكرر.</small>
                        <button class="btn btn-outline-primary btn-block">استيراد CSV</button>
                    </form>
                    <a href="{{ route('admin.seo.export', request()->query()) }}" class="btn btn-light btn-block mt-2"><i class="fas fa-download"></i> تصدير النتائج الحالية (CSV)</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap mb-3" style="gap: 6px">
                        <a href="{{ route('admin.seo.index', request()->except('status', 'page')) }}" class="btn btn-sm {{ $q('status') ? 'btn-light' : 'btn-primary' }}">الكل ({{ $total }})</a>
                        @foreach (SeoKeyword::STATUSES as $key => $label)
                            <a href="{{ route('admin.seo.index', array_merge(request()->except('page'), ['status' => $key])) }}" class="btn btn-sm {{ $q('status') === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
                        @endforeach
                    </div>
                    <form method="GET" class="form-inline mb-3" style="gap: 8px">
                        @if ($q('status'))<input type="hidden" name="status" value="{{ $q('status') }}">@endif
                        <input type="search" name="q" value="{{ $q('q') }}" class="form-control" placeholder="ابحث في الكلمات أو الصفحات">
                        <select name="service" class="form-control">
                            <option value="">كل الخدمات</option>
                            @foreach ($services as $service)<option value="{{ $service->id }}" @selected((int) $q('service') === $service->id)>{{ $service->name }}</option>@endforeach
                        </select>
                        <select name="data" class="form-control">
                            <option value="">بيانات Google: الكل</option>
                            <option value="yes" @selected($q('data') === 'yes')>تظهر في Google</option>
                            <option value="no" @selected($q('data') === 'no')>بلا ظهور / بلا بيانات</option>
                        </select>
                        <select name="sort" class="form-control">
                            @foreach (C::SORTS as $key => $label)<option value="{{ $key }}" @selected(($q('sort') ?: 'newest') === $key)>ترتيب: {{ $label }}</option>@endforeach
                        </select>
                        <button class="btn btn-primary">تطبيق</button>
                    </form>

                    <form method="POST" action="{{ route('admin.seo.destroy') }}" id="seo-bulk" onsubmit="return confirm('حذف الكلمات المحددة نهائيًا؟')">@csrf</form>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr><th style="width: 32px"></th><th>الكلمة المفتاحية</th><th>الصفحة المرتبطة</th><th>بيانات Google (28 يومًا)</th><th>الحالة</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($keywords as $keyword)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $keyword->id }}" form="seo-bulk" aria-label="تحديد"></td>
                                        <td><strong>{{ $keyword->keyword }}</strong>@if ($keyword->notes)<div class="small text-muted">{{ $keyword->notes }}</div>@endif</td>
                                        <td class="small">
                                            @if ($keyword->page_url)<a href="{{ \App\Support\SiteContent::url($keyword->page_url) }}" target="_blank" dir="ltr">{{ $keyword->page_url }}</a>@endif
                                            @if ($keyword->service)<div>{{ $keyword->service->name }}</div>@endif
                                            @if (! $keyword->page_url && ! $keyword->service)—@endif
                                        </td>
                                        <td class="small">
                                            @if ($keyword->gsc_synced_at)
                                                {{ number_format((int) $keyword->gsc_impressions) }} ظهور · {{ number_format((int) $keyword->gsc_clicks) }} نقرة
                                                @if ($keyword->gsc_position !== null)<div>الترتيب {{ (float) $keyword->gsc_position }} · النقر {{ round((float) $keyword->gsc_ctr * 100, 1) }}%</div>@endif
                                            @else
                                                <span class="text-muted">لا بيانات بعد</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $keyword->status === 'linked' ? 'badge-success' : ($keyword->status === 'review' ? 'badge-warning' : 'badge-light') }}">{{ $keyword->statusLabel() }}</span></td>
                                        <td>
                                            <details>
                                                <summary class="btn btn-sm btn-light">تعديل</summary>
                                                <form method="POST" action="{{ route('admin.seo.update', $keyword) }}" class="mt-2" style="min-width: 240px">
                                                    @csrf
                                                    <input type="text" name="keyword" class="form-control form-control-sm mb-1" value="{{ $keyword->keyword }}" required>
                                                    <input type="text" name="page_url" class="form-control form-control-sm mb-1" dir="ltr" value="{{ $keyword->page_url }}" placeholder="الصفحة المرتبطة">
                                                    <select name="sub_category_id" class="form-control form-control-sm mb-1">
                                                        <option value="">— بدون خدمة —</option>
                                                        @foreach ($services as $service)<option value="{{ $service->id }}" @selected($keyword->sub_category_id === $service->id)>{{ $service->name }}</option>@endforeach
                                                    </select>
                                                    <select name="status" class="form-control form-control-sm mb-1">
                                                        @foreach (SeoKeyword::STATUSES as $key => $label)<option value="{{ $key }}" @selected($keyword->status === $key)>{{ $label }}</option>@endforeach
                                                    </select>
                                                    <input type="text" name="notes" class="form-control form-control-sm mb-1" value="{{ $keyword->notes }}" placeholder="ملاحظة">
                                                    <button class="btn btn-sm btn-primary btn-block">حفظ</button>
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">لا توجد كلمات.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap" style="gap: 8px">
                        <button form="seo-bulk" class="btn btn-sm btn-outline-danger">حذف المحدد</button>
                        {{ $keywords->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
