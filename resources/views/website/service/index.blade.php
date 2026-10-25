@extends('website.layouts.master')
@section('title')
    خدماتي المعروضة
@endsection
@section('content')
    @use('App\Models\admin\Service')
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
                                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">لوحتي</a></li>
                                <li class="breadcrumb-item active" aria-current="page">خدماتي المعروضة</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="_dashboard_content">
                        <div class="_dashboard_content_header">
                            <div class="_dashboard__header_flex">
                                <h4><i class="bi bi-grid"></i> خدماتي المعروضة</h4>
                                <a href="{{ url('service/add') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> إضافة خدمة</a>
                            </div>
                        </div>
                        <div class="_dashboard_content_body">
                            @if (Session::has('Success_message'))
                                <div class="alert alert-success">{{ Session::get('Success_message') }}</div>
                            @endif
                            @if ($services->isNotEmpty())
                                <p class="text-muted small">خدمات جاهزة بسعر ثابت يشتريها العميل مباشرة. تظهر للعملاء بعد مراجعة فريق نفّذها، وتستلم سعرها بعد خصم عمولة نفّذها ({{ rtrim(rtrim(number_format((float) $commission, 2), '0'), '.') }}%).</p>
                                <div class="rf-pf-list">
                                    @foreach ($services as $serv)
                                        @php $editUrl = url('service/update/' . $serv->id); @endphp
                                        <article class="rf-pf-card">
                                            <a href="{{ $serv->isActive() ? $serv->url() : $editUrl }}" class="rf-pf-card-img">
                                                <img src="{{ asset('assets/uploads/services/' . $serv->image) }}" alt="{{ $serv->name }}" loading="lazy">
                                                @if ($serv->isActive())
                                                    <span class="rf-acc-pill stamp">منشورة</span>
                                                @elseif ((int) $serv->status === Service::STATUS_REJECTED)
                                                    <span class="rf-acc-pill bad">غير مقبولة</span>
                                                @else
                                                    <span class="rf-acc-pill warn">بانتظار المراجعة</span>
                                                @endif
                                            </a>
                                            <div class="rf-pf-card-body">
                                                <h3><a href="{{ $editUrl }}">{{ $serv->name }}</a></h3>
                                                @if ((int) $serv->status === Service::STATUS_REJECTED && $serv->review_note)
                                                    <p class="rf-pf-note"><strong>ملاحظة الفريق:</strong> {{ $serv->review_note }} — عدّلها واحفظها لتُراجع من جديد.</p>
                                                @endif
                                                <small>{{ $serv->subcategory?->name ?? $serv->category?->name }}</small>
                                                <p class="rf-sf-price">{{ \App\Support\Money::show($serv->price) }}</p>
                                            </div>
                                            <div class="rf-pf-card-actions">
                                                <a href="{{ $editUrl }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i> تعديل</a>
                                                @if ($serv->isActive())
                                                    <a href="{{ $serv->url() }}" class="btn btn-light btn-sm" target="_blank"><i class="bi bi-eye"></i> عرض</a>
                                                @endif
                                                <a data-method="post" href="{{ url('service/delete/' . $serv->id) }}" class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('حذف هذه الخدمة؟')"><i class="bi bi-trash"></i> حذف</a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="rf-pf-empty">
                                    <i class="bi bi-grid"></i>
                                    <strong>لم تضف أي خدمة بعد</strong>
                                    <p>اعرض خدمة جاهزة بسعر ثابت (مثل «تصميم شعار» أو «صفحة هبوط»)، يشتريها العميل مباشرة من صفحتها.</p>
                                    <a href="{{ url('service/add') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> أضف أول خدمة</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
