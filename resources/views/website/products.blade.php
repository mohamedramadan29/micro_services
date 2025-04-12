@extends('website.layouts.master')
@section('title')
    منتجات نفذها
@endsection
@section('content')
    <!-- ============================ Main Section Start ================================== -->
    <section class="gray-bg text-right" dir="rtl">
          <!-- ============================ Hero Banner  Start================================== -->
          <div class="hero-banner bg-cover center"
          style="background:#00000057 url({{ asset('assets/website/img/products.jpg') }}) no-repeat;" data-overlay="7">
          <div class="container">
              <h1> منتجات نفذها  </h1>
              {{-- <a href="{{ url('my/course/add') }}" class="btn btn-primary free_consult_button">  {{ __('courses.add_course') }} <i
                      class="fa fa-plus"></i> </a> --}}
          </div>
      </div>
      <!-- ============================ Hero Banner End ================================== -->

        <div class="container">
            {{-- <div class="main_hero_section">
                <div>
                    <h4> منتجات نفذها </h4>
                </div>
            </div> --}}
            <br>
            <div class="row">
                <!-- Item Wrap Start -->
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="row">
                        @foreach ($products as $product)
                            <!-- Single Item -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="ser_110 shadow_0">
                                    <div class="ser_110_thumb">
                                        <a href="{{ url('product/' . $product['slug']) }}" class="ser_100_link"><img
                                                src="{{ asset('assets/uploads/product_images/' . $product['image']) }}"
                                                class="img-fluid" alt=""></a>
                                    </div>
                                    <div class="ser_110_footer bott">
                                    </div>
                                    <div class="ser_110_caption">

                                        <div class="ser_title098">
                                            <h4 class="_ser_title"><a href="{{ url('product/' . $product['slug']) }}">
                                                    {{ $product['name'] }} </a>
                                            </h4>
                                            <p style="color: #595656;font-size: 14px;padding-bottom: 10px;">
                                            {!! \Illuminate\Support\Str::words($product['description'], 12) !!}
                                            </p>
                                        </div>
                                        <div class="_oi0po"><i class="fa fa-bolt"></i> السعر

                                            @if ($product['discount'] > 0)
                                                <strong class="theme-cl" style="opacity: .5; text-decoration:line-through">
                                                    {{ number_format($product['price'], 2) }} $ </strong>
                                                <strong class="theme-cl"> {{ number_format($product['discount'], 2) }} $
                                                </strong>
                                            @else
                                                <strong class="theme-cl"> {{ number_format($product['price'], 2) }} $
                                                </strong>
                                            @endif
                                        </div>
                                        {{--                                        <div class="add_to_cart"> --}}
                                        {{--                                            <form method="post" action=""> --}}
                                        {{--                                                @csrf --}}
                                        {{--                                                <button class="global_button btn" type="submit"> اضف الي السلة  <i class="bi bi-bag"></i> </button> --}}
                                        {{--                                            </form> --}}
                                        {{--                                        </div> --}}
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            {{ $products->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
    <!-- ============================ Main Section End ================================== -->
@endsection
