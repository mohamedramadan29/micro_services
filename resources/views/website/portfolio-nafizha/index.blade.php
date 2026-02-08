@extends('website.layouts.master')
@section('title')
 معرض أعمال نفذها
@endsection
@section('content')
<!-- ============================ Main Section Start ================================== -->
<section class="gray-bg text-right public_portfolio_page" dir="rtl">
    <!-- ============================ Hero Banner  Start================================== -->
    <div class="hero-banner bg-cover center"
        style="background:#00000057 url({{ asset('assets/website/img/products_hero.jpeg') }}) no-repeat;"
        data-overlay="7">
        <div class="container">
            <h1> معرض أعمال نفذها </h1>
            <form class="mt-4" dir="rtl" method="get" action="{{ url('products') }}">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10 col-sm-12">
                        <div class="banner-search style-2">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control lio-rad"
                                    placeholder=" بحث عن عمل   " value="{{ request()->input('search') }}">
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
    <!-- ============================ Hero Banner End ================================== -->

    <div class="container">
        <div class="row">
            <br>
            <br>
            <!-- Item Wrap Start -->
            <div class="col-lg-12 col-md-12 col-sm-12">
                <br>
                <br>
                <div class="row">
                    @foreach ($works as $work)
                    <!-- Single Item -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="ser_110 shadow_0">
                            <div class="ser_110_thumb">
                                <a href="{{ url('nafizha/portfolio/' . $work['id'] . '-' . $work['slug']) }}" class="ser_100_link"><img
                                        src="{{ asset('assets/uploads/portfolios/' . $work['image']) }}"
                                        class="img-fluid" alt="{{ $work['title'] }}"></a>
                            </div>
                            <div class="ser_110_caption">
                                <div class="ser_title098">
                                    <h4 class="_ser_title"><a href="{{ url('portfolio/' . $work['id'] . '-' . $work['slug']) }}">
                                            {!! \Illuminate\Support\Str::words($work['title'], 10) !!} </a>
                                    </h4>
                                    <p style="color: #595656;font-size: 14px;padding-bottom: 10px;">
                                        {{ $work->Category->name }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        {{ $works->links() }}
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>
<!-- ============================ Main Section End ================================== -->
@endsection
