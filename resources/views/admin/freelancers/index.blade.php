@extends('admin.layouts.master')
@php use App\Models\User; @endphp
@section('title')
    المستقلون
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">المستقلون</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ مراجعة واعتماد قبل الظهور في صفحة المستقلين وإرسال العروض</span>
            </div>
        </div>
        <div class="my-auto"><a href="{{ route('freelancers.index') }}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-external-link-alt"></i> صفحة المستقلين</a></div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap mb-3" style="gap: 6px">
                <a href="{{ route('admin.freelancers.index') }}" class="btn btn-sm {{ $status ? 'btn-light' : 'btn-primary' }}">الكل</a>
                <a href="{{ route('admin.freelancers.index', ['status' => 'ready']) }}" class="btn btn-sm {{ $status === 'ready' ? 'btn-primary' : 'btn-outline-primary' }}"
                   title="أكّد بريده وأضاف واتساب وصورة ونبذة وتخصصات">جاهز للمراجعة ({{ $counts['ready'] ?? 0 }})</a>
                @foreach (User::FREELANCER_STATUSES as $key => $label)
                    <a href="{{ route('admin.freelancers.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
                @endforeach
            </div>
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="الاسم، البريد، الواتساب">
                <button class="btn btn-primary">بحث</button>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead><tr><th>المستقل</th><th>التخصصات</th><th>واتساب</th><th>الصورة</th><th>الحالة</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($freelancers as $freelancer)
                            <tr>
                                <td><strong>{{ $freelancer->name }}</strong><div class="small text-muted">{{ $freelancer->email }} · {{ $freelancer->created_at->format('Y-m-d') }}</div></td>
                                <td class="small">{{ $freelancer->specializations->pluck('name')->take(3)->implode('، ') ?: '—' }}{{ $freelancer->specializations->count() > 3 ? '…' : '' }}</td>
                                <td><span dir="ltr">{{ $freelancer->whatsapp ?: '—' }}</span> {!! $freelancer->isVerifiedFreelancer() ? '<span class="badge badge-success">موثّق</span>' : '<span class="badge badge-warning">غير موثّق</span>' !!}</td>
                                <td>{!! $freelancer->professional_photo ? '<span class="badge badge-success">موجودة</span>' : '<span class="badge badge-light">لا</span>' !!}</td>
                                <td>{{ User::FREELANCER_STATUSES[$freelancer->freelancer_status] ?? '—' }}
                                    @if ($freelancer->freelancer_status === User::FREELANCER_PENDING)
                                        {!! $freelancer->isReadyForReview() ? '<span class="badge badge-success">جاهز للمراجعة</span>' : '<span class="badge badge-light">لم يكمل بياناته</span>' !!}
                                    @endif
                                    @if ($freelancer->is_featured)<span class="badge badge-info">موثوق</span>@endif</td>
                                <td><a href="{{ route('admin.freelancers.show', $freelancer) }}" class="btn btn-sm btn-light">مراجعة</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">لا يوجد مستقلون.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $freelancers->links() }}</div>
        </div>
    </div>
@endsection
