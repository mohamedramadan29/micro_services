@extends('website.layouts.master')
@section('title')
    عروضي
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="bredcrumb_wrap">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                <li class="breadcrumb-item active" aria-current="page"> عروضي </li>
                            </ol>
                        </nav>
                    </div>
                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4><i class="bi bi-send-check mr-1"></i> عروضي </h4>
                                <a href="{{ route('freelancer.requests.index') }}" class="rf-link-btn"><i class="bi bi-inboxes"></i> طلبات العملاء</a>
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            @if ($offers->isEmpty())
                                <div class="rf-empty-state">
                                    <p>لم ترسل أي عرض بعد.</p>
                                    <a href="{{ route('freelancer.requests.index') }}" class="rf-link-btn">تصفح طلبات العملاء</a>
                                </div>
                            @else
                                <div class="rf-request-list">
                                    @foreach ($offers as $offer)
                                        <div class="rf-request-row">
                                            <div>
                                                <a href="{{ route('freelancer.requests.show', $offer->request) }}"><strong>{{ $offer->request->service_name }}</strong></a>
                                                <span class="rf-muted">{{ $offer->request->number }} · {{ \App\Support\Money::show($offer->price) }} · {{ $offer->duration_days }} يوم · {{ $offer->updated_at->diffForHumans() }}</span>
                                                <div class="mt-2">
                                                    <a href="{{ route('offers.chat', $offer) }}" class="rf-muted"><i class="bi bi-chat-dots"></i> المحادثة
                                                        @if ($offer->unread_count)
                                                            <span class="rf-unread">{{ $offer->unread_count }}</span>
                                                        @endif
                                                    </a>
                                                </div>
                                            </div>
                                            <span class="rf-status rf-offer-{{ $offer->status }}">{{ $offer->statusLabel() }}</span>
                                            @if ($offer->isActive() && $offer->requestEditedSince())<span class="rf-status rf-status-edited">عُدّل الطلب بعد عرضك</span>@endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">{{ $offers->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
