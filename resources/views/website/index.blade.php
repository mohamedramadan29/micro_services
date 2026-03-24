@extends('website.layouts.master')
@section('title')
    نفذها
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        /* Testimonials Section Styles */
        .testimonials-section {
            background: linear-gradient(135deg, #3fb697 0%, #138b6f 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 20px;
            font-size: 100px;
            color: rgba(255, 255, 255, 0.1);
            font-family: Georgia, serif;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .testimonial-content {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .stars {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .testimonial-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .author-image img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .author-name {
            margin: 0;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
        }

        .author-position {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .author-company {
            color: #ffd700;
        }

        /* Swiper Custom Styles */
        .testimonialSwiper {
            padding: 2rem 0;
        }

        .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
        }

        .swiper-pagination-bullet-active {
            opacity: 1;
            background: #ffd700;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }

            .testimonial-card {
                padding: 1.5rem;
            }

            .testimonial-text {
                font-size: 1rem;
            }
        }
    </style>
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

    <!-- ============================ Testimonials Section Start ============================ -->
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center mb-5">
                        <h2 class="section-title">آراء عملائنا</h2>
                        <p class="section-subtitle">ماذا يقول عملاؤنا عنا</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="testimonials-slider">
                        <div class="swiper testimonialSwiper">
                            <div class="swiper-wrapper">
                                @php
                                    $reviews = \App\Models\front\Review::active()->approved()->ordered()->get();
                                @endphp
                                @forelse($reviews as $review)
                                <div class="swiper-slide">
                                    <div class="testimonial-card">
                                        <div class="testimonial-content">
                                            <div class="stars mb-3">
                                                {!! $review->rating_stars !!}
                                            </div>
                                            <p class="testimonial-text">{{ $review->content }}</p>
                                        </div>
                                        <div class="testimonial-author">
                                            <div class="author-image">
                                                @if($review->client_image)
                                                    <img src="{{ asset('assets/uploads/reviews/' . $review->client_image) }}" alt="{{ $review->client_name }}">
                                                @else
                                                    <img src="{{ asset('assets/website/img/user-default.png') }}" alt="Default">
                                                @endif
                                            </div>
                                            <div class="author-info">
                                                <h5 class="author-name">{{ $review->client_name }}</h5>
                                                <p class="author-position">
                                                    {{ $review->client_position }}
                                                    @if($review->client_company)
                                                        <span class="author-company">@ {{ $review->client_company }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="swiper-slide">
                                    <div class="testimonial-card">
                                        <div class="testimonial-content">
                                            <p class="testimonial-text">سيتم إضافة آراء العملاء قريباً</p>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ============================ Testimonials Section End ============================ -->

@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Testimonials Slider
        const testimonialSwiper = new Swiper('.testimonialSwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });

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
