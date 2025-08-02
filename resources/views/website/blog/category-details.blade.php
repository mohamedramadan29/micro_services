@extends('website.layouts.master')
@section('title')
    المدونة - {{ $category['name'] }}
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <!-- ============================ Hero Banner  Start================================== -->
        <div class="hero-banner bg-cover center"
            style="background:#00000057 url({{ asset('assets/website/img/blog-background.jpg') }}) no-repeat;"
            data-overlay="7">
            <div class="container">
                <h1> {{ $category['name'] }} </h1>
                <form class="mt-4" dir="rtl" method="get" action="{{ url('blog/categories') }}">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10 col-sm-12">
                            <div class="banner-search style-2">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control lio-rad"
                                        placeholder=" بحث في المدونة  " value="{{ request()->input('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn bt-round btn--2"> بحث <i
                                                class="ti-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <br>
        <!-- ============================ Hero Banner End ================================== -->

        <div class="container">
            {{-- <div class="main_hero_section">
                <div>
                    <h4> العقارات </h4>
                </div>
            </div>
            <br> --}}
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach ($category['blogs'] as $blog)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{ url('blog/' . $blog['slug']) }}" class="ser_100_link"><img
                                                src="{{ $blog->Image() }}" class="img-fluid" alt="{{ $blog['name'] }}"></a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                    </div>
                                    <div class="ser_110_caption">

                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a href="{{ url('blog/' . $blog['slug']) }}">
                                                    {{ $blog['name'] }} </a>
                                            </h4>
                                            <span style="font-size: 14px;color: #545353;"> <i  style="font-size: 12px;margin-left: 5px;" class="bi bi-calendar-check"></i>  {{ $blog['created_at']->format('Y-m-d') }} </span>
                                            <p style="color: #595656;font-size: 14px;padding-bottom: 10px;">
                                                {!! \Illuminate\Support\Str::words($blog['description'], 12) !!}
                                            </p>
                                        </div>


                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>



                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
