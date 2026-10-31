@extends('website.layouts.master')
@section('title')
    اقتراحاتي
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
                                <li class="breadcrumb-item active" aria-current="page"> اقتراحاتي </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4><i class="bi bi-lightbulb-fill mr-1"></i> اقتراحاتي</h4>
                                <a href="{{ route('suggestions.create') }}" class="btn rf-link-btn"><i class="bi bi-plus-lg"></i> اقتراح جديد</a>
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            @if ($suggestions->isEmpty())
                                <div class="rf-empty-state">
                                    <p>لم ترسل اقتراحات بعد. عندك فكرة تطوّر نفّذها؟ <a href="{{ route('suggestions.create') }}">شاركنا اقتراحك</a>.</p>
                                </div>
                            @else
                                <div class="rf-request-list">
                                    @foreach ($suggestions as $suggestion)
                                        <a class="rf-request-row" href="{{ route('suggestions.show', $suggestion->reference_code) }}">
                                            <div>
                                                <strong>{{ $suggestion->title }}</strong>
                                                <span class="rf-muted"><span dir="ltr">{{ $suggestion->reference_code }}</span> · {{ $suggestion->categoryLabel() }} · {{ $suggestion->created_at->format('Y-m-d') }}</span>
                                                @if ($suggestion->status_reason)
                                                    <p class="rf-request-snippet">رد الفريق: {{ \Illuminate\Support\Str::limit($suggestion->status_reason, 140) }}</p>
                                                @endif
                                            </div>
                                            <span class="rf-status {{ $suggestion->statusCss() }}">{{ $suggestion->statusLabel() }}</span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mt-3">{{ $suggestions->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
