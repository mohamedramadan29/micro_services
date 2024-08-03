@extends('website.layouts.master')
@section('title')
    الطلبات الواردة
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title bg-cover" style="background:url({{asset('assets/website/img/bn-1.jpg')}})no-repeat;"
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
                            @if(Auth::user()->image !='')
                                <img src="{{asset('assets/uploads/users_image/'.Auth::user()->image)}}"
                                     class="img-fluid rounded" alt="">
                            @else
                                <img src="{{asset('assets/website/img/avatar.png')}}" class="img-fluid rounded" alt="">
                            @endif

                            <h4> {{Auth::user()->user_name}} </h4>
                            <span> {{Auth::user()->email}} </span>
                        </div>

                        <div class="d-navigation">
                            <ul id="metismenu">
                                <li><a href="{{url('dashboard')}}"><i class="ti-dashboard"></i> الملف الشخصي </a>
                                </li>
                                <li><a href="{{url('service/index')}}"><i class="ti-user"></i> الخدمات </a></li>
                                <li><a href="{{url('service/add')}}"><i class="ti-plus"></i> اضف خدمة جديدة </a></li>
                                <li><a href="{{url('chat-main')}}"><i class="ti-email"></i> المحادثات </a></li>
                                <li><a href="{{url('purches')}}"><i class="ti-email"></i> مشترياتي </a></li>
                                <li><a href="{{url('orders')}}"><i class="ti-email"></i> الطلبات الواردة </a></li>
                                <li><a href="{{url('reviews')}}"><i class="ti-email"></i> التقيمات </a></li>
                                <li><a href="{{url('update-account')}}"><i class="ti-email"></i> تعديل الملف الشخصي
                                    </a></li>
                                <li><a href="{{url('balance')}}"><i class="ti-email"></i> الرصيد </a></li>
                                <li><a href="{{url('logout')}}"><i class="ti-power-off"></i> تسجيل خروج </a></li>
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
                                        <li class="breadcrumb-item"><a href="{{url("/")}}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> حسابي</li>
                                        <li class="breadcrumb-item active" aria-current="page"> الطلبات الواردة</li>
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
                                        <h4><i class="fa fa-user mr-1"></i> الطلبات الواردة </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_body">
                                        <div class="_dashboard_list_group">
                                            @if($orders->count() > 0))
                                                @foreach($orders as $order)
                                                    <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                        <div class="_list_jobs_f1ex first">
                                                            <div class="_list_110">
                                                                <div class="_list_110_caption">
                                                                    <h4 class="_jb_title"><a
                                                                            href="{{url('service/'.$order['service_id'].'-'.$order->slug)}}"> {{$order['service_name']}} </a>
                                                                    </h4>
                                                                    <ul class="_grouping_list">
                                                                        <li><span> <a
                                                                                    href="{{'user/'.$order['seller']['user_name']}}"> <i
                                                                                        class="ti-user"></i>  {{$order['seller']['name']}} </span> </a>
                                                                        </li>
                                                                        <li><span>  {{$order['service_price']}} $   <i
                                                                                    class="ti-credit-card"></i> </span>
                                                                        </li>
                                                                        <li><span><i class="ti-location-pin"></i> {{$order['status']}} </span>
                                                                        </li>
                                                                        <li><span><i class="ti-timer"></i> {{$order['created_at']->diffForHumans()}} </span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            @else
                                                <div class="alert alert-info"> لا يوجد لديك طلبات واردة في الوقت
                                                    الحالي
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
