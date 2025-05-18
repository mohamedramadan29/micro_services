@extends('website.layouts.master')
@section('title')
    عقاري
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar">

                        <div class="d-user-avater">
                            @if (Auth::user()->image != '')
                                <img src="{{ asset('assets/uploads/users_image/' . Auth::user()->image) }}"
                                    class="img-fluid rounded" alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif

                            <h4> {{ Auth::user()->user_name }} </h4>
                            <span> {{ Auth::user()->email }} </span>
                        </div>

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
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> عقاري </li>
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
                                            <h4><i class="ti-lock mr-1"></i> عقاراتي  </h4>
                                            <a href="{{ url('my/property/add') }}" class="btn btn-primary btn-sm"> اضافة عقار  <i class="bi bi-plus"></i> </a>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($properties->count() > 0)
                                                @foreach ($properties as $properity)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="ser_110">
                                                            <div class="ser_110_thumb">
                                                                <a href="{{ url('my/property/update/' . $properity['id']) }}"
                                                                    class="ser_100_link"><img
                                                                        src="{{ asset('assets/uploads/properities/' . ($properity->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                                                        class="img-fluid" alt=""></a>
                                                            </div>
                                                            <div class="ser_110_footer bott" style="justify-content: center">
                                                                <div class="">
                                                                    <div>
                                                                        <h5>
                                                                            <a href="{{ url('my/property/update/' . $properity['id']) }}">
                                                                                {{ $properity['title'] }}
                                                                            </a>
                                                                        </h5>
                                                                        <span> {{ $properity['type'] }} <span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="buttons" style="text-align: center;padding:10px">
                                                                <a href="{{ url('my/property/update/' . $properity['id']) }}"
                                                                    class="btn btn-primary btn-sm"> تعديل <i
                                                                        class="fa fa-edit"></i> </a>
                                                                <a style="height: 30px" href="{{ url('my/property/delete/' . $properity['id']) }}"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العنصر؟')">
                                                                    حذف <i class="fa fa-trash"></i> </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-info"> لا يوجد لديك اي عقارات :: ادخل العقار الاول

                                                </div>
                                            @endif
                                        </div>
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
