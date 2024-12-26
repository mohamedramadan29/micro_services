@extends('website.layouts.master')
@section('title')
    نفذها
@endsection
@section('content')
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner full bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/background3.webp') }}) no-repeat;" data-overlay="7">
        <div class="container">
            <h1> {{ __('index.index_h1') }} </h1>
            <p class="lead"> {{ __('index.index_p1') }} </p>
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


    <!-- ============================ How It Work Start ==================================== -->
    <section class="how-it-works" dir="rtl">
        <div class="container">
            @include('website.free_consult_model')
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> {{ __('index.how_work') }} <span class="theme-cl-2"> {{ __('index.how_work2') }} ؟ </span></h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/step-1.png') }}" class="img-responsive"
                                alt=""><span class="process-num">01</span></span>
                        <h4> {{ __('index.add_project') }}</h4>
                        <p> {{ __('index.add_project_text') }} </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/step-2.png') }}" class="img-responsive"
                                alt=""><span class="process-num">02</span></span>
                        <h4> {{ __('index.select_offer') }} </h4>
                        <p> {{ __('index.select_offer_text') }} </p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="working-process"><span class="process-img"><img
                                src="{{ asset('assets/website/img/step-3.png') }}" class="img-responsive"
                                alt=""><span class="process-num">03</span></span>
                        <h4> {{ __('index.accept_project') }} </h4>
                        <p> {{ __('index.accept_project_p') }} </p>
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
                        <h2> {{ __('index.index_h2') }} <span class="theme-cl-2"> {{ __('index.index_h3') }}</span></h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Single Item -->
                @foreach ($main_categories as $category)
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="ser_110">
                            <div class="ser_110_thumb">
                                <a href="{{ url('category/' . $category['slug']) }}" class="ser_100_link"><img
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
    <section class="gray-light">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="sec-heading">
                        <h2> {{ __('index.index_h4') }} <span class="theme-cl-2"> {{ __('index.index_h5') }} </span></h2>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">

                @foreach ($sub_categories as $sub_category)
                    <!-- Single Category -->
                    <div class="col-lg-3 col-md-4 col-sm-6">
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
    </section>
    <!-- ============================ End Popular Category ==================================== -->
@endsection
