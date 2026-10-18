@extends('admin.layouts.master')
@php use App\Models\HelpRequest; @endphp
@section('title')
    طلبات المساعدة
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">طلبات العملاء</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ طلبات المساعدة — طلبات تحتاج متابعة من الفريق</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        @foreach (HelpRequest::STATUSES as $key => $label)
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.help-requests.index', ['status' => $key]) }}" class="card text-center p-3 d-block {{ ($filters['status'] ?? null) === $key ? 'border-primary' : '' }}">
                    <div class="text-muted">{{ $label }}</div>
                    <h4 class="mb-0">{{ $counts[$key] ?? 0 }}</h4>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="ابحث بالاسم أو الرقم أو الخدمة">
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    @foreach (HelpRequest::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="service" class="form-control">
                    <option value="">كل الخدمات</option>
                    <option value="general" @selected(($filters['service'] ?? '') === 'general')>استفسار عام</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) ($filters['service'] ?? '') === (string) $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
                <select name="assignee" class="form-control">
                    <option value="">كل الموظفين</option>
                    <option value="none" @selected(($filters['assignee'] ?? '') === 'none')>بدون مسؤول</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->id }}" @selected((string) ($filters['assignee'] ?? '') === (string) $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                <label class="text-muted">من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                <label class="text-muted">إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                <button class="btn btn-primary">تصفية</button>
                <a href="{{ route('admin.help-requests.index') }}" class="btn btn-light">مسح</a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>الطلب</th><th>العميل</th><th>الخدمة</th><th>التاريخ</th><th>الحالة</th><th>المسؤول</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $help)
                            <tr>
                                <td><a href="{{ route('admin.help-requests.show', $help) }}"><strong dir="ltr">{{ $help->number }}</strong></a></td>
                                <td>{{ $help->user?->name }}<div class="text-muted small" dir="ltr">{{ $help->whatsapp }}</div></td>
                                <td>{{ $help->serviceLabel() }}</td>
                                <td>{{ $help->created_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge {{ $help->status === HelpRequest::STATUS_NEW ? 'badge-warning' : ($help->status === HelpRequest::STATUS_CLOSED ? 'badge-secondary' : 'badge-info') }}">{{ $help->statusLabel() }}</span></td>
                                <td>{{ $help->assignee?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا توجد طلبات مساعدة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $requests->links() }}</div>
        </div>
    </div>
@endsection
