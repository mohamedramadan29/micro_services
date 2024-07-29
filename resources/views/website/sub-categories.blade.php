@extends('website.layouts.master')
@section('title')
    {{$category['name']}}
@endsection
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <div class="page-title" style="height: 350px;text-align: right">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    <h2 class="ipt-title"> {{$category['name']}} </h2>
                    <span class="ipn-subtitle"> مشاهدة جميع الاقسام  </span>

                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <div class="container">
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <!-- Filter Search -->
                            <div class="_filt_tag786">
                                <div class="_tag782">
                                    <div class="_tag780"> {{count($sub_categories)}} اقسام فرعيه</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Single Item -->
                        @foreach($sub_categories as $category)
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110">
                                    <div class="ser_110_thumb">
                                        <a href="{{url('services/'.$category['slug'])}}" class="ser_100_link">
                                            <img src=" {{asset('assets/uploads/service_category/'.$category['image'])}}"
                                                 class="img-fluid" alt="">
                                        </a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                        <div class="_110_foot_left">
                                            <div>
                                                <h5>
                                                    <a href="{{url('services/'.$category['slug'])}}"> {{$category['name']}} </a>
                                                </h5>
                                               <span> عدد الخدمات :: {{ $category->CountServices() }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{$sub_categories->links()}}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->

    <!-- ============================ Call To Action Start ================================== -->
    <section class="call-to-act"
             style="background:#0b85ec url({{asset('assets/website/img/landing-bg.png')}}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-7 col-md-8">
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light"> هل أنت جاهز لبدء مشروعك الخاص ؟ </h2>
                    </div>
                    <div class="inner-flexible-box subscribe-box">
                        <div class="input-group">
                            <button class="btn btn-primary start_job"> ابدا الان</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->
@endsection
