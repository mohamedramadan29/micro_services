@extends('admin.layouts.master')
@php use App\Models\admin\Service; use App\Http\Controllers\admin\ServiceController; @endphp
@section('title')
    خدمات المستقلين
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">خدمات المستقلين</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ خدمات جاهزة بسعر ثابت؛ تظهر وتُشترى بعد نشرها هنا، وأي تعديل من المستقل يعيدها للمراجعة</span>
            </div>
        </div>
        <div class="my-auto"><a href="{{ url('admin/service/add') }}" class="btn btn-primary btn-sm">إضافة خدمة</a></div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (Session::has('Success_message'))<div class="alert alert-success">{{ Session::get('Success_message') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <div class="d-flex flex-wrap mb-3" style="gap: 6px">
                @foreach (ServiceController::TABS as $key => $value)
                    <a href="{{ url('admin/services?tab=' . $key) }}" class="btn btn-sm {{ $tab === $key ? 'btn-primary' : 'btn-light' }}">{{ Service::STATUSES[$value] }} ({{ $counts[$value] ?? 0 }})</a>
                @endforeach
            </div>
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="اسم الخدمة أو المستقل">
                <button class="btn btn-primary">بحث</button>
            </form>

            <div class="rf-fw-list">
                @forelse ($services as $service)
                    <article class="rf-fw-card">
                        <div class="rf-fw-media">
                            <a href="{{ asset('assets/uploads/services/' . $service->image) }}" target="_blank" rel="noopener"><img src="{{ asset('assets/uploads/services/' . $service->image) }}" alt="" loading="lazy"></a>
                        </div>
                        <div class="rf-fw-body">
                            <h5 class="mb-1">{{ $service->name }} <span class="badge badge-light">{{ \App\Support\Money::format($service->price) }}</span></h5>
                            <div class="small text-muted mb-2">
                                @if ($service->user)<a href="{{ route('admin.freelancers.show', $service->user) }}">{{ $service->user->name }}</a> ·@endif
                                {{ $service->category?->name ?? '—' }} › {{ $service->subcategory?->name ?? '—' }} · {{ $service->updated_at?->format('Y-m-d H:i') }}
                            </div>
                            <p class="mb-2" style="white-space: pre-line">{{ \Illuminate\Support\Str::limit(strip_tags((string) $service->description), 500) }}</p>
                            @if ($service->review_note)<div class="alert alert-light py-2 mb-2 small"><strong>سبب عدم القبول:</strong> {{ $service->review_note }}</div>@endif
                            <div class="d-flex flex-wrap align-items-start" style="gap: 8px">
                                @unless ($service->isActive())
                                    <form method="POST" action="{{ route('admin.services.publish', $service->id) }}">@csrf
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> نشر الخدمة</button></form>
                                @endunless
                                @if ((int) $service->status !== Service::STATUS_REJECTED)
                                    <details class="rf-fw-reject">
                                        <summary class="btn btn-outline-danger btn-sm">{{ $service->isActive() ? 'إلغاء النشر' : 'رفض' }}</summary>
                                        <form method="POST" action="{{ route('admin.services.reject', $service->id) }}" class="mt-2">@csrf
                                            <textarea name="note" rows="2" maxlength="500" required class="form-control mb-2" placeholder="السبب (يصل للمستقل): مثلًا الصورة فيها رقم هاتف، أو الوصف غير واضح…"></textarea>
                                            <button class="btn btn-danger btn-sm">تأكيد وإبلاغ المستقل</button>
                                        </form>
                                    </details>
                                @endif
                                <a href="{{ $service->url() }}" target="_blank" class="btn btn-light btn-sm"><i class="fas fa-external-link-alt"></i> صفحة الخدمة</a>
                                <a href="{{ url('admin/service/update/' . $service->id) }}" class="btn btn-light btn-sm"><i class="fa fa-edit"></i> تعديل</a>
                                <button data-target="#delete_model_{{ $service->id }}" data-toggle="modal" class="btn btn-light btn-sm text-danger"><i class="fa fa-trash"></i> حذف</button>
                            </div>
                        </div>
                    </article>
                    @include('admin.services.delete')
                @empty
                    <p class="text-center text-muted my-4">{{ $tab === 'pending' ? 'لا توجد خدمات بانتظار المراجعة.' : 'لا توجد خدمات هنا.' }}</p>
                @endforelse
            </div>
            <div class="mt-3">{{ $services->links() }}</div>
        </div>
    </div>
@endsection
