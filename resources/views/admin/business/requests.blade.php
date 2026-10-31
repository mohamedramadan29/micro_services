@extends('admin.layouts.master')
@php use App\Models\BusinessRequest as R; use App\Models\BusinessCompany as C; @endphp
@section('title')
    طلبات الشركات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto"><div class="d-flex">
            <h4 class="content-title mb-0 my-auto">نفّذها للأعمال</h4>
            <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ صندوق طلبات الشركات — المساران (الحر والموجّه) في مكان واحد</span>
        </div></div>
        <div class="my-auto"><a href="{{ route('business.index') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt"></i> صفحة الشركات</a></div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        @foreach (R::STATUSES as $key => $label)
            <div class="col-6 col-md">
                <a href="{{ route('admin.business.requests', ['status' => $key]) }}" class="card text-center p-3 d-block {{ ($filters['status'] ?? null) === $key ? 'border-primary' : '' }}">
                    <div class="text-muted small">{{ $label }}</div><h4 class="mb-0">{{ $counts[$key] ?? 0 }}</h4>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card"><div class="card-body">
        <form method="GET" class="form-inline mb-3" style="gap: 8px">
            <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="رقم، شركة، هاتف، بريد، نص">
            <select name="status" class="form-control" aria-label="الحالة"><option value="">كل الحالات</option>
                @foreach (R::STATUSES as $key => $label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
            <select name="source" class="form-control" aria-label="المسار"><option value="">كل المسارات</option>
                @foreach (R::SOURCES as $key => $label)<option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
            <select name="size" class="form-control" aria-label="حجم الشركة"><option value="">كل الأحجام</option>
                @foreach (C::SIZES as $key => $label)<option value="{{ $key }}" @selected(($filters['size'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
            <select name="assignee" class="form-control" aria-label="المسؤول"><option value="">كل الموظفين</option><option value="none" @selected(($filters['assignee'] ?? '') === 'none')>بدون مسؤول</option>
                @foreach ($staff as $member)<option value="{{ $member->id }}" @selected((string) ($filters['assignee'] ?? '') === (string) $member->id)>{{ $member->name }}</option>@endforeach</select>
            <select name="payment" class="form-control" aria-label="الدفع"><option value="">الدفع: الكل</option><option value="paid" @selected(($filters['payment'] ?? '') === 'paid')>مدفوع</option><option value="unpaid" @selected(($filters['payment'] ?? '') === 'unpaid')>غير مدفوع</option></select>
            <button class="btn btn-primary">تصفية</button><a href="{{ route('admin.business.requests') }}" class="btn btn-light">مسح</a>
        </form>
        <div class="table-responsive"><table class="table table-bordered mb-0">
            <thead><tr><th>الطلب</th><th>الشركة / المرسل</th><th>المسار</th><th>عرض السعر</th><th>التاريخ</th><th>الحالة</th><th>المسؤول</th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td class="text-nowrap"><a href="{{ route('admin.business.requests.show', $item) }}"><strong dir="ltr">{{ $item->number }}</strong></a></td>
                        <td>{{ $item->displayName() }}<div class="text-muted small" dir="ltr">{{ $item->phone }}</div>@if ($item->company_size)<span class="badge badge-light">{{ $item->sizeLabel() }}</span>@endif @if (! $item->user_id)<span class="badge badge-light">زائر بلا حساب</span>@endif</td>
                        <td>{{ $item->sourceLabel() }}</td>
                        <td>@if ($item->hasQuote()){{ \App\Support\Money::format($item->quote_amount) }} @if ($item->isPaid())<span class="badge badge-success">مدفوع</span>@endif @else — @endif</td>
                        <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                        <td><span class="badge {{ match ($item->status) { 'new' => 'badge-warning', 'converted', 'accepted' => 'badge-success', 'rejected' => 'badge-secondary', default => 'badge-info' } }}">{{ $item->statusLabel() }}</span></td>
                        <td>{{ $item->assignee?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">لا توجد طلبات.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $items->links() }}</div>
    </div></div>
@endsection
