@extends('admin.layouts.master')
@php use App\Models\BusinessProject as P; @endphp
@section('title')
    مشاريع الشركات
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto"><div class="d-flex">
            <h4 class="content-title mb-0 my-auto">نفّذها للأعمال</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مشاريع الشركات</span>
        </div></div>
    </div>
    @include('admin.catalog.partials.flash')
    <div class="mb-3">
        @foreach (P::STATUSES as $key => $label)
            <a href="{{ route('admin.business.projects', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @endforeach
    </div>
    <div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-bordered mb-0">
        <thead><tr><th>المشروع</th><th>العميل</th><th>المدير</th><th>الإنجاز</th><th>بانتظار العميل</th><th>المهام المتأخرة</th><th>التسليم</th></tr></thead>
        <tbody>
            @forelse ($projects as $project)
                @php($late = $project->tasks->filter->isLate()->count())
                <tr>
                    <td><a href="{{ route('admin.business.projects.show', $project) }}"><strong>{{ $project->title }}</strong></a><div class="text-muted small" dir="ltr">{{ $project->number }}</div></td>
                    <td>{{ $project->user?->name ?? '—' }}</td>
                    <td>{{ $project->manager?->name ?? '—' }}</td>
                    <td style="min-width: 120px"><div class="progress" style="height: 8px"><div class="progress-bar bg-success" style="width: {{ $project->progress() }}%"></div></div><small>{{ $project->progress() }}%</small></td>
                    <td>{{ $project->pending_count ?: '—' }}</td>
                    <td>@if ($late)<span class="badge badge-danger">{{ $late }}</span>@else — @endif</td>
                    <td>{{ $project->due_at?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">لا توجد مشاريع هنا. يتحوّل طلب الشركة لمشروع من صفحة الطلب بعد قبول عرض السعر.</td></tr>
            @endforelse
        </tbody>
    </table></div><div class="mt-3">{{ $projects->links() }}</div></div></div>
@endsection
