@extends('website.layouts.master')
@section('title')
    الطلب {{ $item->number }} — نفّذها للأعمال
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid"><div class="row m-0">
            <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">@include('website.service-requests.partials.dashboard-nav')</div>
            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                <div class="bredcrumb_wrap"><nav aria-label="breadcrumb"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('business.mine') }}"> نفّذها للأعمال </a></li>
                    <li class="breadcrumb-item active" aria-current="page" dir="ltr"> {{ $item->number }} </li>
                </ol></nav></div>

                @if (session('business_created'))
                    <div class="rf-notice rf-notice-success" role="status">وصل طلبك <bdi dir="ltr">{{ $item->number }}</bdi> لفريق نفّذها للأعمال، وسنتواصل معك قريبًا.</div>
                @endif
                @if ($errors->any())<div class="rf-notice rf-notice-error" role="alert">{{ $errors->first() }}</div>@endif

                <div class="_dashboard_content">
                    <div class="_dashboard_content_header"><div class="_dashboard__header_flex">
                        <h4>{{ $item->company_name ?: 'طلب شركة' }} <small class="rf-muted" dir="ltr">{{ $item->number }}</small></h4>
                        <span class="rf-status rf-status-{{ in_array($item->status, ['accepted', 'converted'], true) ? 'completed' : ($item->status === 'rejected' ? 'not_completed' : 'inquiry') }}">{{ $item->statusLabel() }}</span>
                    </div></div>
                    <div class="_dashboard_content_body">
                        @if ($item->hasQuote())
                            <div class="bz-quote">
                                <h5>عرض السعر</h5>
                                <div class="bz-quote-amount">{{ \App\Support\Money::show($item->quote_amount) }}</div>
                                <div style="white-space: pre-line">{{ $item->quote_scope }}</div>
                                @if ($item->quote_due_at)<small class="rf-muted d-block mt-1">صالح حتى {{ $item->quote_due_at->format('Y-m-d') }}</small>@endif

                                @if ($item->awaitsClientDecision())
                                    <form method="POST" action="{{ route('business.requests.decide', $item) }}" class="mt-3">
                                        @csrf
                                        <label class="rf-field-label" for="bz-note">ملاحظة (اختياري)</label>
                                        <textarea id="bz-note" name="note" rows="2" class="form-control mb-2" maxlength="1000"></textarea>
                                        <div class="d-flex flex-wrap" style="gap: 8px">
                                            <button name="decision" value="accept" class="btn rf-btn-primary">اعتماد عرض السعر</button>
                                            <button name="decision" value="reject" class="btn rf-btn-outline" onclick="return confirm('رفض عرض السعر؟')">رفض</button>
                                        </div>
                                    </form>
                                @elseif ($item->quote_decided_at)
                                    <p class="mt-2 mb-0">{{ in_array($item->status, ['accepted', 'converted'], true) ? 'اعتمدت العرض' : 'رُفض العرض' }} في {{ $item->quote_decided_at->format('Y-m-d') }}.</p>
                                @endif

                                @if (in_array($item->status, ['accepted', 'converted'], true))
                                    @if ($item->isPaid())
                                        <p class="mt-2 mb-0"><span class="badge badge-success">مدفوع</span> {{ $item->paymentMethodLabel() }} · {{ $item->paid_at?->format('Y-m-d') }}</p>
                                    @else
                                        <form method="POST" action="{{ route('business.requests.pay', $item) }}" class="mt-3">
                                            @csrf
                                            <button class="btn rf-btn-primary"><i class="bi bi-credit-card"></i> ادفع إلكترونيًا {{ \App\Support\Money::format($item->quote_amount) }}</button>
                                            <small class="rf-muted d-block mt-1">أو ادفع نقدًا / تحويلًا بالاتفاق مع الفريق، ويسجّله لك الفريق.</small>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="rf-notice">يراجع الفريق طلبك الآن، وسيصلك عرض السعر هنا وبإشعار.</div>
                        @endif

                        @if ($item->project)
                            <p class="mt-3"><a href="{{ route('business.projects.show', $item->project) }}" class="btn rf-btn-primary">متابعة المشروع {{ $item->project->number }}</a></p>
                        @endif

                        <h5 class="rf-section-title mt-4">تفاصيل طلبك</h5>
                        @include('website.business.partials.answers', ['item' => $item])

                        @if ($item->events->isNotEmpty())
                            <h5 class="rf-section-title mt-4">سجل الطلب</h5>
                            <ul class="bz-log">
                                @foreach ($item->events as $event)
                                    <li><span>{{ \App\Http\Controllers\admin\BusinessAdminController::actionLabel($event->action) }}</span> <small class="rf-muted">{{ $event->created_at?->format('Y-m-d H:i') }}</small></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div></div>
    </section>
@endsection
