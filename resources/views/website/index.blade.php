@extends('website.layouts.master')
@section('title')
    نفذها
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        .payment-slider {
            width: 90%;
            margin: 30px auto;
            overflow: hidden;
        }

        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-slide img {
            width: 100px;
            height: 100px;
            filter: drop-shadow(2px 2px 5px rgba(0, 0, 0, 0.1));
        }
    </style>
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner full bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/nafizha.jpg') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1> {{ __('index.index_h1') }} </h1>
            <p class="lead"> دليل شامل لاختيار وتوظيف أفضل المستقلين لعملك </p>
            <button data-bs-toggle="modal" data-bs-target="#FreeConsultModel" class="btn btn-primary free_consult_button"><i
                    class="bi bi-patch-question-fill"></i> احصل علي استشارة مجانية </button>
            <form class="mt-4" dir="rtl" method="get" action="{{ url('search') }}">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 col-sm-12">
                        <div class="banner-search style-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control lio-rad"
                                    placeholder="  {{ __('index.search_text') }} ">
                                <div class="input-group-append">
                                    <button type="submit" class="btn bt-round btn--2"> {{ __('index.search') }} <i
                                            class="ti-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- =========================== Start Payment Slider  ============================ -->
    <div class="container">
        <div class="payment-slider">

            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/website/img/visa.png') }}" alt="Visa"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/website/img/master.png') }}" alt="MasterCard">
                    </div>
                    <div class="swiper-slide"><img src="{{ asset('assets/website/img/paypal.png') }}" alt="PayPal"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/website/img/apple-pay.png') }}" alt="ApplePay">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        const swiper = new Swiper('.swiper', {
            slidesPerView: '3', // عرض عدد غير محدد بناءً على حجم الشاشة
            spaceBetween: 20,
            loop: true, // تشغيل اللوب لانهائي
            speed: 3000, // سرعة الحركة (كلما زاد الرقم، زادت السرعة)
            autoplay: {
                delay: 0, // بدون توقف
                disableOnInteraction: false
            }
        });
    </script>


    <!-- =========================== End Payment Slider ============================== -->

    <!-- ============================ How It Work Start ==================================== -->
    <section class="how-it-works" dir="rtl">
        <div class="container">
            @include('website.free_consult_model')
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> {{ __('index.how_work') }} <span class="theme-cl-2"> {{ __('index.how_work2') }} ؟ </span>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/new-project.png') }}" class="img-responsive"
                                alt=""><span class="process-num">01</span></span>
                        <h4> نشر مشروع </h4>
                        <p> قم بنشر تفاصيل مشروعك على المنصة، واطلب من الخبراء تقديم عروضهم. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/select_user.png') }}" class="img-responsive"
                                alt=""><span class="process-num">02</span></span>
                        <h4> استقر على العرض الأفضل </h4>
                        <p> بعد مقارنة العروض، حدد العرض الذي يلبي متطلبات مشروعك بالكامل. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/completed-task.png') }}" class="img-responsive"
                                alt=""><span class="process-num">03</span></span>
                        <h4> إتمام مشروعك </h4>
                        <p> إتمام المشروع بنجاح هو الهدف الأساسي للمنصة، حيث يرافق المستقل صاحب المشروع خطوة بخطوة حتى
                            التسليم النهائي. </p>
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

                        <h2>خدمات احترافية مميزة <span class="theme-cl-2"> لدعم نمو أعمالك وتعزيز أرباحك</span></h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Single Item -->
                @foreach ($main_categories as $category)
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="ser_110">
                            <div class="ser_110_thumb">
                                <a href="{{ url('services/' . $category['slug']) }}" class="ser_100_link">
                                    <img style="width: 120px; height:120px;margin:auto;padding-top: 15px;display:block;"
                                        src=" {{ asset('assets/uploads/service_category/' . $category['image']) }}"
                                        class="img-fluid" alt=""></a>
                            </div>
                            <div class="ser_110_footer bott">
                                <div class="_110_foot_left">
                                    <div>
                                        <h5>
                                            <a href="{{ url('category/' . $category['slug']) }}"> {{ $category['name'] }}
                                            </a>
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
    {{-- <section class="gray-light">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">



                        <h2> خدمات لا غنى عنها <span class="theme-cl-2"> لأي رائد أعمال طموح </span></h2>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">

                @foreach ($sub_categories as $sub_category)
                    <!-- Single Category -->
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="urip_cated shadow">
                            <div class="urip_cated_avater">
                                <i class="ti-bar-chart"></i>
                            </div>
                            <div class="urip_cated_caps">
                                <h3 class="cats_urip_title"><a href="{{ url('services/' . $sub_category['slug']) }}">
                                        {{ $sub_category['name'] }} </a></h3>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section> --}}
    <!-- ============================ End Popular Category ==================================== -->

    <!-- ########################################### Presit Section ######################## -->

    <section class="call-to-act gift_section"
        style="background:#3fb697 url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="inner-flexible-box subscribe-box">
                        <img class="animate-pulse" src="{{ asset('assets/website/img/gift.png') }}" alt="">
                    </div>
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light">هدية مميزة بانتظارك </h2>

                        <p class="text-light">


                            بمجرد أن تصل قيمة مشترياتك إلى <strong>1000 دولار</strong>، ستحصل على رصيد مجاني بقيمة
                            <strong>50 دولار</strong> يمكن إضافته إلى حسابك بعد طلبك القادم! استمتع بتسوقك معنا واستفد من
                            المزيد من المزايا.

                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!------------------------------------- End Present Section ##################### -->
@endsection


@section('js')
    <script>
        window.onload = () => {
            confetti({
                particleCount: 300,
                spread: 300,
                origin: {
                    y: 0.6
                }
            });
        };
    </script>
@endsection
