@extends('website.layouts.master')
@section('title')
    العقارات
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
        <!-- ============================ Hero Banner  Start================================== -->
        <div class="hero-banner bg-cover center"
            style="background:#00000057 url({{ asset('assets/website/img/real.jpg') }}) no-repeat;" data-overlay="7">
            <div class="container">
                <h1> العقارات </h1>
                <a href="{{ url('my/property/add') }}" class="btn btn-primary free_consult_button">
                    اضف عقارك الان <i class="fa fa-plus"></i> </a>
                <form class="mt-4" dir="rtl" method="get" action="{{ url('properties') }}">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10 col-sm-12">
                            <div class="banner-search style-2">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control lio-rad"
                                        placeholder="بحث عن عقار  " value="{{ request()->input('search') }}">
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
                        @foreach ($properities as $properity)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{ url('property/' . $properity['id'] . '-' . $properity['slug']) }}"
                                            class="ser_100_link"><img
                                                src="{{ asset('assets/uploads/properities/' . ($properity->ProperityFirstImage->image ?? 'default.jpg')) }}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                    </div>
                                    <div class="ser_110_caption">

                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a
                                                    href="{{ url('property/' . $properity['id'] . '-' . $properity['slug']) }}">
                                                    {{ $properity['title'] }} </a>
                                            </h4>
                                            <p style="color: #595656;font-size: 14px;padding-bottom: 10px;">
                                                {!! \Illuminate\Support\Str::words($properity['description'], 12) !!}
                                            </p>
                                        </div>
                                        <div class="_oi0po"><i class="fa fa-bolt"></i> السعر

                                            <strong class="theme-cl"> {{ number_format($properity['price'], 2) }} $
                                            </strong>

                                        </div>

                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{ $properities->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
