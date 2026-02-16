@extends('website.layouts.master')
@section('title')
    {{ __('services.services') }}
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right services_page" dir="rtl">
        <!-- ============================ Hero Banner  Start================================== -->
        <div class="hero-banner bg-cover center"
            style="background:#00000057 url({{ asset('assets/website/img/services-hero.jpeg') }}) no-repeat;"
            data-overlay="7">
            <div class="container">
                <h1>
                    <span>
                        خدمات تساعدك على نمو مشاريعك
                    </span>
                    <br>
                    <span>
                        منصة تجمع نخبة من المستقلين المحترفين لتقديم خدمات متخصصة تساعد
                    </span>
                    <br>
                    <span>
                      الأفراد والشركات على تطوير أعمالهم، وتنفيذ مشاريعهم بكفاءة،
                    </span>
                    <br>
                    <span>
                      وتحقيق نمو مستدام عبر حلول عملية وموثوقة
                    </span>
                </h1>
                {{-- <p>
                    منصة تجمع نخبة من المستقلين المحترفين لتقديم خدمات متخصصة تساعد الأفراد والشركات على تطوير أعمالهم، <br>
                    وتنفيذ مشاريعهم بكفاءة، وتحقيق نمو مستدام عبر حلول عملية وموثوقة
                </p> --}}
                <a href="{{ url('service/add') }}" class="btn btn-primary free_consult_button">
                    أضف خدمتك من فضلك<i class="fa fa-plus"></i> </a>
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

        <div class="container">
            <div class="services_page">
                {{-- <div class="service_header">
                    <h3> خدمات تساعدك على نمو مشاريعك </h3>
                    <p> يقدم المستقلون على كاف مجموعة متنوعة من الخدمات الإحترافية التى تساعدك على تطوير أعمالك و نمو
                        مشاريعك
                    </p>
                </div> --}}

                <div class="categories">
                    <div class="row">
                        @foreach ($categories as $category)
                            <div class="col-lg-3 col-6">
                                {{-- <a href="{{ url('services/' . $category['slug']) }}"> {{ $category['name'] }}
                                                </a> --}}
                                <a href="{{ url('category/service/' . $category['slug']) }}">
                                    <div class="info">
                                        <img src="{{ asset('assets/uploads/service_category/' . $category['image'] . '') }}"
                                            alt="">
                                        <h4> {{ $category['name'] }} </h4>
                                    </div>
                                </a>
                            </div>
                        @endforeach


                    </div>
                </div>
            </div>
            @foreach ($categories2 as $category)
                <div class="main_hero_section" style="margin-bottom: 10px;padding:10px">
                    <div>
                        <h4> خدمات {{ $category['name'] }} </h4>
                    </div>
                    <div>
                        <a class="btn btn-global-button" href="{{ url('category/service/' . $category['slug']) }}"> مشاهدة
                            المزيد
                            <i class="fa fa-plus"></i> </a>
                    </div>
                </div>
                <div class="row">
                    <!-- Item Wrap Start -->
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row">
                            @foreach ($category['services'] as $serv)
                                <!-- Single Item -->
                                <div class="col-lg-3 col-md-6 col-sm-12">
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
                                                        <a href="{{ url('user/' . $serv['user']['user_name']) }}">{{ $serv['user']['user_name'] }}
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
                                                <h4 class="_ser_title"><a
                                                        href="{{ url('service/' . $serv['id'] . '-' . $serv['slug']) }}">

                                                        {{ Str::limit($serv['name'], 35, '...') }} </a>
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
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                {{ $services->links() }}
                            </div>
                        </div>

                    </div>

                </div>
            @endforeach


        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
