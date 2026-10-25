@extends('website.layouts.master')
@section('title')
معرض أعمالي
@endsection
@section('content')

<!-- ============================ Main Section Start ================================== -->
<section class="gray-bg pt-4 text-right profile_page" dir="rtl">
    <div class="container-fluid">
        <div class="row m-0">

            <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                <div class="dashboard-navbar">

                    @include('website.user.partials.account-head')

                    @include('website.layouts.dashboard-sidebar')


                </div>
            </div>

            <!-- Item Wrap Start -->
            <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <!-- Breadcrumbs -->
                        <div class="bredcrumb_wrap">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">لوحتي</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"> معرض أعمالي </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">

                        <!-- Single Wrap -->
                        <div class="_dashboard_content">
                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex d-flex justify-content-between"
                                        style="width: 100%">
                                        <h4><i class="bi bi-images"></i> أعمالي </h4>
                                        <a href="{{ route('user.portfolio.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> إضافة عمل</a>
                                    </div>
                                </div>

                                <div class="_dashboard_content_body">
                                    @if (Session::has('Success_message'))
                                        <div class="alert alert-success">{{ Session::get('Success_message') }}</div>
                                    @endif
                                    @if ($portfolios->count() > 0)
                                        <div class="rf-pf-list">
                                            @foreach ($portfolios as $portfolio)
                                                @php $editUrl = route('user.portfolio.edit', [$portfolio->id, $portfolio->slug]); @endphp
                                                <article class="rf-pf-card">
                                                    <a href="{{ $editUrl }}" class="rf-pf-card-img">
                                                        <img src="{{ asset('assets/uploads/portfolios/' . $portfolio->image) }}" alt="{{ $portfolio->title }}" loading="lazy">
                                                        @if ($portfolio->isPublished())
                                                            <span class="rf-acc-pill stamp">منشور</span>
                                                        @elseif ((int) $portfolio->status === \App\Models\front\UserPortfolio::STATUS_REJECTED)
                                                            <span class="rf-acc-pill bad">غير مقبول</span>
                                                        @else
                                                            <span class="rf-acc-pill warn">بانتظار المراجعة</span>
                                                        @endif
                                                    </a>
                                                    <div class="rf-pf-card-body">
                                                        <h3><a href="{{ $editUrl }}">{{ $portfolio->title }}</a></h3>
                                                        @if ((int) $portfolio->status === \App\Models\front\UserPortfolio::STATUS_REJECTED && $portfolio->review_note)
                                                            <p class="rf-pf-note"><strong>ملاحظة الفريق:</strong> {{ $portfolio->review_note }} — عدّل العمل واحفظه ليُراجع من جديد.</p>
                                                        @endif
                                                        <small>{{ $portfolio->category?->name }}@if (is_array($portfolio->more_images) && count($portfolio->more_images)) · {{ count($portfolio->more_images) + 1 }} صور @endif</small>
                                                    </div>
                                                    <div class="rf-pf-card-actions">
                                                        <a href="{{ $editUrl }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i> تعديل</a>
                                                        <a data-method="post" href="{{ route('user.portfolio.delete', $portfolio->id) }}" class="btn btn-outline-danger btn-sm"
                                                           onclick="return confirm('حذف هذا العمل من معرضك؟')"><i class="bi bi-trash"></i> حذف</a>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rf-pf-empty">
                                            <i class="bi bi-images"></i>
                                            <strong>لم تضف أي عمل بعد</strong>
                                            <p>أضف أعمالك السابقة بصورها؛ تساعد العملاء على اختيارك وتساعد الفريق على اعتماد حسابك.</p>
                                            <a href="{{ route('user.portfolio.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> أضف أول عمل</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
</section>
<!-- ============================ Main Section End ================================== -->

@endsection
