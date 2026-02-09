@extends('website.layouts.master')
@section('title')
    نفذها
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner full bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/hero.jpeg') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1 style="line-height: 2">
                <span> نفّذها… وخلّي فكرتك واقع بسهولة منصة عربية تربط أصحاب </span>
                <br>
                <span> المشاريع بأفضل المستقلين لتنفيذ أعمالهم باحتراف وسرعة </span>
                <br>
                <span> تصميم ، البرمجة، التسويق، الكتابة والمزيد </span>
            </h1>
            <p class="lead" style="margin-top: -15px;"> دليل شامل لاختيار وتوظيف أفضل المستقلين لعملك </p>
            {{-- <button data-bs-toggle="modal" data-bs-target="#FreeConsultModel" class="btn btn-primary free_consult_button"><i
                    class="bi bi-patch-question-fill"></i> احصل علي استشارة مجانية </button> --}}
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
            <br>
            <a href="{{ url('service/add') }}" style="padding:12px 25px" class="btn btn-primary"> <i class="bi bi-plus"></i>
                  أضف خدمتك  من فضلك </a>
        </div>
    </div>
    <!-- ============================ Hero Banner End ================================== -->

    <!-- =========================== Start Payment Slider  ============================ -->
    <div class="container">
        <div class="payment-slider">

            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img loading="lazy" src="{{ asset('assets/website/img/visa.png') }}"
                            alt="Visa"></div>
                    <div class="swiper-slide"><img loading="lazy" src="{{ asset('assets/website/img/master.png') }}"
                            alt="MasterCard">
                    </div>
                    <div class="swiper-slide"><img loading="lazy" src="{{ asset('assets/website/img/paypal.png') }}"
                            alt="PayPal"></div>
                    <div class="swiper-slide"><img loading="lazy" src="{{ asset('assets/website/img/apple-pay.png') }}"
                            alt="ApplePay">
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
                    <div class="working-process"><span class="process-img"><img loading="lazy"
                                src="{{ asset('assets/website/img/new-project.png') }}" class="img-responsive"
                                alt=""><span class="process-num">01</span></span>
                        <h4> نشر مشروع </h4>
                        <p> قم بنشر تفاصيل مشروعك على المنصة، واطلب من الخبراء تقديم عروضهم. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img loading="lazy"
                                src="{{ asset('assets/website/img/select_user.png') }}" class="img-responsive"
                                alt=""><span class="process-num">02</span></span>
                        <h4> استقر على العرض الأفضل </h4>
                        <p> بعد مقارنة العروض، حدد العرض الذي يلبي متطلبات مشروعك بالكامل. </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img loading="lazy"
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

    <!-- ############################# Start Last Services ################# -->

    <section class="min-sec categories home_services" dir="rtl" style="background-color: #CCEBE6">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> أحدث الخدمات </h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="swiper services-slider">
                        <div class="swiper-wrapper">
                            <!-- Single Item -->
                            @foreach ($services as $serv)
                                <!-- Single Item -->
                                <div class="swiper-slide">
                                    <div class="ser_110 shadow_0 serv_data_new_details">
                                        <div class="ser_110_thumb">
                                            <a href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}"
                                                class="ser_100_link"><img
                                                    src="{{ asset('assets/uploads/services/' . $serv['image']) }}"
                                                    class="img-fluid" alt=""></a>
                                        </div>
                                        <div class="ser_110_footer bott">
                                            <div class="_110_foot_left">
                                                <div class="_autho098">
                                                    @if (empty($serv['user']['image']))
                                                        <img src="{{ asset('assets/website/img/avatar.png') }}"
                                                            class="img-fluid circle" alt="">
                                                    @else
                                                        <img src="{{ asset('assets/uploads/users_image/' . $serv['user']['image']) }}"
                                                            class="img-fluid circle" alt="">
                                                    @endif

                                                    <img src="{{ asset('assets/website/img/verify.svg') }}"
                                                        class="verified" width="12" alt="">
                                                </div>

                                                <div class="_autho097">
                                                    <h5>
                                                        <a href="{{ url('user/' . $serv['user']['user_name']) }}">{{ Str::limit($serv['user']['user_name'], 15, '...') }}
                                                        </a>
                                                    </h5>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="ser_110_caption">
                                            <div class="ser_rev098">
                                                @for ($i = 0; $i < 5; $i++)
                                                    @if ($i < $serv['rate'])
                                                        <i class="fa fa-star filled"></i>
                                                    @else
                                                        <i class="fa fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <div class="ser_title098">
                                                <h4 class="_ser_title" style="height: 40px;overflow:hidden"><a
                                                        href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}">
                                                        {{ Str::limit($serv['name'], 15, '...') }} </a>
                                                </h4>
                                            </div>
                                            <div class="_oi0po price_section"><i class="fa fa-bolt"></i>
                                                {{ __('services.serive_price') }} <strong class="theme-cl">
                                                    {{ number_format($serv['price'], 2) }} $ </strong>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- Add Pagination (optional) -->
                        <div class="swiper-pagination" style="margin-top: 15px"></div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Include Swiper JS and initialization script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const swiper = new Swiper('.services-slider', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true, // Enable continuous looping
                    autoplay: {
                        delay: 3000, // Slide every 3 seconds
                        disableOnInteraction: false, // Continue autoplay after user interaction
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        576: {
                            slidesPerView: 2,
                        },
                        768: {
                            slidesPerView: 3,
                        },
                        992: {
                            slidesPerView: 4,
                        },
                    },
                });
            });
        </script>
        <!-- Inline CSS for basic styling -->
        <!-- Inline CSS for basic styling -->
        <style>
            .services-slider .swiper-slide {
                display: flex;
                justify-content: center;
            }

            .services-slider .ser_110 {
                max-width: 300px;
                /* Adjust as needed */
                width: 100%;
            }

            .swiper-pagination-bullet {
                background: #333;
                opacity: 0.5;
            }

            .swiper-pagination-bullet-active {
                background: #3FB698;
                opacity: 1;
            }
        </style>
    </section>


    <!-- ############################# End Last Services ################# -->

    <!--############################# Start Latest Courses ############### -->

    <section class="latest-courses">
        <div class="header-courses">
            <h2>أحدث الكورسات</h2>
            <a href="{{ url('courses') }}" class="enroll-btn"> جميع الكورسات </a>
        </div>

        <div class="courses-container">
            @foreach ($courses as $course)
                <div class="course-card">
                    <img src="" alt="">
                    @if ($course['User']['image'] == '')
                        <img class="instructor-img" src="{{ asset('assets/uploads/user.png') }}" alt="صورة المحاضر">
                    @else
                        <img class="instructor-img"
                            src="{{ asset('assets/uploads/users_image/' . $course['User']['image']) }}"
                            alt="صورة المحاضر">
                    @endif
                    <h3 class="course-title"> {{ Str::limit($course['title'], 30, '...') }} </h3>
                    <p class="course-summary"> {{ Str::limit($course['desc'], 100, '...') }} </p>
                    <div class="rating">★★★★★</div>
                    <p class="course-price">السعر: {{ number_format($course['price'], 2) }} دولار </p>
                    <a class="enroll-btn" href="{{ url('course/' . $course['id'] . '-' . $course['slug']) }}"> تفاصيل
                        الكورس </a>
                </div>
            @endforeach

        </div>
    </section>

    <!-- ########################### End Latest Courses ###########################-->

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
                                    <img loading="lazy"
                                        style="width: 120px; height:120px;margin:auto;padding-top: 15px;display:block;"
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

    {{-- <section class="call-to-act gift_section"
        style="background:#3fb697 url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="inner-flexible-box subscribe-box">
                        <img loading="lazy" class="animate-pulse" src="{{ asset('assets/website/img/gift.png') }}"
                            alt="">
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

    </section> --}}

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
