@extends('website.layouts.master')
@section('title')
    مشترياتي
@endsection
@section('content')

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg pt-4 text-right profile_page" dir="rtl">
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
                                        <li class="breadcrumb-item"><a href="{{url("/")}}"> الرئيسية </a></li>
                                        <li class="breadcrumb-item active" aria-current="page"> حسابي</li>
                                        <li class="breadcrumb-item active" aria-current="page"> مشترياتي</li>
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
                                        <h4><i class="fa fa-user mr-1"></i> مشترياتي </h4>
                                    </div>
                                </div>
                                <div class="_dashboard_content">
                                    <div class="_dashboard_content_body">
                                        <div class="_dashboard_list_group">
                                            @if($purches->count() > 0)
                                                @foreach($purches as $purche)

                                                    <div class="_list_jobs_wraps mng_list shadow_0 border">
                                                        <div class="_list_jobs_f1ex first">
                                                            <div class="_list_110">
                                                                <div class="_list_110_caption">
                                                                    <h4 class="_jb_title"><a
                                                                            href="{{url('service/'.$purche['service_id'].'-'.$purche->slug)}}"> {{$purche['service_name']}} </a>
                                                                    </h4>
                                                                    <ul class="_grouping_list">
                                                                        <li><span> <a
                                                                                    href="{{'user/'.$purche['seller']['user_name']}}"> <i
                                                                                        class="ti-user"></i>  {{$purche['seller']['name']}} </span> </a>
                                                                        </li>
                                                                        <li><span>  {{$purche['service_price']}} $   <i
                                                                                    class="ti-credit-card"></i> </span>
                                                                        </li>
                                                                        <li><span><i class="ti-location-pin"></i> {{$purche['status']}} </span>
                                                                        </li>
                                                                        <li><span><i class="ti-timer"></i> {{$purche['created_at']->diffForHumans()}} </span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                @endforeach

                                            @else
                                                <div class="alert alert-info"> لا يوجد لديك مشتريات في الوقت الحالي
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
