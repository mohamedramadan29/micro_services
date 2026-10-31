@extends('admin.layouts.master')
@php
    use App\Services\ServiceRequests\ClientClassifier as C;
    use App\Models\ServiceRequest;
@endphp
@section('title')
    ملف العميل — {{ $user->name }}
@endsection
@section('css')
    @include('admin.clients.partials.style')
@endsection
@section('content')
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">العملاء</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ {{ $user->name }}</span>
            </div>
        </div>
        <div class="my-auto"><a href="{{ route('admin.clients.index') }}" class="btn btn-light"><i class="fas fa-arrow-right"></i> كل العملاء</a></div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <div class="cl-muted mb-2">{{ $user->user_name }} · عضو منذ {{ $user->created_at?->format('Y-m-d') }}</div>
                    <div class="mb-3">
                        @foreach ($classification['tags'] as $tag)
                            <span class="cl-tag cl-tag-{{ $tag }}">{{ C::CATEGORIES[$tag]['label'] }}</span>
                        @endforeach
                        @if (! $classification['contacts'])
                            <span class="cl-tag cl-tag-no_requests">مسجّل بلا طلبات بعد</span>
                        @endif
                    </div>
                    @php
                        $state = match (true) {
                            (string) $user->block_status === '0' => ['محظور', 'badge-danger'],
                            (int) $user->status !== 1 => ['غير مفعّل', 'badge-secondary'],
                            default => ['نشط', 'badge-success'],
                        };
                    @endphp
                    <dl class="mb-0">
                        <dt>البريد</dt>
                        <dd dir="ltr" class="text-right">{{ $user->email }}
                            <span class="badge {{ $user->email_verified_at ? 'badge-success' : 'badge-warning' }}" dir="rtl">{{ $user->email_verified_at ? 'مؤكد' : 'غير مؤكد' }}</span>
                        </dd>
                        <dt>الهاتف</dt><dd dir="ltr" class="text-right">{{ $user->phone ?: ($latestContact?->contact_phone ?? '—') }}</dd>
                        @if ($user->whatsapp)<dt>واتساب</dt><dd dir="ltr" class="text-right">{{ $user->whatsapp }}</dd>@endif
                        <dt>حالة الحساب</dt><dd><span class="badge {{ $state[1] }}">{{ $state[0] }}</span></dd>
                        <dt>تاريخ التسجيل</dt><dd>{{ $user->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        <dt>آخر تواصل</dt><dd>{{ $classification['last_activity']?->format('Y-m-d H:i') ?? 'لم يتواصل بعد' }}</dd>
                        <dt>الرصيد</dt><dd>{{ \App\Support\Money::format($user->balance ?? 0) }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card">
                <div class="card-body d-flex justify-content-around text-center">
                    <div><h4 class="mb-0">{{ $classification['requests'] }}</h4><span class="cl-muted">طلبات</span></div>
                    <div><h4 class="mb-0">{{ $classification['completed'] }}</h4><span class="cl-muted">مكتملة</span></div>
                    <div><h4 class="mb-0">{{ \App\Support\Money::format($paid) }}</h4><span class="cl-muted">مدفوع</span></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">سجل الطلبات والاستفسارات</h6></div>
                <div class="card-body">
                    @if ($requests->isEmpty())
                        <p class="cl-muted mb-0">سجّل هذا العميل {{ $user->created_at?->diffForHumans() }} ولم يرسل أي طلب خدمة بعد. يمكنك التواصل معه على بريده أو هاتفه ليبدأ.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>الطلب</th><th>الحالة</th><th>العروض</th><th>آخر رسالة مع الفريق</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($requests as $item)
                                    @php($last = $item->adminMessages->first())
                                    <tr>
                                        <td><strong>{{ $item->number }}</strong><br><span class="cl-muted">{{ $item->service_name }} · {{ $item->created_at->format('Y-m-d') }}</span></td>
                                        <td>
                                            {{ $item->statusLabel() }}
                                            @if ($item->closed_reason)<br><span class="cl-muted">{{ $item->closeReasonLabel() }}</span>@endif
                                        </td>
                                        <td>
                                            {{ $item->offers_count }}
                                            @if ($item->acceptedOffer)<br><span class="cl-muted">المختار: {{ $item->acceptedOffer->freelancer?->name }}</span>@endif
                                        </td>
                                        <td>
                                            @if ($last)
                                                <span class="cl-tag {{ $last->from_admin ? 'cl-tag-answering' : 'cl-tag-asking' }}">{{ $last->from_admin ? 'بانتظار رد العميل' : 'بانتظار ردنا' }}</span>
                                                <div class="cl-muted">{{ \Illuminate\Support\Str::limit($last->body, 60) }}</div>
                                            @else
                                                <span class="cl-muted">—</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.requests.show', $item) }}" class="btn btn-sm btn-primary">السجل الكامل</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">فحوصات «اختبر موقعك» ({{ $siteTests->count() }})</h6></div>
                <div class="card-body">
                    @if ($siteTests->isEmpty())
                        <p class="cl-muted mb-0">لم يفحص هذا العميل أي موقع بحسابه أو ببريده.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead><tr><th>الفحص</th><th>الرابط</th><th>الحالة</th><th></th></tr></thead>
                                <tbody>
                                    @foreach ($siteTests as $test)
                                        <tr>
                                            <td><strong>{{ $test->number }}</strong><br><span class="cl-muted">{{ $test->created_at->format('Y-m-d') }}</span></td>
                                            <td dir="ltr" class="text-left">{{ $test->host }}</td>
                                            <td>{{ $test->stageLabel() }}</td>
                                            <td><a href="{{ route('admin.site-tests.show', $test) }}" class="btn btn-sm btn-primary">فتح الفحص</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">طلبات «ساعدوني أختار» ({{ $helpRequests->count() }})</h6></div>
                <div class="card-body">
                    @if ($helpRequests->isEmpty())
                        <p class="cl-muted mb-0">لم يطلب مساعدة من الفريق.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($helpRequests as $help)
                                <li class="border-bottom py-2 d-flex justify-content-between align-items-center" style="gap: 8px">
                                    <span><strong dir="ltr">{{ $help->number }}</strong> · {{ $help->serviceLabel() }} · <span class="cl-muted">{{ $help->created_at->format('Y-m-d') }}</span></span>
                                    <span><span class="badge badge-light">{{ $help->statusLabel() }}</span>
                                        <a href="{{ route('admin.help-requests.show', $help) }}" class="btn btn-sm btn-primary">فتح</a></span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">اقتراحاته لتطوير المنصة ({{ $suggestions->count() }})</h6></div>
                <div class="card-body">
                    @if ($suggestions->isEmpty())
                        <p class="cl-muted mb-0">لم يرسل اقتراحات.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach ($suggestions as $suggestion)
                                <li class="border-bottom py-2 d-flex justify-content-between align-items-center" style="gap: 8px">
                                    <span><strong dir="ltr">{{ $suggestion->reference_code }}</strong> · {{ $suggestion->title }} · <span class="cl-muted">{{ $suggestion->created_at->format('Y-m-d') }}</span></span>
                                    <span><span class="badge badge-light">{{ $suggestion->statusLabel() }}</span>
                                        @if (\App\Support\StaffPermissions::canSuggestions(auth()->user(), 'view'))
                                            <a href="{{ route('admin.suggestions.show', $suggestion) }}" class="btn btn-sm btn-primary">فتح</a>
                                        @endif</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
