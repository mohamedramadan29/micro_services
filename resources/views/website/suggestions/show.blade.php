@extends('website.layouts.master')
@section('title')
    الاقتراح {{ $suggestion->reference_code }}
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
                                <li class="breadcrumb-item"><a href="{{ route('suggestions.mine') }}"> اقتراحاتي </a></li>
                                <li class="breadcrumb-item active" aria-current="page" dir="ltr"> {{ $suggestion->reference_code }} </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4>{{ $suggestion->title }}</h4>
                                <span class="rf-status {{ $suggestion->statusCss() }}">{{ $suggestion->statusLabel() }}</span>
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            <p class="rf-muted">رقم المتابعة <span dir="ltr">{{ $suggestion->reference_code }}</span> · {{ $suggestion->categoryLabel() }} · أُرسل {{ $suggestion->created_at->format('Y-m-d H:i') }}</p>
                            <div style="white-space: pre-line">{{ $suggestion->description }}</div>
                            @if ($suggestion->status_reason)
                                <div class="rf-notice mt-3"><strong>رد الفريق:</strong> <span style="white-space: pre-line">{{ $suggestion->status_reason }}</span></div>
                            @endif
                            <p class="rf-muted mt-3 mb-0">نخبرك عندما يصبح اقتراحك «مخططًا للتنفيذ» أو «تم تنفيذه» أو يُغلق. الاقتراحات خاصة ولا تظهر لغيرك.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
