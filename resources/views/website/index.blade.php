@extends('website.layouts.master')
@section('title')  نفذها   @endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner full bg-cover center"
         style="background:#00000057 url({{asset('assets/website/img/background3.webp')}}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1> أنجز مشاريعك عبر الإنترنت بسهولة وأمان </h1>
            <p class="lead">وظّف مستقلين محترفين لإنجاز أعمالك </p>
            <form class="mt-4" dir="rtl" method="get" action="{{url('search')}}">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 col-sm-12">
                        <div class="banner-search style-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control lio-rad" placeholder=" ابحث عن الخدمة  ">
                                <div class="input-group-append">
                                    <button type="submit" class="btn bt-round btn--2"> بحث <i class="ti-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- ============================ How It Work Start ==================================== -->
    <section class="how-it-works" dir="rtl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> كيف <span class="theme-cl-2"> نعمل  ؟ </span></h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{asset('assets/website/img/step-1.png')}}"
                                class="img-responsive" alt=""><span
                                class="process-num">01</span></span>
                        <h4> أضف المشروع </h4>
                        <p> أضف تفاصيل مشروعك والمهارات المطلوبة لإنجازه وابدأ باستقبال عروض المستقلين عليه. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{asset('assets/website/img/step-2.png')}}"
                                class="img-responsive" alt=""><span
                                class="process-num">02</span></span>
                        <h4> اختر العرض المناسب </h4>
                        <p> من بين العروض المقدمة لمشروعك، اختر العرض المناسب لمتطلبات المشروع ثم ابدأ مباشرة مرحلة
                            التنفيذ. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{asset('assets/website/img/step-3.png')}}"
                                class="img-responsive" alt=""><span
                                class="process-num">03</span></span>
                        <h4> استلم المشروع </h4>
                        <p> سيعمل المستقل الذي اخترته معك حتى انتهاء العمل وتسليم مشروعك بشكل كامل كما أردته. </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ How It Work End ==================================== -->

    <!-- =========================== Start Categories ========================================== -->
    <section class="min-sec categories" dir="rtl">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> كافة الخدمات الاحترافية <span class="theme-cl-2">  لتطوير أعمالك </span></h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Single Item -->
                @foreach($main_categories as $category)
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ser_110">
                            <div class="ser_110_thumb">
                                <a href="{{url('category/'.$category['slug'])}}" class="ser_100_link"><img src=" {{asset('assets/uploads/service_category/'.$category['image'])}}"
                                                                      class="img-fluid" alt=""></a>
                            </div>
                            <div class="ser_110_footer bott">
                                <div class="_110_foot_left">
                                    <div>
                                        <h5>
                                            <a href="{{url('category/'.$category['slug'])}}"> {{$category['name']}} </a>
                                        </h5>

                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </section>

    <!-- =========================== End Categories  ======================================= -->

    <!-- ============================ Start  Popular Category ==================================== -->
    <section class="gray-light">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> خدمات  <span class="theme-cl-2"> شائعه  </span></h2>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-bar-chart"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> تصميم شعار  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-palette"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> مونتاج فيديو  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-car"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> انشاء تطبيق جوال  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-home"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> انشاء متجر الكتروني  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-desktop"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> موشن جرافيك  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-brush-alt"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> تحسين محركات البحث  </a></h3>
                        </div>
                    </div>
                </div>

                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-car"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> ترجمة  </a></h3>
                        </div>
                    </div>
                </div>
                <!-- Single Category -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="urip_cated shadow">
                        <div class="urip_cated_avater">
                            <i class="ti-bar-chart-alt"></i>
                        </div>
                        <div class="urip_cated_caps">
                            <h3 class="cats_urip_title"><a href="#"> ادارة حسابات التواصل الاجتماعي  </a></h3>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- ============================ End Popular Category ==================================== -->

    <!-- ============================ Call To Action Start ================================== -->
    <section class="call-to-act" style="background:#0b85ec url({{asset('assets/website/img/landing-bg.png')}}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-7 col-md-8">
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light"> هل أنت جاهز لبدء مشروعك الخاص ؟ </h2>
                    </div>
                    <div class="inner-flexible-box subscribe-box">
                        <div class="input-group">
                            <button class="btn btn-primary start_job"> ابدا الان  </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Call To Action End ================================== -->
@endsection
