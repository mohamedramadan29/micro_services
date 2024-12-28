@extends('website.layouts.master')
@section('title')
    {{ $user->user_name }}
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{ asset('assets/website/img/bn-1.jpg') }})no-repeat;"
        data-overlay="5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12"></div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right" dir="rtl">
        <div class="container-fluid">
            <div class="row m-0">

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12">
                    <div class="dashboard-navbar overlio-top">

                        <div class="d-user-avater">
                            @if ($user->image != '')
                                <img src="{{ asset('assets/uploads/users_image/' . $user->image) }}" class="img-fluid rounded"
                                    alt="">
                            @else
                                <img src="{{ asset('assets/website/img/avatar.png') }}" class="img-fluid rounded"
                                    alt="">
                            @endif

                            <h4> {{ $user->user_name }} </h4>
                            <span> {{ $user->email }} </span>
                        </div>

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{ url('user/' . $user['user_name']) }}"><i class="ti-dashboard"></i> الملف
                                        الشخصي </a>
                                </li>
                                <li>
                                    <a href="{{ url('user/' . $user['user_name'] . '/services') }}"><i class="ti-user"></i>
                                        الخدمات </a>
                                    </li>
                                {{-- <li>
                                    <a href="{{ url('reviews') }}"><i class="ti-email"></i> التقيمات </a>
                                </li> --}}
                            </ul>
                        </div>

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
                                        <li class="breadcrumb-item active" aria-current="page"> الملف الشخصي </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">

                            <!-- Single Wrap -->
                            <div class="_dashboard_content">
                                <div class="_dashboard_content_header">
                                    <div class="_dashboard__header_flex">
                                        <h4><i class="fa fa-user mr-1"></i> الملف الشخصي </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <div class="col-xl-12 col-lg-12">
                                                <div class="form-group">
                                                    <label> نبذة عني </label>
                                                    <textarea class="form-control with-light">{{ $user->info }}</textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Single Wrap End -->

                                <!-- Single Wrap -->
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_header">
                                        <div class="_dashboard__header_flex">
                                            <h4><i class="ti-lock mr-1"></i> خدماتي </h4>
                                        </div>
                                    </div>

                                    <div class="_dashboard_content_body">
                                        <div class="row">
                                            <!-- Single Item -->
                                            @if ($services->count() > 0)
                                                @foreach ($services as $serv)
                                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                                        <div class="ser_110">
                                                            <div class="ser_110_thumb">
                                                                <a href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}"
                                                                    class="ser_100_link"><img
                                                                        src=" {{ asset('assets/uploads/services/' . $serv['image']) }}"
                                                                        class="img-fluid" alt=""></a>
                                                            </div>
                                                            <div class="ser_110_footer bott">
                                                                <div class="_110_foot_left">
                                                                    <div>
                                                                        <h5>
                                                                            <a
                                                                                href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}">
                                                                                {{ $serv['name'] }} </a>
                                                                        </h5>
                                                                        <span> {{ $serv['category']['name'] }} <span>
                                                                                <div class="_dash_usr_rates mb-1">
                                                                                    <span class="good">
                                                                                        {{ $serv['rate'] }} </span>
                                                                                    @for ($i = 0; $i < 5; $i++)
                                                                                        @if ($i < $serv['rate'])
                                                                                            <i class="fa fa-star"></i>
                                                                                        @else
                                                                                            <i class="fa fa-star-o"></i>
                                                                                        @endif
                                                                                    @endfor
                                                                                </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-info">
                                                    لا يوجد خدمات للمستخدم حتي الان
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
