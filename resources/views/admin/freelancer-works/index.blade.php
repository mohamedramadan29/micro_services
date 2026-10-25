@extends('admin.layouts.master')
@php use App\Models\front\UserPortfolio; use App\Http\Controllers\admin\FreelancerWorkController; @endphp
@section('title')
    أعمال المستقلين
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">أعمال المستقلين</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ يظهر العمل في الموقع بعد نشره هنا؛ أي تعديل من المستقل يعيده للمراجعة</span>
            </div>
        </div>
    </div>
    @include('admin.catalog.partials.flash')

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap mb-3" style="gap: 6px">
                @foreach (FreelancerWorkController::TABS as $key => $value)
                    <a href="{{ route('admin.freelancer-works.index', ['tab' => $key]) }}" class="btn btn-sm {{ $tab === $key ? 'btn-primary' : 'btn-light' }}">
                        {{ UserPortfolio::STATUSES[$value] }} ({{ $counts[$value] ?? 0 }})</a>
                @endforeach
            </div>
            <form method="GET" class="form-inline mb-3" style="gap: 8px">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="عنوان العمل أو اسم المستقل">
                <button class="btn btn-primary">بحث</button>
            </form>

            <div class="rf-fw-list">
                @forelse ($works as $work)
                    @php $images = array_merge([$work->image], is_array($work->more_images) ? $work->more_images : []); @endphp
                    <article class="rf-fw-card">
                        <div class="rf-fw-media">
                            <a href="{{ asset('assets/uploads/portfolios/' . $work->image) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('assets/uploads/portfolios/' . $work->image) }}" alt="" loading="lazy"></a>
                            @if (count($images) > 1)
                                <div class="rf-fw-thumbs">
                                    @foreach (array_slice($images, 1, 4) as $img)
                                        <a href="{{ asset('assets/uploads/portfolios/' . $img) }}" target="_blank" rel="noopener"><img src="{{ asset('assets/uploads/portfolios/' . $img) }}" alt="" loading="lazy"></a>
                                    @endforeach
                                    @if (count($images) > 5)<span>+{{ count($images) - 5 }}</span>@endif
                                </div>
                            @endif
                        </div>
                        <div class="rf-fw-body">
                            <h5 class="mb-1">{{ $work->title }}</h5>
                            <div class="small text-muted mb-2">
                                @if ($work->User)
                                    <a href="{{ route('admin.freelancers.show', $work->User) }}">{{ $work->User->name }}</a>
                                    · {{ \App\Models\User::FREELANCER_STATUSES[$work->User->freelancer_status] ?? '' }}
                                @endif
                                · {{ $work->Category?->name ?? '—' }} · {{ $work->updated_at?->format('Y-m-d H:i') }}
                            </div>
                            <p class="mb-2" style="white-space: pre-line">{{ \Illuminate\Support\Str::limit(strip_tags($work->description), 500) }}</p>
                            @if ($work->sub_categories->isNotEmpty())
                                <div class="mb-2">@foreach ($work->sub_categories as $skill)<span class="badge badge-light ml-1">{{ $skill->name }}</span>@endforeach</div>
                            @endif
                            @if ($work->link)
                                <div class="small mb-2"><a href="{{ $work->link }}" target="_blank" rel="noopener nofollow" dir="ltr">{{ \Illuminate\Support\Str::limit($work->link, 70) }}</a></div>
                            @endif
                            @if ($work->review_note)
                                <div class="alert alert-light py-2 mb-2 small"><strong>سبب عدم القبول:</strong> {{ $work->review_note }}</div>
                            @endif

                            <div class="d-flex flex-wrap align-items-start" style="gap: 8px">
                                @unless ($work->isPublished())
                                    <form method="POST" action="{{ route('admin.freelancer-works.publish', $work) }}">@csrf
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> نشر العمل</button></form>
                                @endunless
                                @if ((int) $work->status !== UserPortfolio::STATUS_REJECTED)
                                    <details class="rf-fw-reject">
                                        <summary class="btn btn-outline-danger btn-sm">{{ $work->isPublished() ? 'إلغاء النشر' : 'رفض' }}</summary>
                                        <form method="POST" action="{{ route('admin.freelancer-works.reject', $work) }}" class="mt-2">@csrf
                                            <textarea name="note" rows="2" maxlength="500" required class="form-control mb-2" placeholder="السبب (يصل للمستقل): مثلًا الصور غير واضحة، أو العمل لا يخصه…"></textarea>
                                            <button class="btn btn-danger btn-sm">تأكيد وإبلاغ المستقل</button>
                                        </form>
                                    </details>
                                @endif
                                <a href="{{ url('portfolio/' . $work->id . '-' . $work->slug) }}" target="_blank" class="btn btn-light btn-sm"><i class="fas fa-external-link-alt"></i> صفحة العمل</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-center text-muted my-4">{{ $tab === 'pending' ? 'لا توجد أعمال بانتظار المراجعة.' : 'لا توجد أعمال هنا.' }}</p>
                @endforelse
            </div>
            <div class="mt-3">{{ $works->links() }}</div>
        </div>
    </div>
@endsection
