@extends('admin.layouts.master')
@php use App\Models\Suggestion; @endphp
@section('title')
    الاقتراحات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">إدارة الاقتراحات</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ متابعة الأفكار الواردة من العملاء والمستقلين والزوار</span>
            </div>
        </div>
        <div class="my-auto d-flex" style="gap: 8px">
            @if ($canExport)
                <a href="{{ route('admin.suggestions.export', request()->query()) }}" class="btn btn-light"><i class="fas fa-file-csv"></i> تصدير CSV</a>
            @endif
            @if ($canSettings)
                <a href="{{ route('admin.suggestions.settings') }}" class="btn btn-light"><i class="fas fa-cog"></i> الإعدادات</a>
            @endif
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="row row-sm">
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.suggestions.index') }}" class="card text-center p-3 d-block {{ empty($filters['status']) ? 'border-primary' : '' }}">
                <div class="text-muted">الإجمالي</div>
                <h4 class="mb-0">{{ $counts->sum() }}</h4>
            </a>
        </div>
        @foreach (Suggestion::STATUSES as $key => $label)
            <div class="col-6 col-md-2">
                <a href="{{ route('admin.suggestions.index', ['status' => $key]) }}" class="card text-center p-3 d-block {{ ($filters['status'] ?? null) === $key ? 'border-primary' : '' }}">
                    <div class="text-muted">{{ $label }}</div>
                    <h4 class="mb-0">{{ $counts[$key] ?? 0 }}</h4>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="بحث برقم أو عنوان">
                <select name="status" class="form-control" aria-label="الحالة">
                    <option value="">كل الحالات</option>
                    @foreach (Suggestion::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="category" class="form-control" aria-label="النوع">
                    <option value="">كل الأنواع</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (مخفي)' }}</option>
                    @endforeach
                </select>
                <select name="role" class="form-control" aria-label="المصدر">
                    <option value="">كل المصادر</option>
                    @foreach (Suggestion::ROLES as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['role'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="text-muted" for="sg-from">من</label><input type="date" id="sg-from" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                <label class="text-muted" for="sg-to">إلى</label><input type="date" id="sg-to" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                <button class="btn btn-primary">تصفية</button>
                <a href="{{ route('admin.suggestions.index') }}" class="btn btn-light">مسح</a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>رقم</th><th>عنوان الاقتراح</th><th>النوع</th><th>المصدر</th><th>التاريخ</th><th>الحالة</th><th>المسؤول</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($suggestions as $suggestion)
                            <tr>
                                <td class="text-nowrap"><a href="{{ route('admin.suggestions.show', $suggestion) }}"><strong dir="ltr">{{ $suggestion->reference_code }}</strong></a></td>
                                <td>
                                    <a href="{{ route('admin.suggestions.show', $suggestion) }}">{{ $suggestion->title }}</a>
                                    @if ($suggestion->duplicate_of_id)<span class="badge badge-light">مكرر</span>@endif
                                    @if ($suggestion->duplicates_count)<span class="badge badge-light">{{ $suggestion->duplicates_count }} مكرر</span>@endif
                                    @if ($suggestion->internal_priority === 'high')<span class="badge badge-danger">أولوية عالية</span>@endif
                                </td>
                                <td>{{ $suggestion->categoryLabel() }}</td>
                                <td>{{ $suggestion->roleLabel() }}</td>
                                <td>{{ $suggestion->created_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge {{ match ($suggestion->status) { 'new' => 'badge-warning', 'done' => 'badge-success', 'closed' => 'badge-secondary', default => 'badge-info' } }}">{{ $suggestion->statusLabel() }}</span></td>
                                <td>{{ $suggestion->assignee?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">لا توجد اقتراحات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $suggestions->links() }}</div>
        </div>
    </div>
@endsection
