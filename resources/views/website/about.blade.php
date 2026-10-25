@extends('website.layouts.master')
@section('title')
    من نحن
@endsection
@section('content')
    @php
        $about = \App\Support\SiteContent::first('about.page');
        $aboutSections = \App\Support\SiteContent::items('about.sections');
        $aboutBg = \App\Support\SiteContent::media($about['hero_background']);
        $aboutImage = \App\Support\SiteContent::media($about['image']);
    @endphp
    <!-- ============================ Page Title Start================================== -->
    <div class="hero-banner bg-cover side-effect"
        style="background:red url({{ $aboutBg ? $aboutBg->url() : asset('assets/website/img/background3.webp') }})no-repeat;" data-overlay="6">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 text-center">
                    @if ($about['hero_small'])<h2 style='font-weight:bold'> {{ $about['hero_small'] }} </h2>@endif
                    <h1 class="ipt-title mb-3" style="white-space: pre-line">{{ $about['hero_title'] }}</h1>
                    @if ($about['hero_button'])
                        <div class="_ebl_link"><a style="color:#ffffff" href="#about_us" class="btn _loonking_job"> {{ $about['hero_button'] }} </a></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ============================ Page Title End ================================== -->

    <!-- ========================== About Elements ============================= -->
    <section id="about_us">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <img src="{{ $aboutImage ? $aboutImage->url() : asset('assets/website/img/about_us.svg') }}" alt=""
                        class="img-fluid about_us_image">
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="about-captione text-right">
                        @if ($about['heading'])<h4> {{ $about['heading'] }} </h4><br>@endif
                        @foreach ($aboutSections as $section)
                            <h2 style='font-size:20px'> {{ $section['heading'] }} </h2>
                            <p style="white-space: pre-line">{{ $section['text'] }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ========================== About Elements ============================= -->

    <!-- ########################################### Presit Section ######################## -->

    {{-- <section class="call-to-act gift_section"
        style="background:#3fb699 url({{ asset('assets/website/img/landing-bg.png') }}) no-repeat">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="inner-flexible-box subscribe-box">
                        <img class="animate-pulse" src="{{ asset('assets/website/img/gift.png') }}" alt="">
                    </div>
                    <div class="clt-caption text-center mb-4">
                        <h2 class="text-light">هدية مميزة بانتظارك!</h2>

                        <p class="text-light">


                            بمجرد أن تصل قيمة مشترياتك إلى <strong> 1000 جنيه </strong> ، ستُفاجأ برصيد مجاني بقيمة
                            <strong> 50 جنيه </strong> سيضاف تلقائيًا إلى حسابك! استمتع بتسوقك معنا واحصل على المزيد.


                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section> --}}

    <!------------------------------------- End Present Section ##################### -->
@endsection
