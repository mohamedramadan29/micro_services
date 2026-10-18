@extends('website.layouts.master')
@section('title')
    طلباتي
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
                                <li class="breadcrumb-item active" aria-current="page"> طلباتي </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4><i class="bi bi-clipboard-check mr-1"></i> طلباتي </h4>
                                <a href="{{ route('requests.create') }}" class="btn rf-link-btn"><i class="bi bi-plus-lg"></i> طلب جديد</a>
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            @if ($requests->isEmpty())
                                <div class="rf-empty-state">
                                    <p>لم ترسل أي طلب بعد.</p>
                                    <a href="{{ route('requests.create') }}" class="btn rf-link-btn">اطلب عرضًا لمشروعك</a>
                                </div>
                            @else
                                <div class="rf-request-list">
                                    @foreach ($requests as $item)
                                        <a class="rf-request-row" href="{{ route('requests.show', $item) }}">
                                            <div>
                                                <strong>{{ $item->service_name }}</strong>
                                                <span class="rf-muted">{{ $item->number }} · {{ $item->created_at->format('Y-m-d') }}</span>
                                                <p class="rf-request-snippet">{{ \Illuminate\Support\Str::limit($item->problem, 120) }}</p>
                                            </div>
                                            <span class="rf-status rf-status-{{ $item->status }}">{{ $item->statusLabel() }}</span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mt-3">{{ $requests->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
