@extends('website.layouts.master')
@section('title')
    نفّذها للأعمال — طلباتي ومشاريعي
@endsection
@section('content')
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    @include('website.service-requests.partials.dashboard-nav')
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                    <div class="bredcrumb_wrap"><nav aria-label="breadcrumb"><ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                        <li class="breadcrumb-item active" aria-current="page"> نفّذها للأعمال </li>
                    </ol></nav></div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header"><div class="_dashboard__header_flex">
                            <h4><i class="bi bi-buildings-fill mr-1"></i> مشاريع شركتك{{ $company ? ' — ' . $company->name : '' }}</h4>
                            <a href="{{ route('business.index') }}" class="btn rf-link-btn"><i class="bi bi-plus-lg"></i> طلب جديد</a>
                        </div></div>
                        <div class="_dashboard_content_body">
                            <h5 class="rf-section-title">المشاريع</h5>
                            @forelse ($projects as $project)
                                <a class="rf-request-row" href="{{ route('business.projects.show', $project) }}">
                                    <div>
                                        <strong>{{ $project->title }}</strong>
                                        <span class="rf-muted"><span dir="ltr">{{ $project->number }}</span> · {{ $project->tasks_count }} مهام · الإنجاز {{ $project->progress() }}%</span>
                                        <div class="bz-progress" aria-hidden="true"><span style="width: {{ $project->progress() }}%"></span></div>
                                    </div>
                                    <span class="rf-status {{ $project->status === 'completed' ? 'rf-status-completed' : ($project->status === 'cancelled' ? 'rf-status-not_completed' : 'rf-status-open') }}">{{ $project->statusLabel() }}</span>
                                </a>
                            @empty
                                <div class="rf-empty-state"><p>لا توجد مشاريع بعد. يتحول طلبك لمشروع بعد اعتماد عرض السعر.</p></div>
                            @endforelse

                            <h5 class="rf-section-title mt-4">الطلبات</h5>
                            @forelse ($requests as $item)
                                <a class="rf-request-row" href="{{ route('business.requests.show', $item) }}">
                                    <div>
                                        <strong>{{ $item->company_name ?: 'طلب شركة' }}</strong>
                                        <span class="rf-muted"><span dir="ltr">{{ $item->number }}</span> · {{ $item->sourceLabel() }} · {{ $item->created_at->format('Y-m-d') }}
                                            @if ($item->hasQuote()) · عرض السعر {{ \App\Support\Money::show($item->quote_amount) }}@endif</span>
                                        @if ($item->awaitsClientDecision())<p class="rf-request-snippet rf-your-turn"><i class="bi bi-arrow-left-circle-fill"></i> عرض سعر بانتظار اعتمادك</p>@endif
                                    </div>
                                    <span class="rf-status rf-status-{{ in_array($item->status, ['accepted', 'converted'], true) ? 'completed' : ($item->status === 'rejected' ? 'not_completed' : 'inquiry') }}">{{ $item->statusLabel() }}</span>
                                </a>
                            @empty
                                <div class="rf-empty-state"><p>لم ترسل طلبات بحسابك بعد. <a href="{{ route('business.index') }}">أرسل طلب شركتك</a>.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
