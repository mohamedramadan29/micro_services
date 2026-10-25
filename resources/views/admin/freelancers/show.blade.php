@extends('admin.layouts.master')
@php use App\Models\User; $f = $freelancer; @endphp
@section('title')
    {{ $f->name }} — المستقلون
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">المستقلون</h4>
                <span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $f->name }} · {{ User::FREELANCER_STATUSES[$f->freelancer_status] ?? '—' }}</span>
            </div>
        </div>
        <div class="my-auto"><a href="{{ route('admin.freelancers.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> رجوع</a></div>
    </div>
    @include('admin.catalog.partials.flash')

    {{-- What stands between this freelancer and approval: verification and approval are two different steps. --}}
    @php
        $missing = array_filter([
            $f->isVerifiedFreelancer() ? null : 'توثيق الحساب (بريد مؤكد أو توثيق يدوي — خانة 1)',
            $f->professional_photo ? null : 'الصورة الاحترافية (خانة 2)',
        ]);
    @endphp
    @if ($f->isApprovedFreelancer())
        <div class="alert alert-success"><strong>معتمد</strong> — يظهر في صفحة المستقلين ويستطيع إرسال العروض.</div>
    @elseif ($missing)
        <div class="alert alert-warning mb-3"><strong>قبل الاعتماد ينقص:</strong> {{ implode('، ', $missing) }}. الحالة تبقى «{{ User::FREELANCER_STATUSES[$f->freelancer_status] ?? 'قيد المراجعة' }}» حتى تعتمده.</div>
    @else
        <div class="alert alert-info mb-3"><strong>الخطوة التالية:</strong> الحساب موثّق والصورة موجودة. راجع النبذة والأعمال ثم اضغط «اعتماد» في خانة «3. القرار» — التوثيق وحده لا يغيّر حالته من «{{ User::FREELANCER_STATUSES[$f->freelancer_status] ?? 'قيد المراجعة' }}».</div>
    @endif

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">بيانات المستقل</h6></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">الاسم</dt><dd class="col-sm-9">{{ $f->name }} · <a href="{{ url('user/' . $f->user_name) }}" target="_blank">الملف العام</a></dd>
                        <dt class="col-sm-3">البريد</dt><dd class="col-sm-9" dir="ltr">{{ $f->email }}</dd>
                        <dt class="col-sm-3">التخصصات</dt><dd class="col-sm-9">{{ $f->specializations->pluck('name')->implode('، ') ?: 'لم يختر بعد' }}</dd>
                        <dt class="col-sm-3">النبذة</dt><dd class="col-sm-9">{{ $f->bio ?: '—' }}</dd>
                        <dt class="col-sm-3">التوفر</dt><dd class="col-sm-9">{{ User::AVAILABILITY[$f->availability] ?? '—' }}</dd>
                        <dt class="col-sm-3">اللغات</dt><dd class="col-sm-9">{{ $f->languages ?: '—' }}</dd>
                        <dt class="col-sm-3">يبدأ سعره من</dt><dd class="col-sm-9">{{ $f->starting_price !== null ? \App\Support\Money::format($f->starting_price) : '—' }}</dd>
                        <dt class="col-sm-3">التقييم</dt><dd class="col-sm-9">{{ $f->rate ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">نماذج الأعمال ({{ $portfolio->count() }})</h6>
                    <a href="{{ route('admin.freelancer-works.index', ['q' => $f->email]) }}" class="btn btn-sm btn-light">مراجعة الأعمال</a></div>
                <div class="card-body">
                    @forelse ($portfolio as $item)
                        <div class="border-bottom py-2"><strong>{{ $item->title }}</strong>
                            <span class="badge {{ $item->isPublished() ? 'badge-success' : ((int) $item->status === \App\Models\front\UserPortfolio::STATUS_REJECTED ? 'badge-danger' : 'badge-warning') }}">{{ \App\Models\front\UserPortfolio::STATUSES[(int) $item->status] ?? '' }}</span>
                            @if ($item->link) · <a href="{{ $item->link }}" target="_blank" rel="noopener nofollow">رابط</a>@endif
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 180) }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">لم يضف نماذج أعمال بعد.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">1. توثيق الحساب</h6></div>
                <div class="card-body">
                    <p class="mb-1">البريد: <span dir="ltr">{{ $f->email }}</span>
                        {!! $f->email_verified_at ? '<span class="badge badge-success">مؤكد ' . e($f->email_verified_at->format('Y-m-d')) . '</span>' : '<span class="badge badge-warning">لم يُؤكَّد</span>' !!}</p>
                    @unless ($f->email_verified_at)
                        <form method="POST" action="{{ route('admin.freelancers.verify-email', $f) }}" class="mb-2">@csrf
                            <button class="btn btn-outline-primary btn-block">إرسال رابط تأكيد البريد</button></form>
                    @endunless
                    <p class="mb-1">توثيق يدوي من الإدارة:
                        {!! $f->admin_verified_at ? '<span class="badge badge-success">' . e($f->admin_verified_at->format('Y-m-d')) . '</span>' : '<span class="badge badge-light">لا</span>' !!}</p>
                    <form method="POST" action="{{ route('admin.freelancers.verify', $f) }}">
                        @csrf
                        <button class="btn btn-block {{ $f->admin_verified_at ? 'btn-light' : 'btn-success' }}">{{ $f->admin_verified_at ? 'إلغاء التوثيق اليدوي' : 'توثيق الحساب يدويًا' }}</button>
                    </form>
                    <small class="text-muted d-block mt-1">يكفي أحدهما: بريد مؤكد أو توثيق يدوي. التوثيق خطوة أولى فقط؛ الاعتماد من خانة «3. القرار».</small>
                    @if ($f->whatsapp)
                        <a class="btn btn-outline-success btn-block mt-2" target="_blank" rel="noopener"
                            href="https://wa.me/{{ preg_replace('/\D/', '', $f->whatsapp) }}?text={{ rawurlencode('مرحبًا ' . $f->name . '، معك فريق نفّذها.') }}">
                            <i class="fab fa-whatsapp"></i> مراسلته على واتساب <span dir="ltr">{{ $f->whatsapp }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">2. الصورة الاحترافية</h6></div>
                <div class="card-body">
                    @if ($f->professional_photo)
                        <img src="{{ $f->avatarUrl(256) }}" alt="" class="rounded mb-2" style="width: 128px; height: 128px; object-fit: cover;">
                    @endif
                    <form method="POST" action="{{ route('admin.freelancers.photo', $f) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control mb-2">
                        <small class="text-muted d-block mb-2">مقاس 400×400 على الأقل. تُقصّ مربعة وتُحفظ بثلاثة مقاسات حتى تبقى واضحة.</small>
                        <button class="btn btn-primary btn-block">{{ $f->professional_photo ? 'استبدال الصورة' : 'رفع الصورة' }}</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">3. القرار</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.freelancers.decide', $f) }}">
                        @csrf
                        <textarea name="note" rows="2" class="form-control mb-2" placeholder="سبب عدم الاعتماد (يظهر للمستقل)">{{ old('note') }}</textarea>
                        <div class="d-flex" style="gap: 6px">
                            <button name="decision" value="{{ User::FREELANCER_APPROVED }}" class="btn btn-success flex-fill" @disabled(! $f->isVerifiedFreelancer() || ! $f->professional_photo)>اعتماد</button>
                            <button name="decision" value="{{ User::FREELANCER_REJECTED }}" class="btn btn-outline-danger flex-fill">عدم اعتماد</button>
                        </div>
                    </form>
                    @if (! $f->isVerifiedFreelancer() || ! $f->professional_photo)
                        <small class="text-muted d-block mt-2">زر «اعتماد» يعمل بعد توثيق الحساب وإضافة الصورة الاحترافية.</small>
                    @endif
                    @if ($f->isApprovedFreelancer())
                        <form method="POST" action="{{ route('admin.freelancers.feature', $f) }}" class="mt-2">
                            @csrf
                            <button class="btn btn-block {{ $f->is_featured ? 'btn-info' : 'btn-light' }}">{{ $f->is_featured ? 'ضمن «مستقلون موثوقون» — إزالة' : 'إضافة إلى «مستقلون موثوقون»' }}</button>
                        </form>
                    @endif
                    @if ($f->freelancer_reviewed_at)<small class="text-muted d-block mt-2">آخر قرار {{ $f->freelancer_reviewed_at->format('Y-m-d H:i') }}</small>@endif
                </div>
            </div>
        </div>
    </div>
@endsection
